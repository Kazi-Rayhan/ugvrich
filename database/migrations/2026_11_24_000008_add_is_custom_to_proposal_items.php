<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('proposal_items', fn (Blueprint $table) => $table->boolean('is_custom')->default(false));

        DB::table('proposal_items')->whereNull('service_id')->where('service_name', 'Custom')->update(['is_custom' => true]);
    }

    public function down(): void
    {
        Schema::table('proposal_items', fn (Blueprint $table) => $table->dropColumn('is_custom'));
    }
};
