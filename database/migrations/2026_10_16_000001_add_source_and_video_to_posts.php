<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Where a news item came from (a link to the original and the name of the
 * outlet or organisation), and a video to go with it.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            $table->string('reference_name', 180)->nullable()->after('author');
            $table->string('external_url', 500)->nullable()->after('reference_name');
            $table->string('video_url', 500)->nullable()->after('external_url');
        });
    }

    public function down(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            $table->dropColumn(['reference_name', 'external_url', 'video_url']);
        });
    }
};
