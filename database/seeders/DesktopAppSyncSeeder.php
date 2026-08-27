<?php

namespace Database\Seeders;

use App\Enums\BenefitType;
use App\Enums\FundingType;
use App\Enums\LedgerEntryType;
use App\Enums\ProgramStatus;
use App\Models\AyudaProgram;
use App\Models\BudgetLedgerEntry;
use App\Models\Donation;
use App\Models\FundingSource;
use App\Models\GgmsConsolidatedTransaction;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use PDO;

class DesktopAppSyncSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::updateOrCreate(
            ['username' => 'admin'],
            [
                'name' => 'System Administrator',
                'email' => 'admin@barangay.local',
                'password' => Hash::make('password'),
                'role' => 'SuperAdmin',
                'is_active' => true,
            ]
        );

        $staffUsers = [
            ['username' => 'hr', 'name' => 'HR Staff', 'email' => 'treasurer@barangay.local', 'role' => 'Admin'],
            ['username' => 'manager1', 'name' => 'Manager 1', 'email' => 'staff1@barangay.local', 'role' => 'Admin'],
            ['username' => 'manager2', 'name' => 'Manager 2', 'email' => 'staff2@barangay.local', 'role' => 'Admin'],
            ['username' => 'crew', 'name' => 'Field Crew', 'email' => 'worker@barangay.local', 'role' => 'Admin'],
        ];

        foreach ($staffUsers as $u) {
            User::updateOrCreate(
                ['username' => $u['username']],
                [
                    'name' => $u['name'],
                    'email' => $u['email'],
                    'password' => Hash::make('password'),
                    'role' => $u['role'],
                    'is_active' => true,
                ]
            );
        }

        // 1. Replicate Government Funding Allocations from GGMS
        $govAllocations = [
            ['title' => 'Ayuda Municipal Allocation', 'code' => 'OFF-2026-0006', 'amount' => 300000.00],
            ['title' => 'Student Scholarship Allocation', 'code' => 'OFF-2026-0001', 'amount' => 300000.00],
            ['title' => 'Senior Citizen Pension Allocation', 'code' => 'OFF-2026-0002', 'amount' => 40000.00],
            ['title' => 'Health Care Medical Fund', 'code' => 'OFF-2026-0003', 'amount' => 80000.00],
            ['title' => 'Insurance Assistance Fund', 'code' => 'OFF-2026-0004', 'amount' => 90000.00],
            ['title' => 'Visitors Management Assistance', 'code' => 'OFF-2026-0005', 'amount' => 800000.00],
        ];

        foreach ($govAllocations as $gov) {
            FundingSource::updateOrCreate(
                ['source_code' => $gov['code']],
                [
                    'funding_type' => FundingType::Government,
                    'title' => $gov['title'],
                    'allocated_amount' => $gov['amount'],
                    'spent_amount' => 0.00,
                    'remaining_balance' => $gov['amount'],
                ]
            );
        }

        // 2. Replicate Private Donations from AMS
        $privateDonations = [
            ['donor_name' => 'OVP', 'type' => 'Organization', 'amount' => 1000000.00, 'date' => '2026-08-19'],
            ['donor_name' => 'Mayor', 'type' => 'Person', 'amount' => 1000000.00, 'date' => '2026-08-19'],
            ['donor_name' => 'Sara', 'type' => 'Person', 'amount' => 500000.00, 'date' => '2026-08-22'],
            ['donor_name' => 'Gian', 'type' => 'Person', 'amount' => 100000.00, 'date' => '2026-08-22'],
            ['donor_name' => 'Tupad', 'type' => 'Organization', 'amount' => 200000.00, 'date' => '2026-08-22'],
            ['donor_name' => 'Facundo', 'type' => 'Person', 'amount' => 50000.00, 'date' => '2026-08-22'],
        ];

        $donationFundingSources = [];

        foreach ($privateDonations as $idx => $d) {
            $code = 'DON-2026-'.sprintf('%04d', $idx + 1);
            $fs = FundingSource::updateOrCreate(
                ['source_code' => $code],
                [
                    'funding_type' => FundingType::Private,
                    'title' => "Donation from {$d['donor_name']}",
                    'allocated_amount' => $d['amount'],
                    'spent_amount' => 0.00,
                    'remaining_balance' => $d['amount'],
                ]
            );

            $donationFundingSources[$idx + 1] = $fs;

            Donation::updateOrCreate(
                ['reference_no' => 'REF-'.$code],
                [
                    'donor_type' => $d['type'],
                    'donor_name' => $d['donor_name'],
                    'amount' => $d['amount'],
                    'donation_date' => $d['date'],
                    'funding_source_id' => $fs->id,
                    'received_by' => $admin->id,
                ]
            );
        }

        // 3. Replicate Ayuda Programs from AMS
        $primaryGov = FundingSource::where('source_code', 'OFF-2026-0006')->first();
        $mayorFund = FundingSource::where('source_code', 'DON-2026-0002')->first();
        $tupadFund = FundingSource::where('source_code', 'DON-2026-0005')->first();
        $facundoFund = FundingSource::where('source_code', 'DON-2026-0006')->first();

        $programs = [
            [
                'program_code' => 'AMS-PD-000001',
                'title' => 'Indigent Emergency Cash Assistance',
                'benefit_type' => BenefitType::Cash,
                'unit_amount' => 5000.00,
                'budget_cap' => 150000.00,
                'target_beneficiaries' => 30,
                'funding_source_id' => $primaryGov?->id,
            ],
            [
                'program_code' => 'CFW-123124',
                'title' => 'Cash For Work 1 (CFW1)',
                'benefit_type' => BenefitType::Cash,
                'unit_amount' => 10000.00,
                'budget_cap' => 500000.00,
                'target_beneficiaries' => 50,
                'funding_source_id' => $mayorFund?->id,
            ],
            [
                'program_code' => 'CFW-1212',
                'title' => 'Community Cleanliness CFW',
                'benefit_type' => BenefitType::Cash,
                'unit_amount' => 5000.00,
                'budget_cap' => 200000.00,
                'target_beneficiaries' => 40,
                'funding_source_id' => $tupadFund?->id,
            ],
            [
                'program_code' => '341231',
                'title' => 'Special Relief Grant',
                'benefit_type' => BenefitType::Cash,
                'unit_amount' => 3000.00,
                'budget_cap' => 50000.00,
                'target_beneficiaries' => 16,
                'funding_source_id' => $facundoFund?->id,
            ],
        ];

        foreach ($programs as $prog) {
            if ($prog['funding_source_id']) {
                AyudaProgram::updateOrCreate(
                    ['program_code' => $prog['program_code']],
                    [
                        'funding_source_id' => $prog['funding_source_id'],
                        'title' => $prog['title'],
                        'benefit_type' => $prog['benefit_type'],
                        'unit_amount' => $prog['unit_amount'],
                        'budget_cap' => $prog['budget_cap'],
                        'target_beneficiaries' => $prog['target_beneficiaries'],
                        'status' => ProgramStatus::Active,
                        'starts_at' => '2026-08-01',
                        'ends_at' => '2026-12-31',
                    ]
                );
            }
        }

        // 4. Replicate 6 Budget Ledger Entries from AMS
        $amsLedgers = [
            ['id' => 1, 'source' => 'donation:1', 'amount' => 1000000.00, 'date' => '2026-08-19', 'remarks' => 'Private donation from OVP.'],
            ['id' => 2, 'source' => 'donation:2', 'amount' => 1000000.00, 'date' => '2026-08-19', 'remarks' => 'Private donation from MAyor.'],
            ['id' => 3, 'source' => 'donation:3', 'amount' => 500000.00, 'date' => '2026-08-22', 'remarks' => 'Private donation from sara.'],
            ['id' => 4, 'source' => 'donation:4', 'amount' => 100000.00, 'date' => '2026-08-22', 'remarks' => 'Private donation from gian.'],
            ['id' => 5, 'source' => 'donation:5', 'amount' => 200000.00, 'date' => '2026-08-22', 'remarks' => 'Private donation from tupad.'],
            ['id' => 6, 'source' => 'donation:6', 'amount' => 50000.00, 'date' => '2026-08-22', 'remarks' => 'Private donation from facundo.'],
        ];

        foreach ($amsLedgers as $l) {
            $fs = $donationFundingSources[$l['id']] ?? $primaryGov;

            BudgetLedgerEntry::updateOrCreate(
                ['reference_code' => 'LEDGER-'.$l['source']],
                [
                    'funding_source_id' => $fs->id,
                    'entry_type' => LedgerEntryType::Donation,
                    'amount' => $l['amount'],
                    'previous_balance' => 0.00,
                    'new_balance' => $l['amount'],
                    'notes' => $l['remarks'],
                    'created_by' => $admin->id,
                    'created_at' => $l['date'],
                ]
            );
        }

        // 5. Replicate 89 GGMS Consolidated Transactions from u518908950_ggms
        try {
            $dsn = 'mysql:host=193.203.175.157;port=3306;dbname=u518908950_ggms;charset=utf8mb4';
            $ggmsPdo = new PDO($dsn, 'u518908950_ggms', 'Sulop@2025', [
                PDO::ATTR_TIMEOUT => 5,
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            ]);

            $txs = $ggmsPdo->query('SELECT * FROM consolidated_transactions')->fetchAll(PDO::FETCH_ASSOC);

            foreach ($txs as $tx) {
                GgmsConsolidatedTransaction::updateOrCreate(
                    ['project_code' => $tx['project_code'] ?: ('GGMS-TX-'.$tx['id']), 'first_name' => $tx['first_name'] ?: 'Beneficiary', 'last_name' => $tx['last_name'] ?: 'Sulop'],
                    [
                        'project_details_id' => $tx['project_details_id'] ?? null,
                        'project_name' => $tx['project_name'] ?: ($tx['transaction_type'] ?: 'Project Distribution'),
                        'civil_registry_id' => $tx['civil_registry_id'] ?: ($tx['beneficiary_id'] ?: null),
                        'first_name' => $tx['first_name'] ?: 'Beneficiary',
                        'middle_name' => $tx['middle_name'] ?: null,
                        'last_name' => $tx['last_name'] ?: 'Citizen',
                        'barangay' => $tx['barangay'] ?: 'Poblacion',
                        'household_no' => $tx['household_no'] ?: 'HH-SULOP',
                        'amount' => (float) ($tx['amount'] ?? 0.00),
                        'benefit_type' => 'Cash',
                        'disbursement_date' => $tx['transaction_date'] ?: ($tx['created_at'] ?: now()),
                        'sync_status' => 'Synced',
                        'recorded_by' => $admin->id,
                    ]
                );
            }
        } catch (\Throwable) {
            // If offline, preserve existing transactions
        }
    }
}
