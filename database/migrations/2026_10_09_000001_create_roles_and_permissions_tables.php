<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Roles and permissions, laid out the way UGVOS does it.
 *
 * A permission is a name (`view_any_research_idea`); a role is a bag of them;
 * a user has one primary role and may have others besides. Nothing is hard
 * coded in the application: the names live in the database, so a new role is
 * made by someone sitting at the admin rather than by a developer.
 *
 * The existing `users.role` column stays exactly as it is. It is what tells
 * admin from researcher across the whole site, and this sits on top of it:
 * finer grained, and additive.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('roles', function (Blueprint $table) {
            $table->id();

            $table->string('name')->unique();          // admin, research_officer…
            $table->string('display_name');            // what a person reads
            $table->string('group')->nullable();       // how roles are grouped
            $table->text('description')->nullable();

            $table->timestamps();
        });

        Schema::create('permissions', function (Blueprint $table) {
            $table->id();

            $table->string('name')->unique();          // view_any_research_idea
            $table->string('group')->nullable()->index();
            $table->string('status')->default('active');
            $table->text('description')->nullable();

            $table->timestamps();
        });

        Schema::create('permission_role', function (Blueprint $table) {
            $table->id();
            $table->foreignId('permission_id')->constrained()->cascadeOnDelete();
            $table->foreignId('role_id')->constrained()->cascadeOnDelete();
            $table->unique(['permission_id', 'role_id']);
        });

        Schema::create('role_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('role_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->unique(['role_id', 'user_id']);
        });

        Schema::table('users', function (Blueprint $table) {
            // The primary role. Additional ones hang off role_user.
            $table->foreignId('role_id')->nullable()->after('role')->constrained()->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('role_id');
        });

        Schema::dropIfExists('role_user');
        Schema::dropIfExists('permission_role');
        Schema::dropIfExists('permissions');
        Schema::dropIfExists('roles');
    }
};
