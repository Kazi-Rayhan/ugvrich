<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Innovation areas get the same media as service categories: a video to play
 * in place of the cover image, and a hero photo and video for the banner of
 * the area's own page.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('innovation_areas', function (Blueprint $table) {
            $table->string('video')->nullable()->after('image');
            $table->string('hero_image')->nullable()->after('video');
            $table->string('hero_video')->nullable()->after('hero_image');
        });
    }

    public function down(): void
    {
        Schema::table('innovation_areas', function (Blueprint $table) {
            $table->dropColumn(['video', 'hero_image', 'hero_video']);
        });
    }
};
