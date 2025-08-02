<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('projects', function (Blueprint $table) {
            // Budget tolerance settings - ONLY MISSING FIELDS
            $table->decimal('budget_tolerance_percentage', 5, 2)->default(10.00)->after('spent');
            $table->decimal('budget_warning_percentage', 5, 2)->default(5.00)->after('budget_tolerance_percentage');
            
            // Budget tracking calculations
            $table->decimal('allocated_budget', 10, 2)->default(0)->after('budget_warning_percentage');
            $table->decimal('remaining_budget', 10, 2)->default(0)->after('allocated_budget');
            
            // Budget status tracking
            $table->enum('budget_status', ['under', 'on_track', 'warning', 'over'])->default('on_track')->after('remaining_budget');
            
            // Last calculation timestamp
            $table->timestamp('budget_last_calculated')->nullable()->after('budget_status');
        });
    }

    public function down()
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->dropColumn([
                'budget_tolerance_percentage',
                'budget_warning_percentage', 
                'allocated_budget',
                'remaining_budget',
                'budget_status',
                'budget_last_calculated'
            ]);
        });
    }
};