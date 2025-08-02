<?php

// 9. MIGRATIE: Update Tasks en Subtasks voor multiple assignees
// database/migrations/2024_XX_XX_add_team_features_to_tasks_and_subtasks.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // Voeg team feature flags toe aan tasks
        Schema::table('tasks', function (Blueprint $table) {
            if (!Schema::hasColumn('tasks', 'has_team_features')) {
                $table->boolean('has_team_features')->default(true)->after('notify_watchers');
            }
        });
        
        // Voeg team feature flags toe aan subtasks
        Schema::table('subtasks', function (Blueprint $table) {
            if (!Schema::hasColumn('subtasks', 'has_team_features')) {
                $table->boolean('has_team_features')->default(true)->after('notify_watchers');
                $table->boolean('is_completed')->default(false)->after('has_team_features');
            }
        });
        
        // Migreer bestaande assigned_to data naar assignees tabel
        $this->migrateExistingAssignments();
    }
    
    private function migrateExistingAssignments()
    {
        // Migreer task assignments
        $tasks = DB::table('tasks')->whereNotNull('assigned_to')->get();
        foreach ($tasks as $task) {
            DB::table('assignees')->insertOrIgnore([
                'assignable_type' => 'App\\Models\\Task',
                'assignable_id' => $task->id,
                'user_id' => $task->assigned_to,
                'role' => 'assignee',
                'created_at' => $task->created_at,
                'updated_at' => $task->updated_at
            ]);
        }
        
        // Migreer subtask assignments
        $subtasks = DB::table('subtasks')->whereNotNull('assigned_to')->get();
        foreach ($subtasks as $subtask) {
            DB::table('assignees')->insertOrIgnore([
                'assignable_type' => 'App\\Models\\Subtask',
                'assignable_id' => $subtask->id,
                'user_id' => $subtask->assigned_to,
                'role' => 'assignee',
                'created_at' => $subtask->created_at,
                'updated_at' => $subtask->updated_at
            ]);
        }
    }

    public function down()
    {
        Schema::table('tasks', function (Blueprint $table) {
            if (Schema::hasColumn('tasks', 'has_team_features')) {
                $table->dropColumn('has_team_features');
            }
        });
        
        Schema::table('subtasks', function (Blueprint $table) {
            if (Schema::hasColumn('subtasks', 'has_team_features')) {
                $table->dropColumn(['has_team_features', 'is_completed']);
            }
        });
    }
};