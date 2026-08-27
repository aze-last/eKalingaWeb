<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('beneficiaries', function (Blueprint $table) {
            $table->id();
            $table->string('civil_registry_id')->unique()->nullable();
            $table->string('household_no')->index();
            $table->string('first_name');
            $table->string('middle_name')->nullable();
            $table->string('last_name');
            $table->string('ext_name')->nullable(); // Jr., Sr., III
            $table->string('barangay')->index();
            $table->string('gender')->nullable();
            $table->date('birth_date')->nullable();
            $table->string('contact_no')->nullable();
            $table->text('address')->nullable();
            $table->text('notes')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['barangay', 'household_no']);
            $table->index(['last_name', 'first_name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('beneficiaries');
    }
};
