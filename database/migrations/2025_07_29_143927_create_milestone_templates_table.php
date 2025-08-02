<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('milestone_templates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('service_template_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->enum('fee_type', ['in_fee', 'extended'])->default('in_fee');
            $table->enum('pricing_type', ['fixed_price', 'hourly_rate'])->default('fixed_price');
            $table->decimal('price', 10, 2)->default(0);
            $table->decimal('hourly_rate', 10, 2)->nullable();
            $table->decimal('estimated_hours', 8, 2)->nullable();
            $table->json('deliverables')->nullable();
            $table->integer('order')->default(0);
            $table->timestamps();
            
            $table->index(['service_template_id', 'order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('milestone_templates');
    }
};