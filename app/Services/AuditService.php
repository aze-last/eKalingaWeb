<?php

namespace App\Services;

use App\Models\ActivityLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

class AuditService
{
    /**
     * Log an administrative activity.
     */
    public static function log(
        string $action,
        string $module,
        string $description,
        ?string $subjectType = null,
        ?int $subjectId = null,
        ?array $details = null
    ): ActivityLog {
        return ActivityLog::create([
            'user_id' => Auth::id(),
            'action' => $action,
            'module' => $module,
            'subject_type' => $subjectType,
            'subject_id' => $subjectId,
            'description' => $description,
            'details' => $details,
            'ip_address' => Request::ip(),
        ]);
    }
}
