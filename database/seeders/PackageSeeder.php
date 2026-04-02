<?php

namespace Database\Seeders;

use App\Models\Package;
use Illuminate\Database\Seeder;

class PackageSeeder extends Seeder
{
    public function run(): void
    {
        Package::create([
            'name' => '1 Bulan Premium',
            'duration_days' => 30,
            'price' => 29000,
        ]);

        Package::create([
            'name' => '3 Bulan Hemat',
            'duration_days' => 90,
            'price' => 75000,
        ]);

        Package::create([
            'name' => '1 Tahun Super Hemat',
            'duration_days' => 365,
            'price' => 249000,
        ]);
    }
}
