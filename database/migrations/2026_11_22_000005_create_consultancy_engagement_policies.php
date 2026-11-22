<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('consultancy_engagement_policies', function (Blueprint $table) {
            $table->id();
            $table->string('contract_type')->unique();
            $table->string('version');
            $table->text('privacy_notice');
            $table->text('participation_policy');
            $table->timestamps();
        });

        Schema::table('consultancy_applications', function (Blueprint $table) {
            $table->json('engagement_policy')->nullable();
            $table->foreignId('engagement_assigned_by')->nullable()->constrained('users', indexName: 'consultancy_engagement_assigner_fk')->nullOnDelete();
            $table->timestamp('engagement_assigned_at')->nullable();
        });

        $policy = [
            'version' => '2026-11-22.2',
            'privacy_notice' => 'UGV RICH uses your contact details, qualifications and application materials to review your eligibility and administer your consultancy profile. Authorized Consultancy Wing and Director reviewers can see your application and signature. Your public listing includes your name, occupation, category, skills, professional summary and profile photo if provided. Your signature and uploaded portfolio files are stored privately.',
            'participation_policy' => 'Keep your profile accurate and upload only materials you have permission to share. Respect client confidentiality and disclose conflicts of interest to the Consultancy Wing. Accepting these policies activates your listing. Project scope, payment and other engagement terms are agreed separately.',
        ];

        foreach (['long_term', 'freelancer'] as $contract) {
            DB::table('consultancy_engagement_policies')->insert([
                ...$policy,
                'contract_type' => $contract,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        DB::table('consultancy_applications')->whereNotNull('contract_type')->whereNull('signed_at')
            ->update(['engagement_policy' => json_encode($policy, JSON_THROW_ON_ERROR)]);
    }

    public function down(): void
    {
        Schema::table('consultancy_applications', function (Blueprint $table) {
            $table->dropForeign('consultancy_engagement_assigner_fk');
            $table->dropColumn(['engagement_policy', 'engagement_assigned_by', 'engagement_assigned_at']);
        });
        Schema::dropIfExists('consultancy_engagement_policies');
    }
};
