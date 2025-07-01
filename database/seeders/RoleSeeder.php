<?php

namespace Database\Seeders;

use App\Enums\RoleEnum;
use App\Enums\RoleIdEnum;
use App\Models\Role;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
         Role::query()->create([
            'id' => RoleIdEnum::ADMIN,
            'name' => RoleEnum::ADMIN,
            'description' => 'admin',
        ]);

        Role::query()->create([
            'id' => RoleIdEnum::USER,
            'name' => RoleEnum::USER,
            'description' => 'user',
        ]);
    }

}
