<?php

// 4. MIGRATIE: Watchers tabel (als deze nog niet bestaat)
// database/migrations/2024_XX_XX_create_watchers_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('watchers')) {
            Schema::create('watchers', function (Blueprint $table) {
                $table->id();
                $table->string('watchable_type');
                $table->unsignedBigInteger('watchable_id');
                $table->foreignId('user_id')->constrained()->onDelete('cascade');
                $table->timestamps();
                
                // Unieke combinatie
                $table->unique(['watchable_type', 'watchable_id', 'user_id'], 'watchers_unique');
                
                // Polymorphic index
                $table->index(['watchable_type', 'watchable_id']);
                $table->index('user_id');
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('watchers');
    }
};