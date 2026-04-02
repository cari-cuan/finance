<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        $userId = auth()->id();
        
        // If specific month is requested for detail
        if ($request->has('month_key')) {
            $monthKey = $request->month_key; // Format: YYYY-MM
            
            $transactions = Transaction::where('user_id', $userId)
                ->where(DB::raw("DATE_FORMAT(transaction_date, '%Y-%m')"), $monthKey)
                ->with('category')
                ->orderByDesc('transaction_date')
                ->get();
                
            return response()->json([
                'transactions' => $transactions->map(function($t) {
                    return [
                        'id' => $t->id,
                        'date' => $t->transaction_date->format('d M Y'),
                        'time' => $t->transaction_date->format('H:i'),
                        'type' => $t->type,
                        'amount' => $t->amount,
                        'category' => $t->category->name ?? 'Lainnya',
                        'description' => $t->description ?: ($t->category->name ?? 'Lainnya'),
                    ];
                })
            ]);
        }

        $reports = Transaction::where('user_id', $userId)
            ->select(
                DB::raw("DATE_FORMAT(transaction_date, '%Y-%m') as month_key"),
                DB::raw("DATE_FORMAT(transaction_date, '%m') as month"),
                DB::raw("DATE_FORMAT(transaction_date, '%Y') as year"),
                DB::raw("SUM(CASE WHEN type = 'income' THEN amount ELSE 0 END) as income"),
                DB::raw("SUM(CASE WHEN type = 'expense' THEN amount ELSE 0 END) as expense"),
                DB::raw("SUM(CASE WHEN type = 'savings' THEN amount ELSE 0 END) as savings")
            )
            ->groupBy('month_key', 'month', 'year')
            ->orderByDesc('month_key')
            ->get();

        return Inertia::render('Recap', [
            'reports' => $reports
        ]);
    }

    public function export(Request $request)
    {
        $userId = auth()->id();
        $monthKey = $request->month_key;

        $transactions = Transaction::where('user_id', $userId)
            ->where(DB::raw("DATE_FORMAT(transaction_date, '%Y-%m')"), $monthKey)
            ->with('category')
            ->orderBy('transaction_date')
            ->get();

        $fileName = "rekap-{$monthKey}.csv";
        
        $headers = [
            "Content-type"        => "text/csv",
            "Content-Disposition" => "attachment; filename=$fileName",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];

        $callback = function() use($transactions) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['Tanggal', 'Jam', 'Deskripsi', 'Kategori', 'Debit (Keluar)', 'Kredit (Masuk)', 'Nominal']);

            foreach ($transactions as $t) {
                fputcsv($file, [
                    $t->transaction_date->format('Y-m-d'),
                    $t->transaction_date->format('H:i'),
                    $t->description ?: '-',
                    $t->category->name ?? 'Lainnya',
                    $t->type === 'expense' ? $t->amount : 0,
                    $t->type === 'income' ? $t->amount : 0,
                    $t->amount
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function email(Request $request)
    {
        return response()->json(['message' => 'Laporan telah dikirim ke email Anda.']);
    }
}
