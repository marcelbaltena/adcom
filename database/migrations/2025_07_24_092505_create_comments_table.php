<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('comments', function (Blueprint $table) {
            $table->id();
            $table->string('commentable_type'); // Milestone, Task, Subtask
            $table->unsignedBigInteger('commentable_id');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->text('content');
            $table->json('mentions')->nullable(); // Array of mentioned user IDs
            $table->boolean('is_internal')->default(false); // Internal vs client notes
            $table->foreignId('parent_id')->nullable()->constrained('comments')->onDelete('cascade'); // For replies
            $table->timestamps();
            
            $table->index(['commentable_type', 'commentable_id']);
            $table->index('user_id');
            $table->index('created_at');
        });
    }

    public function down()
    {
        Schema::dropIfExists('comments');
    }
};
