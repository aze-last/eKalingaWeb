<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('budget_ledger_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('funding_source_id')->constrained('funding_sources')->cascadeOnUpdate()->restrictOnDelete();
            $table->foreignId('ayuda_program_id')->nullable()->constrained('ayuda_programs')->nullOnDelete();
            $table->string('entry_type'); // Donation, GoodsDonation, Release, Reallocation
            $table->string('reference_code')->index();
            $table->decimal('amount', 14, 2)->default(0.00);
            $table->string('item_name')->nullable();
            $table->integer('item_quantity')->nullable();
            $table->string('item_unit')->nullable();
            $table->decimal('previous_balance', 14, 2);
            $table->decimal('new_balance', 14, 2);
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['funding_source_id', 'created_at']);
            $table->index(['entry_type', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('budget_ledger_entries');
    }
};
