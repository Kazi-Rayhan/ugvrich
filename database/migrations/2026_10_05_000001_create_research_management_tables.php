<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Funding, projects and the project lifecycle.
 *
 * `projects` is extended rather than replaced. It already carries a code, a
 * title, a department, a lead, a team, dates, progress, status and a budget —
 * which is most of a research project — and the public site reads it. A second
 * "projects" table would mean two answers to the same question.
 *
 * What it gains: the proposal it came from, the researcher who owns it, the
 * lifecycle stages, and a visibility flag. That last one matters: a project
 * created in the portal must not appear on the public site until somebody
 * decides it should, so it starts internal and existing rows stay public.
 *
 * Funding is its own table because a proposal may be funded in parts, from
 * more than one source, and the decision has to be recorded whether or not it
 * is favourable. Approving a proposal does not fund it.
 */
return new class extends Migration
{
    public function up(): void
    {
        /* ----------------------------------------------------- funding */
        Schema::create('research_fundings', function (Blueprint $table) {
            $table->id();

            $table->foreignId('research_proposal_id')->constrained()->cascadeOnDelete();
            $table->foreignId('decided_by')->nullable()->constrained('users')->nullOnDelete();

            $table->string('status')->default('pending')->index();   // pending|approved|partial|unfunded
            $table->string('source')->nullable();
            $table->string('type')->nullable();                      // internal, seed, external…

            $table->decimal('requested_amount', 14, 2)->nullable();
            $table->decimal('approved_amount', 14, 2)->nullable();
            $table->string('currency', 8)->default('BDT');

            $table->date('starts_on')->nullable();
            $table->date('ends_on')->nullable();

            $table->text('decision')->nullable();
            $table->text('notes')->nullable();
            $table->string('document')->nullable();

            $table->timestamp('decided_at')->nullable();
            $table->timestamps();
        });

        /* --------------------------------------- funding opportunities */
        Schema::create('funding_opportunities', function (Blueprint $table) {
            $table->id();

            $table->string('title');
            $table->string('slug')->unique();
            $table->string('category')->index();      // internal_seed, faculty, student, national…
            $table->string('organization')->nullable();

            $table->date('deadline')->nullable();
            $table->string('amount')->nullable();
            $table->string('research_area')->nullable();
            $table->string('department')->nullable();

            $table->text('description')->nullable();
            $table->text('eligibility')->nullable();
            $table->text('how_to_apply')->nullable();
            $table->string('external_url')->nullable();
            $table->string('document')->nullable();

            $table->boolean('is_active')->default(true)->index();
            $table->unsignedInteger('sort_order')->default(0);

            $table->timestamps();
        });

        /* ------------------------------------- the research project side */
        Schema::table('projects', function (Blueprint $table) {
            $table->foreignId('research_proposal_id')->nullable()->after('id')->constrained()->nullOnDelete();
            $table->foreignId('user_id')->nullable()->after('research_proposal_id')->constrained()->nullOnDelete();

            /* Projects already on the site are public; one created from a
               proposal is internal until somebody publishes it. */
            $table->string('visibility')->default('public')->after('status')->index();

            // Where the work has got to, beyond the overall status.
            $table->string('ethics_status')->nullable();
            $table->string('data_collection_status')->nullable();
            $table->string('analysis_status')->nullable();
            $table->string('manuscript_status')->nullable();
        });

        Schema::create('project_updates', function (Blueprint $table) {
            $table->id();

            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();

            $table->unsignedTinyInteger('progress')->nullable();
            $table->string('status')->nullable();
            $table->text('comment');
            $table->string('document')->nullable();

            $table->timestamps();
        });

        Schema::create('project_documents', function (Blueprint $table) {
            $table->id();

            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();

            $table->string('kind')->index();          // approval, ethics, proposal, data, analysis…
            $table->string('title');
            $table->string('file');
            $table->text('notes')->nullable();

            // Research documents are private unless somebody says otherwise.
            $table->boolean('is_public')->default(false);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('project_documents');
        Schema::dropIfExists('project_updates');
        Schema::dropIfExists('funding_opportunities');
        Schema::dropIfExists('research_fundings');

        Schema::table('projects', function (Blueprint $table) {
            $table->dropConstrainedForeignId('research_proposal_id');
            $table->dropConstrainedForeignId('user_id');
            $table->dropColumn([
                'visibility', 'ethics_status', 'data_collection_status',
                'analysis_status', 'manuscript_status',
            ]);
        });
    }
};
