<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * The portal's proposals go in the table that already holds proposals.
 *
 * `research_proposals` was built for the public form, where anyone may send one
 * in without an account. A portal proposal is the same thing written by a
 * signed-in researcher and carried forward from an approved idea, so it gains
 * an owner, a link back to that idea, and the sections a full proposal needs —
 * rather than a second table meaning the same word.
 *
 * `user_id` and `research_idea_id` stay nullable: a proposal from the public
 * form has neither, and that is a legitimate row.
 *
 * `assigned_reviewer_id` is added to ideas as well, so a Research Wing member
 * can be put against a submission before anyone has decided anything.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('research_proposals', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable()->after('id')->constrained()->nullOnDelete();
            $table->foreignId('research_idea_id')->nullable()->after('user_id')->constrained()->nullOnDelete();
            $table->foreignId('assigned_reviewer_id')->nullable()->after('status')->constrained('users')->nullOnDelete();

            // The sections a full proposal carries beyond the short public form.
            $table->text('background')->nullable()->after('summary');
            $table->text('research_gap')->nullable()->after('background');
            $table->text('research_questions')->nullable()->after('objectives');
            $table->text('hypothesis')->nullable()->after('research_questions');
            $table->string('study_design')->nullable()->after('methodology');
            $table->text('population')->nullable()->after('study_design');
            $table->text('data_collection')->nullable()->after('population');
            $table->text('data_analysis')->nullable()->after('data_collection');
            $table->text('expected_outcome')->nullable()->after('data_analysis');
            $table->text('expected_impact')->nullable()->after('expected_outcome');
            $table->text('timeline')->nullable()->after('duration');
            $table->string('budget')->nullable()->after('funding_needed');
            $table->text('research_team')->nullable()->after('collaborators_needed');
            $table->string('principal_investigator')->nullable()->after('research_team');
            $table->string('proposal_document')->nullable()->after('document');

            $table->timestamp('submitted_at')->nullable()->after('assigned_reviewer_id');
            $table->timestamp('decided_at')->nullable()->after('submitted_at');
        });

        Schema::table('research_ideas', function (Blueprint $table) {
            $table->foreignId('assigned_reviewer_id')->nullable()->after('status')->constrained('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('research_ideas', function (Blueprint $table) {
            $table->dropConstrainedForeignId('assigned_reviewer_id');
        });

        Schema::table('research_proposals', function (Blueprint $table) {
            $table->dropConstrainedForeignId('user_id');
            $table->dropConstrainedForeignId('research_idea_id');
            $table->dropConstrainedForeignId('assigned_reviewer_id');

            $table->dropColumn([
                'background', 'research_gap', 'research_questions', 'hypothesis',
                'study_design', 'population', 'data_collection', 'data_analysis',
                'expected_outcome', 'expected_impact', 'timeline', 'budget',
                'research_team', 'principal_investigator', 'proposal_document',
                'submitted_at', 'decided_at',
            ]);
        });
    }
};
