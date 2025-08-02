<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('milestones', function (Blueprint $table) {
            // Datum velden
            $table->date('start_date')->nullable()->after('due_date');
            $table->date('end_date')->nullable()->after('start_date'); // hernoem due_date later
            
            // Prioriteit (update existing enum)
            $table->enum('priority', ['laag', 'normaal', 'hoog'])->default('normaal')->after('status');
            
            // Financiële velden
            $table->enum('fee_type', ['in_fee', 'extended_fee'])->default('in_fee')->after('budget');
            $table->enum('pricing_type', ['fixed_price', 'hourly_rate'])->default('fixed_price')->after('fee_type');
            $table->decimal('price', 10, 2)->nullable()->after('pricing_type'); // vaste prijs of uurtarief
            $table->decimal('estimated_hours', 8, 2)->nullable()->after('price');
            
            // Progress percentage (handmatig instelbaar)
            $table->integer('manual_progress')->default(0)->after('estimated_hours');
        });
    }

    public function down()
    {
        Schema::table('milestones', function (Blueprint $table) {
            $table->dropColumn([
                'start_date', 
                'end_date', 
                'priority', 
                'fee_type', 
                'pricing_type', 
                'price', 
                'estimated_hours',
                'manual_progress'
            ]);
        });
    }
};