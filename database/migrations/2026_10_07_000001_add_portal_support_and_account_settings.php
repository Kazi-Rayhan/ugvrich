<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Two small additions, so the last two corners of the portal can be built on
 * what already exists rather than on new tables.
 *
 * - A support request can now belong to an account. Requests sent from the
 *   public form keep a null user_id, which is also what keeps them out of the
 *   portal: a researcher sees their own requests and nothing else.
 * - An account can turn the email side of notifications off. The database
 *   record is always written either way, so nothing is lost by opting out.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('research_supports', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable()->after('id')
                ->constrained()->nullOnDelete();
        });

        Schema::table('users', function (Blueprint $table) {
            $table->boolean('email_notifications')->default(true)->after('role');
        });
    }

    public function down(): void
    {
        Schema::table('research_supports', function (Blueprint $table) {
            $table->dropConstrainedForeignId('user_id');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('email_notifications');
        });
    }
};
