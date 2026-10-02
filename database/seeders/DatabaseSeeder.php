<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'admin@ugv.edu.bd'],
            ['name' => 'RICH Administrator', 'role' => User::ADMIN, 'password' => Hash::make('password')],
        );

        // Roles and permissions first: everything else assumes they are there.
        $this->call([RolePermissionSeeder::class]);

        $this->call([RichContentSeeder::class, RichInnovationSeeder::class, RichResearchSeeder::class, RichServiceDepartmentSeeder::class, RichFacilitySeeder::class, BanglaContentSeeder::class, InnovationRecordSeeder::class, ConsultancyWingSeeder::class]);
    }
}
