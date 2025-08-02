<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->foreignId('billing_company_id')->nullable()->after('user_id')->constrained('companies');
            $table->foreignId('created_by_company_id')->nullable()->after('billing_company_id')->constrained('companies');
            $table->decimal('project_value', 10, 2)->nullable()->after('budget');
            $table->string('currency', 3)->default('EUR')->after('project_value');
        });
    }

    public function down()
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->dropForeign(['billing_company_id']);
            $table->dropForeign(['created_by_company_id']);
            $table->dropColumn(['billing_company_id', 'created_by_company_id', 'project_value', 'currency']);
        });
    }
};