<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('finance_accounts', function (Blueprint $table) {
            $table->id();
            $table->string('name', 120)->unique();
            $table->string('type', 20);
            $table->unsignedBigInteger('balance_minor')->default(0);
            $table->boolean('is_active')->default(true);
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('finance_transactions', function (Blueprint $table) {
            $table->id();
            $table->string('kind', 16);
            $table->foreignId('from_account_id')->nullable()->constrained('finance_accounts')->restrictOnDelete();
            $table->foreignId('to_account_id')->nullable()->constrained('finance_accounts')->restrictOnDelete();
            $table->unsignedBigInteger('amount_minor');
            $table->date('transaction_date');
            $table->string('category', 100)->nullable();
            $table->string('reference', 150)->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('recorded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('recorded_by_name');
            $table->uuid('request_key')->unique();
            $table->foreignId('reversal_of_id')->nullable()->unique()->constrained('finance_transactions')->restrictOnDelete();
            $table->timestamps();
            $table->index(['kind', 'transaction_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('finance_transactions');
        Schema::dropIfExists('finance_accounts');
    }
};
