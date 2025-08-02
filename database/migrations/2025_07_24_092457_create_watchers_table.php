<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('watchers', function (Blueprint $table) {
            $table->id();
            $table->string('watchable_type'); // Milestone, Task, Subtask
            $table->unsignedBigInteger('watchable_id');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->enum('notification_level', ['all', 'important', 'mentions_only'])->default('all');
            $table->timestamps();
            
            $table->index(['watchable_type', 'watchable_id']);
            $table->unique(['watchable_type', 'watchable_id', 'user_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('watchers');
    }
};
