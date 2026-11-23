<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('consultancy_applications', function (Blueprint $table) {
            $table->foreignId('expertise_service_id')->nullable()->constrained('services')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('consultancy_applications', function (Blueprint $table) {
            $table->dropConstrainedForeignId('expertise_service_id');
        });
    }
};
