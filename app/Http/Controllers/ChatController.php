<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\Category;
use App\Services\GroqService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ChatController extends Controller
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

        return Inertia::render('Chat', [
            'categories' => $categories,
            'accounts' => $accounts,
        ]);
    }

    public function process(Request $request)
    {
        $request->validate([
            'message' => 'required|string',
            'history' => 'sometimes|array',
        ]);

        $message = $request->message;
        $history = $request->history ?? [];

        $result = $this->groqService->chat($message, $history);

        return response()->json($result);
    }
}
