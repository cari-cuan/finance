<?php

namespace App\Services;

use App\Models\Category;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GroqService
{
    protected $apiKey;

    protected $model;

    public function __construct()
    {
        $this->apiKey = config('services.groq.api_key');
        $this->model = config('services.groq.model', 'llama-3.1-8b-instant');
    }

    public function chat(string $message, array $history = []): array
    {
        $lowerMessage = strtolower($message);

        if ($lowerMessage === 'ok' && session()->has('pending_transaction')) {
            return $this->confirmTransaction();
        }

        if ($lowerMessage === 'batal' && session()->has('pending_transaction')) {
            session()->forget('pending_transaction');

            return [
                'message' => 'Transaksi dibatalkan. Ada yang bisa saya bantu lagi?',
                'quick_replies' => [],
            ];
        }

        $monthYear = $this->detectMonthYear($message);

        try {
            $financialContext = $this->getFinancialContext($monthYear['month'], $monthYear['year']);
            $categories = Category::all(['id', 'name', 'type', 'icon', 'color']);

            $systemPrompt = $this->buildSystemPrompt($categories, $financialContext, $monthYear);

            $messages = [
                ['role' => 'system', 'content' => $systemPrompt],
            ];

            foreach ($history as $msg) {
                $messages[] = ['role' => $msg['role'], 'content' => $msg['content']];
            }

            $messages[] = ['role' => 'user', 'content' => $message];

            $response = Http::withHeaders([
                'Authorization' => 'Bearer '.$this->apiKey,
                'Content-Type' => 'application/json',
            ])->post('https://api.groq.com/openai/v1/chat/completions', [
                'model' => $this->model,
                'messages' => $messages,
                'temperature' => 0.3,
                'max_tokens' => 800,
            ]);

            if ($response->successful()) {
                $content = $response->json('choices.0.message.content');

                return $this->parseResponse($content);
            }
        } catch (\Exception $e) {
            Log::error('Groq API error: '.$e->getMessage());
        }

        return $this->fallbackNlp($message);
    }

    protected function detectMonthYear(string $message): array
    {
        $now = Carbon::now();
        $lower = strtolower($message);

        $months = [
            'januari' => 1, 'februari' => 2, 'maret' => 3, 'april' => 4, 'mei' => 5, 'juni' => 6,
            'juli' => 7, 'agustus' => 8, 'september' => 9, 'oktober' => 10, 'november' => 11, 'desember' => 12,
            'jan' => 1, 'feb' => 2, 'mar' => 3, 'apr' => 4, 'jun' => 6, 'jul' => 7, 'agu' => 8, 'sep' => 9, 'okt' => 10, 'nov' => 11, 'des' => 12,
        ];

        $month = null;
        $year = $now->year;

        foreach ($months as $name => $num) {
            if (str_contains($lower, $name)) {
                $month = $num;
                break;
            }
        }

        if (preg_match('/\b(20\d{2})\b/', $message, $matches)) {
            $year = (int) $matches[1];
        }

        if (str_contains($lower, 'bulan ini') || str_contains($lower, 'bulan sekarang')) {
            $month = $now->month;
        } elseif (str_contains($lower, 'bulan lalu')) {
            $month = $now->copy()->subMonth()->month;
            if ($month === 12) {
                $year = $now->year - 1;
            }
        }

        if (! $month) {
            $month = $now->month;
        }

        return ['month' => $month, 'year' => $year];
    }

    protected function getFinancialContext(int $month, int $year): array
    {
        $userId = auth()->id();
        $now = Carbon::now();
        $monthName = Carbon::create()->month($month)->translatedFormat('F');

        $totalIncome = Transaction::where('user_id', $userId)->where('type', 'income')->sum('amount');
        $totalExpense = Transaction::where('user_id', $userId)->where('type', 'expense')->sum('amount');
        $balance = $totalIncome - $totalExpense;

        $monthIncome = Transaction::where('user_id', $userId)
            ->where('type', 'income')
            ->whereMonth('transaction_date', $month)
            ->whereYear('transaction_date', $year)
            ->sum('amount');

        $monthExpense = Transaction::where('user_id', $userId)
            ->where('type', 'expense')
            ->whereMonth('transaction_date', $month)
            ->whereYear('transaction_date', $year)
            ->sum('amount');

        $monthTxCount = Transaction::where('user_id', $userId)
            ->whereMonth('transaction_date', $month)
            ->whereYear('transaction_date', $year)
            ->count();

        $savingsRate = $monthIncome > 0 ? round((($monthIncome - $monthExpense) / $monthIncome) * 100, 1) : 0;

        $recentTransactions = Transaction::with('category')
            ->where('user_id', $userId)
            ->orderByDesc('transaction_date')
            ->limit(10)
            ->get()
            ->map(function ($t) {
                return [
                    'type' => $t->type,
                    'amount' => $t->amount,
                    'description' => $t->description ?: '-',
                    'category' => $t->category?->name ?? '-',
                    'date' => Carbon::parse($t->transaction_date)->translatedFormat('d M Y'),
                ];
            });

        $topCategories = Transaction::selectRaw('category_id, type, SUM(amount) as total')
            ->where('user_id', $userId)
            ->whereMonth('transaction_date', $month)
            ->whereYear('transaction_date', $year)
            ->groupBy('category_id', 'type')
            ->orderByDesc('total')
            ->limit(5)
            ->with('category')
            ->get()
            ->map(function ($t) {
                return [
                    'category' => $t->category?->name ?? '-',
                    'type' => $t->type,
                    'total' => $t->total,
                ];
            });

        $allTransactions = Transaction::with('category')
            ->where('user_id', $userId)
            ->orderByDesc('transaction_date')
            ->limit(50)
            ->get()
            ->map(function ($t) {
                return [
                    'date' => Carbon::parse($t->transaction_date)->translatedFormat('d M Y'),
                    'type' => $t->type,
                    'category' => $t->category?->name ?? '-',
                    'description' => $t->description ?: '-',
                    'amount' => $t->amount,
                ];
            });

        $categorySummary = Transaction::selectRaw('category_id, type, SUM(amount) as total, COUNT(*) as count')
            ->where('user_id', $userId)
            ->groupBy('category_id', 'type')
            ->orderByDesc('total')
            ->with('category')
            ->get()
            ->map(function ($t) {
                $typeLabel = $t->type === 'income' ? 'Pemasukan' : 'Pengeluaran';
                $cat = $t->category?->name ?? '-';

                return [
                    'category' => $cat,
                    'type' => $typeLabel,
                    'total' => $t->total,
                    'count' => $t->count,
                ];
            });

        return [
            'total_income' => $totalIncome,
            'total_expense' => $totalExpense,
            'balance' => $balance,
            'month_income' => $monthIncome,
            'month_expense' => $monthExpense,
            'month_name' => $monthName,
            'year' => $year,
            'month_tx_count' => $monthTxCount,
            'savings_rate' => $savingsRate,
            'recent_transactions' => $recentTransactions,
            'top_categories' => $topCategories,
            'all_transactions' => $allTransactions,
            'category_summary' => $categorySummary,
        ];
    }

    protected function buildSystemPrompt($categories, $context, $monthYear): string
    {
        $catList = $categories->map(function ($cat) {
            $typeLabel = $cat->type === 'income' ? 'Pemasukan' : 'Pengeluaran';

            return "- ID: {$cat->id}, Nama: {$cat->name} ({$typeLabel})";
        })->implode("\n");

        $ctx = $context;
        $incomeFmt = number_format($ctx['total_income'], 0, ',', '.');
        $expenseFmt = number_format($ctx['total_expense'], 0, ',', '.');
        $balanceFmt = number_format($ctx['balance'], 0, ',', '.');
        $monthIncomeFmt = number_format($ctx['month_income'], 0, ',', '.');
        $monthExpenseFmt = number_format($ctx['month_expense'], 0, ',', '.');
        $monthName = $ctx['month_name'];
        $year = $ctx['year'];
        $txCount = $ctx['month_tx_count'];
        $savingsRate = $ctx['savings_rate'];

        $analysis = '';
        if ($ctx['month_income'] > 0) {
            if ($savingsRate >= 30) {
                $analysis = "🌟 Kamu RAJIN MENABUNG! Tingkat tabungan {$savingsRate}% dari pemasukan.";
            } elseif ($savingsRate >= 10) {
                $analysis = "👍 Cukup baik. Tingkat tabungan {$savingsRate}%. Bisa ditingkatkan lagi.";
            } elseif ($savingsRate >= 0) {
                $analysis = "⚠️ Cukup boros. Hanya {$savingsRate}% yang ditabung. Coba kurangi pengeluaran tidak penting.";
            } else {
                $analysis = '🚨 BOROS! Pengeluaran melebihi pemasukan. Segera evaluasi keuanganmu!';
            }
        } else {
            $analysis = '📊 Belum ada data pemasukan bulan ini.';
        }

        $recentTx = $ctx['recent_transactions']->map(function ($t) {
            $emoji = $t['type'] === 'income' ? '💰' : '💸';

            return "{$emoji} {$t['date']} | {$t['category']} | {$t['description']} | Rp ".number_format($t['amount'], 0, ',', '.');
        })->implode("\n") ?: 'Belum ada transaksi.';

        $topCat = $ctx['top_categories']->map(function ($t) {
            $emoji = $t['type'] === 'income' ? '💰' : '💸';

            return "{$emoji} {$t['category']}: Rp ".number_format($t['total'], 0, ',', '.');
        })->implode("\n") ?: 'Belum ada data.';

        $txTableRows = $ctx['all_transactions']->map(function ($t) {
            $typeLabel = $t['type'] === 'income' ? 'Pemasukan' : 'Pengeluaran';

            return "| {$t['date']} | {$typeLabel} | {$t['category']} | {$t['description']} | Rp ".number_format($t['amount'], 0, ',', '.').' |';
        })->implode("\n") ?: '| - | - | - | - | - |';

        $catTableRows = $ctx['category_summary']->map(function ($t) {
            return "| {$t['category']} | {$t['type']} | Rp ".number_format($t['total'], 0, ',', '.')." | {$t['count']}x |";
        })->implode("\n") ?: '| - | - | - | - |';

        return <<<PROMPT
Kamu adalah asisten keuangan keluarga. Bahasa: Indonesia.

📅 PERIODE: {$monthName} {$year}

💵 Saldo: Rp {$balanceFmt}
📈 Pemasukan Total: Rp {$incomeFmt}
📉 Pengeluaran Total: Rp {$expenseFmt}

📊 {$monthName} {$year} ({$txCount} transaksi):
💰 Pemasukan: Rp {$monthIncomeFmt}
💸 Pengeluaran: Rp {$monthExpenseFmt}
🏦 Tabungan: {$savingsRate}%

🔍 {$analysis}

📋 Transaksi Terakhir:
{$recentTx}

📈 Kategori Teratas:
{$topCat}

📊 TABEL TRANSAKSI:
| Tanggal | Tipe | Kategori | Keterangan | Nominal |
|---------|------|----------|------------|---------|
{$txTableRows}

📊 RINGKASAN PER KATEGORI:
| Kategori | Tipe | Total | Frekuensi |
|----------|------|-------|-----------|
{$catTableRows}

ATURAN:
1. Jika user minta laporan → tampilkan ringkasan dengan emoji & analisis. JANGAN return JSON.
2. Jika user tanya saldo/pemasukan/pengeluaran → jawab langsung dengan data di atas.
3. Jika user catat transaksi (ada nominal) → return JSON format di bawah.
4. Jika user tanya hal lain → jawab ramah, JANGAN return JSON.

FORMAT JSON HANYA UNTUK PENCATATAN TRANSAKSI:
{"action":"record_transaction","type":"income/expense","amount":25000,"category_id":1,"category_name":"Makanan","description":"Makan siang","transaction_date":"2026-04-02 12:00:00","confirmation_message":"Oke, saya catat:\n━━━━━━━━━━━━━━━━\n💸 Pengeluaran\n📦 Makanan\n📝 Makan siang\n💵 Rp 25.000\n🕐 02 Apr 2026, 12:00\n━━━━━━━━━━━━━━━━\nSudah benar? Ketik OK untuk simpan."}

DAFTAR KATEGORI:
{$catList}

PENTING:
- Default type = "expense" kecuali user sebut "pemasukan", "gaji", "masuk", "dapat"
- "rb"/"ribu"/"k" = ×1000, "jt"/"juta" = ×1000000
- Jika tidak yakin kategori, gunakan "Lainnya"
- Gunakan emoji: 💰💸📦📝💵🕐📊📈📉🏦🔍🌟👍⚠️🚨
- JANGAN pernah mengarang data. Gunakan data yang diberikan.
PROMPT;
    }

    protected function parseResponse(string $content): array
    {
        $parsed = $this->extractJson($content);

        if ($parsed && isset($parsed['action'])) {
            if ($parsed['action'] === 'record_transaction') {
                return $this->prepareConfirmation($parsed);
            }
            if ($parsed['action'] === 'reply') {
                return [
                    'message' => $parsed['reply'] ?? $content,
                    'quick_replies' => $parsed['quick_replies'] ?? [],
                ];
            }
        }

        return [
            'message' => $content,
            'quick_replies' => [],
        ];
    }

    protected function extractJson(string $content): ?array
    {
        // Try code block first
        if (preg_match('/```json\s*(.*?)\s*```/s', $content, $m)) {
            $fixed = $this->fixJsonNewlines($m[1]);
            $d = json_decode($fixed, true);
            if ($d) {
                return $d;
            }
        }

        // Find JSON with action key using regex
        if (preg_match('/\{[^{}]*"action"\s*:\s*"[^"]*"[^{}]*\}/s', $content, $m)) {
            $fixed = $this->fixJsonNewlines($m[0]);
            $d = json_decode($fixed, true);
            if ($d) {
                return $d;
            }
        }

        // Find balanced braces
        $start = strpos($content, '{');
        if ($start === false) {
            return null;
        }

        $depth = 0;
        $inStr = false;
        $esc = false;

        for ($i = $start; $i < strlen($content); $i++) {
            $c = $content[$i];

            if ($esc) {
                $esc = false;

                continue;
            }
            if ($c === '\\') {
                $esc = true;

                continue;
            }
            if ($c === '"') {
                $inStr = ! $inStr;

                continue;
            }
            if ($inStr) {
                continue;
            }

            if ($c === '{') {
                $depth++;
            }
            if ($c === '}') {
                $depth--;
                if ($depth === 0) {
                    $jsonStr = substr($content, $start, $i - $start + 1);
                    $fixed = $this->fixJsonNewlines($jsonStr);
                    $d = json_decode($fixed, true);
                    if ($d && isset($d['action'])) {
                        return $d;
                    }

                    $nextStart = strpos($content, '{', $start + 1);
                    if ($nextStart !== false) {
                        $start = $nextStart;
                        $depth = 0;

                        continue;
                    }

                    return null;
                }
            }
        }

        return null;
    }

    protected function fixJsonNewlines(string $json): string
    {
        $result = '';
        $inStr = false;
        $esc = false;

        for ($i = 0; $i < strlen($json); $i++) {
            $c = $json[$i];

            if ($esc) {
                $result .= $c;
                $esc = false;

                continue;
            }

            if ($c === '\\') {
                $result .= $c;
                $esc = true;

                continue;
            }

            if ($c === '"') {
                $inStr = ! $inStr;
                $result .= $c;

                continue;
            }

            if ($inStr && ($c === "\n" || $c === "\r")) {
                $result .= '\\n';

                continue;
            }

            $result .= $c;
        }

        return $result;
    }

    protected function prepareConfirmation(array $parsed): array
    {
        session(['pending_transaction' => $parsed]);

        return [
            'message' => $parsed['confirmation_message'] ?? 'Transaksi siap disimpan.',
            'quick_replies' => ['OK', 'batal'],
            'parsed' => $parsed,
        ];
    }

    protected function confirmTransaction(): array
    {
        $data = session('pending_transaction');
        Transaction::create([
            'user_id' => auth()->id(),
            'category_id' => $data['category_id'],
            'type' => $data['type'],
            'amount' => $data['amount'],
            'description' => $data['description'],
            'transaction_date' => $data['transaction_date'],
        ]);
        session()->forget('pending_transaction');

        return [
            'message' => 'Transaksi berhasil disimpan! ✅',
            'quick_replies' => [],
        ];
    }

    protected function fallbackNlp(string $message): array
    {
        $lower = strtolower($message);
        $hasAmount = preg_match('/\d+\s*(rb|ribu|k|jt|juta)?/i', $lower);
        $isTransaction = $hasAmount || preg_match('/(beli|bayar|makan|minum|bensin|gaji|masuk|keluar)/i', $lower);

        if (! $isTransaction) {
            return [
                'message' => 'Halo! 👋 Saya bisa bantu catat transaksi atau jawab pertanyaan keuangan kamu.',
                'quick_replies' => ['Makan siang 25rb', 'Gaji masuk 5jt'],
            ];
        }

        $nlp = new NlpParsingService;
        $parsed = $nlp->parse($message);

        if (! $parsed || ! isset($parsed['amount']) || $parsed['amount'] <= 0) {
            return [
                'message' => 'Maaf, saya tidak menemukan nominal. Coba format "Makan siang 25rb"?',
                'quick_replies' => [],
            ];
        }

        session(['pending_transaction' => $parsed]);
        $amountFmt = 'Rp '.number_format($parsed['amount'], 0, ',', '.');
        $dateFmt = Carbon::parse($parsed['transaction_date'])->translatedFormat('d M Y, H:i');
        $desc = trim(preg_replace('/^[-+]/', '', preg_replace('/#manual/i', '', $parsed['description']))) ?: 'Transaksi Manual';
        $emoji = $parsed['type'] === 'income' ? '💰' : '💸';
        $typeLabel = $parsed['type'] === 'income' ? 'Pemasukan' : 'Pengeluaran';

        return [
            'message' => "Oke, saya catat:\n━━━━━━━━━━━━━━━━\n{$emoji} {$typeLabel}\n📦 {$parsed['category_name']}\n📝 {$desc}\n💵 {$amountFmt}\n🕐 {$dateFmt}\n━━━━━━━━━━━━━━━━\nSudah benar? Ketik OK untuk simpan.",
            'quick_replies' => ['OK', 'batal'],
            'parsed' => $parsed,
        ];
    }
}
