<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('experts', function (Blueprint $table) {
            $table->longText('bio_html')->nullable();
            $table->longText('bio_html_bn')->nullable();
            $table->json('professional_skills')->nullable();
        });

        DB::table('consultancy_applications')->whereNotNull('expert_id')->select('id', 'expert_id', 'skills')
            ->chunkById(100, function ($applications): void {
                foreach ($applications as $application) {
                    $skills = array_column(json_decode($application->skills, true, flags: JSON_THROW_ON_ERROR) ?? [], 'name');
                    DB::table('experts')->where('id', $application->expert_id)
                        ->update(['professional_skills' => json_encode($skills, JSON_THROW_ON_ERROR)]);
                }
            });
    }

    public function down(): void
    {
        Schema::table('experts', function (Blueprint $table) {
            $table->dropColumn(['bio_html', 'bio_html_bn', 'professional_skills']);
        });
    }
};
