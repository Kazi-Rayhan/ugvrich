<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * The Researcher Portal's own tables.
 *
 * `researcher_profiles` is one row per user and holds only what `users` does
 * not: name, email and password stay where they are. `research_ideas` is the
 * first thing a researcher submits. `research_reviews` is deliberately
 * polymorphic — an idea, a proposal and a paper are all reviewed the same way,
 * by possibly several people in turn, and one table means a second reviewer or
 * a new review stage can be added later without another migration.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('researcher_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();

            // Personal
            $table->string('photo')->nullable();
            $table->string('gender')->nullable();
            $table->date('date_of_birth')->nullable();
            $table->string('nationality')->nullable();
            $table->string('country')->nullable();
            $table->string('researcher_scope')->nullable();      // national | international
            $table->string('profession')->nullable();
            $table->string('designation')->nullable();
            $table->string('organization')->nullable();

            // Academic
            $table->string('department')->nullable();
            $table->string('faculty')->nullable();
            $table->string('highest_degree')->nullable();
            $table->text('qualifications')->nullable();
            $table->string('research_experience')->nullable();
            $table->json('research_interests')->nullable();
            $table->json('research_fields')->nullable();
            $table->json('expertise')->nullable();

            // Contact — email lives on `users`; this is everything else.
            $table->string('phone')->nullable();
            $table->string('alternative_phone')->nullable();
            $table->string('address')->nullable();
            $table->string('city')->nullable();

            // Academic profiles elsewhere
            $table->string('orcid')->nullable();
            $table->string('google_scholar')->nullable();
            $table->string('scopus')->nullable();
            $table->string('researchgate')->nullable();
            $table->string('linkedin')->nullable();
            $table->string('website')->nullable();

            // Additional
            $table->text('biography')->nullable();
            $table->text('research_profile')->nullable();
            $table->json('languages')->nullable();
            $table->text('awards')->nullable();
            $table->text('publications')->nullable();

            $table->timestamps();
        });

        Schema::create('research_ideas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();

            $table->string('title');
            $table->string('department');
            $table->string('research_field');
            $table->string('research_area')->nullable();

            $table->text('problem');
            $table->text('description');
            $table->text('motivation')->nullable();
            $table->text('expected_contribution')->nullable();
            $table->json('sdgs')->nullable();
            $table->json('keywords')->nullable();
            $table->text('proposed_team')->nullable();
            $table->string('collaboration_requirement')->nullable();
            $table->string('document')->nullable();

            // draft → submitted → under_review → revision_required → approved
            //       → proposal_development, or rejected
            $table->string('status')->default('draft')->index();
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('decided_at')->nullable();

            $table->timestamps();
        });

        Schema::create('research_reviews', function (Blueprint $table) {
            $table->id();

            // An idea, a proposal or a paper — all reviewed the same way.
            $table->morphs('reviewable');

            $table->foreignId('reviewer_id')->nullable()->constrained('users')->nullOnDelete();

            $table->string('stage')->nullable();       // initial, methodological, ethics…
            $table->string('decision');                // approve | revision | reject | recommend
            $table->text('comment')->nullable();
            $table->text('recommendation')->nullable();
            $table->string('document')->nullable();

            // What the record moved from and to, so the row is a full audit line.
            $table->string('status_from')->nullable();
            $table->string('status_to')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('research_reviews');
        Schema::dropIfExists('research_ideas');
        Schema::dropIfExists('researcher_profiles');
    }
};
