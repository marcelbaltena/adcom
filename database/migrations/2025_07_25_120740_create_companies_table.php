<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('companies', function (Blueprint $table) {
            $table->id();
            
            // Basic company info
            $table->string('name');
            $table->string('legal_name')->nullable();
            $table->text('description')->nullable();
            
            // Contact information
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('website')->nullable();
            
            // Address information
            $table->string('address_line_1')->nullable();
            $table->string('address_line_2')->nullable();
            $table->string('city')->nullable();
            $table->string('state')->nullable();
            $table->string('postal_code')->nullable();
            $table->string('country')->default('Netherlands');
            
            // Business information
            $table->string('kvk_number')->nullable(); // Dutch Chamber of Commerce
            $table->string('vat_number')->nullable(); // BTW nummer
            $table->string('iban')->nullable();
            
            // Settings and preferences
            $table->string('currency', 3)->default('EUR');
            $table->string('timezone')->default('Europe/Amsterdam');
            $table->string('date_format')->default('d-m-Y');
            $table->decimal('default_hourly_rate', 8, 2)->nullable();
            
            // Status and metadata
            $table->boolean('is_active')->default(true);
            $table->json('settings')->nullable(); // For additional settings
            $table->json('billing_settings')->nullable(); // Billing preferences
            
            $table->timestamps();
            
            // Indexes
            $table->index('name');
            $table->index('is_active');
            $table->unique(['kvk_number'], 'unique_kvk_when_not_null');
        });
    }

    public function down()
    {
        Schema::dropIfExists('companies');
    }
};
