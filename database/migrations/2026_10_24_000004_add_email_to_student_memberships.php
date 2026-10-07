<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('student_memberships', 'email')) {
            Schema::table('student_memberships', function (Blueprint $table) {
                $table->string('email', 150)->unique()->after('phone');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('student_memberships', 'email')) {
            Schema::table('student_memberships', function (Blueprint $table) {
                $table->dropUnique(['email']);
                $table->dropColumn('email');
            });
        }
    }
};
