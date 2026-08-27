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
        Schema::create('government_budget_snapshots', function (Blueprint $table) {
            $table->id();
            $table->string('office_code')->default('OFF-2026-0006')->index();
            $table->year('fiscal_year')->default(2026);
            $table->decimal('allocated_amount', 14, 2)->default(0.00);
            $table->decimal('computed_spent_amount', 14, 2)->default(0.00);
            $table->decimal('remaining_balance', 14, 2)->default(0.00);
            $table->string('source_table')->default('officeallocations');
            $table->json('raw_payload')->nullable();
            $table->timestamp('synced_at')->useCurrent();
            $table->timestamps();

            $table->index(['office_code', 'synced_at']);
        });

        Schema::table('ayuda_programs', function (Blueprint $table) {
            if (! Schema::hasColumn('ayuda_programs', 'source_project_details_id')) {
                $table->string('source_project_details_id')->nullable()->index()->after('funding_source_id');
            }
            if (! Schema::hasColumn('ayuda_programs', 'source_donation_id')) {
                $table->unsignedBigInteger('source_donation_id')->nullable()->index()->after('source_project_details_id');
            }
            if (! Schema::hasColumn('ayuda_programs', 'source_ggms_budget_id')) {
                $table->unsignedBigInteger('source_ggms_budget_id')->nullable()->index()->after('source_donation_id');
            }
        });

        Schema::table('ggms_project_caches', function (Blueprint $table) {
            if (! Schema::hasColumn('ggms_project_caches', 'description')) {
                $table->text('description')->nullable()->after('title');
            }
            if (! Schema::hasColumn('ggms_project_caches', 'voucher_code')) {
                $table->string('voucher_code')->nullable()->after('description');
            }
            if (! Schema::hasColumn('ggms_project_caches', 'status')) {
                $table->string('status')->default('active')->after('voucher_code');
            }
            if (! Schema::hasColumn('ggms_project_caches', 'allocated_budget')) {
                $table->decimal('allocated_budget', 14, 2)->default(0.00)->after('status');
            }
            if (! Schema::hasColumn('ggms_project_caches', 'spent_budget')) {
                $table->decimal('spent_budget', 14, 2)->default(0.00)->after('allocated_budget');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('government_budget_snapshots');

        Schema::table('ayuda_programs', function (Blueprint $table) {
            $table->dropColumn(['source_project_details_id', 'source_donation_id', 'source_ggms_budget_id']);
        });
    }
};
