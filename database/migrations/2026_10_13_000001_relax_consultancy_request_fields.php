<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * The public form no longer insists on an email and no longer asks for a
 * written requirement, so both columns have to accept nothing.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('consultancy_requests', function (Blueprint $table) {
            $table->string('email')->nullable()->change();
            $table->longText('requirement')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('consultancy_requests', function (Blueprint $table) {
            $table->string('email')->nullable(false)->change();
            $table->longText('requirement')->nullable(false)->change();
        });
    }
};
