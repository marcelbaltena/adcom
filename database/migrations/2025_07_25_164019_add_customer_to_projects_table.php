<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('projects', function (Blueprint $table) {
            // Add customer relationship - NULLABLE for now
            $table->foreignId('customer_id')->nullable()->after('user_id');
            
            // Add project source tracking
            $table->enum('source', ['direct', 'referral', 'marketing', 'existing_customer'])->default('direct');
            
            // Add customer-specific settings
            $table->boolean('customer_can_view')->default(true);
            $table->json('customer_permissions')->nullable();
        });

        // Add foreign key constraint in separate statement
        Schema::table('projects', function (Blueprint $table) {
            $table->foreign('customer_id')->references('id')->on('customers')->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::table('projects', function (Blueprint $table) {
            // Drop foreign key first
            $table->dropForeign(['customer_id']);
            
            // Then drop columns
            $table->dropColumn([
                'customer_id', 
                'source', 
                'customer_can_view', 
                'customer_permissions'
            ]);
        });
    }
};