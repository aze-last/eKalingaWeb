<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\Schema;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // If CRS connection is sqlite in-memory for testing, create val_beneficiaries schema
        if (config('database.connections.crs.driver') === 'sqlite') {
            Schema::connection('crs')->create('val_beneficiaries', function ($table) {
                $table->id();
                $table->string('civil_registry_id')->nullable();
                $table->string('household_no')->nullable();
                $table->string('first_name')->nullable();
                $table->string('middle_name')->nullable();
                $table->string('last_name')->nullable();
                $table->string('ext_name')->nullable();
                $table->string('barangay')->nullable();
                $table->string('gender')->nullable();
                $table->date('birth_date')->nullable();
                $table->string('contact_no')->nullable();
                $table->text('address')->nullable();
                $table->tinyInteger('IsDeleted')->default(0);
                $table->timestamps();
            });

            Schema::connection('crs')->create('barangays', function ($table) {
                $table->id();
                $table->string('name');
                $table->timestamps();
            });
        }
    }
}
