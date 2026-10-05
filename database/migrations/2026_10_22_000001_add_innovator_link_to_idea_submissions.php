<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * An idea now belongs to the innovator account it was submitted from, so the
 * innovator can follow it from their dashboard, and carries the category it
 * was submitted under (config('rich.idea_categories')).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('idea_submissions', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable()->after('id')->constrained()->nullOnDelete();
            $table->string('category', 30)->nullable()->after('title')->index();
        });
    }

    public function down(): void
    {
        Schema::table('idea_submissions', function (Blueprint $table) {
            $table->dropConstrainedForeignId('user_id');
            $table->dropColumn('category');
        });
    }
};
