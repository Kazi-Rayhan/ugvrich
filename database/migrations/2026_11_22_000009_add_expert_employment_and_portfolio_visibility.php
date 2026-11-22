<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        foreach (['consultancy_applications', 'experts'] as $tableName) {
            Schema::table($tableName, function (Blueprint $table) {
                $table->string('affiliation_type')->nullable();
                $table->string('current_job')->nullable();
                if ($table->getTable() === 'consultancy_applications') {
                    $table->string('current_designation')->nullable();
                }
            });
        }

        Schema::table('expert_portfolios', function (Blueprint $table) {
            $table->boolean('is_public')->default(false);
        });
    }

    public function down(): void
    {
        Schema::table('consultancy_applications', fn (Blueprint $table) => $table->dropColumn(['affiliation_type', 'current_job', 'current_designation']));
        Schema::table('experts', fn (Blueprint $table) => $table->dropColumn(['affiliation_type', 'current_job']));
        Schema::table('expert_portfolios', fn (Blueprint $table) => $table->dropColumn('is_public'));
    }
};
