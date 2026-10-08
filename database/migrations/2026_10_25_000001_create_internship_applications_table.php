<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Internship applications from the public /internship form: who is applying,
 * where they study, and what internship they want (track, duration, start,
 * mode), with their CV. Handled from the admin panel.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('internship_applications', function (Blueprint $table) {
            $table->id();

            // About the applicant
            $table->string('name', 150);
            $table->string('email', 180)->index();
            $table->string('phone', 20);

            // Academic
            $table->string('university', 180);
            $table->string('department', 150);
            $table->string('programme', 150);
            $table->string('year_level', 20);
            $table->string('student_id', 40)->nullable();
            $table->string('cgpa', 10)->nullable();

            // The internship
            $table->string('track', 30)->index();
            $table->unsignedTinyInteger('duration_months');
            $table->date('start_date');
            $table->string('mode', 20);
            $table->text('skills')->nullable();
            $table->text('motivation');
            $table->string('cv');
            $table->string('portfolio_url', 300)->nullable();

            // Handling
            $table->string('status', 20)->default('new')->index();
            $table->text('admin_notes')->nullable();
            $table->timestamp('reviewed_at')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('internship_applications');
    }
};
