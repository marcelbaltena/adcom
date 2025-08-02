<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('service_templates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('category')->nullable();
            $table->enum('service_type', ['hourly', 'fixed', 'package'])->default('fixed');
            $table->decimal('base_price', 10, 2)->default(0);
            $table->decimal('hourly_rate', 10, 2)->nullable();
            $table->decimal('estimated_hours', 8, 2)->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('is_popular')->default(false);
            $table->json('tags')->nullable();
            $table->integer('order')->default(0);
            $table->timestamps();
            
            $table->index(['company_id', 'is_active']);
            $table->index(['category', 'order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('service_templates');
    }
};