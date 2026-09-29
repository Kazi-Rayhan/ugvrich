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
            ['name' => 'RICH Administrator', 'password' => Hash::make('password')],
        );

        $this->call([RichContentSeeder::class, RichInnovationSeeder::class, RichResearchSeeder::class, RichServiceDepartmentSeeder::class, RichFacilitySeeder::class, BanglaContentSeeder::class, InnovationRecordSeeder::class]);
    }
}
