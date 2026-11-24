<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('work_orders', function (Blueprint $table) {
            $table->string('number', 40)->nullable()->change();
            $table->string('sector', 10)->nullable()->change();
            $table->string('status', 20)->nullable()->default('draft')->change();
            $table->decimal('total', 12, 2)->nullable()->default(0)->change();
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->foreignId('work_order_id')->nullable()->unique()->constrained()->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropForeign(['work_order_id']);
            $table->dropUnique(['work_order_id']);
            $table->dropColumn('work_order_id');
        });

        Schema::table('work_orders', function (Blueprint $table) {
            $table->string('number', 40)->nullable(false)->change();
            $table->string('sector', 10)->nullable(false)->change();
            $table->string('status', 20)->nullable(false)->default('draft')->change();
            $table->decimal('total', 12, 2)->nullable(false)->default(0)->change();
        });
    }
};
