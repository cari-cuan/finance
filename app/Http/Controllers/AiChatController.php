<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\Category;
use App\Models\ChatMessage;
use App\Services\GroqService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AiChatController extends Controller
{
    protected $groqService;

    public function __construct(GroqService $groqService)
    {
        $this->groqService = $groqService;
    }

    public function index()
    {
        // Auto delete chat messages older than today
        ChatMessage::where('user_id', auth()->id())
            ->whereDate('created_at', '<', now()->startOfDay())
            ->delete();

        $categories = Category::all(['id', 'name', 'type', 'icon', 'color']);
        $accounts = Account::where('user_id', auth()->id())->get();
        $history = ChatMessage::where('user_id', auth()->id())
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get()
            ->reverse()
            ->values();

        return Inertia::render('Chat', [
            'categories' => $categories,
            'accounts' => $accounts,
            'history' => $history,
        ]);
    }

    public function history(Request $request)
    {
        $request->validate([
            'before' => 'nullable|integer',
            'limit' => 'nullable|integer|max:20',
        ]);

        $query = ChatMessage::where('user_id', auth()->id())
            ->orderBy('created_at', 'asc');

        if ($request->before) {
            $query->where('id', '<', $request->before);
        }

        $messages = $query->limit($request->limit ?? 10)->get();

        return response()->json([
            'messages' => $messages,
            'has_more' => $messages->count() >= ($request->limit ?? 10),
        ]);
    }

    public function process(Request $request)
    {
        $request->validate([
            'message' => 'required|string',
        ]);

        $message = $request->message;

        // Save user message
        ChatMessage::create([
            'user_id' => auth()->id(),
            'role' => 'user',
            'content' => $message,
        ]);

        // Get AI response
        $result = $this->groqService->chat($message);

        // Save AI response
        ChatMessage::create([
            'user_id' => auth()->id(),
            'role' => 'assistant',
            'content' => $result['message'],
            'quick_replies' => $result['quick_replies'] ?? [],
            'parsed' => $result['parsed'] ?? null,
        ]);

        return response()->json($result);
    }
}
