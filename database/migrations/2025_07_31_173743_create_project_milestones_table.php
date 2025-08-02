<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('project_milestones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_template_id')->constrained()->onDelete('cascade');
            $table->string('title');
            $table->text('description')->nullable();
            $table->integer('days_from_start')->default(0);
            $table->integer('duration_days')->default(1);
            $table->enum('fee_type', ['in_fee', 'extended'])->default('in_fee');
            $table->enum('pricing_type', ['fixed_price', 'hourly_rate'])->default('fixed_price');
            $table->decimal('estimated_hours', 8, 2)->nullable();
            $table->json('deliverables')->nullable();
            $table->integer('order')->default(0);
            $table->timestamps();
            
            $table->index(['project_template_id', 'order']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('project_milestones');
    }
};