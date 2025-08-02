<?php

// 2. MIGRATIE: Project Teams tabel
// database/migrations/2024_XX_XX_create_project_teams_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('project_teams', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->enum('role', ['project_manager', 'team_member', 'viewer'])->default('team_member');
            $table->json('permissions')->nullable();
            $table->timestamps();
            
            // Unieke index om dubbele entries te voorkomen
            $table->unique(['project_id', 'user_id']);
            
            // Extra indexes voor performance
            $table->index('user_id');
            $table->index(['project_id', 'role']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('project_teams');
    }
};