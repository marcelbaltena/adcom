<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('subtask_templates', function (Blueprint $table) {
            // Voeg alle velden toe die subtasks nodig hebben
            $table->date('default_start_date')->nullable()->after('description');
            $table->date('default_end_date')->nullable()->after('default_start_date');
            $table->enum('fee_type', ['in_fee', 'extended'])->default('in_fee')->after('default_end_date');
            $table->enum('pricing_type', ['fixed_price', 'hourly_rate'])->default('fixed_price')->after('fee_type');
            $table->decimal('price', 10, 2)->nullable()->after('pricing_type');
            $table->decimal('hourly_rate', 10, 2)->nullable()->after('price');
            $table->decimal('estimated_hours', 8, 2)->nullable()->after('hourly_rate');
        });
    }

    public function down(): void
    {
        Schema::table('subtask_templates', function (Blueprint $table) {
            $table->dropColumn([
                'default_start_date', 
                'default_end_date',
                'fee_type',
                'pricing_type',
                'price',
                'hourly_rate',
                'estimated_hours'
            ]);
        });
    }
};