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

    protected $systemPrompt;

    public function __construct()
    {
        $this->apiKey = config('services.groq.api_key');
        $this->model = config('services.groq.model', 'llama-3.1-8b-instant');
        $this->systemPrompt = $this->buildSystemPrompt();
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

        // Try Groq AI first
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer '.$this->apiKey,
                'Content-Type' => 'application/json',
            ])->post('https://api.groq.com/openai/v1/chat/completions', [
                'model' => $this->model,
                'messages' => $this->buildMessages($message, $history),
                'temperature' => 0.5,
                'max_tokens' => 800,
            ]);

            if ($response->successful()) {
                $content = $response->json('choices.0.message.content');
                $parsed = $this->extractJsonFromResponse($content);

                if ($parsed && isset($parsed['action']) && $parsed['action'] === 'record_transaction') {
                    return $this->prepareConfirmation($parsed);
                }

                // For non-transaction responses, just return the text
                if ($parsed && isset($parsed['action']) && $parsed['action'] === 'reply') {
                    return [
                        'message' => $parsed['reply'] ?? $content,
                        'quick_replies' => $parsed['quick_replies'] ?? [],
                    ];
                }

                // If no JSON detected, treat as free conversation
                return [
                    'message' => $content,
                    'quick_replies' => [],
                ];
            }
        } catch (\Exception $e) {
            Log::error('Groq API error: '.$e->getMessage().' | Trace: '.$e->getTraceAsString());
        }

        // Fallback to NLP
        return $this->fallbackNlp($message);
    }

    protected function buildMessages(string $message, array $history = []): array
    {
        $messages = [
            ['role' => 'system', 'content' => $this->systemPrompt],
        ];

        foreach ($history as $msg) {
            $messages[] = ['role' => $msg['role'], 'content' => $msg['content']];
        }

        $messages[] = ['role' => 'user', 'content' => $message];

        return $messages;
    }

    protected function buildSystemPrompt(): string
    {
        $categories = Category::all(['id', 'name', 'type', 'icon', 'color'])->map(function ($cat) {
            return [
                'id' => $cat->id,
                'name' => $cat->name,
                'type' => $cat->type,
            ];
        })->toArray();

        return <<<PROMPT
Kamu adalah asisten pencatatan keuangan keluarga yang ramah dan membantu. Bahasa yang digunakan adalah Bahasa Indonesia.

Tugasmu:
1. Mencatat transaksi keuangan dari pesan natural user
2. Menjawab pertanyaan tentang keuangan
3. Memberikan saran pengelolaan keuangan

FORMAT RESPON UNTUK PENCATATAN TRANSAKSI:
Jika user mencatat transaksi, WAJIB reply dengan JSON format ini:
```json
{
  "action": "record_transaction",
  "type": "income" atau "expense",
  "amount": 25000,
  "category_id": 1,
  "category_name": "Makanan",
  "description": "Makan siang bakso",
  "transaction_date": "2026-04-02 12:00:00",
  "confirmation_message": "Oke, saya catat:\n━━━━━━━━━━━━━━━━\n💸 Pengeluaran\n📦 Makanan\n📝 Makan siang bakso\n💵 Rp 25.000\n🕐 02 Apr 2026, 12:00\n━━━━━━━━━━━━━━━━\nSudah benar? Ketik OK untuk simpan."
}
```

FORMAT RESPON UNTUK CHAT BIASA:
```json
{
  "action": "reply",
  "reply": "Jawaban kamu di sini",
  "quick_replies": ["opsi1", "opsi2"]
}
```

DAFTAR KATEGORI TERSEDIA:
{$this->formatCategories($categories)}

ATURAN PENTING:
- Default type adalah "expense" kecuali user sebut "pemasukan", "gaji", "masuk", "dapat"
- Format amount: angka tanpa titik/koma (contoh: 25000 bukan 25.000)
- "rb" atau "ribu" = kalikan 1000, "jt" atau "juta" = kalikan 1000000
- Jika tidak ada tanggal, gunakan tanggal dan waktu sekarang
- Pilih kategori yang paling cocok dari daftar di atas
- Jika tidak yakin kategori, gunakan "Lainnya"
- Selalu berikan konfirmasi sebelum menyimpan transaksi
- Gunakan emoji: 💰 Pemasukan, 💸 Pengeluaran, 📦 Kategori, 📝 Keterangan, 💵 Nominal, 🕐 Tanggal
- Jika user bertanya tentang keuangan, jawab dengan ramah dan informatif
- Jangan pernah mengarang data keuangan, jika tidak tahu bilang saja
PROMPT;
    }

    protected function formatCategories(array $categories): string
    {
        $lines = [];
        foreach ($categories as $cat) {
            $typeLabel = $cat['type'] === 'income' ? 'Pemasukan' : 'Pengeluaran';
            $lines[] = "- ID: {$cat['id']}, Nama: {$cat['name']} ({$typeLabel})";
        }

        return implode("\n", $lines);
    }

    protected function extractJsonFromResponse(string $content): ?array
    {
        // Extract JSON from code blocks
        if (preg_match('/```json\s*(.*?)\s*```/s', $content, $matches)) {
            $decoded = json_decode($matches[1], true);
            if ($decoded) {
                return $decoded;
            }
        }

        // Try to find JSON object in response (match balanced braces)
        $start = strpos($content, '{');
        if ($start !== false) {
            $depth = 0;
            $inString = false;
            $escapeNext = false;
            for ($i = $start; $i < strlen($content); $i++) {
                $char = $content[$i];
                if ($escapeNext) {
                    $escapeNext = false;

                    continue;
                }
                if ($char === '\\') {
                    $escapeNext = true;

                    continue;
                }
                if ($char === '"') {
                    $inString = ! $inString;

                    continue;
                }
                if ($inString) {
                    continue;
                }
                if ($char === '{') {
                    $depth++;
                }
                if ($char === '}') {
                    $depth--;
                    if ($depth === 0) {
                        $jsonStr = substr($content, $start, $i - $start + 1);
                        $decoded = json_decode($jsonStr, true);
                        if ($decoded) {
                            return $decoded;
                        }
                        break;
                    }
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
        // If message doesn't look like a transaction, return a friendly chat response
        $lowerMessage = strtolower($message);
        $hasAmount = preg_match('/\d+\s*(rb|ribu|k|jt|juta)?/i', $lowerMessage);
        $isTransaction = $hasAmount || preg_match('/(beli|bayar|makan|minum|bensin|gaji|masuk|keluar)/i', $lowerMessage);

        if (! $isTransaction) {
            return [
                'message' => 'Halo! 👋 Saya bisa bantu catat transaksi keuangan kamu. Coba ketik seperti "Makan siang 25rb" atau tanya tentang keuangan kamu.',
                'quick_replies' => ['Makan siang 25rb', 'Gaji masuk 5jt', 'Bensin 50rb'],
            ];
        }

        $nlpService = new NlpParsingService;
        $parsed = $nlpService->parse($message);

        if (! $parsed || ! isset($parsed['amount']) || $parsed['amount'] <= 0) {
            return [
                'message' => 'Maaf, saya tidak menemukan nominal transaksi. Coba format seperti "Makan siang 25rb"?',
                'quick_replies' => [],
            ];
        }

        session(['pending_transaction' => $parsed]);

        $amountFormatted = 'Rp '.number_format($parsed['amount'], 0, ',', '.');
        $dateFormatted = Carbon::parse($parsed['transaction_date'])->translatedFormat('d M Y, H:i');

        $cleanDesc = trim($parsed['description']);
        $cleanDesc = preg_replace('/^[-+]/', '', $cleanDesc);
        $cleanDesc = preg_replace('/#manual/i', '', $cleanDesc);
        $cleanDesc = trim($cleanDesc) ?: 'Transaksi Manual';

        $isIncome = $parsed['type'] === 'income';
        $typeEmoji = $isIncome ? '💰' : '💸';
        $typeLabel = $isIncome ? 'Pemasukan' : 'Pengeluaran';

        return [
            'message' => "Oke, saya catat transaksi ini ya:\n━━━━━━━━━━━━━━━━\n{$typeEmoji}  {$typeLabel}\n📦  {$parsed['category_name']}\n📝  {$cleanDesc}\n💵  {$amountFormatted}\n🕐  {$dateFormatted}\n━━━━━━━━━━━━━━━━\nSudah benar? Ketik **OK** untuk simpan.",
            'quick_replies' => ['OK', 'batal'],
            'parsed' => $parsed,
        ];
    }
}
