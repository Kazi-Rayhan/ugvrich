<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('research_proposals', function (Blueprint $table) {
            $table->string('designation')->nullable();
            $table->string('institution')->nullable();
            $table->string('researcher_department')->nullable();
            $table->string('research_type')->nullable();
            $table->json('co_researchers')->nullable();
            $table->string('external_collaborator')->nullable();
            $table->string('external_department')->nullable();
            $table->string('external_institution')->nullable();
            $table->text('innovation_novelty')->nullable();
            $table->boolean('funding_required')->nullable();
            $table->text('budget_breakdown')->nullable();
            $table->text('funding_source')->nullable();
            $table->boolean('external_funding_applied')->nullable();
            $table->boolean('human_participants')->nullable();
            $table->boolean('sensitive_data')->nullable();
            $table->boolean('ethical_approval_required')->nullable();
            $table->boolean('informed_consent_required')->nullable();
            $table->boolean('ai_used')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('research_proposals', function (Blueprint $table) {
            $table->dropColumn([
                'designation', 'institution', 'researcher_department', 'research_type',
                'co_researchers', 'external_collaborator', 'external_department',
                'external_institution', 'innovation_novelty', 'funding_required',
                'budget_breakdown', 'funding_source', 'external_funding_applied',
                'human_participants', 'sensitive_data', 'ethical_approval_required',
                'informed_consent_required', 'ai_used',
            ]);
        });
    }
};
