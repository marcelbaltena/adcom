<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('company_id')->nullable()->after('id')->constrained()->onDelete('cascade');
            $table->string('role')->default('user')->after('company_id'); // admin, manager, user
            $table->decimal('hourly_rate', 8, 2)->nullable()->after('role');
            $table->boolean('is_active')->default(true)->after('hourly_rate');
            
            // Indexes
            $table->index('company_id');
            $table->index('role');
            $table->index('is_active');
        });
    }

    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['company_id']);
            $table->dropColumn(['company_id', 'role', 'hourly_rate', 'is_active']);
        });
    }
};
