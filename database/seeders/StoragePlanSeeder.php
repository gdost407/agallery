<?php

namespace Database\Seeders;

use App\Models\StoragePlan;
use Illuminate\Database\Seeder;

class StoragePlanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        foreach ([['extra-1gb-monthly', 'Extra 1 GB', 1000000000, 9900], ['extra-2gb-monthly', 'Extra 2 GB', 2000000000, 19900], ['extra-5gb-monthly', 'Extra 5 GB', 5000000000, 39900]] as [$code, $name, $bytes, $price]) {
            StoragePlan::firstOrCreate(['code' => $code], [
                'name' => $name,
                'additional_storage_bytes' => $bytes,
                'price_paise' => $price,
            ]);
        }
    }
}
