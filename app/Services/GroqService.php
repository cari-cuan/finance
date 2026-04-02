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

        // Handle Confirmation
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

        try {
            $financialContext = $this->getFinancialContext();
            $categories = Category::all(['id', 'name', 'type', 'icon', 'color']);

            $systemPrompt = $this->buildSystemPrompt($categories, $financialContext);

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

    protected function getFinancialContext(): array
    {
        $userId = auth()->id();
        $now = Carbon::now();

        $totalIncome = Transaction::where('user_id', $userId)
            ->where('type', 'income')
            ->sum('amount');

        $totalExpense = Transaction::where('user_id', $userId)
            ->where('type', 'expense')
            ->sum('amount');

        $balance = $totalIncome - $totalExpense;

        $thisMonthIncome = Transaction::where('user_id', $userId)
            ->where('type', 'income')
            ->whereMonth('transaction_date', $now->month)
            ->whereYear('transaction_date', $now->year)
            ->sum('amount');

        $thisMonthExpense = Transaction::where('user_id', $userId)
            ->where('type', 'expense')
            ->whereMonth('transaction_date', $now->month)
            ->whereYear('transaction_date', $now->year)
            ->sum('amount');

        $recentTransactions = Transaction::with('category')
            ->where('user_id', $userId)
            ->orderByDesc('transaction_date')
            ->limit(10)
            ->get()
            ->map(function ($t) {
                return [
                    'type' => $t->type,
                    'amount' => $t->amount,
                    'description' => $t->description,
                    'category' => $t->category?->name ?? '-',
                    'date' => Carbon::parse($t->transaction_date)->translatedFormat('d M Y'),
                ];
            });

        $topCategories = Transaction::selectRaw('category_id, type, SUM(amount) as total')
            ->where('user_id', $userId)
            ->whereMonth('transaction_date', $now->month)
            ->whereYear('transaction_date', $now->year)
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

        return [
            'total_income' => $totalIncome,
            'total_expense' => $totalExpense,
            'balance' => $balance,
            'this_month_income' => $thisMonthIncome,
            'this_month_expense' => $thisMonthExpense,
            'recent_transactions' => $recentTransactions,
            'top_categories' => $topCategories,
        ];
    }

    protected function buildSystemPrompt($categories, $context): string
    {
        $catList = $categories->map(function ($cat) {
            $typeLabel = $cat->type === 'income' ? 'Pemasukan' : 'Pengeluaran';

            return "- ID: {$cat->id}, Nama: {$cat->name} ({$typeLabel})";
        })->implode("\n");

        $ctx = $context;
        $incomeFormatted = number_format($ctx['total_income'], 0, ',', '.');
        $expenseFormatted = number_format($ctx['total_expense'], 0, ',', '.');
        $balanceFormatted = number_format($ctx['balance'], 0, ',', '.');
        $monthIncome = number_format($ctx['this_month_income'], 0, ',', '.');
        $monthExpense = number_format($ctx['this_month_expense'], 0, ',', '.');

        $recentTx = $ctx['recent_transactions']->map(function ($t) {
            $emoji = $t['type'] === 'income' ? '💰' : '💸';

            return "{$emoji} {$t['date']} | {$t['category']} | {$t['description']} | Rp ".number_format($t['amount'], 0, ',', '.');
        })->implode("\n") ?: 'Belum ada transaksi.';

        $topCat = $ctx['top_categories']->map(function ($t) {
            $emoji = $t['type'] === 'income' ? '💰' : '💸';

            return "{$emoji} {$t['category']}: Rp ".number_format($t['total'], 0, ',', '.');
        })->implode("\n") ?: 'Belum ada data.';

        $hasData = $ctx['this_month_income'] > 0 || $ctx['this_month_expense'] > 0;
        $dataLabel = $hasData ? 'Ada data' : 'Belum ada data';

        return <<<PROMPT
Kamu adalah asisten keuangan keluarga yang ramah dan membantu. Bahasa: Indonesia.

DATA KEUANGAN USER (WAJIB GUNAKAN DATA INI):
━━━━━━━━━━━━━━━━
💰 Total Pemasukan: Rp {$incomeFormatted}
💸 Total Pengeluaran: Rp {$expenseFormatted}
💵 Saldo Tersedia: Rp {$balanceFormatted}

📊 Bulan Ini ({$dataLabel}):
- Pemasukan: Rp {$monthIncome}
- Pengeluaran: Rp {$monthExpense}

📋 5 Transaksi Terakhir:
{$recentTx}

📈 Kategori Teratas Bulan Ini:
{$topCat}
━━━━━━━━━━━━━━━━

ATURAN:
1. Jika user tanya tentang saldo, pemasukan, pengeluaran, atau laporan → JAWAB LANGSUNG dengan data di atas. JANGAN return JSON.
2. Jika user catat transaksi (ada nominal) → return JSON format di bawah.
3. Jika user tanya hal lain → jawab ramah, JANGAN return JSON.

FORMAT JSON HANYA UNTUK PENCATATAN TRANSAKSI:
```json
{"action":"record_transaction","type":"income/expense","amount":25000,"category_id":1,"category_name":"Makanan","description":"Makan siang","transaction_date":"2026-04-02 12:00:00","confirmation_message":"Oke, saya catat:\n━━━━━━━━━━━━━━━━\n💸 Pengeluaran\n📦 Makanan\n📝 Makan siang\n💵 Rp 25.000\n🕐 02 Apr 2026, 12:00\n━━━━━━━━━━━━━━━━\nSudah benar? Ketik OK untuk simpan."}
```

DAFTAR KATEGORI:
{$catList}

PENTING:
- Default type = "expense" kecuali user sebut "pemasukan", "gaji", "masuk", "dapat"
- "rb"/"ribu"/"k" = ×1000, "jt"/"juta" = ×1000000
- Jika tidak yakin kategori, gunakan "Lainnya"
- Gunakan emoji: 💰💸📦📝💵🕐
- JANGAN pernah mengarang data keuangan. Gunakan data yang diberikan.
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

        // No JSON = free conversation
        return [
            'message' => $content,
            'quick_replies' => [],
        ];
    }

    protected function extractJson(string $content): ?array
    {
        // Try code block first
        if (preg_match('/```json\s*(.*?)\s*```/s', $content, $m)) {
            $d = json_decode($m[1], true);
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
                    $d = json_decode(substr($content, $start, $i - $start + 1), true);

                    return $d;
                }
            }
        }

        return null;
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
