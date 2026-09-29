<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * The innovations shown on /innovation, so they can be maintained in the admin.
 *
 * The page began as a faithful rendering of the Innovation Wing proposal, held
 * in config/innovation_framework.php. That file stays: it is the document of
 * record and the fallback. These rows sit on top of it — once a phase has rows,
 * the page reads them instead, and the wing can add the next innovation without
 * a deployment.
 *
 * The shape follows the document rather than a generic "project": the current
 * innovations carry a lead line and a highlights paragraph, while the proposed
 * ones are written up as concept / how / why, with departments and SDGs beside
 * them. Both live in one table, told apart by `phase`.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('innovations', function (Blueprint $table) {
            $table->id();

            $table->string('phase')->default('current')->index();
            $table->string('number')->nullable();

            $table->string('name');
            $table->string('subtitle')->nullable();
            $table->string('tagline')->nullable();

            // Current innovations: who leads it, and where it stands.
            $table->string('lead')->nullable();
            $table->text('highlights')->nullable();

            // Proposed innovations: the three-part write-up.
            $table->text('concept')->nullable();
            $table->text('how')->nullable();
            $table->text('why')->nullable();
            $table->text('departments')->nullable();
            $table->string('sdg')->nullable();

            // Bangla twins; blank ones fall back to the English text.
            $table->string('name_bn')->nullable();
            $table->string('subtitle_bn')->nullable();
            $table->string('tagline_bn')->nullable();
            $table->string('lead_bn')->nullable();
            $table->text('highlights_bn')->nullable();
            $table->text('concept_bn')->nullable();
            $table->text('how_bn')->nullable();
            $table->text('why_bn')->nullable();
            $table->text('departments_bn')->nullable();
            $table->string('sdg_bn')->nullable();

            $table->string('image')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('innovations');
    }
};
