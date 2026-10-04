<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Which KPIs the home page dashboard shows. Every stat stays available to the
 * rest of the site; the home page keeps to six headline figures, so the four
 * supporting ones start switched off there.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('stats', function (Blueprint $table) {
            $table->boolean('show_on_home')->default(true)->after('is_active');
        });

        DB::table('stats')
            ->whereIn('label', [
                'Consultancy & research projects',
                'Faculty experts on call',
                'Partner organisations',
                'Client satisfaction',
            ])
            ->update(['show_on_home' => false]);
    }

    public function down(): void
    {
        Schema::table('stats', function (Blueprint $table) {
            $table->dropColumn('show_on_home');
        });
    }
};
