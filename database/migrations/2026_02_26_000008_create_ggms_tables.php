<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ggms_consolidated_transactions', function (Blueprint $table) {
            $table->id();
            $table->string('project_code')->index(); // e.g. AMS-PD-000001
            $table->string('project_details_id')->nullable()->index(); // e.g. OPP-2026-0006
            $table->string('project_name')->default('Project Distribution'); // Project Distribution, Cash For Work, Seminar, Aid Request
            $table->unsignedBigInteger('beneficiary_id')->nullable()->index();
            $table->string('civil_registry_id')->nullable()->index();
            $table->string('first_name');
            $table->string('middle_name')->nullable();
            $table->string('last_name');
            $table->string('barangay')->index();
            $table->string('household_no')->index();
            $table->decimal('amount', 14, 2)->default(0.00);
            $table->string('benefit_type')->default('Cash'); // Cash, Goods
            $table->string('item_summary')->nullable(); // For goods
            $table->dateTime('disbursement_date');
            $table->string('sync_status')->default('Synced'); // Synced, Pending, Failed
            $table->json('payload')->nullable();
            $table->foreignId('recorded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['project_name', 'disbursement_date']);
            $table->index(['barangay', 'disbursement_date']);
        });

        Schema::create('ggms_pending_transactions', function (Blueprint $table) {
            $table->id();
            $table->string('transaction_uuid')->unique();
            $table->string('project_code');
            $table->string('project_name');
            $table->json('payload');
            $table->integer('retry_count')->default(0);
            $table->text('last_error')->nullable();
            $table->string('status')->default('Pending'); // Pending, Failed, Completed
            $table->timestamp('last_attempted_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ggms_pending_transactions');
        Schema::dropIfExists('ggms_consolidated_transactions');
    }
};
