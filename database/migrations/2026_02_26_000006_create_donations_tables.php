<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('donations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('funding_source_id')->constrained('funding_sources')->cascadeOnUpdate()->restrictOnDelete();
            $table->string('reference_no')->unique();
            $table->string('donor_type'); // Person, Organization
            $table->string('donor_name');
            $table->string('contact_no')->nullable();
            $table->string('email')->nullable();
            $table->decimal('amount', 14, 2);
            $table->date('donation_date');
            $table->string('proof_attachment')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('received_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('goods_donations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('funding_source_id')->constrained('funding_sources')->cascadeOnUpdate()->restrictOnDelete();
            $table->string('reference_no')->unique();
            $table->string('donor_type'); // Person, Organization
            $table->string('donor_name');
            $table->string('contact_no')->nullable();
            $table->string('item_name');
            $table->integer('quantity');
            $table->string('unit'); // Sacks, Boxes, Cans, Pieces, Kits
            $table->decimal('estimated_value', 14, 2)->default(0.00);
            $table->date('donation_date');
            $table->string('proof_attachment')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('received_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('goods_donations');
        Schema::dropIfExists('donations');
    }
};
