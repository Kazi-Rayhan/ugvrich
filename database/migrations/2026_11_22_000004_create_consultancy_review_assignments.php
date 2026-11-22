<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('consultancy_review_stages', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
        });

        Schema::create('consultancy_review_stage_user', function (Blueprint $table) {
            $table->foreignId('consultancy_review_stage_id')->constrained(indexName: 'consultancy_reviewer_stage_fk')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained(indexName: 'consultancy_reviewer_user_fk')->cascadeOnDelete();
            $table->primary(['consultancy_review_stage_id', 'user_id'], 'consultancy_reviewer_pk');
        });

        foreach (['wing_review' => 'consultancy_wing_reviewer', 'director_review' => 'consultancy_director'] as $stage => $role) {
            $stageId = DB::table('consultancy_review_stages')->insertGetId(['key' => $stage]);
            $roleId = DB::table('roles')->where('name', $role)->value('id');
            if ($roleId === null) {
                continue;
            }

            $users = DB::table('users')->selectRaw('? as stage_id, users.id', [$stageId])
                ->where(function ($query) use ($roleId) {
                    $query->where('users.role_id', $roleId)
                        ->orWhereExists(function ($roles) use ($roleId) {
                            $roles->selectRaw('1')->from('role_user')
                                ->whereColumn('role_user.user_id', 'users.id')->where('role_user.role_id', $roleId);
                        });
                });

            DB::table('consultancy_review_stage_user')->insertUsing(['consultancy_review_stage_id', 'user_id'], $users);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('consultancy_review_stage_user');
        Schema::dropIfExists('consultancy_review_stages');
    }
};
