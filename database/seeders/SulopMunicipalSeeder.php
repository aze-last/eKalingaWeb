<?php

namespace Database\Seeders;

use App\Enums\BenefitType;
use App\Enums\DistributionStatus;
use App\Enums\FundingType;
use App\Enums\LedgerEntryType;
use App\Enums\ProgramStatus;
use App\Enums\UserRole;
use App\Models\ActivityLog;
use App\Models\AyudaProgram;
use App\Models\AyudaProjectClaim;
use App\Models\Beneficiary;
use App\Models\BudgetLedgerEntry;
use App\Models\DistributionEnrollment;
use App\Models\Donation;
use App\Models\FundingSource;
use App\Models\GgmsConsolidatedTransaction;
use App\Models\GgmsProjectCache;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class SulopMunicipalSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Seed Municipal Settings
        $settings = [
            'municipality_name' => 'Municipality of Sulop',
            'province_name' => 'Province of Davao del Sur',
            'region_name' => 'Region XI (Davao Region)',
            'app_title' => 'eKalinga+ Ayuda Management System',
            'municipal_seal_url' => '/images/Site_logo.png',
            'municipal_mayor_name' => 'Hon. Jose Jimmy S. Sagarino',
            'municipal_accountant_name' => 'Grace T. Manalo, CPA',
            'mswdo_head_name' => 'Maria Elena R. Santos, RSW',
            'currency_symbol' => '₱',
            'scanner_cooldown_ms' => '1500',
            'scanner_sound_enabled' => '1',
        ];

        foreach ($settings as $key => $val) {
            Setting::set($key, $val);
        }

        // 2. Seed Users
        $superAdmin = User::updateOrCreate(
            ['email' => 'superadmin@sulop.gov.ph'],
            [
                'name' => 'Municipal Super Admin',
                'username' => 'superadmin',
                'password' => Hash::make('password'),
                'role' => UserRole::SuperAdmin,
                'is_active' => true,
            ]
        );

        $admin = User::updateOrCreate(
            ['email' => 'admin@sulop.gov.ph'],
            [
                'name' => 'MSWDO Aid Administrator',
                'username' => 'admin',
                'password' => Hash::make('password'),
                'role' => UserRole::Admin,
                'is_active' => true,
            ]
        );

        // 3. Seed Beneficiaries across Sulop Barangays
        $barangays = [
            'Poblacion', 'Balasinon', 'Buguis', 'Carre', 'Clib',
            'Harada Butai', 'Katipunan', 'Kiblagon', 'Labon', 'Laperas',
            'Lapla', 'Litos', 'Luparan', 'Mckinley', 'New Cebu',
            'Osmeña', 'Palili', 'Parame', 'Roxas', 'Solongvale',
            'Tagolilong', 'Tala-o', 'Talas', 'Tanwalang', 'Waterfall',
        ];

        $sampleNames = [
            ['Juan', 'Dela', 'Cruz', 'M', '1985-04-12'],
            ['Maria', 'Santos', 'Reyes', 'F', '1990-08-23'],
            ['Pedro', 'Alcantara', 'Penduko', 'M', '1978-11-05'],
            ['Ana', 'Gomez', 'Lim', 'F', '1995-02-17'],
            ['Carlos', 'Bautista', 'Tan', 'M', '1982-06-30'],
            ['Elena', 'Villanueva', 'Torres', 'F', '1988-09-14'],
            ['Roberto', 'Mercado', 'Aquino', 'M', '1975-01-25'],
            ['Lourdes', 'Flores', 'Castro', 'F', '1992-12-08'],
            ['Gabriel', 'Mendoza', 'Soriano', 'M', '1980-03-19'],
            ['Teresa', 'Navarro', 'Salazar', 'F', '1987-07-22'],
            ['Antonio', 'Ramos', 'Pascual', 'M', '1968-10-10'],
            ['Corazon', 'Castillo', 'Rivera', 'F', '1972-05-04'],
            ['Danilo', 'Morales', 'Perez', 'M', '1983-09-28'],
            ['Florencia', 'Diaz', 'Gutierrez', 'F', '1991-03-15'],
            ['Eduardo', 'Tolentino', 'Cruz', 'M', '1979-12-01'],
            ['Rosario', 'De Jesus', 'Valdez', 'F', '1986-06-18'],
            ['Fernando', 'Aguilar', 'Santiago', 'M', '1974-08-09'],
            ['Marites', 'Estrada', 'Hernandez', 'F', '1989-11-20'],
            ['Arnel', 'Ignacio', 'Padilla', 'M', '1993-01-11'],
            ['Jocelyn', 'Ocampo', 'Vargas', 'F', '1984-04-05'],
        ];

        $beneficiaries = [];
        $householdIndex = 1001;

        foreach ($sampleNames as $i => $item) {
            $barangay = $barangays[$i % count($barangays)];
            // Group every 2 persons into same household to test duplicate checks
            $hhNo = 'HH-SULOP-'.($householdIndex + intdiv($i, 2));
            $crId = 'CRN-'.(20260000 + $i + 1);

            $beneficiaries[] = Beneficiary::create([
                'civil_registry_id' => $crId,
                'household_no' => $hhNo,
                'first_name' => $item[0],
                'middle_name' => $item[1],
                'last_name' => $item[2],
                'gender' => $item[3],
                'birth_date' => $item[4],
                'barangay' => $barangay,
                'contact_no' => '09'.rand(10, 99).rand(1000000, 9999999),
                'address' => 'Purok '.(($i % 5) + 1).", Brgy. {$barangay}, Sulop, Davao del Sur",
                'is_active' => true,
            ]);
        }

        // 4. Seed Funding Sources
        $ggmsGovFund = FundingSource::create([
            'funding_type' => FundingType::Government,
            'title' => '2026 Municipal Ayuda Disaster Relief Grant (GGMS)',
            'source_code' => 'GGMS-2026-001',
            'project_details_id' => 'OPP-2026-0006',
            'office' => 'MSWDO / LGU Sulop',
            'fiscal_year' => 2026,
            'allocated_amount' => 1500000.00,
            'spent_amount' => 375000.00,
            'remaining_balance' => 875000.00, // 1,500,000 - 250,000 project A - 375,000 project B
            'status' => 'Active',
            'description' => 'Sub-allocated municipal emergency financial and food subsidy grant mirrored from Provincial GGMS.',
        ]);

        GgmsProjectCache::create([
            'project_details_id' => 'OPP-2026-0006',
            'title' => '2026 Municipal Ayuda Disaster Relief Grant (GGMS)',
            'office' => 'MSWDO / LGU Sulop',
            'fiscal_year' => 2026,
            'total_allocation' => 1500000.00,
            'available_balance' => 875000.00,
            'sub_allocations' => [
                ['name' => 'Emergency Cash Payouts', 'amount' => 1000000.00],
                ['name' => 'Rice & Food Relief Distribution', 'amount' => 500000.00],
            ],
            'last_synced_at' => now(),
        ]);

        $privateCashPool = FundingSource::create([
            'funding_type' => FundingType::Private,
            'title' => 'Private Cash Donations Pool 2026',
            'source_code' => 'DON-CASH-2026',
            'office' => 'MSWDO / Treasury',
            'fiscal_year' => 2026,
            'allocated_amount' => 250000.00,
            'spent_amount' => 50000.00,
            'remaining_balance' => 100000.00,
            'status' => 'Active',
            'description' => 'Aggregated private cash donations from philanthropic foundations and business leaders.',
        ]);

        $privateGoodsPool = FundingSource::create([
            'funding_type' => FundingType::Private,
            'title' => 'Private In-Kind & Goods Donations Pool 2026',
            'source_code' => 'DON-GOODS-2026',
            'office' => 'MSWDO / Warehouse',
            'fiscal_year' => 2026,
            'allocated_amount' => 180000.00,
            'spent_amount' => 60000.00,
            'remaining_balance' => 120000.00,
            'status' => 'Active',
            'description' => 'In-kind rice sacks and hygiene relief kits donated by civic groups.',
        ]);

        // Seed Private Donation record
        Donation::create([
            'funding_source_id' => $privateCashPool->id,
            'reference_no' => 'DON-20260201-0089',
            'donor_type' => 'Organization',
            'donor_name' => 'Sulop United Chamber of Commerce',
            'contact_no' => '09171234567',
            'email' => 'contact@sulopchamber.ph',
            'amount' => 250000.00,
            'donation_date' => now()->subDays(15),
            'notes' => 'Cash assistance donation for vulnerable indigent families.',
            'received_by' => $superAdmin->id,
        ]);

        // 5. Seed Initial Budget Ledger Entries
        BudgetLedgerEntry::create([
            'funding_source_id' => $ggmsGovFund->id,
            'entry_type' => LedgerEntryType::Donation,
            'reference_code' => 'GGMS-SYNC-202601',
            'amount' => 1500000.00,
            'previous_balance' => 0.00,
            'new_balance' => 1500000.00,
            'notes' => 'Initial ingestion of GGMS Government Grant OPP-2026-0006',
            'created_by' => $superAdmin->id,
        ]);

        BudgetLedgerEntry::create([
            'funding_source_id' => $privateCashPool->id,
            'entry_type' => LedgerEntryType::Donation,
            'reference_code' => 'DON-20260201-0089',
            'amount' => 250000.00,
            'previous_balance' => 0.00,
            'new_balance' => 250000.00,
            'notes' => 'Cash Donation received from Sulop United Chamber of Commerce',
            'created_by' => $superAdmin->id,
        ]);

        // 6. Seed Operational Ayuda Programs
        $cashProgram = AyudaProgram::create([
            'funding_source_id' => $ggmsGovFund->id,
            'program_code' => 'AMS-PD-000001',
            'title' => 'Emergency Indigent Cash Subsidy Payout',
            'benefit_type' => BenefitType::Cash,
            'budget_cap' => 500000.00,
            'unit_amount' => 5000.00,
            'target_beneficiaries' => 100,
            'total_disbursed_amount' => 25000.00,
            'total_claimed_count' => 5,
            'status' => ProgramStatus::Active,
            'target_barangay' => 'Poblacion',
            'start_date' => now()->subDays(5),
            'end_date' => now()->addDays(25),
            'description' => '₱5,000 direct emergency financial subsidy per qualified indigent family.',
            'created_by' => $admin->id,
        ]);

        $goodsProgram = AyudaProgram::create([
            'funding_source_id' => $ggmsGovFund->id,
            'program_code' => 'AMS-PD-000002',
            'title' => 'High-Grade Rice & Food Relief Pack Distribution',
            'benefit_type' => BenefitType::Goods,
            'budget_cap' => 350000.00,
            'unit_amount' => 1500.00,
            'item_name' => 'Premium Well-Milled Rice (25kg Sack)',
            'item_unit' => 'Sacks',
            'item_quantity_per_beneficiary' => 1,
            'target_beneficiaries' => 200,
            'total_disbursed_amount' => 4500.00,
            'total_claimed_count' => 3,
            'status' => ProgramStatus::Active,
            'target_barangay' => null, // Municipality-wide
            'start_date' => now()->subDays(2),
            'end_date' => now()->addDays(20),
            'description' => '25kg Rice Sack relief per family head.',
            'created_by' => $admin->id,
        ]);

        // 7. Seed Enrollments & Claims for Cash Program
        foreach ($beneficiaries as $index => $b) {
            $status = DistributionStatus::PENDING;

            if ($index < 5) {
                $status = DistributionStatus::RELEASED;
            } elseif ($index === 18 || $index === 19) {
                $status = DistributionStatus::UNRELEASED;
            }

            $enrollment = DistributionEnrollment::create([
                'ayuda_program_id' => $cashProgram->id,
                'beneficiary_id' => $b->id,
                'status' => $status,
                'exclusion_reason' => $status === DistributionStatus::UNRELEASED ? 'Incomplete supporting document / Valid ID pending verification' : null,
                'enrolled_at' => now()->subDays(4),
                'processed_at' => $status !== DistributionStatus::PENDING ? now()->subHours(rand(1, 48)) : null,
                'processed_by' => $status !== DistributionStatus::PENDING ? $admin->id : null,
            ]);

            // If released, create Claim and GGMS Transaction
            if ($status === DistributionStatus::RELEASED) {
                $claimCode = sprintf('CLM-2026-%06d', $index + 1);

                AyudaProjectClaim::create([
                    'ayuda_program_id' => $cashProgram->id,
                    'beneficiary_id' => $b->id,
                    'user_id' => $admin->id,
                    'claim_code' => $claimCode,
                    'unit_amount' => 5000.00,
                    'item_details' => '₱5,000.00 Cash Aid',
                    'scanned_qr_payload' => "EKALIN-{$b->civil_registry_id}-{$cashProgram->program_code}",
                    'verification_method' => 'QR_SCAN',
                    'claimed_at' => now()->subHours(rand(2, 36)),
                ]);

                GgmsConsolidatedTransaction::create([
                    'project_code' => $cashProgram->program_code,
                    'project_details_id' => 'OPP-2026-0006',
                    'project_name' => 'Project Distribution',
                    'beneficiary_id' => $b->id,
                    'civil_registry_id' => $b->civil_registry_id,
                    'first_name' => $b->first_name,
                    'middle_name' => $b->middle_name,
                    'last_name' => $b->last_name,
                    'barangay' => $b->barangay,
                    'household_no' => $b->household_no,
                    'amount' => 5000.00,
                    'benefit_type' => 'Cash',
                    'item_summary' => '₱5,000.00 Cash Aid',
                    'disbursement_date' => now()->subHours(rand(2, 36)),
                    'sync_status' => 'Synced',
                    'recorded_by' => $admin->id,
                ]);

                BudgetLedgerEntry::create([
                    'funding_source_id' => $ggmsGovFund->id,
                    'ayuda_program_id' => $cashProgram->id,
                    'entry_type' => LedgerEntryType::Release,
                    'reference_code' => $claimCode,
                    'amount' => 5000.00,
                    'previous_balance' => 875000.00,
                    'new_balance' => 875000.00,
                    'notes' => "Ayuda release for {$cashProgram->program_code} to {$b->full_name}",
                    'created_by' => $admin->id,
                ]);
            }
        }

        // 8. Log Initial Activity
        ActivityLog::create([
            'user_id' => $superAdmin->id,
            'action' => 'Create',
            'module' => 'Auth',
            'description' => 'System bootstrapped with Municipality of Sulop default master records.',
            'ip_address' => '127.0.0.1',
        ]);
    }
}
