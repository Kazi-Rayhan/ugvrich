<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Innovations belong to an innovation area, the way a service belongs to its
 * service category, so each area lists its own innovations.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('innovations', function (Blueprint $table) {
            $table->foreignId('innovation_area_id')->nullable()->after('phase')
                ->constrained()->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('innovations', function (Blueprint $table) {
            $table->dropConstrainedForeignId('innovation_area_id');
        });
    }
};
