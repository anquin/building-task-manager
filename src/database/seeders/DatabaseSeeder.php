<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Building;
use Illuminate\Support\Facades\Hash;
use App\Enums\UserRole;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $buildingId = '6d92e1c3-61c7-4bcc-ba30-532a6193a332';

        // Create building if not exists
        $building = Building::firstOrCreate([
            'id' => $buildingId,
        ], [
            'name' => 'Main Building',
        ]);

        // Create Owner user
        User::firstOrCreate([
            'email' => 'owner@example.com',
        ], [
            'id' => '518c5881-6e37-4394-937d-554537a50a3e',
            'name' => 'Owner',
            'password' => Hash::make('owner'),
            'building_id' => $building->id,
            'role' => UserRole::OWNER->value,
        ]);

        // Create Employee user
        User::firstOrCreate([
            'email' => 'employee@example.com',
        ], [
            'id' => 'b7d5066b-78fc-48ca-9d0f-4a41f491a287',
            'name' => 'Employee',
            'password' => Hash::make('employee'),
            'building_id' => $building->id,
            'role' => UserRole::EMPLOYEE->value,
        ]);
    }
}
