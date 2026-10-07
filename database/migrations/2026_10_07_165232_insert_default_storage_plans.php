<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $timestamp = now();

        foreach ([['extra-1gb-monthly', 'Extra 1 GB', 1000000000, 9900], ['extra-2gb-monthly', 'Extra 2 GB', 2000000000, 19900], ['extra-5gb-monthly', 'Extra 5 GB', 5000000000, 39900]] as [$code, $name, $bytes, $price]) {
            DB::table('storage_plans')->insert([
                'code' => $code,
                'name' => $name,
                'additional_storage_bytes' => $bytes,
                'price_paise' => $price,
                'currency' => 'INR',
                'billing_interval' => 'month',
                'is_active' => true,
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ]);
        }
    }

    public function down(): void
    {
        DB::table('storage_plans')->whereIn('code', ['extra-1gb-monthly', 'extra-2gb-monthly', 'extra-5gb-monthly'])->delete();
    }
};
