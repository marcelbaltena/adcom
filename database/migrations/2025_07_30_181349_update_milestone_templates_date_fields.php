<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('milestone_templates', function (Blueprint $table) {
            // Voeg default start en end dates toe
            $table->date('default_start_date')->nullable()->after('description');
            $table->date('default_end_date')->nullable()->after('default_start_date');
            
            // Verwijder de oude velden (als ze bestaan)
            if (Schema::hasColumn('milestone_templates', 'start_after_days')) {
                $table->dropColumn('start_after_days');
            }
            if (Schema::hasColumn('milestone_templates', 'duration_days')) {
                $table->dropColumn('duration_days');
            }
        });
    }

    public function down(): void
    {
        Schema::table('milestone_templates', function (Blueprint $table) {
            $table->dropColumn(['default_start_date', 'default_end_date']);
            $table->integer('start_after_days')->default(0)->after('description');
            $table->integer('duration_days')->default(30)->after('start_after_days');
        });
    }
};