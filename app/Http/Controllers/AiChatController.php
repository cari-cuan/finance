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
        $categories = Category::all(['id', 'name', 'type', 'icon', 'color']);
        $accounts = Account::where('user_id', auth()->id())->get();
        $history = ChatMessage::where('user_id', auth()->id())
            ->orderBy('created_at', 'asc')
            ->limit(50)
            ->get();

        return Inertia::render('Chat', [
            'categories' => $categories,
            'accounts' => $accounts,
            'history' => $history,
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
