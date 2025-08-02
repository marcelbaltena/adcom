<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // Add team-related fields to milestones
        Schema::table('milestones', function (Blueprint $table) {
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null')->after('project_id');
            $table->foreignId('owned_by')->nullable()->constrained('users')->onDelete('set null')->after('created_by');
            $table->timestamp('last_activity_at')->nullable()->after('updated_at');
            $table->boolean('allow_comments')->default(true)->after('manual_progress');
            $table->boolean('notify_watchers')->default(true)->after('allow_comments');
        });

        // Add team-related fields to tasks
        Schema::table('tasks', function (Blueprint $table) {
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null')->after('milestone_id');
            $table->foreignId('owned_by')->nullable()->constrained('users')->onDelete('set null')->after('created_by');
            $table->timestamp('last_activity_at')->nullable()->after('updated_at');
            $table->boolean('allow_comments')->default(true)->after('manual_progress');
            $table->boolean('notify_watchers')->default(true)->after('allow_comments');
        });

        // Add team-related fields to subtasks
        Schema::table('subtasks', function (Blueprint $table) {
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null')->after('task_id');
            $table->foreignId('owned_by')->nullable()->constrained('users')->onDelete('set null')->after('created_by');
            $table->timestamp('last_activity_at')->nullable()->after('updated_at');
            $table->boolean('allow_comments')->default(true)->after('manual_progress');
            $table->boolean('notify_watchers')->default(true)->after('allow_comments');
        });
    }

    public function down()
    {
        Schema::table('milestones', function (Blueprint $table) {
            $table->dropForeign(['created_by']);
            $table->dropForeign(['owned_by']);
            $table->dropColumn(['created_by', 'owned_by', 'last_activity_at', 'allow_comments', 'notify_watchers']);
        });

        Schema::table('tasks', function (Blueprint $table) {
            $table->dropForeign(['created_by']);
            $table->dropForeign(['owned_by']);
            $table->dropColumn(['created_by', 'owned_by', 'last_activity_at', 'allow_comments', 'notify_watchers']);
        });

        Schema::table('subtasks', function (Blueprint $table) {
            $table->dropForeign(['created_by']);
            $table->dropForeign(['owned_by']);
            $table->dropColumn(['created_by', 'owned_by', 'last_activity_at', 'allow_comments', 'notify_watchers']);
        });
    }
};