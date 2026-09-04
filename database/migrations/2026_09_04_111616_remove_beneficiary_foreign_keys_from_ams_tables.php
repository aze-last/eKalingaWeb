<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $tables = ['distribution_enrollments', 'ayuda_project_claims', 'ggms_consolidated_transactions'];

        foreach ($tables as $table) {
            if (! Schema::hasTable($table)) {
                continue;
            }

            try {
                Schema::table($table, function (Blueprint $blueprint) {
                    $blueprint->dropForeign(['beneficiary_id']);
                });
            } catch (Throwable) {
                // Ignore if foreign key was already dropped or does not exist
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Beneficiary records reside in the external CRS database; foreign keys to local beneficiaries table should not be restored.
    }
};
