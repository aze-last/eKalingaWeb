<?php

namespace App\Enums;

enum ReportType: string
{
    case BudgetUtilization = 'BudgetUtilization';
    case DistributionClaims = 'DistributionClaims';
    case AdminActivityAudit = 'AdminActivityAudit';

    public function label(): string
    {
        return match ($this) {
            self::BudgetUtilization => 'Budget Utilization & Cap Analysis',
            self::DistributionClaims => 'Ayuda Distribution Claims Log',
            self::AdminActivityAudit => 'Administrator Activity & Audit Trail',
        };
    }

    public function description(): string
    {
        return match ($this) {
            self::BudgetUtilization => 'Analyzes budget caps vs actual released amounts, remaining balances, and envelope allocations.',
            self::DistributionClaims => 'Detailed claim records capturing beneficiary identity, unit amounts, items, timestamps, and releasing admins.',
            self::AdminActivityAudit => 'Immutable audit trail tracking create, update, and release operations executed by administrators.',
        };
    }
}
