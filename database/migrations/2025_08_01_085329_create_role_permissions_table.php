<?php

// 3. MIGRATIE: Role Permissions tabel
// database/migrations/2024_XX_XX_create_role_permissions_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('role_permissions', function (Blueprint $table) {
            $table->id();
            $table->string('role', 50);
            $table->string('permission', 100);
            $table->string('resource', 100);
            $table->enum('action', ['view', 'create', 'update', 'delete', 'manage']);
            $table->boolean('allowed')->default(true);
            $table->string('description')->nullable();
            $table->timestamps();
            
            // Unieke combinatie
            $table->unique(['role', 'permission', 'resource', 'action'], 'role_perm_unique');
            
            // Indexes voor snelle lookups
            $table->index('role');
            $table->index(['role', 'resource']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('role_permissions');
    }
};
