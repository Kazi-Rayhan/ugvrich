<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Where the money comes from.
 *
 * Funding was one undifferentiated thing. It is really two: the university's
 * own money, and somebody else's — a ministry, a donor, a UN agency working to
 * an SDG, an industry partner. They are approved differently, reported
 * differently and asked for differently, so a decision now says which it is and,
 * where it is external, who the funder is.
 *
 * The proposal gains the researcher's side of the same question: what they are
 * asking for, before anybody has decided anything.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('research_fundings', function (Blueprint $table) {
            $table->string('source_type')->default('internal')->after('status')->index();
            $table->string('organization')->nullable()->after('source');
            $table->string('organization_type')->nullable()->after('organization');
        });

        Schema::table('research_proposals', function (Blueprint $table) {
            $table->string('funding_type')->nullable()->after('funding_needed');
            $table->string('funding_organization')->nullable()->after('funding_type');
        });
    }

    public function down(): void
    {
        Schema::table('research_fundings', function (Blueprint $table) {
            $table->dropColumn(['source_type', 'organization', 'organization_type']);
        });

        Schema::table('research_proposals', function (Blueprint $table) {
            $table->dropColumn(['funding_type', 'funding_organization']);
        });
    }
};
