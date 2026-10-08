<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('student_memberships', function (Blueprint $table) {
            $table->id();

            $table->string('name', 150);
            $table->string('student_id', 40)->unique();
            $table->unsignedTinyInteger('semester');
            $table->string('department', 10);
            $table->string('phone', 20)->unique();
            $table->string('email', 150)->unique();
            $table->string('track', 30);

            $table->string('status', 20)->default('new')->index();
            $table->text('admin_notes')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_memberships');
    }
};
