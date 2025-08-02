<?php
// 1. MIGRATIE: Update Users tabel
// database/migrations/2024_XX_XX_add_permissions_to_users_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            // Voeg nieuwe velden toe
            $table->json('permissions')->nullable()->after('role');
            $table->boolean('can_see_all_projects')->default(false)->after('permissions');
            $table->boolean('can_see_financial_data')->default(false)->after('can_see_all_projects');
            $table->string('department')->nullable()->after('can_see_financial_data');
            $table->string('timezone')->default('Europe/Amsterdam')->after('department');
            $table->json('notification_preferences')->nullable()->after('timezone');
            $table->string('avatar')->nullable()->after('notification_preferences');
        });

        // Update de role kolom om nieuwe rollen toe te voegen
        // Eerst backup maken van huidige roles
        DB::statement("UPDATE users SET role = 'user' WHERE role NOT IN ('admin', 'user')");
        
        // Dan kolom wijzigen
        DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('admin', 'beheerder', 'account_manager', 'user') DEFAULT 'user'");
    }

    public function down()
    {
        // Eerst role kolom terugzetten
        DB::statement("ALTER TABLE users MODIFY COLUMN role VARCHAR(255) DEFAULT 'user'");
        
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'permissions',
                'can_see_all_projects', 
                'can_see_financial_data',
                'department',
                'timezone',
                'notification_preferences',
                'avatar'
            ]);
        });
    }
};