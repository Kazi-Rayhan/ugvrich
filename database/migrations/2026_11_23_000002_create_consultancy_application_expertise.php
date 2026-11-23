<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('consultancy_application_expertise', function (Blueprint $table) {
            $table->foreignId('consultancy_application_id')->constrained(indexName: 'consultancy_expertise_application_fk')->cascadeOnDelete();
            $table->foreignId('service_id')->constrained()->cascadeOnDelete();
            $table->primary(['consultancy_application_id', 'service_id']);
        });

        DB::table('consultancy_application_expertise')->insertUsing(
            ['consultancy_application_id', 'service_id'],
            DB::table('consultancy_applications')->select('id', 'expertise_service_id')->whereNotNull('expertise_service_id'),
        );
    }

    public function down(): void
    {
        Schema::dropIfExists('consultancy_application_expertise');
    }
};
