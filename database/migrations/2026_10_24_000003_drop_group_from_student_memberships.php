<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('student_memberships', 'group')) {
            Schema::table('student_memberships', function (Blueprint $table) {
                $table->dropColumn('group');
            });
        }
    }

    public function down(): void
    {
        if (! Schema::hasColumn('student_memberships', 'group')) {
            Schema::table('student_memberships', function (Blueprint $table) {
                $table->string('group', 60)->nullable();
            });
        }
    }
};
