<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Gives the services tree its third level.
 *
 * The shape was already there — a category holding services — but only the
 * category had a page, so a service was a line of text on a list. These columns
 * give each service enough to stand on its own page: what it is at length, what
 * the client gets, and a picture.
 *
 * The category gains the department that runs it. A main service belongs to a
 * sector — Smart ICT to CSE, automation to EEE, infrastructure to Civil — and
 * that belonging is what lets a visitor find the right desk.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('service_categories', function (Blueprint $table) {
            // The sector that runs this service. Keys come from config/rich.php.
            $table->string('department')->nullable()->after('slug');
        });

        Schema::table('services', function (Blueprint $table) {
            $table->text('body')->nullable()->after('description');
            $table->json('highlights')->nullable()->after('body');
            $table->string('image')->nullable()->after('highlights');

            $table->text('body_bn')->nullable();
            $table->json('highlights_bn')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('service_categories', function (Blueprint $table) {
            $table->dropColumn('department');
        });

        Schema::table('services', function (Blueprint $table) {
            $table->dropColumn(['body', 'highlights', 'image', 'body_bn', 'highlights_bn']);
        });
    }
};
