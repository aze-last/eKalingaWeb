<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ayuda_programs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('funding_source_id')->constrained('funding_sources')->cascadeOnUpdate()->restrictOnDelete();
            $table->string('program_code')->unique(); // e.g. AMS-PD-000001
            $table->string('title');
            $table->string('benefit_type'); // Cash, Goods
            $table->decimal('budget_cap', 14, 2);
            $table->decimal('unit_amount', 14, 2)->default(0.00); // For cash
            $table->string('item_name')->nullable(); // For goods
            $table->string('item_unit')->nullable(); // For goods (e.g. Sacks, Boxes, Kits)
            $table->integer('item_quantity_per_beneficiary')->default(1);
            $table->integer('target_beneficiaries')->default(0);
            $table->decimal('total_disbursed_amount', 14, 2)->default(0.00);
            $table->integer('total_claimed_count')->default(0);
            $table->string('status')->default('Active'); // Draft, Active, Completed, Cancelled
            $table->string('target_barangay')->nullable(); // Nullable for municipality-wide
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->text('description')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['status', 'benefit_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ayuda_programs');
    }
};
