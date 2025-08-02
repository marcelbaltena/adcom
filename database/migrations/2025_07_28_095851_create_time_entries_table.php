<?php
// database/migrations/xxxx_create_time_entries_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('time_entries', function (Blueprint $table) {
            $table->id();
            
            // Polymorphic relationship (can be linked to milestone, task, or subtask)
            $table->morphs('trackable'); // creates trackable_type and trackable_id
            
            // Time tracking
            $table->foreignId('user_id')->constrained('users');
            $table->date('date');
            $table->time('start_time')->nullable();
            $table->time('end_time')->nullable();
            $table->decimal('hours', 8, 2);
            $table->boolean('billable')->default(true);
            
            // Description and notes
            $table->text('description');
            $table->text('internal_notes')->nullable();
            
            // Rate and cost
            $table->decimal('hourly_rate', 8, 2);
            $table->decimal('total_cost', 10, 2); // hours * hourly_rate
            
            // Status
            $table->enum('status', ['draft', 'submitted', 'approved', 'billed', 'rejected'])->default('draft');
            $table->foreignId('approved_by')->nullable()->constrained('users');
            $table->timestamp('approved_at')->nullable();
            
            // Billing tracking
            $table->boolean('invoiced')->default(false);
            $table->foreignId('invoice_id')->nullable(); // For future invoice system
            
            // Tags for categorization
            $table->json('tags')->nullable();
            
            $table->timestamps();
            
            // Indexes for performance
            $table->index(['user_id', 'date']);
            $table->index('date');
            $table->index('status');
            $table->index('billable');
            $table->index('invoiced');
        });
    }

    public function down()
    {
        Schema::dropIfExists('time_entries');
    }
};