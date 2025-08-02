<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('tasks', function (Blueprint $table) {
            // Datum velden
            $table->date('start_date')->nullable()->after('due_date');
            $table->date('end_date')->nullable()->after('start_date'); // hernoem due_date later
            
            // Priority update (change existing enum)
            // Note: In production, you might need to handle this differently
            $table->enum('priority_new', ['laag', 'normaal', 'hoog'])->default('normaal')->after('status');
            
            // Financiële velden
            $table->enum('fee_type', ['in_fee', 'extended_fee'])->default('in_fee')->after('estimated_hours');
            $table->enum('pricing_type', ['fixed_price', 'hourly_rate'])->default('hourly_rate')->after('fee_type');
            $table->decimal('price', 10, 2)->nullable()->after('pricing_type');
            
            // Progress percentage
            $table->integer('manual_progress')->default(0)->after('price');
        });
        
        // Copy old priority values and drop old column
        Schema::table('tasks', function (Blueprint $table) {
            // Migrate existing priority values
            DB::statement("UPDATE tasks SET priority_new = CASE 
                WHEN priority = 'low' THEN 'laag'
                WHEN priority = 'medium' THEN 'normaal' 
                WHEN priority = 'high' THEN 'hoog'
                ELSE 'normaal'
            END");
            
            $table->dropColumn('priority');
        });
        
        Schema::table('tasks', function (Blueprint $table) {
            $table->renameColumn('priority_new', 'priority');
        });
    }

    public function down()
    {
        Schema::table('tasks', function (Blueprint $table) {
            $table->dropColumn([
                'start_date', 
                'end_date', 
                'fee_type', 
                'pricing_type', 
                'price',
                'manual_progress'
            ]);
            
            // Restore old priority enum
            $table->dropColumn('priority');
            $table->enum('priority', ['low', 'medium', 'high'])->default('medium');
        });
    }
};
