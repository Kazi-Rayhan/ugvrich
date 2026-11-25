<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('consultancy_applications', function (Blueprint $table): void {
            $table->unsignedInteger('engagement_term_years')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('consultancy_applications', function (Blueprint $table): void {
            $table->dropColumn('engagement_term_years');
        });
    }
};
