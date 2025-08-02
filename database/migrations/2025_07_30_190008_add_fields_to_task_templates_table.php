<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('task_templates', function (Blueprint $table) {
            // Voeg datum velden toe
            $table->date('default_start_date')->nullable()->after('description');
            $table->date('default_end_date')->nullable()->after('default_start_date');
            
            // Als deze kolommen nog niet bestaan
            if (!Schema::hasColumn('task_templates', 'deliverables')) {
                $table->json('deliverables')->nullable()->after('checklist_items');
            }
        });
    }

    public function down(): void
    {
        Schema::table('task_templates', function (Blueprint $table) {
            $table->dropColumn(['default_start_date', 'default_end_date']);
            if (Schema::hasColumn('task_templates', 'deliverables')) {
                $table->dropColumn('deliverables');
            }
        });
    }
};