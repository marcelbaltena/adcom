<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // Update milestones status
        Schema::table('milestones', function (Blueprint $table) {
            $table->enum('status_new', ['concept', 'in_progress', 'completed'])->default('concept')->after('description');
        });
        
        DB::statement("UPDATE milestones SET status_new = CASE 
            WHEN status = 'pending' THEN 'concept'
            WHEN status = 'in_progress' THEN 'in_progress' 
            WHEN status = 'completed' THEN 'completed'
            ELSE 'concept'
        END");
        
        Schema::table('milestones', function (Blueprint $table) {
            $table->dropColumn('status');
            $table->renameColumn('status_new', 'status');
        });
        
        // Update tasks status
        Schema::table('tasks', function (Blueprint $table) {
            $table->enum('status_new', ['concept', 'in_progress', 'completed'])->default('concept')->after('description');
        });
        
        DB::statement("UPDATE tasks SET status_new = CASE 
            WHEN status = 'todo' THEN 'concept'
            WHEN status = 'in_progress' THEN 'in_progress' 
            WHEN status = 'completed' THEN 'completed'
            ELSE 'concept'
        END");
        
        Schema::table('tasks', function (Blueprint $table) {
            $table->dropColumn('status');
            $table->renameColumn('status_new', 'status');
        });
        
        // Update subtasks status
        Schema::table('subtasks', function (Blueprint $table) {
            $table->enum('status_new', ['concept', 'in_progress', 'completed'])->default('concept')->after('description');
        });
        
        DB::statement("UPDATE subtasks SET status_new = CASE 
            WHEN status = 'todo' THEN 'concept'
            WHEN status = 'in_progress' THEN 'in_progress' 
            WHEN status = 'completed' THEN 'completed'
            ELSE 'concept'
        END");
        
        Schema::table('subtasks', function (Blueprint $table) {
            $table->dropColumn('status');
            $table->renameColumn('status_new', 'status');
        });
    }

    public function down()
    {
        // Reverse the status changes
        // Implementation similar to up() but in reverse
    }
};