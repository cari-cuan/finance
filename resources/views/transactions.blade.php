@extends('layouts.app')

@section('title', 'Daftar Transaksi')
@section('header_title', 'Semua Transaksi')

@section('content')
    <div class="space-y-4">
        @forelse($transactions as $transaction)
            <div class="flex items-center justify-between bg-white p-3 rounded-lg border border-gray-100 shadow-sm">
                <div class="flex items-center">
                    <div class="w-10 h-10 rounded-full bg-gray-50 flex items-center justify-center mr-3">
                        <i data-lucide="{{ $transaction->category->icon ?? 'circle' }}" class="w-5 h-5 text-gray-400"></i>
                    </div>
                    <div>
                        <div class="text-sm font-medium">{{ $transaction->description ?: $transaction->category->name }}</div>
                        <div class="text-xs text-gray-400">{{ $transaction->transaction_date->translatedFormat('d M Y') }}</div>
                    </div>
                </div>
                <div class="text-sm font-bold {{ $transaction->type == 'income' ? 'text-green-600' : 'text-red-600' }}">
                    {{ $transaction->type == 'income' ? '+' : '-' }} Rp {{ number_format($transaction->amount, 0, ',', '.') }}
                </div>
            </div>
        @empty
            <div class="text-center py-20 text-gray-400">
                <p>Belum ada transaksi.</p>
            </div>
        @endforelse

        <div class="mt-4">
            {{ $transactions->links() }}
        </div>
    </div>
@endsection
