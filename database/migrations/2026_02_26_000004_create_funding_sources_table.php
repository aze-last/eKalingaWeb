<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('funding_sources', function (Blueprint $table) {
            $table->id();
            $table->string('funding_type'); // Government, Private
            $table->string('title');
            $table->string('source_code')->unique(); // e.g. GGMS-2026-001, DON-2026-001
            $table->string('project_details_id')->nullable()->index(); // external GGMS code e.g. OPP-2026-0006
            $table->string('office')->nullable(); // e.g. MSWDO, Mayor's Office, MDRRMO
            $table->year('fiscal_year')->default(2026);
            $table->decimal('allocated_amount', 14, 2)->default(0.00);
            $table->decimal('spent_amount', 14, 2)->default(0.00);
            $table->decimal('remaining_balance', 14, 2)->default(0.00);
            $table->string('status')->default('Active'); // Active, Depleted, Closed
            $table->text('description')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('ggms_project_caches', function (Blueprint $table) {
            $table->id();
            $table->string('project_details_id')->unique();
            $table->string('title');
            $table->string('office');
            $table->year('fiscal_year');
            $table->decimal('total_allocation', 14, 2);
            $table->decimal('available_balance', 14, 2);
            $table->json('sub_allocations')->nullable();
            $table->timestamp('last_synced_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ggms_project_caches');
        Schema::dropIfExists('funding_sources');
    }
};
