<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('milestone_templates', function (Blueprint $table) {
            // Voor het berekenen van start/end dates bij clonen
            $table->integer('start_after_days')->default(0)->after('description');
            $table->integer('duration_days')->default(30)->after('start_after_days');
        });
    }

    public function down(): void
    {
        Schema::table('milestone_templates', function (Blueprint $table) {
            $table->dropColumn(['start_after_days', 'duration_days']);
        });
    }
};