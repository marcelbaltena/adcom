<?php
// database/migrations/xxxx_create_customers_table.php (SAFE VERSION)

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            
            // Basic Information
            $table->string('name');
            $table->text('description')->nullable();
            $table->enum('type', ['individual', 'company'])->default('company');
            
            // Company Relationship
            $table->foreignId('company_id')->constrained('companies')->onDelete('cascade');
            
            // Contact Information
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('website')->nullable();
            
            // Address Information
            $table->string('address')->nullable();
            $table->string('city')->nullable();
            $table->string('postal_code', 20)->nullable();
            $table->string('country')->nullable();
            
            // Business Information (for company customers)
            $table->string('kvk_number', 20)->nullable();
            $table->string('vat_number', 50)->nullable();
            $table->string('iban', 50)->nullable();
            
            // Contact Person Information
            $table->string('contact_person')->nullable();
            $table->string('contact_email')->nullable();
            $table->string('contact_phone')->nullable();
            
            // Financial Information
            $table->string('currency', 3)->default('EUR');
            $table->decimal('default_hourly_rate', 8, 2)->nullable();
            $table->enum('payment_terms', ['7', '14', '30', '60', '90'])->default('30');
            
            // Status & Settings
            $table->boolean('is_active')->default(true);
            $table->json('billing_settings')->nullable();
            $table->json('preferences')->nullable();
            
            // Additional Information
            $table->text('notes')->nullable();
            $table->string('industry')->nullable();
            $table->enum('size', ['small', 'medium', 'large', 'enterprise'])->nullable();
            
            // Timestamps
            $table->timestamps();
            
            // Indexes
            $table->index(['company_id', 'is_active']);
            $table->index(['company_id', 'type']);
        });
    }

    public function down()
    {
        // Safe drop - no foreign key dependencies yet
        Schema::dropIfExists('customers');
    }
};
