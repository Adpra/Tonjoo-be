<?php

namespace Database\Seeders;

use App\Enums\CategoryEnum;
use App\Models\MsCategory;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        MsCategory::query()->create([
            'name' => CategoryEnum::INCOME,
        ]);

        MsCategory::query()->create([
            'name' => CategoryEnum::EXPENSE,
        ]);
    }
}
