<?php

// 6. MIGRATIE: Activity Logs tabel
// database/migrations/2024_XX_XX_create_activity_logs_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('activity_logs')) {
            Schema::create('activity_logs', function (Blueprint $table) {
                $table->id();
                $table->string('loggable_type');
                $table->unsignedBigInteger('loggable_id');
                $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');
                $table->string('action', 100);
                $table->text('description')->nullable();
                $table->json('old_values')->nullable();
                $table->json('new_values')->nullable();
                $table->string('ip_address', 45)->nullable();
                $table->string('user_agent')->nullable();
                $table->timestamps();
                
                // Indexes
                $table->index(['loggable_type', 'loggable_id']);
                $table->index('user_id');
                $table->index('action');
                $table->index('created_at');
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('activity_logs');
    }
};