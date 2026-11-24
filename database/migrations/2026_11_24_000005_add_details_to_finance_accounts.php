<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('finance_accounts', fn (Blueprint $table) => $table->text('details')->nullable());
    }

    public function down(): void
    {
        Schema::table('finance_accounts', fn (Blueprint $table) => $table->dropColumn('details'));
    }
};
