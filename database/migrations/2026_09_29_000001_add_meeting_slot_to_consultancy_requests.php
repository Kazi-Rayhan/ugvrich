<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * A consultancy request can now name when its sender would like to meet:
 * a date the office is open, and one of the half-hour slots in the day.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('consultancy_requests', function (Blueprint $table) {
            $table->date('preferred_date')->nullable()->after('requirement');
            $table->string('preferred_slot', 11)->nullable()->after('preferred_date');
        });
    }

    public function down(): void
    {
        Schema::table('consultancy_requests', function (Blueprint $table) {
            $table->dropColumn(['preferred_date', 'preferred_slot']);
        });
    }
};
