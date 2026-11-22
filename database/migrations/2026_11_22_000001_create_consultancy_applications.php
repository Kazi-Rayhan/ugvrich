<?php

use App\Models\Role;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('consultancy_applications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('service_category_id')->constrained()->restrictOnDelete();
            $table->foreignId('user_id')->nullable()->unique()->constrained()->nullOnDelete();
            $table->foreignId('expert_id')->nullable()->unique()->constrained()->nullOnDelete();
            $table->string('name');
            $table->string('email', 180)->unique();
            $table->string('phone', 40);
            $table->string('location');
            $table->string('occupation');
            $table->json('education');
            $table->json('experience')->nullable();
            $table->json('skills');
            $table->text('bio')->nullable();
            $table->string('status')->default('wing_review')->index();
            $table->json('decisions')->nullable();
            $table->string('contract_type')->nullable();
            $table->string('policy_version')->nullable();
            $table->string('signature_path')->nullable();
            $table->timestamp('signed_at')->nullable();
            $table->timestamps();
        });

        Schema::create('expert_portfolios', function (Blueprint $table) {
            $table->id();
            $table->foreignId('consultancy_application_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->string('path');
            $table->timestamps();
        });

        foreach (['consultancy_wing_reviewer' => 'Consultancy Wing Reviewer', 'consultancy_director' => 'Consultancy Director'] as $name => $label) {
            Role::firstOrCreate(['name' => $name], ['display_name' => $label, 'group' => Role::DASHBOARD]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('expert_portfolios');
        Schema::dropIfExists('consultancy_applications');
    }
};
