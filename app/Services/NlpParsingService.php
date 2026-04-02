<?php

namespace App\Services;

use App\Models\Category;
use Carbon\Carbon;

class NlpParsingService
{
    /**
     * Parse a natural language string into transaction data.
     *
     * @param string $text
     * @return array
     */
    public function parse(string $text): array
    {
        $text = strtolower($text);
        
        // 1. Extract Amount
        $amount = $this->extractAmount($text);
        
        // 2. Extract Date
        $date = $this->extractDate($text);
        
        // 3. Extract Type (Income or Expense)
        $type = $this->detectType($text);
        
        // 4. Extract Category
        $category = $this->detectCategory($text, $type);
        
        // 5. Clean Description
        $description = $this->cleanDescription($text, $amount['raw'], $date['raw'], $category['name'] ?? '');

        return [
            'type' => $type,
            'amount' => $amount['value'],
            'category_id' => $category['id'] ?? null,
            'category_name' => $category['name'] ?? 'Lainnya',
            'description' => ucfirst($description),
            'transaction_date' => $date['value'],
        ];
    }

    private function extractAmount(string $text): array
    {
        // Matches: 25rb, 25 ribu, 1jt, 1.5jt, 25000, 25.000
        $regex = '/(\d+[\.,]?\d*)\s*(rb|ribu|k|jt|juta)?/i';
        
        if (preg_match($regex, $text, $matches)) {
            $value = (float) str_replace(',', '.', $matches[1]);
            $unit = strtolower($matches[2] ?? '');
            
            if (in_array($unit, ['rb', 'ribu', 'k'])) {
                $value *= 1000;
            } elseif (in_array($unit, ['jt', 'juta'])) {
                $value *= 1000000;
            }
            
            return ['value' => $value, 'raw' => $matches[0]];
        }
        
        return ['value' => 0, 'raw' => ''];
    }

    private function extractDate(string $text): array
    {
        $now = Carbon::now();

        // 1. Specific Date Format (DD/MM/YYYY or YYYY-MM-DD)
        if (preg_match('/(\d{1,2})[\/\-](\d{1,2})[\/\-](\d{2,4})/', $text, $matches)) {
            try {
                $day = (int)$matches[1];
                $month = (int)$matches[2];
                $year = (int)$matches[3];
                if ($year < 100) $year += 2000;

                // Merge parsed date with current time
                $date = Carbon::create($year, $month, $day, $now->hour, $now->minute, $now->second);
                return ['value' => $date->toDateTimeString(), 'raw' => $matches[0]];
            } catch (\Exception $e) {}
        }

        // 2. Relative Dates (Use sub/add instead of yesterday/tomorrow to preserve time)
        if (str_contains($text, 'kemarin')) {
            return ['value' => $now->copy()->subDay()->toDateTimeString(), 'raw' => 'kemarin'];
        }
        if (str_contains($text, 'bulan lalu')) {
            return ['value' => $now->copy()->subMonth()->toDateTimeString(), 'raw' => 'bulan lalu'];
        }
        if (str_contains($text, 'lusa')) {
            return ['value' => $now->copy()->addDays(2)->toDateTimeString(), 'raw' => 'lusa'];
        }
        if (str_contains($text, 'besok')) {
            return ['value' => $now->copy()->addDay()->toDateTimeString(), 'raw' => 'besok'];
        }

        // 3. Month Names (e.g., 5 Maret, Maret 5)
        $months = [
            'januari' => 1, 'februari' => 2, 'maret' => 3, 'april' => 4, 'mei' => 5, 'juni' => 6,
            'juli' => 7, 'agustus' => 8, 'september' => 9, 'oktober' => 10, 'november' => 11, 'desember' => 12,
            'jan' => 1, 'feb' => 2, 'mar' => 3, 'apr' => 4, 'jun' => 6, 'jul' => 7, 'aug' => 8, 'sep' => 9, 'okt' => 10, 'nov' => 11, 'des' => 12
        ];

        foreach ($months as $name => $num) {
            if (preg_match('/(\d{1,2})\s*' . $name . '/i', $text, $matches)) {
                $date = $now->copy()->setMonth($num)->setDay((int)$matches[1]);
                return ['value' => $date->toDateTimeString(), 'raw' => $matches[0]];
            }
            if (preg_match('/' . $name . '\s*(\d{1,2})/i', $text, $matches)) {
                $date = $now->copy()->setMonth($num)->setDay((int)$matches[1]);
                return ['value' => $date->toDateTimeString(), 'raw' => $matches[0]];
            }
        }

        // 4. Day only (tgl 5)
        if (preg_match('/(tgl|tanggal)\s*(\d{1,2})/i', $text, $matches)) {
            $day = (int) $matches[2];
            $date = $now->copy()->setDay($day);
            return ['value' => $date->toDateTimeString(), 'raw' => $matches[0]];
        }

        return ['value' => $now->toDateTimeString(), 'raw' => ''];
    }

    private function detectType(string $text): string
    {
        // Check for signs
        if (str_starts_with($text, '+')) return 'income';
        if (str_starts_with($text, '-')) return 'expense';

        $incomeKeywords = ['gaji', 'bonus', 'dapat', 'terima', 'masuk', 'pemasukan', 'income', 'pemasukn', 'dpt'];
        foreach ($incomeKeywords as $word) {
            if (str_contains($text, $word)) {
                return 'income';
            }
        }
        return 'expense';
    }

    private function detectCategory(string $text, string $type): array
    {
        $categories = Category::all();
        
        // Direct name match
        foreach ($categories as $category) {
            $name = strtolower($category->name);
            if (str_contains($text, $name)) {
                return ['id' => $category->id, 'name' => $category->name];
            }
        }

        // Keyword mapping
        $mapping = [
            'makan' => 'Makanan', 'minum' => 'Makanan', 'bakso' => 'Makanan', 'nasi' => 'Makanan', 'ayam' => 'Makanan', 'cilok' => 'Makanan', 'cemilan' => 'Makanan', 'snack' => 'Makanan',
            'bensin' => 'Transportasi', 'ojek' => 'Transportasi', 'parkir' => 'Transportasi', 'tol' => 'Transportasi', 'grab' => 'Transportasi', 'gojek' => 'Transportasi',
            'bioskop' => 'Hiburan', 'nonton' => 'Hiburan', 'netflix' => 'Hiburan', 'game' => 'Hiburan',
            'belanja' => 'Belanja', 'baju' => 'Belanja', 'sepatu' => 'Belanja', 'shopee' => 'Belanja', 'tokopedia' => 'Belanja',
            'listrik' => 'Tagihan', 'air' => 'Tagihan', 'wifi' => 'Tagihan', 'pulsa' => 'Tagihan', 'kuota' => 'Tagihan', 'iuran' => 'Tagihan',
            'gaji' => 'Gaji', 'salary' => 'Gaji', 'bonus' => 'Bonus', 'thr' => 'Bonus',
        ];

        foreach ($mapping as $keyword => $categoryName) {
            if (str_contains($text, $keyword)) {
                $cat = Category::where('name', $categoryName)->first();
                if ($cat) return ['id' => $cat->id, 'name' => $cat->name];
            }
        }

        return [];
    }

    private function cleanDescription(string $text, string $amountRaw, string $dateRaw, string $categoryName): string
    {
        // Words to remove
        $toRemove = [
            $amountRaw, 
            $dateRaw, 
            strtolower($categoryName),
            'pemasukan', 'pengeluaran', 'pemasukn', 'pengeularan',
            'beli', 'bayar', 'buat', 'untuk', 'di', 'ke', 'tgl', 'tanggal', 'masuk', 'keluar', 'habis'
        ];
        
        $clean = str_replace($toRemove, '', $text);
        
        // Remove extra spaces
        $clean = trim(preg_replace('/\s+/', ' ', $clean));
        
        // If empty, use category name or original text (limited)
        if (empty($clean)) {
            $clean = $categoryName ?: 'Transaksi';
        }
        
        return ucfirst($clean);
    }
}
