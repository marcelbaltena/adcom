<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('assignees', function (Blueprint $table) {
            $table->id();
            $table->string('assignable_type'); // Milestone, Task, Subtask
            $table->unsignedBigInteger('assignable_id');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->enum('role', ['owner', 'assignee', 'reviewer'])->default('assignee');
            $table->timestamps();
            
            $table->index(['assignable_type', 'assignable_id']);
            $table->unique(['assignable_type', 'assignable_id', 'user_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('assignees');
    }
};