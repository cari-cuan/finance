<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;

class TransactionController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        // Auto create default account if none exists
        if ($user->accounts()->count() === 0) {
            $user->accounts()->create([
                'name' => 'Pocket',
                'type' => 'cash',
                'balance' => 0,
            ]);
        }

        $transactions = $user->transactions()
            ->with(['category', 'account'])
            ->orderByDesc('transaction_date')
            ->orderByDesc('created_at')
            ->paginate(30);

        return inertia('Chat', [
            'categories' => Category::all(),
            'accounts' => $user->accounts()->get(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'type' => 'required|in:income,expense,savings',
            'amount' => 'required|numeric|min:1',
            'category_id' => 'required|exists:categories,id',
            'description' => 'nullable|string|max:500',
            'transaction_date' => 'required|date',
        ]);

        $transaction = auth()->user()->transactions()->create($validated);

        return redirect()->back()->with('success', 'Transaksi berhasil disimpan');
    }
}
