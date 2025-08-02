<?php
// =============================================================================
// MIGRATION 3: database/migrations/xxxx_create_milestones_table.php
// =============================================================================
?>
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('milestones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->onDelete('cascade');
            $table->string('title');
            $table->text('description')->nullable();
            $table->enum('status', ['pending', 'in_progress', 'completed'])->default('pending');
            $table->date('due_date')->nullable();
            $table->date('completed_at')->nullable();
            $table->decimal('budget', 10, 2)->nullable();
            $table->decimal('spent', 10, 2)->default(0);
            $table->integer('order')->default(0);
            $table->timestamps();
            
            $table->index(['project_id', 'order']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('milestones');
    }
};
