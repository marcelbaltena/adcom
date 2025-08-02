<?php

// 8. MIGRATIE: Attachments tabel
// database/migrations/2024_XX_XX_create_attachments_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('attachments')) {
            Schema::create('attachments', function (Blueprint $table) {
                $table->id();
                $table->string('attachable_type');
                $table->unsignedBigInteger('attachable_id');
                $table->foreignId('user_id')->constrained()->onDelete('cascade');
                $table->string('filename');
                $table->string('original_filename');
                $table->string('mime_type');
                $table->unsignedBigInteger('size');
                $table->string('path');
                $table->string('disk')->default('local');
                $table->json('metadata')->nullable();
                $table->timestamps();
                
                // Indexes
                $table->index(['attachable_type', 'attachable_id']);
                $table->index('user_id');
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('attachments');
    }
};