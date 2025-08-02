<?php

// 5. MIGRATIE: Comments tabel (voor HasTeamFeatures)
// database/migrations/2024_XX_XX_create_comments_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('comments')) {
            Schema::create('comments', function (Blueprint $table) {
                $table->id();
                $table->string('commentable_type');
                $table->unsignedBigInteger('commentable_id');
                $table->foreignId('user_id')->constrained()->onDelete('cascade');
                $table->text('content');
                $table->boolean('is_internal')->default(false);
                $table->json('mentions')->nullable();
                $table->timestamps();
                
                // Indexes
                $table->index(['commentable_type', 'commentable_id']);
                $table->index('user_id');
                $table->index('created_at');
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('comments');
    }
};