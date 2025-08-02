<?php

// 7. MIGRATIE: Mentions tabel
// database/migrations/2024_XX_XX_create_mentions_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('mentions')) {
            Schema::create('mentions', function (Blueprint $table) {
                $table->id();
                $table->string('mentionable_type');
                $table->unsignedBigInteger('mentionable_id');
                $table->foreignId('user_id')->constrained()->onDelete('cascade'); // User die mention maakt
                $table->foreignId('mentioned_user_id')->constrained('users')->onDelete('cascade'); // User die genoemd wordt
                $table->boolean('is_read')->default(false);
                $table->timestamp('read_at')->nullable();
                $table->timestamps();
                
                // Indexes
                $table->index(['mentionable_type', 'mentionable_id']);
                $table->index('mentioned_user_id');
                $table->index(['mentioned_user_id', 'is_read']);
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('mentions');
    }
};