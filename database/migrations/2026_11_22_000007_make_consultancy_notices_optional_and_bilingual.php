<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('consultancy_engagement_policies', function (Blueprint $table) {
            $table->text('privacy_notice')->nullable()->change();
            $table->text('participation_policy')->nullable()->change();
            $table->text('privacy_notice_bn')->nullable();
            $table->text('participation_policy_bn')->nullable();
        });

        DB::table('consultancy_engagement_policies')->where('version', '2026-11-22.2')->update([
            'privacy_notice' => null,
            'participation_policy' => null,
            'version' => (string) Str::ulid(),
        ]);
    }

    public function down(): void
    {
        DB::table('consultancy_engagement_policies')->whereNull('privacy_notice')->update(['privacy_notice' => '']);
        DB::table('consultancy_engagement_policies')->whereNull('participation_policy')->update(['participation_policy' => '']);

        Schema::table('consultancy_engagement_policies', function (Blueprint $table) {
            $table->dropColumn(['privacy_notice_bn', 'participation_policy_bn']);
            $table->text('privacy_notice')->nullable(false)->change();
            $table->text('participation_policy')->nullable(false)->change();
        });
    }
};
