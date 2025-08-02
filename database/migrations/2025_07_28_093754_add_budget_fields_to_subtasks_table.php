<?php
// database/migrations/xxxx_add_budget_fields_to_subtasks_table.php (FIXED)

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('subtasks', function (Blueprint $table) {
            // Additional timeline fields (keeping existing start_date, end_date)
            $table->date('actual_start_date')->nullable()->after('end_date');
            $table->date('actual_end_date')->nullable()->after('actual_start_date');
            
            // Budget tracking
            $table->decimal('spent_amount', 10, 2)->default(0)->after('actual_hours');
            $table->decimal('remaining_budget', 10, 2)->default(0)->after('spent_amount');
            
            // Budget status
            $table->enum('budget_status', ['under', 'on_track', 'warning', 'over'])->default('on_track')->after('remaining_budget');
            $table->text('budget_notes')->nullable()->after('budget_status');
            
            // Progress tracking
            $table->decimal('completion_percentage', 5, 2)->default(0)->after('budget_notes');
            $table->enum('timeline_status', ['not_started', 'on_time', 'behind', 'ahead'])->default('not_started')->after('completion_percentage');
            
            // Time tracking (keeping existing actual_hours)
            $table->decimal('billable_hours', 8, 2)->default(0)->after('timeline_status');
            
            // Additional rate field (complement to existing price)
            $table->decimal('hourly_rate', 8, 2)->nullable()->after('billable_hours');
            
            // Assignment tracking - SAFE CHECK
            $table->timestamp('assigned_at')->nullable()->after('hourly_rate');
        });

        // Add assigned_to foreign key in separate statement for safety
        if (!Schema::hasColumn('subtasks', 'assigned_to')) {
            Schema::table('subtasks', function (Blueprint $table) {
                $table->foreignId('assigned_to')->nullable()->after('assigned_at')->constrained('users');
            });
        }
    }

    public function down()
    {
        Schema::table('subtasks', function (Blueprint $table) {
            // Drop columns that we definitely added
            $table->dropColumn([
                'actual_start_date', 'actual_end_date', 'spent_amount', 
                'remaining_budget', 'budget_status', 'budget_notes', 
                'completion_percentage', 'timeline_status', 'billable_hours', 
                'hourly_rate', 'assigned_at'
            ]);
        });

        // Only drop assigned_to foreign key if it exists and was created by this migration
        if (Schema::hasColumn('subtasks', 'assigned_to')) {
            // Check if foreign key exists before dropping
            $foreignKeys = Schema::getConnection()
                ->getDoctrineSchemaManager()
                ->listTableForeignKeys('subtasks');
            
            $hasAssignedToForeignKey = collect($foreignKeys)->contains(function ($foreignKey) {
                return in_array('assigned_to', $foreignKey->getColumns());
            });

            if ($hasAssignedToForeignKey) {
                Schema::table('subtasks', function (Blueprint $table) {
                    $table->dropForeign(['assigned_to']);
                    $table->dropColumn('assigned_to');
                });
            } else {
                Schema::table('subtasks', function (Blueprint $table) {
                    $table->dropColumn('assigned_to');
                });
            }
        }
    }
};