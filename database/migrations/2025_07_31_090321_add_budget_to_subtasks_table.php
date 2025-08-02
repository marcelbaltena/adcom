<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('subtasks', function (Blueprint $table) {
            if (!Schema::hasColumn('subtasks', 'budget')) {
                $table->decimal('budget', 10, 2)->nullable()->after('price');
            }
        });
    }

    public function down()
    {
        Schema::table('subtasks', function (Blueprint $table) {
            $table->dropColumn('budget');
        });
    }
};