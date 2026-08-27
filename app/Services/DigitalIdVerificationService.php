<?php

namespace App\Services;

use App\Models\Beneficiary;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

class DigitalIdVerificationService
{
    /**
     * Cache TTL in seconds (24 hours for offline resilience).
     */
    public const CACHE_TTL_SECONDS = 86400;

    /**
     * Normalize the raw scan input payload.
     */
    public function normalizePayload(string $rawScan): string
    {
        // Strip non-printable ASCII characters, carriage returns, trailing newlines
        $clean = preg_replace('/[^\x20-\x7E]/', '', trim($rawScan));

        return trim($clean);
    }

    /**
     * Determine payload format type.
     */
    public function detectPayloadType(string $payload): string
    {
        if (preg_match('/^CRS-\d{4}-\d+-/i', $payload)) {
            return 'CRS_CARD_NUMBER';
        }

        if (preg_match('/^BEN-\d{4}-/i', $payload)) {
            return 'CRS_BENEFICIARY_ID';
        }

        if (preg_match('/^EKALIN-/i', $payload)) {
            return 'EKALINGA_QR';
        }

        if (preg_match('/^(ASM-?BID|BID)-/i', $payload)) {
            return 'OWN_DIGITAL_ID';
        }

        if (preg_match('/^CRN-/i', $payload)) {
            return 'CIVIL_REGISTRY_ID';
        }

        if (str_contains($payload, ',')) {
            return 'NAME_LOOKUP';
        }

        if (is_numeric($payload)) {
            return 'RAW_NUMERIC_ID';
        }

        return 'GENERIC_IDENTIFIER';
    }

    /**
     * Authoritative Verification of Digital ID / e-Kard.
     *
     * @return array{
     *     success: bool,
     *     status: string, // VERIFIED, OFFLINE_CACHED, REVOKED, EXPIRED, NOT_FOUND, CRS_UNAVAILABLE, INVALID_FORMAT
     *     source: string, // CRS_LIVE, OFFLINE_CACHE, LOCAL_MIRROR
     *     payload_type: string,
     *     digital_id_info: ?array,
     *     beneficiary: ?Beneficiary,
     *     message: string
     * }
     */
    public function verify(string $rawPayload, ?int $programId = null): array
    {
        $payload = $this->normalizePayload($rawPayload);

        if (empty($payload)) {
            return [
                'success' => false,
                'status' => 'INVALID_FORMAT',
                'source' => 'VALIDATOR',
                'payload_type' => 'EMPTY',
                'digital_id_info' => null,
                'beneficiary' => null,
                'message' => 'Empty scan payload received.',
            ];
        }

        $payloadType = $this->detectPayloadType($payload);
        $cacheKey = 'crs_did_v1_'.md5(strtolower($payload));

        // ----------------------------------------------------
        // 1. ATTEMPT AUTHORITATIVE REMOTE CRS VERIFICATION
        // ----------------------------------------------------
        try {
            $remoteResult = $this->queryAuthoritativeCrs($payload, $payloadType);

            if ($remoteResult['status'] === 'NOT_FOUND') {
                // If authoritative CRS returned explicit NOT FOUND, do not use stale cache
                return [
                    'success' => false,
                    'status' => 'NOT_FOUND',
                    'source' => 'CRS_LIVE',
                    'payload_type' => $payloadType,
                    'digital_id_info' => null,
                    'beneficiary' => null,
                    'message' => "Digital ID / Card [{$payload}] not found in Civil Registry.",
                ];
            }

            if ($remoteResult['status'] === 'REVOKED') {
                // Update cache with revoked status so offline knows it's revoked
                Cache::put($cacheKey, [
                    'status' => 'REVOKED',
                    'digital_id_info' => $remoteResult['digital_id_info'],
                    'beneficiary_id' => $remoteResult['beneficiary']?->id,
                    'cached_at' => now()->toIso8601String(),
                ], self::CACHE_TTL_SECONDS);

                $reason = $remoteResult['digital_id_info']['revocation_reason'] ?? 'Not specified';

                return [
                    'success' => false,
                    'status' => 'REVOKED',
                    'source' => 'CRS_LIVE',
                    'payload_type' => $payloadType,
                    'digital_id_info' => $remoteResult['digital_id_info'],
                    'beneficiary' => $remoteResult['beneficiary'],
                    'message' => "Digital ID [{$payload}] is REVOKED (Reason: {$reason}).",
                ];
            }

            if ($remoteResult['status'] === 'EXPIRED') {
                return [
                    'success' => false,
                    'status' => 'EXPIRED',
                    'source' => 'CRS_LIVE',
                    'payload_type' => $payloadType,
                    'digital_id_info' => $remoteResult['digital_id_info'],
                    'beneficiary' => $remoteResult['beneficiary'],
                    'message' => "Digital ID [{$payload}] expired on {$remoteResult['digital_id_info']['expiry_date']}.",
                ];
            }

            if ($remoteResult['status'] === 'VERIFIED' && $remoteResult['beneficiary']) {
                // Cache authoritative active record for offline resilience
                Cache::put($cacheKey, [
                    'status' => 'VERIFIED',
                    'digital_id_info' => $remoteResult['digital_id_info'],
                    'beneficiary_id' => $remoteResult['beneficiary']->id,
                    'full_name' => $remoteResult['beneficiary']->full_name,
                    'barangay' => $remoteResult['beneficiary']->barangay,
                    'household_no' => $remoteResult['beneficiary']->household_no,
                    'cached_at' => now()->toIso8601String(),
                ], self::CACHE_TTL_SECONDS);

                return [
                    'success' => true,
                    'status' => 'VERIFIED',
                    'source' => 'CRS_LIVE',
                    'payload_type' => $payloadType,
                    'digital_id_info' => $remoteResult['digital_id_info'],
                    'beneficiary' => $remoteResult['beneficiary'],
                    'message' => "Verified Active e-Kard: {$remoteResult['beneficiary']->full_name}",
                ];
            }
        } catch (Throwable $e) {
            Log::warning('CRS Remote verification unreachable, falling back to local cache/mirror: '.$e->getMessage());
        }

        // ----------------------------------------------------
        // 2. OFFLINE CACHE RESILIENCE FALLBACK
        // ----------------------------------------------------
        $cached = Cache::get($cacheKey);
        if ($cached && is_array($cached)) {
            if ($cached['status'] === 'REVOKED') {
                return [
                    'success' => false,
                    'status' => 'REVOKED',
                    'source' => 'OFFLINE_CACHE',
                    'payload_type' => $payloadType,
                    'digital_id_info' => $cached['digital_id_info'] ?? null,
                    'beneficiary' => null,
                    'message' => "Digital ID [{$payload}] is REVOKED (Cached).",
                ];
            }

            if (! empty($cached['beneficiary_id'])) {
                $cachedBen = Beneficiary::find($cached['beneficiary_id']);
                if ($cachedBen) {
                    return [
                        'success' => true,
                        'status' => 'OFFLINE_CACHED',
                        'source' => 'OFFLINE_CACHE',
                        'payload_type' => $payloadType,
                        'digital_id_info' => $cached['digital_id_info'] ?? null,
                        'beneficiary' => $cachedBen,
                        'message' => "Verified via Offline Cache: {$cachedBen->full_name}",
                    ];
                }
            }
        }

        // Local mirror fallback (when beneficiary exists in local SQLite registry)
        try {
            $localBen = Beneficiary::where('civil_registry_id', $payload)
                ->orWhere('household_no', $payload)
                ->orWhere('id', is_numeric($payload) ? (int) $payload : -1)
                ->first();

            if ($localBen) {
                return [
                    'success' => true,
                    'status' => 'OFFLINE_CACHED',
                    'source' => 'LOCAL_MIRROR',
                    'payload_type' => $payloadType,
                    'digital_id_info' => [
                        'id_number' => $payload,
                        'status' => 'Active',
                    ],
                    'beneficiary' => $localBen,
                    'message' => "Verified via Local Registry Mirror: {$localBen->full_name}",
                ];
            }
        } catch (Throwable) {
        }

        // ----------------------------------------------------
        // 3. CRS UNAVAILABLE & NO VALID CACHE
        // ----------------------------------------------------
        return [
            'success' => false,
            'status' => 'NOT_FOUND',
            'source' => 'NONE',
            'payload_type' => $payloadType,
            'digital_id_info' => null,
            'beneficiary' => null,
            'message' => "Digital ID / Card [{$payload}] not found in Civil Registry.",
        ];
    }

    /**
     * Query authoritative CRS database for Digital ID and Beneficiary record.
     */
    protected function queryAuthoritativeCrs(string $payload, string $payloadType): array
    {
        $digitalIdRecord = null;
        $beneficiary = null;

        // 1. Direct match on CRS `digital_ids` table (e.g. CRS-2026-18-00001)
        if ($payloadType === 'CRS_CARD_NUMBER' || str_starts_with(strtoupper($payload), 'CRS-')) {
            $digitalIdRecord = DB::connection('crs')->table('digital_ids')
                ->where('id_number', $payload)
                ->where('IsDeleted', 0)
                ->first();

            if ($digitalIdRecord) {
                $status = ucfirst(strtolower($digitalIdRecord->status ?? 'Active'));

                if ($status === 'Revoked') {
                    return [
                        'status' => 'REVOKED',
                        'digital_id_info' => (array) $digitalIdRecord,
                        'beneficiary' => null,
                    ];
                }

                if (! empty($digitalIdRecord->expiry_date) && strtotime($digitalIdRecord->expiry_date) < time()) {
                    return [
                        'status' => 'EXPIRED',
                        'digital_id_info' => (array) $digitalIdRecord,
                        'beneficiary' => null,
                    ];
                }

                // Resolve beneficiary linked to this digital ID
                if (! empty($digitalIdRecord->beneficiary_id)) {
                    $beneficiary = Beneficiary::where('beneficiary_id', $digitalIdRecord->beneficiary_id)->first();
                }
            }
        }

        // 2. Beneficiary ID lookup (e.g. BEN-2026-703984046-1)
        if (! $beneficiary && ($payloadType === 'CRS_BENEFICIARY_ID' || str_starts_with(strtoupper($payload), 'BEN-'))) {
            $beneficiary = Beneficiary::where('beneficiary_id', $payload)->first();
        }

        // 3. Own eKalinga QR Code Format (e.g. EKALIN-693631224-AMS-PD-000001)
        if (! $beneficiary && $payloadType === 'EKALINGA_QR') {
            $parts = explode('-', $payload);
            if (count($parts) >= 2) {
                $civId = $parts[1];
                $beneficiary = Beneficiary::where('civilregistry_id', $civId)
                    ->orWhere('beneficiary_id', $civId)
                    ->orWhere('id', (int) $civId)
                    ->first();
            }
        }

        // 4. Legacy Own Digital ID format (e.g. ASMBID-12345, BID-12345)
        if (! $beneficiary && $payloadType === 'OWN_DIGITAL_ID') {
            $cleanId = preg_replace('/^(ASM-?BID|BID)-/i', '', $payload);
            $beneficiary = Beneficiary::where('civilregistry_id', $cleanId)
                ->orWhere('beneficiary_id', $cleanId)
                ->orWhere('id', (int) $cleanId)
                ->first();
        }

        // 5. Civil Registry ID or CRN format (e.g. CRN-693631224, 693631224)
        if (! $beneficiary) {
            $cleanCiv = preg_replace('/^CRN-/i', '', $payload);
            try {
                $beneficiary = Beneficiary::where(function ($q) use ($cleanCiv, $payload) {
                    $q->where('civilregistry_id', $cleanCiv)
                        ->orWhere('civilregistry_id', $payload)
                        ->orWhere('beneficiary_id', $cleanCiv)
                        ->orWhere('beneficiary_id', $payload)
                        ->orWhere('household_no', $payload);
                })->first();
            } catch (Throwable) {
                try {
                    $beneficiary = Beneficiary::where(function ($q) use ($cleanCiv, $payload) {
                        $q->where('civil_registry_id', $cleanCiv)
                            ->orWhere('civil_registry_id', $payload)
                            ->orWhere('household_no', $payload);
                    })->first();
                } catch (Throwable) {
                }
            }
        }

        // 6. Name lookup fallback (e.g. "Ababa, Victoria")
        if (! $beneficiary && $payloadType === 'NAME_LOOKUP') {
            [$last, $first] = array_map('trim', explode(',', $payload, 2));
            $beneficiary = Beneficiary::where('last_name', 'like', $last.'%')
                ->where('first_name', 'like', $first.'%')
                ->first();
        }

        // 7. Raw numeric ID
        if (! $beneficiary && is_numeric($payload)) {
            $beneficiary = Beneficiary::find((int) $payload);
        }

        if (! $beneficiary) {
            return [
                'status' => 'NOT_FOUND',
                'digital_id_info' => null,
                'beneficiary' => null,
            ];
        }

        return [
            'status' => 'VERIFIED',
            'digital_id_info' => $digitalIdRecord ? (array) $digitalIdRecord : [
                'id_number' => $payload,
                'status' => 'Active',
                'issued_date' => now()->toDateString(),
            ],
            'beneficiary' => $beneficiary,
        ];
    }
}
