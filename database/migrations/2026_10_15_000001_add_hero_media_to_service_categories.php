<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * A banner of its own for each main service page: a hero photo, and an
 * optional video that plays behind the hero text. Separate from the image and
 * video further down the page.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('service_categories', function (Blueprint $table) {
            $table->string('hero_image')->nullable()->after('video');
            $table->string('hero_video')->nullable()->after('hero_image');
        });
    }

    public function down(): void
    {
        Schema::table('service_categories', function (Blueprint $table) {
            $table->dropColumn(['hero_image', 'hero_video']);
        });
    }
};
