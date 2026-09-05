<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Beneficiary extends Model
{
    /**
     * The database connection that should be used by the model.
     * CRS is a separate, external read-only database.
     */
    protected $connection = 'crs';

    /**
     * The table associated with the model in CRS.
     * Snapshot of all validated accounts from Sulop.
     */
    protected $table = 'val_beneficiaries';

    /**
     * Indicates if the model should be timestamped.
     */
    public $timestamps = false;

    /**
     * The attributes that aren't mass assignable.
     */
    protected $guarded = ['id'];

    /**
     * Primary key for the CRS table.
     */
    protected $primaryKey = 'id';

    /**
     * Formatted full name accessor supporting both separate and combined column schemas.
     */
    public function fullName(): Attribute
    {
        return Attribute::make(
            get: function () {
                if (! empty($this->attributes['full_name'])) {
                    return $this->attributes['full_name'];
                }

                $first = $this->attributes['first_name'] ?? '';
                $mid = ! empty($this->attributes['middle_name']) ? " {$this->attributes['middle_name']}" : '';
                $last = $this->attributes['last_name'] ?? '';
                $ext = ! empty($this->attributes['ext_name']) ? " {$this->attributes['ext_name']}" : '';

                $combined = trim("{$last}, {$first}{$mid}{$ext}");

                return ! empty($combined) && $combined !== ',' ? $combined : ($this->attributes['name'] ?? 'N/A');
            }
        );
    }

    /**
     * Normalized Civil Registry ID / Identifier attribute.
     */
    public function civilRegistryId(): Attribute
    {
        return Attribute::make(
            get: function () {
                return $this->attributes['civilregistry_id']
                    ?? $this->attributes['civil_registry_id']
                    ?? $this->attributes['beneficiary_id']
                    ?? $this->attributes['crn']
                    ?? $this->attributes['national_id']
                    ?? (string) ($this->attributes['id'] ?? '');
            }
        );
    }

    /**
     * Normalized Household Number attribute.
     */
    public function householdNo(): Attribute
    {
        return Attribute::make(
            get: function () {
                if (! empty($this->attributes['household_no'])) {
                    return (string) $this->attributes['household_no'];
                }
                if (! empty($this->attributes['household_id'])) {
                    return (string) $this->attributes['household_id'];
                }
                if (! empty($this->attributes['household_number'])) {
                    return (string) $this->attributes['household_number'];
                }
                if (! empty($this->attributes['family_id'])) {
                    return (string) $this->attributes['family_id'];
                }

                $benId = $this->attributes['beneficiary_id'] ?? '';
                if ($benId && preg_match('/BEN-\d+-(\d+)-\d+/', $benId, $matches)) {
                    return $matches[1];
                }

                return 'N/A';
            }
        );
    }

    /**
     * Dynamic Barangay attribute resolved from address or column.
     */
    public function barangay(): Attribute
    {
        return Attribute::make(
            get: function () {
                if (! empty($this->attributes['barangay'])) {
                    return $this->attributes['barangay'];
                }

                $addr = $this->attributes['address'] ?? '';
                if ($addr) {
                    $parts = array_map('trim', explode(',', $addr));
                    if (count($parts) >= 2) {
                        return $parts[1];
                    }
                }

                return 'Poblacion';
            }
        );
    }

    /**
     * Normalized Gender / Sex attribute.
     */
    public function gender(): Attribute
    {
        return Attribute::make(
            get: function () {
                return $this->attributes['sex'] ?? $this->attributes['gender'] ?? 'N/A';
            }
        );
    }

    /**
     * Normalized Birth Date attribute.
     */
    public function birthDate(): Attribute
    {
        return Attribute::make(
            get: function () {
                return $this->attributes['date_of_birth'] ?? $this->attributes['birth_date'] ?? null;
            }
        );
    }

    /**
     * Normalized Age attribute.
     */
    public function age(): Attribute
    {
        return Attribute::make(
            get: function () {
                if (isset($this->attributes['age']) && is_numeric($this->attributes['age']) && (int) $this->attributes['age'] > 0) {
                    return (int) $this->attributes['age'];
                }

                $rawBirth = $this->attributes['date_of_birth'] ?? $this->attributes['birth_date'] ?? null;
                if ($rawBirth) {
                    try {
                        $dob = Carbon::parse($rawBirth);
                        if ($dob->isPast() && $dob->year > 1900) {
                            return $dob->age;
                        }
                    } catch (\Throwable) {
                    }
                }

                return null;
            }
        );
    }

    /**
     * Normalized Senior Citizen attribute.
     */
    public function isSenior(): Attribute
    {
        return Attribute::make(
            get: function () {
                $persisted = ! empty($this->attributes['is_senior']);
                if ($persisted) {
                    return true;
                }

                $age = $this->age;

                return $age !== null && $age >= 60;
            }
        );
    }

    /**
     * Normalized PWD attribute.
     */
    public function isPwd(): Attribute
    {
        return Attribute::make(
            get: function () {
                return ! empty($this->attributes['is_pwd']);
            }
        );
    }

    /**
     * Local claims tied to this beneficiary's civil registry ID.
     */
    public function localClaims(): HasMany
    {
        return $this->hasMany(AyudaProjectClaim::class, 'civil_registry_id', 'civilregistry_id');
    }

    /**
     * Local GGMS consolidated transactions tied to this beneficiary's civil registry ID.
     */
    public function localGgmsTransactions(): HasMany
    {
        return $this->hasMany(GgmsConsolidatedTransaction::class, 'civil_registry_id', 'civilregistry_id');
    }
}
