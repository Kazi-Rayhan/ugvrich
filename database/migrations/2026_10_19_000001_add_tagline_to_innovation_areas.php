<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * A one-line short description for each innovation area, as a service
 * category has its tagline: shown under the area in the menu and above its
 * title on its own page. With a Bangla twin, like the other text.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('innovation_areas', function (Blueprint $table) {
            $table->string('tagline')->nullable()->after('name');
            $table->string('tagline_bn')->nullable()->after('tagline');
        });
    }

    public function down(): void
    {
        Schema::table('innovation_areas', function (Blueprint $table) {
            $table->dropColumn(['tagline', 'tagline_bn']);
        });
    }
};
