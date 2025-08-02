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
        // Add position to milestones if it doesn't exist
        if (!Schema::hasColumn('milestones', 'position')) {
            Schema::table('milestones', function (Blueprint $table) {
                $table->integer('position')->default(0)->after('project_id');
                $table->index(['project_id', 'position']);
            });
        }

        // Add position to tasks if it doesn't exist
        if (!Schema::hasColumn('tasks', 'position')) {
            Schema::table('tasks', function (Blueprint $table) {
                $table->integer('position')->default(0)->after('milestone_id');
                $table->index(['milestone_id', 'position']);
            });
        }

        // Add position to subtasks if it doesn't exist
        if (!Schema::hasColumn('subtasks', 'position')) {
            Schema::table('subtasks', function (Blueprint $table) {
                $table->integer('position')->default(0)->after('task_id');
                $table->index(['task_id', 'position']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('milestones', function (Blueprint $table) {
            $table->dropColumn('position');
        });

        Schema::table('tasks', function (Blueprint $table) {
            $table->dropColumn('position');
        });

        Schema::table('subtasks', function (Blueprint $table) {
            $table->dropColumn('position');
        });
    }
};