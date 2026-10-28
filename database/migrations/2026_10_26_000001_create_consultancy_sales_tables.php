<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Consultancy sales: the clients RICH works for, the proposals sent to them,
 * the orders an accepted proposal becomes, and the payments made against an
 * order over time.
 *
 * Only adds. The one change to an existing table is a nullable default price
 * on `services`, which existing rows simply leave empty.
 *
 * Money is decimal(12,2) in BDT throughout, and line items keep their own copy
 * of the service name and price, so editing the catalogue later never rewrites
 * a proposal or order that has already been made.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('services', function (Blueprint $table) {
            $table->decimal('price', 12, 2)->nullable()->after('description');
        });

        Schema::create('clients', function (Blueprint $table) {
            $table->id();
            $table->string('name', 150);
            $table->string('organization', 200)->nullable();
            $table->string('organization_type', 20)->nullable();
            $table->string('email', 180)->nullable()->unique();
            $table->string('phone', 30)->nullable();
            // Digits only, so "+880 1711-000000" and "01711000000" are one client.
            $table->string('phone_normalized', 20)->nullable()->unique();
            $table->text('address')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('proposals', function (Blueprint $table) {
            $table->id();
            $table->string('number', 30)->unique();
            $table->foreignId('client_id')->constrained()->restrictOnDelete();
            $table->foreignId('consultancy_request_id')->nullable()->constrained()->nullOnDelete();
            $table->string('title', 200);
            $table->string('status', 20)->default('draft')->index();
            $table->date('issue_date');
            $table->date('valid_until')->nullable();
            $table->string('delivery_timeline', 150)->nullable();
            $table->text('payment_terms')->nullable();
            $table->text('terms')->nullable();
            $table->text('notes')->nullable();
            // Worked out on the server from the items; never taken from the form.
            $table->decimal('subtotal', 12, 2)->default(0);
            $table->decimal('extra_discount', 12, 2)->default(0);
            $table->decimal('discount_total', 12, 2)->default(0);
            $table->decimal('tax_rate', 5, 2)->default(0);
            $table->decimal('tax_amount', 12, 2)->default(0);
            $table->decimal('total', 12, 2)->default(0);
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('accepted_at')->nullable();
            $table->foreignId('accepted_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('converted_at')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('proposal_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('proposal_id')->constrained()->cascadeOnDelete();
            $table->foreignId('service_category_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('service_id')->nullable()->constrained()->nullOnDelete();
            $table->string('service_name', 200);
            $table->text('description')->nullable();
            $table->decimal('quantity', 10, 2)->default(1);
            $table->decimal('unit_price', 12, 2)->default(0);
            $table->decimal('discount', 12, 2)->default(0);
            $table->decimal('line_total', 12, 2)->default(0);
            $table->unsignedSmallInteger('sort')->default(0);
            $table->timestamps();
        });

        Schema::create('proposal_service_category', function (Blueprint $table) {
            $table->foreignId('proposal_id')->constrained()->cascadeOnDelete();
            $table->foreignId('service_category_id')->constrained()->cascadeOnDelete();
            $table->primary(['proposal_id', 'service_category_id']);
        });

        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('number', 30)->unique();
            $table->foreignId('client_id')->constrained()->restrictOnDelete();
            // Unique: one proposal can become one order, never two.
            $table->foreignId('proposal_id')->nullable()->unique()->constrained()->restrictOnDelete();
            $table->string('status', 20)->default('pending')->index();
            $table->date('order_date');
            $table->date('expected_delivery_date')->nullable();
            $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
            $table->decimal('subtotal', 12, 2)->default(0);
            $table->decimal('extra_discount', 12, 2)->default(0);
            $table->decimal('discount_total', 12, 2)->default(0);
            $table->decimal('tax_rate', 5, 2)->default(0);
            $table->decimal('tax_amount', 12, 2)->default(0);
            $table->decimal('total', 12, 2)->default(0);
            $table->text('payment_terms')->nullable();
            $table->text('terms')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('service_category_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('service_id')->nullable()->constrained()->nullOnDelete();
            $table->string('service_name', 200);
            $table->text('description')->nullable();
            $table->decimal('quantity', 10, 2)->default(1);
            $table->decimal('unit_price', 12, 2)->default(0);
            $table->decimal('discount', 12, 2)->default(0);
            $table->decimal('line_total', 12, 2)->default(0);
            $table->unsignedSmallInteger('sort')->default(0);
            $table->timestamps();
        });

        Schema::create('order_service_category', function (Blueprint $table) {
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('service_category_id')->constrained()->cascadeOnDelete();
            $table->primary(['order_id', 'service_category_id']);
        });

        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->string('reference', 30)->unique();
            // An order with payments against it cannot be deleted.
            $table->foreignId('order_id')->constrained()->restrictOnDelete();
            $table->decimal('amount', 12, 2);
            $table->date('paid_on');
            $table->string('method', 20);
            $table->string('transaction_id', 100)->nullable();
            $table->string('status', 20)->default('pending')->index();
            $table->text('notes')->nullable();
            $table->string('receipt')->nullable();
            $table->foreignId('recorded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('confirmed_at')->nullable();
            $table->foreignId('confirmed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reversed_at')->nullable();
            $table->foreignId('reversed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('reversal_reason')->nullable();
            $table->timestamps();

            // The same bank or mobile transaction cannot be entered twice on one order.
            $table->unique(['order_id', 'transaction_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
        Schema::dropIfExists('order_service_category');
        Schema::dropIfExists('order_items');
        Schema::dropIfExists('orders');
        Schema::dropIfExists('proposal_service_category');
        Schema::dropIfExists('proposal_items');
        Schema::dropIfExists('proposals');
        Schema::dropIfExists('clients');

        Schema::table('services', function (Blueprint $table) {
            $table->dropColumn('price');
        });
    }
};
