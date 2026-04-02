<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            ['name' => 'Gaji', 'icon' => 'payments', 'color' => '#10b981', 'type' => 'income'],
            ['name' => 'Bonus', 'icon' => 'card_giftcard', 'color' => '#22c55e', 'type' => 'income'],
            ['name' => 'Investasi', 'icon' => 'trending_up', 'color' => '#16a34a', 'type' => 'income'],
            ['name' => 'Makanan', 'icon' => 'restaurant', 'color' => '#ef4444', 'type' => 'expense'],
            ['name' => 'Transportasi', 'icon' => 'directions_car', 'color' => '#f59e0b', 'type' => 'expense'],
            ['name' => 'Hiburan', 'icon' => 'sports_esports', 'color' => '#8b5cf6', 'type' => 'expense'],
            ['name' => 'Belanja', 'icon' => 'shopping_bag', 'color' => '#f97316', 'type' => 'expense'],
            ['name' => 'Tagihan', 'icon' => 'receipt_long', 'color' => '#dc2626', 'type' => 'expense'],
            ['name' => 'Tabungan Utama', 'icon' => 'savings', 'color' => '#005bbf', 'type' => 'savings'],
            ['name' => 'Dana Darurat', 'icon' => 'emergency', 'color' => '#fbbf24', 'type' => 'savings'],
            ['name' => 'Lainnya', 'icon' => 'more_horiz', 'color' => '#6b7280', 'type' => 'expense'],
        ];

        foreach ($categories as $category) {
            DB::table('categories')->updateOrInsert(
                ['name' => $category['name']],
                array_merge($category, [
                    'created_at' => now(),
                    'updated_at' => now(),
                ])
            );
        }
    }
}
