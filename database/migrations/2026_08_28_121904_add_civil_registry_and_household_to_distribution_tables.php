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
        Schema::table('distribution_enrollments', function (Blueprint $table) {
            if (! Schema::hasColumn('distribution_enrollments', 'civil_registry_id')) {
                $table->string('civil_registry_id')->nullable()->after('beneficiary_id')->index();
            }
            if (! Schema::hasColumn('distribution_enrollments', 'household_no')) {
                $table->string('household_no')->nullable()->after('civil_registry_id')->index();
            }
        });

        Schema::table('ayuda_project_claims', function (Blueprint $table) {
            if (! Schema::hasColumn('ayuda_project_claims', 'civil_registry_id')) {
                $table->string('civil_registry_id')->nullable()->after('beneficiary_id')->index();
            }
            if (! Schema::hasColumn('ayuda_project_claims', 'household_no')) {
                $table->string('household_no')->nullable()->after('civil_registry_id')->index();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('distribution_enrollments', function (Blueprint $table) {
            if (Schema::hasColumn('distribution_enrollments', 'civil_registry_id')) {
                $table->dropColumn('civil_registry_id');
            }
            if (Schema::hasColumn('distribution_enrollments', 'household_no')) {
                $table->dropColumn('household_no');
            }
        });

        Schema::table('ayuda_project_claims', function (Blueprint $table) {
            if (Schema::hasColumn('ayuda_project_claims', 'civil_registry_id')) {
                $table->dropColumn('civil_registry_id');
            }
            if (Schema::hasColumn('ayuda_project_claims', 'household_no')) {
                $table->dropColumn('household_no');
            }
        });
    }
};
