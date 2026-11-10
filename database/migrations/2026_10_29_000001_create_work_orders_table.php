<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Service work orders (সেবা কার্যাদেশ), filled in on the controlled forms
 * WO-ICT-01 … WO-LAN-01 (config/work_orders.php).
 *
 * The fields the list needs are columns; everything written into the form
 * itself (parties, scope, section 04, schedule, fees, instalments,
 * signatories …) lives in `details`. Only adds a table.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('work_orders', function (Blueprint $table) {
            $table->id();
            $table->string('number', 40)->unique();
            $table->string('sector', 10)->index();
            $table->string('status', 20)->default('draft')->index();
            $table->foreignId('proposal_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('consultancy_request_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('client_id')->nullable()->constrained()->nullOnDelete();
            $table->date('issue_date')->nullable();
            $table->string('client_name', 200)->nullable();
            $table->decimal('total', 12, 2)->default(0);
            $table->json('details')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('work_orders');
    }
};
