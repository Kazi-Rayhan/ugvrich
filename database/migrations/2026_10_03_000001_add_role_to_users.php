<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Who a user is, now that the site has more than one kind.
 *
 * Until now every row in `users` was a RICH staff account and the admin panel
 * let all of them in. Researchers register themselves, so the two have to be
 * told apart — and the panel has to stop admitting everyone.
 *
 * A column rather than a roles table or a package: there are two kinds of user
 * and no permissions to assign. A pivot can be introduced later without moving
 * any of this.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('role')->default('admin')->after('email')->index();
        });

        // Everyone who already had an account is staff; the default covers new
        // admin rows, and this covers the ones already there.
        DB::table('users')->update(['role' => 'admin']);
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('role');
        });
    }
};
