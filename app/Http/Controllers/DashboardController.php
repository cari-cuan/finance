<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index()
    {
        $userId = auth()->id();
        
        $summary = [
            'income' => Transaction::where('user_id', $userId)->where('type', 'income')->sum('amount'),
            'expense' => Transaction::where('user_id', $userId)->where('type', 'expense')->sum('amount'),
            'savings' => Transaction::where('user_id', $userId)->where('type', 'savings')->sum('amount'),
        ];
        $summary['balance'] = $summary['income'] - $summary['expense'] - $summary['savings'];

        $topCategories = Transaction::where('transactions.user_id', $userId)
            ->where('transactions.type', 'expense')
            ->join('categories', 'transactions.category_id', '=', 'categories.id')
            ->select('categories.name', DB::raw('SUM(transactions.amount) as total'))
            ->groupBy('categories.name')
            ->orderByDesc('total')
            ->limit(3)
            ->get();

        $latestTransactions = Transaction::where('transactions.user_id', $userId)
            ->with('category')
            ->orderByDesc('transaction_date')
            ->orderByDesc('created_at')
            ->limit(10)
            ->get();

        // Chart Data (Last 6 Months)
        $chartData = Transaction::where('user_id', $userId)
            ->select(
                DB::raw("DATE_FORMAT(transaction_date, '%Y-%m') as month_key"),
                DB::raw("DATE_FORMAT(transaction_date, '%m') as month"),
                DB::raw("SUM(CASE WHEN type = 'income' THEN amount ELSE 0 END) as income"),
                DB::raw("SUM(CASE WHEN type = 'expense' THEN amount ELSE 0 END) as expense"),
                DB::raw("SUM(CASE WHEN type = 'savings' THEN amount ELSE 0 END) as savings")
            )
            ->groupBy('month_key', 'month')
            ->orderBy('month_key')
            ->limit(6)
            ->get();

        return Inertia::render('Dashboard', [
            'summary' => $summary,
            'topCategories' => $topCategories,
            'latestTransactions' => $latestTransactions,
            'chartData' => $chartData
        ]);
    }
}
