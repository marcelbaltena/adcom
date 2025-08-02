<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            // Fix string column lengths
            $table->string('name', 500)->change();
            $table->text('description')->nullable()->change();
            $table->string('status', 50)->change();
            $table->string('currency', 10)->change();
            $table->string('source', 100)->change();
            $table->string('budget_status', 50)->change();
            
            // Ensure decimal columns have proper precision
            $table->decimal('budget', 15, 2)->change();
            $table->decimal('project_value', 15, 2)->nullable()->change();
            $table->decimal('spent', 15, 2)->default(0)->change();
            $table->decimal('allocated_budget', 15, 2)->default(0)->change();
            $table->decimal('remaining_budget', 15, 2)->default(0)->change();
            
            // Fix percentage columns
            $table->decimal('budget_tolerance_percentage', 5, 2)->default(10.00)->change();
            $table->decimal('budget_warning_percentage', 5, 2)->default(5.00)->change();
            
            // Ensure JSON columns are properly set
            $table->json('customer_permissions')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            // Revert changes if needed
            $table->string('name', 255)->change();
            $table->string('status', 20)->change();
            $table->string('currency', 3)->change();
            $table->string('source', 50)->change();
            $table->string('budget_status', 20)->change();
        });
    }
};
