<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Models\Category;
use App\Services\NlpParsingService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ChatController extends Controller
{
    protected $nlpService;

    public function __construct(NlpParsingService $nlpService)
    {
        $this->nlpService = $nlpService;
    }

    public function index()
    {
        $categories = Category::all(['id', 'name', 'is_income']);
        return Inertia::render('Chat', [
            'categories' => $categories
        ]);
    }

    public function process(Request $request)
    {
        $request->validate([
            'message' => 'required|string',
        ]);

        $message = $request->message;
        $lowerMessage = strtolower($message);

        // Handle Confirmation
        if ($lowerMessage === 'ok' && session()->has('pending_transaction')) {
            return $this->confirmTransaction();
        }

        if ($lowerMessage === 'batal' && session()->has('pending_transaction')) {
            session()->forget('pending_transaction');
            return response()->json([
                'message' => 'Transaksi dibatalkan. Ada yang bisa saya bantu lagi?',
                'quick_replies' => []
            ]);
        }

        // Parse Message
        try {
            $parsed = $this->nlpService->parse($message);
        } catch (\Exception $e) {
            \Log::error('NLP Parsing Error: ' . $e->getMessage(), [
                'message' => $message,
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'message' => 'Maaf, saya sedang kesulitan memahami pesan ini. Bisa coba format lain?',
                'quick_replies' => []
            ], 500);
        }

        if (!$parsed || !isset($parsed['amount']) || $parsed['amount'] <= 0) {
            return response()->json([
                'message' => 'Maaf, saya tidak menemukan nominal transaksi. Bisa diulangi dengan format seperti "Makan siang 25rb"?',
                'quick_replies' => []
            ]);
        }

        // Store in session for confirmation
        session(['pending_transaction' => $parsed]);

        $amountFormatted = 'Rp ' . number_format($parsed['amount'], 0, ',', '.');
        $dateFormatted = \Carbon\Carbon::parse($parsed['transaction_date'])->translatedFormat('d M Y, H:i');

        // Clean description from artifacts like -#manual, +, -, #manual
        $cleanDesc = trim($parsed['description']);
        $cleanDesc = preg_replace('/^[-+]/', '', $cleanDesc);          // strip leading +/-
        $cleanDesc = preg_replace('/#manual/i', '', $cleanDesc);       // strip #manual tag
        $cleanDesc = trim($cleanDesc) ?: 'Transaksi Manual';

        $isIncome = $parsed['type'] === 'income';
        $typeEmoji = $isIncome ? '💰' : '💸';
        $typeLabel = $isIncome ? 'Pemasukan' : 'Pengeluaran';

        $responseMessage =
            "Oke, saya catat transaksi ini ya:\n" .
            "━━━━━━━━━━━━━━━━\n" .
            "{$typeEmoji}  {$typeLabel}\n" .
            "📦  {$parsed['category_name']}\n" .
            "📝  {$cleanDesc}\n" .
            "💵  {$amountFormatted}\n" .
            "🕐  {$dateFormatted}\n" .
            "━━━━━━━━━━━━━━━━\n" .
            "Sudah benar? Ketik **OK** untuk simpan.";

        return response()->json([
            'message' => $responseMessage,
            'quick_replies' => ['OK', 'batal'],
            'parsed' => $parsed
        ]);
    }

    private function confirmTransaction()
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

        return response()->json([
            'message' => 'Transaksi berhasil disimpan! ✅',
            'quick_replies' => []
        ]);
    }
}
