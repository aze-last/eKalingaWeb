<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('distribution_enrollments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ayuda_program_id')->constrained('ayuda_programs')->cascadeOnDelete();
            $table->unsignedBigInteger('beneficiary_id')->nullable()->index();
            $table->string('civil_registry_id')->nullable()->index();
            $table->string('household_no')->nullable()->index();
            $table->string('status')->default('PENDING'); // PENDING, RELEASED, UNRELEASED
            $table->text('exclusion_reason')->nullable();
            $table->timestamp('enrolled_at')->useCurrent();
            $table->timestamp('processed_at')->nullable();
            $table->foreignId('processed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['ayuda_program_id', 'beneficiary_id']);
            $table->index(['ayuda_program_id', 'civil_registry_id']);
            $table->index(['ayuda_program_id', 'status']);
        });

        Schema::create('ayuda_project_claims', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ayuda_program_id')->constrained('ayuda_programs')->cascadeOnDelete();
            $table->unsignedBigInteger('beneficiary_id')->nullable()->index();
            $table->string('civil_registry_id')->nullable()->index();
            $table->string('household_no')->nullable()->index();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete(); // Releasing admin
            $table->string('claim_code')->unique(); // e.g. CLM-2026-00001
            $table->decimal('unit_amount', 14, 2)->default(0.00);
            $table->string('item_details')->nullable();
            $table->text('scanned_qr_payload')->nullable();
            $table->string('verification_method')->default('QR_SCAN'); // QR_SCAN, MANUAL_SEARCH
            $table->timestamp('claimed_at')->useCurrent();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['ayuda_program_id', 'claimed_at']);
            $table->index(['beneficiary_id', 'claimed_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ayuda_project_claims');
        Schema::dropIfExists('distribution_enrollments');
    }
};
