<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('subtask_templates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('task_template_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->integer('estimated_minutes')->default(0);
            $table->integer('order')->default(0);
            $table->timestamps();
            
            $table->index(['task_template_id', 'order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('subtask_templates');
    }
};