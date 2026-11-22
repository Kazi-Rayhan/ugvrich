<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('consultancy_applications', function (Blueprint $table) {
            $table->text('wallet_destinations')->nullable();
        });

        Schema::create('expert_wallet_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('consultancy_application_id')->constrained()->restrictOnDelete();
            $table->string('kind', 16);
            $table->string('status', 16)->index();
            $table->unsignedBigInteger('amount_minor');
            $table->string('request_key', 64);
            $table->string('method', 12)->nullable();
            $table->text('destination')->nullable();
            $table->string('reference', 150)->nullable();
            $table->text('note')->nullable();
            $table->json('history')->nullable();
            $table->timestamps();
            $table->unique(['consultancy_application_id', 'request_key'], 'expert_wallet_request_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('expert_wallet_entries');
        Schema::table('consultancy_applications', fn (Blueprint $table) => $table->dropColumn('wallet_destinations'));
    }
};
