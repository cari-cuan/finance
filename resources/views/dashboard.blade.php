@extends('layouts.app')

@section('title', 'Dashboard')
@section('header_title', 'Ringkasan Keuangan')

@section('content')
    <!-- Summary Cards -->
    <div class="grid grid-cols-2 gap-4 mb-6">
        <div class="bg-blue-50 p-4 rounded-xl border border-blue-100">
            <span class="text-xs text-blue-600 font-medium">Pemasukan</span>
            <div class="text-lg font-bold text-blue-800">Rp {{ number_format($summary['income'], 0, ',', '.') }}</div>
        </div>
        <div class="bg-red-50 p-4 rounded-xl border border-red-100">
            <span class="text-xs text-red-600 font-medium">Pengeluaran</span>
            <div class="text-lg font-bold text-red-800">Rp {{ number_format($summary['expense'], 0, ',', '.') }}</div>
        </div>
        <div class="col-span-2 bg-green-50 p-4 rounded-xl border border-green-100 flex justify-between items-center">
            <div>
                <span class="text-xs text-green-600 font-medium">Saldo Saat Ini</span>
                <div class="text-xl font-bold text-green-800">Rp {{ number_format($summary['balance'], 0, ',', '.') }}</div>
            </div>
            <i data-lucide="wallet" class="w-8 h-8 text-green-500"></i>
        </div>
    </div>

    <!-- Monthly Comparison Chart -->
    <div class="mb-6 bg-white p-4 rounded-xl border border-gray-100 shadow-sm">
        <h3 class="text-sm font-semibold mb-4 text-gray-700">Perbandingan Bulanan</h3>
        <div class="h-40">
            <canvas id="financeChart"></canvas>
        </div>
    </div>

    <!-- Top Categories -->
    @if($topCategories->count() > 0)
    <div class="mb-6">
        <h3 class="text-sm font-semibold mb-3 text-gray-700">Pengeluaran Terbesar</h3>
        <div class="space-y-3">
            @foreach($topCategories as $category)
                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <div class="w-2 h-2 rounded-full bg-red-500 mr-2"></div>
                        <span class="text-sm text-gray-600">{{ $category->name }}</span>
                    </div>
                    <span class="text-sm font-bold">Rp {{ number_format($category->total, 0, ',', '.') }}</span>
                </div>
                <div class="w-full bg-gray-100 rounded-full h-1.5">
                    <div class="bg-red-500 h-1.5 rounded-full" style="width: {{ ($category->total / max($summary['expense'], 1)) * 100 }}%"></div>
                </div>
            @endforeach
        </div>
    </div>
    @endif

    <!-- Latest Transactions -->
    <div>
        <div class="flex justify-between items-center mb-3">
            <h3 class="text-sm font-semibold text-gray-700">Transaksi Terbaru</h3>
            <a href="{{ route('transactions') }}" class="text-xs text-blue-600">Lihat Semua</a>
        </div>
        <div class="space-y-4">
            @forelse($latestTransactions as $transaction)
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
                <div class="text-center py-10">
                    <div class="text-gray-400 text-sm mb-2">Belum ada transaksi.</div>
                    <a href="{{ route('chat') }}" class="text-blue-600 text-sm font-medium">Mulai Catat</a>
                </div>
            @endforelse
        </div>
    </div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    const ctx = document.getElementById('financeChart').getContext('2d');
    const chartData = @json($chartData);
    
    const labels = chartData.map(d => {
        const date = new Date(d.month_key + '-01');
        return date.toLocaleDateString('id-ID', { month: 'short' });
    });
    
    const incomes = chartData.map(d => d.income);
    const expenses = chartData.map(d => d.expense);

    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [
                {
                    label: 'Masuk',
                    data: incomes,
                    backgroundColor: '#10b981',
                    borderRadius: 4,
                },
                {
                    label: 'Keluar',
                    data: expenses,
                    backgroundColor: '#ef4444',
                    borderRadius: 4,
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        usePointStyle: true,
                        boxWidth: 6,
                        font: { size: 9 }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grace: '10%', // Add space at the top so it doesn't hit the ceiling
                    grid: { 
                        display: true,
                        color: '#f3f4f6',
                        drawBorder: false
                    },
                    ticks: {
                        font: { size: 8 },
                        maxTicksLimit: 5, // Limit the number of ticks
                        callback: function(value) {
                            if (value >= 1000000000) return (value/1000000000) + 'M';
                            if (value >= 1000000) return (value/1000000) + 'jt';
                            if (value >= 1000) return (value/1000) + 'rb';
                            return value;
                        }
                    }
                },
                x: {
                    grid: { display: false },
                    ticks: { font: { size: 9 } }
                }
            }
        }
    });
</script>
@endpush
