<?php
// database/migrations/xxxx_create_subtasks_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('subtasks', function (Blueprint $table) {
            $table->id();
            
            // Task relationship
            $table->foreignId('task_id')->constrained('tasks')->onDelete('cascade');
            
            // Basic information
            $table->string('title');
            $table->text('description')->nullable();
            $table->enum('status', ['concept', 'in_progress', 'completed'])->default('concept');
            $table->enum('priority', ['laag', 'normaal', 'hoog'])->default('normaal');
            
            // Timeline fields
            $table->date('due_date')->nullable();
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->date('completed_at')->nullable();
            $table->date('actual_start_date')->nullable();
            $table->date('actual_end_date')->nullable();
            
            // Budget fields
            $table->enum('fee_type', ['in_fee', 'extended_fee'])->default('in_fee');
            $table->enum('pricing_type', ['fixed_price', 'hourly_rate'])->default('hourly_rate');
            $table->decimal('price', 10, 2)->nullable();
            $table->decimal('estimated_hours', 8, 2)->nullable();
            $table->decimal('actual_hours', 8, 2)->default(0.00);
            
            // Budget tracking
            $table->decimal('spent_amount', 10, 2)->default(0);
            $table->decimal('remaining_budget', 10, 2)->default(0);
            
            // Budget status
            $table->enum('budget_status', ['under', 'on_track', 'warning', 'over'])->default('on_track');
            $table->text('budget_notes')->nullable();
            
            // Progress tracking
            $table->integer('manual_progress')->default(0);
            $table->decimal('completion_percentage', 5, 2)->default(0);
            $table->enum('timeline_status', ['not_started', 'on_time', 'behind', 'ahead'])->default('not_started');
            
            // Time tracking
            $table->decimal('billable_hours', 8, 2)->default(0);
            $table->decimal('hourly_rate', 8, 2)->nullable();
            
            // Assignment
            $table->foreignId('assigned_to')->nullable()->constrained('users');
            $table->timestamp('assigned_at')->nullable();
            
            // Options
            $table->boolean('allow_comments')->default(true);
            $table->boolean('notify_watchers')->default(true);
            
            // Order
            $table->integer('order')->default(0);
            
            // Audit fields
            $table->foreignId('created_by')->nullable()->constrained('users');
            $table->foreignId('owned_by')->nullable()->constrained('users');
            
            $table->timestamps();
            $table->timestamp('last_activity_at')->nullable();
            
            // Indexes
            $table->index(['task_id', 'order']);
            $table->index('status');
            $table->index('priority');
            $table->index('assigned_to');
        });
    }

    public function down()
    {
        Schema::dropIfExists('subtasks');
    }
};