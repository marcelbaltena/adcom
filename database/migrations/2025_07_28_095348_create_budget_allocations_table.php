<?php
// database/migrations/xxxx_create_budget_allocations_table.php (FIXED)

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('budget_allocations', function (Blueprint $table) {
            $table->id();
            
            // Polymorphic relationship (milestone, task, or subtask)
            $table->morphs('allocatable'); // This ALREADY creates the index automatically
            
            // Budget details
            $table->decimal('allocated_amount', 10, 2);
            $table->decimal('spent_amount', 10, 2)->default(0);
            $table->decimal('committed_amount', 10, 2)->default(0);
            $table->date('allocation_date');
            
            // Allocation type
            $table->enum('allocation_type', ['initial', 'adjustment', 'reallocation', 'transfer']);
            $table->text('allocation_reason')->nullable();
            
            // Approval workflow
            $table->enum('status', ['pending', 'approved', 'rejected', 'auto_approved'])->default('auto_approved');
            $table->foreignId('created_by')->constrained('users');
            $table->foreignId('approved_by')->nullable()->constrained('users');
            $table->timestamp('approved_at')->nullable();
            
            // Additional tracking
            $table->json('metadata')->nullable(); // For storing additional info
            $table->decimal('previous_amount', 10, 2)->nullable(); // For tracking changes
            
            $table->timestamps();
            
            // Indexes for performance (REMOVED the duplicate morphs index)
            $table->index('allocation_date');
            $table->index('status');
            $table->index('allocation_type');
            $table->index('created_by');
        });
    }

    public function down()
    {
        Schema::dropIfExists('budget_allocations');
    }
};