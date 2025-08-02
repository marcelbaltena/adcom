<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Werkschema per gebruiker (standaard werkdagen en uren)
        Schema::create('user_work_schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->boolean('monday')->default(true);
            $table->boolean('tuesday')->default(true);
            $table->boolean('wednesday')->default(true);
            $table->boolean('thursday')->default(true);
            $table->boolean('friday')->default(true);
            $table->boolean('saturday')->default(false);
            $table->boolean('sunday')->default(false);
            $table->decimal('hours_per_day', 4, 2)->default(8.00); // Standaard 8 uur per dag
            $table->decimal('hours_per_week', 5, 2)->default(40.00); // Standaard 40 uur per week
            $table->decimal('hours_per_month', 6, 2)->default(173.33); // Gemiddeld per maand
            $table->timestamps();
            
            $table->unique('user_id');
        });

        // Maandelijkse uren registratie
        Schema::create('user_monthly_hours', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->integer('year');
            $table->integer('month'); // 1-12
            $table->decimal('contracted_hours', 6, 2); // Gecontracteerde uren
            $table->decimal('billable_hours', 6, 2)->nullable(); // Declarabele uren
            $table->decimal('non_billable_hours', 6, 2)->nullable(); // Niet-declarabele uren
            $table->decimal('vacation_hours', 6, 2)->default(0); // Vakantie uren
            $table->decimal('sick_hours', 6, 2)->default(0); // Ziekte uren
            $table->text('notes')->nullable(); // Opmerkingen
            $table->timestamps();
            
            $table->unique(['user_id', 'year', 'month']);
            $table->index(['year', 'month']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_monthly_hours');
        Schema::dropIfExists('user_work_schedules');
    }
};