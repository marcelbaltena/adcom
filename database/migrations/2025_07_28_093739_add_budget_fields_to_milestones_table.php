<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('milestones', function (Blueprint $table) {
            // Timeline fields - REPLACE existing start_date/end_date if needed
            // Note: Check if these already exist, if so, just add the actual dates
            $table->date('actual_start_date')->nullable()->after('due_date');
            $table->date('actual_end_date')->nullable()->after('actual_start_date');
            
            // Budget tracking (keeping existing budget, price, estimated_hours, spent)
            $table->decimal('allocated_budget', 10, 2)->default(0)->after('spent');
            $table->decimal('remaining_budget', 10, 2)->default(0)->after('allocated_budget');
            
            // Budget status
            $table->enum('budget_status', ['under', 'on_track', 'warning', 'over'])->default('on_track')->after('remaining_budget');
            $table->text('budget_notes')->nullable()->after('budget_status');
            
            // Progress tracking
            $table->decimal('completion_percentage', 5, 2)->default(0)->after('budget_notes');
            $table->enum('timeline_status', ['not_started', 'on_time', 'behind', 'ahead'])->default('not_started')->after('completion_percentage');
            
            // Additional hourly rate field (complement to existing price field)
            $table->decimal('hourly_rate', 8, 2)->nullable()->after('timeline_status');
            
            // Actual spent tracking
            $table->decimal('actual_hours', 8, 2)->default(0)->after('hourly_rate');
            $table->decimal('billable_hours', 8, 2)->default(0)->after('actual_hours');
        });
    }

    public function down()
    {
        Schema::table('milestones', function (Blueprint $table) {
            $table->dropColumn([
                'actual_start_date', 'actual_end_date', 'allocated_budget', 
                'remaining_budget', 'budget_status', 'budget_notes', 
                'completion_percentage', 'timeline_status', 'hourly_rate',
                'actual_hours', 'billable_hours'
            ]);
        });
    }
};
