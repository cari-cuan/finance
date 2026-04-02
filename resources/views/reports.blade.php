@extends('layouts.app')

@section('title', 'Rekap Laporan')
@section('header_title', 'Rekap Bulanan')

@section('content')
    <div class="space-y-4">
        @forelse($reports as $report)
            <div onclick="showDetails('{{ $report->month_key }}', '{{ DateTime::createFromFormat('!m', $report->month)->format('F') }} {{ $report->year }}')" 
                class="bg-white p-4 rounded-xl border border-gray-100 shadow-sm active:bg-gray-50 cursor-pointer transition-colors">
                <div class="flex justify-between items-center mb-3">
                    <div class="text-sm font-bold text-gray-700">
                        {{ \Carbon\Carbon::createFromFormat('m', $report->month)->translatedFormat('F') }} {{ $report->year }}
                    </div>
                    <i data-lucide="chevron-right" class="w-4 h-4 text-gray-300"></i>
                </div>
                <div class="grid grid-cols-3 gap-2">
                    <div class="text-center">
                        <div class="text-[10px] text-gray-400 uppercase font-semibold">Masuk</div>
                        <div class="text-sm font-bold text-green-600">Rp {{ number_format($report->income, 0, ',', '.') }}</div>
                    </div>
                    <div class="text-center">
                        <div class="text-[10px] text-gray-400 uppercase font-semibold">Keluar</div>
                        <div class="text-sm font-bold text-red-600">Rp {{ number_format($report->expense, 0, ',', '.') }}</div>
                    </div>
                    <div class="text-center border-l border-gray-100 pl-2">
                        <div class="text-[10px] text-gray-400 uppercase font-semibold">Sisa</div>
                        <div class="text-sm font-bold text-blue-700">Rp {{ number_format($report->income - $report->expense, 0, ',', '.') }}</div>
                    </div>
                </div>

                @if($report->categories->isNotEmpty())
                    <div class="mt-4 pt-3 border-t border-gray-50">
                        <div class="text-[10px] text-gray-400 uppercase font-bold mb-2">Breakdown Kategori:</div>
                        <div class="space-y-2">
                            @foreach($report->categories as $category)
                                <div class="flex justify-between items-center text-xs">
                                    <span class="text-gray-600 font-medium">{{ $category->name }}</span>
                                    <span class="{{ $category->type === 'income' ? 'text-green-600' : 'text-red-600' }} font-bold">
                                        Rp {{ number_format($category->total, 0, ',', '.') }}
                                    </span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>
        @empty
            <div class="text-center py-20 text-gray-400">
                <i data-lucide="folder-open" class="w-12 h-12 mx-auto mb-2 opacity-20"></i>
                <p>Belum ada data rekap.</p>
            </div>
        @endforelse
    </div>

    <!-- Details Modal -->
    <div id="details-modal" class="fixed inset-0 z-50 hidden overflow-hidden">
        <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" onclick="closeDetails()"></div>
        <div class="absolute bottom-0 left-0 right-0 max-w-md mx-auto bg-white rounded-t-3xl shadow-2xl h-[85vh] flex flex-col transition-transform transform translate-y-full" id="modal-content">
            <div class="p-4 border-b flex justify-between items-center bg-gray-50 rounded-t-3xl">
                <h3 id="modal-title" class="text-base font-bold text-gray-800">Detail Laporan</h3>
                <button onclick="closeDetails()" class="p-2 hover:bg-gray-200 rounded-full transition-colors">
                    <i data-lucide="x" class="w-5 h-5 text-gray-500"></i>
                </button>
            </div>
            <div id="modal-body" class="flex-1 overflow-y-auto p-4 space-y-3">
                <!-- Transactions will be injected here -->
                <div id="loading-spinner" class="flex flex-col items-center justify-center h-full text-gray-400">
                    <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600 mb-2"></div>
                    <p class="text-sm">Memuat detail...</p>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    const modal = document.getElementById('details-modal');
    const modalContent = document.getElementById('modal-content');
    const modalTitle = document.getElementById('modal-title');
    const modalBody = document.getElementById('modal-body');

    async function showDetails(monthKey, title) {
        modalTitle.textContent = 'Detail ' + title;
        modal.classList.remove('hidden');
        
        // Animate up
        setTimeout(() => {
            modalContent.classList.remove('translate-y-full');
        }, 10);

        modalBody.innerHTML = `
            <div class="flex flex-col items-center justify-center h-full text-gray-400 py-20">
                <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600 mb-2"></div>
                <p class="text-sm">Memuat detail...</p>
            </div>
        `;

        try {
            const response = await fetch(`{{ route('reports') }}?month_key=${monthKey}`);
            const data = await response.json();
            
            if (data.transactions.length === 0) {
                modalBody.innerHTML = '<div class="text-center py-10 text-gray-400">Tidak ada transaksi bulan ini.</div>';
                return;
            }

            let html = `
                <div class="overflow-x-auto -mx-4">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-gray-50 border-y border-gray-100">
                                <th class="px-4 py-2 text-[10px] font-bold text-gray-400 uppercase">Waktu</th>
                                <th class="px-2 py-2 text-[10px] font-bold text-gray-400 uppercase">Deskripsi</th>
                                <th class="px-4 py-2 text-[10px] font-bold text-gray-400 uppercase text-right">Nominal</th>
                            </tr>
                        </thead>
                        <tbody>
            `;
            
            let currentDate = '';

            data.transactions.forEach(t => {
                if (t.date !== currentDate) {
                    currentDate = t.date;
                    html += `
                        <tr class="bg-blue-50/30">
                            <td colspan="3" class="px-4 py-1.5 text-[10px] font-bold text-blue-600 uppercase border-b border-blue-50">${t.date}</td>
                        </tr>
                    `;
                }

                html += `
                    <tr class="border-b border-gray-50 hover:bg-gray-50 transition-colors">
                        <td class="px-4 py-3 align-top">
                            <div class="text-[10px] font-medium text-gray-500">${t.time}</div>
                        </td>
                        <td class="px-2 py-3 align-top">
                            <div class="text-xs font-bold text-gray-800 leading-tight">${t.description}</div>
                            <div class="text-[9px] text-gray-400 mt-0.5">${t.category}</div>
                        </td>
                        <td class="px-4 py-3 align-top text-right">
                            <div class="text-xs font-bold ${t.is_income ? 'text-green-600' : 'text-red-600'}">
                                ${t.is_income ? '+' : '-'} ${t.amount.replace('Rp ', '')}
                            </div>
                        </td>
                    </tr>
                `;
            });
            html += `
                        </tbody>
                    </table>
                </div>
            `;
            modalBody.innerHTML = html;
            lucide.createIcons();
        } catch (error) {
            modalBody.innerHTML = '<div class="text-center py-10 text-red-500">Gagal memuat data.</div>';
        }
    }

    function closeDetails() {
        modalContent.classList.add('translate-y-full');
        setTimeout(() => {
            modal.classList.add('hidden');
        }, 300);
    }
</script>
@endpush
