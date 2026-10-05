<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * The public idea form is now two steps — register, then upload the idea as a
 * file — so the written problem and solution are no longer asked for there.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('idea_submissions', function (Blueprint $table) {
            $table->text('problem')->nullable()->change();
            $table->text('solution')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('idea_submissions', function (Blueprint $table) {
            $table->text('problem')->nullable(false)->change();
            $table->text('solution')->nullable(false)->change();
        });
    }
};
