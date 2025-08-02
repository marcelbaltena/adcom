<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // Only run if client_id exists and we want to remove it
        if (Schema::hasColumn('projects', 'client_id')) {
            // First check if there are any client references we need to migrate
            $clientCount = DB::table('projects')->whereNotNull('client_id')->count();
            
            if ($clientCount > 0) {
                // Log warning - manual data migration needed
                Log::warning('Found ' . $clientCount . ' projects with client_id. Manual migration recommended.');
            }
            
            Schema::table('projects', function (Blueprint $table) {
                // Drop foreign key constraint if it exists
                try {
                    $table->dropForeign(['client_id']);
                } catch (Exception $e) {
                    // Foreign key might not exist, continue
                }
                
                // Drop the column
                $table->dropColumn('client_id');
            });
        }
    }

    public function down()
    {
        Schema::table('projects', function (Blueprint $table) {
            // Recreate client_id if needed
            $table->unsignedBigInteger('client_id')->nullable();
            // Note: You'll need to manually recreate foreign key if needed
        });
    }
};