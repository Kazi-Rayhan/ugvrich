<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Manuscripts submitted through the Researcher Portal.
 *
 * A new table, and the reason is worth stating: `publications` already exists,
 * but it is a curated public list — title, authors, venue, year, DOI — with no
 * owner, no status, no files and nothing to review. A manuscript is a piece of
 * work moving through a workflow, which that table cannot represent without
 * being turned into something else.
 *
 * Nothing about the project is copied here. The manuscript points at its
 * project and reads department, field and team through the relation.
 *
 * Reviews are not in this table either: they go in the polymorphic
 * `research_reviews`, the same place idea and proposal reviews go.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('research_manuscripts', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('project_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('assigned_reviewer_id')->nullable()->constrained('users')->nullOnDelete();

            /* ------------------------------------------------ the paper */
            $table->string('title');
            $table->text('abstract');
            $table->json('keywords')->nullable();
            $table->string('manuscript_type')->nullable();     // journal article, conference paper…
            $table->string('department')->nullable();
            $table->string('research_field')->nullable();

            /* --------------------------------------------------- authors */
            $table->string('primary_author')->nullable();
            $table->text('co_authors')->nullable();
            $table->string('corresponding_author')->nullable();
            $table->string('affiliation')->nullable();
            $table->string('orcid')->nullable();

            /* --------------------------------------------------- venue */
            $table->string('target_journal')->nullable();
            $table->string('publisher')->nullable();
            $table->string('issn')->nullable();
            $table->string('journal_url')->nullable();
            $table->string('quartile')->nullable();
            $table->string('impact_factor')->nullable();
            $table->text('journal_scope')->nullable();

            /* --------------------------------------------- declarations */
            $table->string('funding_source')->nullable();
            $table->string('ethics_approval')->nullable();
            $table->text('conflict_of_interest')->nullable();
            $table->text('ai_use_declaration')->nullable();
            $table->string('similarity_report')->nullable();

            /* ------------------------------------------- bibliography */
            $table->text('references_list')->nullable();
            $table->string('citation_style')->nullable();

            /* ------------------------------------------------- files */
            $table->string('manuscript_file')->nullable();
            $table->string('supplementary_file')->nullable();

            /* ---------------------------------------- publication data */
            $table->string('doi')->nullable();
            $table->date('published_on')->nullable();
            $table->string('volume')->nullable();
            $table->string('issue')->nullable();
            $table->string('pages')->nullable();
            $table->string('publication_url')->nullable();

            /* ------------------------------------------------ workflow */
            $table->string('status')->default('draft')->index();
            $table->text('notes')->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('decided_at')->nullable();

            /* A published manuscript is not automatically a public one.
               The repository shows only what somebody has marked public. */
            $table->boolean('is_public')->default(false)->index();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('research_manuscripts');
    }
};
