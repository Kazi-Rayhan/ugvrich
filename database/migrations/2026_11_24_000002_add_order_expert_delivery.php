<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', fn (Blueprint $table) => $table->json('delivery_milestones')->nullable());

        Schema::create('order_expert_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->restrictOnDelete();
            $table->foreignId('consultancy_application_id')->constrained()->restrictOnDelete();
            $table->decimal('engagement_percentage', 5, 2);
            $table->unsignedBigInteger('credited_minor')->default(0);
            $table->timestamps();
            $table->unique(['order_id', 'consultancy_application_id'], 'order_expert_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_expert_assignments');
        Schema::table('orders', fn (Blueprint $table) => $table->dropColumn('delivery_milestones'));
    }
};
