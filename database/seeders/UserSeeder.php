<?php

namespace Database\Seeders;

use App\Enums\RoleEnum;
use App\Enums\RoleIdEnum;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
         User::query()->create([
            'name' => 'Admin',
            'email' => 'admin@email.com',
            'password' => bcrypt('password'),
            'role_id' => RoleIdEnum::ADMIN,
            'created_by' => 1
        ]);

        User::query()->create([
            'name' => 'User',
            'email' => 'user@email.com',
            'password' => bcrypt('password'),
            'role_id' => RoleIdEnum::USER,
            'created_by' => 2
        ]);

    }
}
