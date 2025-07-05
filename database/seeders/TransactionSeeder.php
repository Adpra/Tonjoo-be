<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\TransactionHeader;
use App\Models\TransactionDetail;
use App\Models\MsCategory;
use Illuminate\Support\Str;

class TransactionSeeder extends Seeder
{
    public function run(): void
    {
        $categories = MsCategory::pluck('id')->toArray();

        for ($i = 1; $i <= 4; $i++) {
            $header = TransactionHeader::create([
                'description' => 'Transaction description ' . $i,
                'code' => 'TX-' . Str::upper(Str::random(6)),
                'rate_euro' => rand(15000, 17000),
                'date_paid' => now()->subDays(rand(0, 30)),
            ]);

            for ($j = 1; $j <= 5; $j++) {
                TransactionDetail::create([
                    'name' => "Detail {$i}-{$j}",
                    'value_idr' => rand(10000, 500000),
                    'transaction_id' => $header->id,
                    'transaction_category_id' => $categories[array_rand($categories)],
                    'group' => rand(1, 3),
                ]);
            }
        }
    }
}
