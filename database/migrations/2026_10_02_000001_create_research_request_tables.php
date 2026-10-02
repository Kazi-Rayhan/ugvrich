<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * The two things a researcher can send the Research Wing.
 *
 * Kept as separate tables rather than one "request" table with a type column:
 * a support request and a proposal are answered by different people, carry
 * almost no fields in common, and are triaged on different timescales.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('research_supports', function (Blueprint $table) {
            $table->id();

            $table->string('name');
            $table->string('email');
            $table->string('phone')->nullable();
            $table->string('role')->nullable();
            $table->string('department')->nullable();

            // Which of the support desk's services are being asked for.
            $table->json('support_types');

            $table->string('title');
            $table->text('details');
            $table->string('stage')->nullable();
            $table->date('needed_by')->nullable();
            $table->string('document')->nullable();

            $table->string('status')->default('new')->index();
            $table->text('admin_notes')->nullable();
            $table->timestamp('reviewed_at')->nullable();

            $table->timestamps();
        });

        Schema::create('research_proposals', function (Blueprint $table) {
            $table->id();

            $table->string('name');
            $table->string('email');
            $table->string('phone')->nullable();
            $table->string('role')->nullable();

            // The three steps of the cascade, from the research framework.
            $table->string('department');
            $table->string('research_field');
            $table->string('research_area')->nullable();

            $table->string('title');
            $table->text('summary');
            $table->text('objectives')->nullable();
            $table->text('methodology')->nullable();
            $table->string('duration')->nullable();
            $table->string('collaborators_needed')->nullable();
            $table->string('funding_needed')->nullable();
            $table->json('sdgs')->nullable();
            $table->string('document')->nullable();

            $table->string('status')->default('new')->index();
            $table->text('admin_notes')->nullable();
            $table->timestamp('reviewed_at')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('research_proposals');
        Schema::dropIfExists('research_supports');
    }
};
