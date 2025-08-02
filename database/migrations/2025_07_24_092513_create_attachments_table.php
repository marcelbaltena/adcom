<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('attachments', function (Blueprint $table) {
            $table->id();
            $table->string('attachable_type'); // Milestone, Task, Subtask, Comment
            $table->unsignedBigInteger('attachable_id');
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // Who uploaded
            $table->string('original_name');
            $table->string('file_name'); // Generated unique name
            $table->string('file_path');
            $table->string('mime_type');
            $table->unsignedBigInteger('file_size'); // In bytes
            $table->json('metadata')->nullable(); // For image dimensions, etc.
            $table->timestamps();
            
            $table->index(['attachable_type', 'attachable_id']);
            $table->index('user_id');
        });
    }

    public function down()
    {
        Schema::dropIfExists('attachments');
    }
};