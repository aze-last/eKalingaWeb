<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\Schema;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // If SQLite in-memory testing, ensure val_beneficiaries schema exists on default and crs connections
        foreach (['crs', config('database.default')] as $conn) {
            if (config("database.connections.{$conn}.driver") === 'sqlite') {
                if (! Schema::connection($conn)->hasTable('val_beneficiaries')) {
                    Schema::connection($conn)->create('val_beneficiaries', function ($table) {
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
                        $table->date('date_of_birth')->nullable();
                        $table->integer('age')->nullable();
                        $table->tinyInteger('is_senior')->default(0);
                        $table->tinyInteger('is_pwd')->default(0);
                        $table->string('contact_no')->nullable();
                        $table->text('address')->nullable();
                        $table->tinyInteger('IsDeleted')->default(0);
                        $table->timestamps();
                    });
                }

                if (! Schema::connection($conn)->hasTable('barangays')) {
                    Schema::connection($conn)->create('barangays', function ($table) {
                        $table->id();
                        $table->string('name');
                        $table->timestamps();
                    });
                }
            }
        }
    }
}
