<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (!Schema::hasColumn('milestone_templates', 'project_template_id')) {
            Schema::table('milestone_templates', function (Blueprint $table) {
                $table->unsignedBigInteger('project_template_id')->nullable()->after('service_template_id');
                $table->integer('days_from_start')->default(0)->after('order');
                $table->integer('duration_days')->default(1)->after('days_from_start');
                
                $table->foreign('project_template_id')->references('id')->on('project_templates')->onDelete('cascade');
                $table->index('project_template_id');
            });
        }
    }

    public function down()
    {
        Schema::table('milestone_templates', function (Blueprint $table) {
            $table->dropForeign(['project_template_id']);
            $table->dropColumn(['project_template_id', 'days_from_start', 'duration_days']);
        });
    }
};