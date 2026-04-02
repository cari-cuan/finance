@extends('layouts.app')

@section('title', 'Chat Input')
@section('header_title', 'Catat Transaksi')

@section('content')
    <div id="chat-container" class="flex flex-col h-[calc(100vh-180px)]">
        <!-- Chat History -->
        <div id="chat-history" class="flex-1 overflow-y-auto space-y-4 mb-4 scrollbar-hide pr-2">
            <div class="flex items-start">
                <div class="w-8 h-8 rounded-full bg-blue-100 flex items-center justify-center mr-2 flex-shrink-0">
                    <i data-lucide="bot" class="w-4 h-4 text-blue-600"></i>
                </div>
                <div class="bg-blue-50 text-gray-800 p-3 rounded-2xl rounded-tl-none text-sm max-w-[85%] shadow-sm">
                    Halo! Saya asisten keuangan Anda. Ketik apa yang ingin Anda catat, misalnya: **"beli bakso 15rb"** atau
                    **"gaji 5jt"**.
                </div>
            </div>
        </div>

        <!-- Typing Indicator -->
        <div id="typing-indicator" class="hidden flex items-start mb-4 animate-pulse">
            <div class="w-8 h-8 rounded-full bg-blue-100 flex items-center justify-center mr-2 flex-shrink-0">
                <i data-lucide="bot" class="w-4 h-4 text-blue-600"></i>
            </div>
            <div class="bg-blue-50 text-gray-400 p-3 rounded-2xl rounded-tl-none flex gap-1 items-center">
                <div class="w-1 h-1 bg-gray-400 rounded-full"></div>
                <div class="w-1 h-1 bg-gray-400 rounded-full"></div>
                <div class="w-1 h-1 bg-gray-400 rounded-full"></div>
            </div>
        </div>

        <!-- Quick Replies -->
        <div id="quick-replies" class="flex gap-2 mb-3 hidden overflow-x-auto pb-2">
            <!-- Buttons injected here -->
        </div>

        <!-- Fast Actions -->
        <div id="fast-actions" class="mb-3 space-y-2">
            <div class="flex gap-2 overflow-x-auto pb-1 scrollbar-hide">
                <button id="btn-fast-income" onclick="toggleFastInput('income')"
                    class="flex-none bg-white text-gray-500 border border-gray-200 px-4 py-2 rounded-xl text-xs font-bold shadow-sm transition-all flex items-center gap-1">
                    <i data-lucide="plus-circle" class="w-3.5 h-3.5"></i> Pemasukan
                </button>
                <button id="btn-fast-expense" onclick="toggleFastInput('expense')"
                    class="flex-none bg-white text-gray-500 border border-gray-200 px-4 py-2 rounded-xl text-xs font-bold shadow-sm transition-all flex items-center gap-1">
                    <i data-lucide="minus-circle" class="w-3.5 h-3.5"></i> Pengeluaran
                </button>
            </div>

            <div id="fast-nominal-container"
                class="hidden animate-in slide-in-from-bottom-2 duration-200 bg-gray-50 p-3 rounded-2xl border border-gray-100">
                <!-- Category Selector -->
                <div id="category-chips"
                    class="flex gap-2 overflow-x-auto pb-3 mb-3 border-b border-gray-200 scrollbar-hide">
                    <!-- Category chips injected by JS -->
                </div>

                <!-- Time Selector -->
                <div class="flex flex-col gap-2 mb-3 border-b border-gray-200 pb-3">
                    <div class="flex gap-2 overflow-x-auto scrollbar-hide">
                        <select id="select-year" onchange="updateChatInputPreview()"
                            class="bg-white border border-gray-200 rounded-lg text-[10px] font-bold text-gray-600 px-2 py-1 outline-none focus:ring-1 focus:ring-blue-500">
                            <!-- Years will be injected -->
                        </select>
                        <select id="select-month" onchange="updateChatInputPreview()"
                            class="bg-white border border-gray-200 rounded-lg text-[10px] font-bold text-gray-600 px-2 py-1 outline-none focus:ring-1 focus:ring-blue-500">
                            <option value="1">Januari</option>
                            <option value="2">Februari</option>
                            <option value="3">Maret</option>
                            <option value="4">April</option>
                            <option value="5">Mei</option>
                            <option value="6">Juni</option>
                            <option value="7">Juli</option>
                            <option value="8">Agustus</option>
                            <option value="9">September</option>
                            <option value="10">Oktober</option>
                            <option value="11">November</option>
                            <option value="12">Desember</option>
                        </select>
                        <select id="select-day" onchange="updateChatInputPreview()"
                            class="bg-white border border-gray-200 rounded-lg text-[10px] font-bold text-gray-600 px-2 py-1 outline-none focus:ring-1 focus:ring-blue-500">
                            <!-- Days 1-31 will be injected -->
                        </select>
                        <button onclick="resetToNow()"
                            class="ml-auto bg-gray-100 text-gray-500 px-2 py-1 rounded-lg text-[9px] font-bold hover:bg-gray-200 transition-colors">Reset
                            Now</button>
                    </div>
                </div>

                <!-- Nominal Selector -->
                <div class="flex flex-wrap gap-2 items-center">
                    <button onclick="selectFastNominal(10000)"
                        class="bg-white border border-gray-200 px-3 py-1.5 rounded-lg text-xs font-semibold hover:bg-gray-50 active:bg-blue-50">10rb</button>
                    <button onclick="selectFastNominal(20000)"
                        class="bg-white border border-gray-200 px-3 py-1.5 rounded-lg text-xs font-semibold hover:bg-gray-50 active:bg-blue-50">20rb</button>
                    <button onclick="selectFastNominal(50000)"
                        class="bg-white border border-gray-200 px-3 py-1.5 rounded-lg text-xs font-semibold hover:bg-gray-50 active:bg-blue-50">50rb</button>

                    <div class="flex items-center bg-white border border-gray-200 rounded-lg overflow-hidden ml-auto">
                        <button onclick="adjustCustom(-5000)"
                            class="px-2 py-1.5 hover:bg-gray-50 text-gray-500 border-r border-gray-100">
                            <i data-lucide="minus" class="w-3.5 h-3.5"></i>
                        </button>
                        <span id="custom-nominal-display"
                            class="px-3 py-1.5 text-xs font-bold text-blue-700 min-w-[60px] text-center">5rb</span>
                        <button onclick="adjustCustom(5000)"
                            class="px-2 py-1.5 hover:bg-gray-50 text-gray-500 border-l border-gray-100">
                            <i data-lucide="plus" class="w-3.5 h-3.5"></i>
                        </button>
                        <button onclick="selectCustomNominal()"
                            class="bg-blue-600 text-white px-3 py-1.5 hover:bg-blue-700 transition-colors">
                            <i data-lucide="check" class="w-3.5 h-3.5"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Input Area -->
        <div class="relative pb-4">
            <form id="chat-form" class="flex gap-2">
                <input type="text" id="chat-input"
                    class="flex-1 bg-gray-100 border-none rounded-2xl px-4 py-3 text-sm focus:ring-2 focus:ring-blue-500 outline-none"
                    placeholder="Ketik transaksi di sini..." autocomplete="off">
                <button type="submit"
                    class="bg-blue-600 text-white p-3 rounded-full hover:bg-blue-700 transition-colors shadow-md">
                    <i data-lucide="send" class="w-5 h-5"></i>
                </button>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        const chatForm = document.getElementById('chat-form');
        const chatInput = document.getElementById('chat-input');
        const chatHistory = document.getElementById('chat-history');
        const typingIndicator = document.getElementById('typing-indicator');
        const quickReplies = document.getElementById('quick-replies');
        const fastNominalContainer = document.getElementById('fast-nominal-container');
        const customNominalDisplay = document.getElementById('custom-nominal-display');
        const btnFastIncome = document.getElementById('btn-fast-income');
        const btnFastExpense = document.getElementById('btn-fast-expense');
        const categoryChips = document.getElementById('category-chips');
        const selectYear = document.getElementById('select-year');
        const selectMonth = document.getElementById('select-month');
        const selectDay = document.getElementById('select-day');

        const categories = @json($categories);
        let currentFastType = null; // 'income' or 'expense'
        let currentFastCategory = null;
        let customNominalValue = 5000;

        // Initialize Selects
        function initSelects() {
            const now = new Date();
            const currentYear = now.getFullYear();

            // Year: current, prev, next
            for (let y = currentYear - 1; y <= currentYear + 1; y++) {
                const opt = document.createElement('option');
                opt.value = y;
                opt.textContent = y;
                if (y === currentYear) opt.selected = true;
                selectYear.appendChild(opt);
            }

            // Day: 1-31
            for (let d = 1; d <= 31; d++) {
                const opt = document.createElement('option');
                opt.value = d;
                opt.textContent = d;
                if (d === now.getDate()) opt.selected = true;
                selectDay.appendChild(opt);
            }

            selectMonth.value = now.getMonth() + 1;
        }

        initSelects();

        function resetToNow() {
            const now = new Date();
            selectYear.value = now.getFullYear();
            selectMonth.value = now.getMonth() + 1;
            selectDay.value = now.getDate();
            updateChatInputPreview();
        }

        function toggleFastInput(type) {
            // Reset styles
            btnFastIncome.className =
                'flex-none bg-white text-gray-500 border border-gray-200 px-4 py-2 rounded-xl text-xs font-bold shadow-sm transition-all flex items-center gap-1';
            btnFastExpense.className =
                'flex-none bg-white text-gray-500 border border-gray-200 px-4 py-2 rounded-xl text-xs font-bold shadow-sm transition-all flex items-center gap-1';

            if (currentFastType === type) {
                fastNominalContainer.classList.add('hidden');
                currentFastType = null;
            } else {
                currentFastType = type;
                currentFastCategory = null; // Reset category when switching type
                fastNominalContainer.classList.remove('hidden');

                // Highlight active button
                if (type === 'income') {
                    btnFastIncome.className =
                        'flex-none bg-green-600 text-white border border-green-600 px-4 py-2 rounded-xl text-xs font-bold shadow-md transition-all flex items-center gap-1';
                } else {
                    btnFastExpense.className =
                        'flex-none bg-red-600 text-white border border-red-600 px-4 py-2 rounded-xl text-xs font-bold shadow-md transition-all flex items-center gap-1';
                }

                renderCategoryChips();
                updateChatInputPreview();
                lucide.createIcons();
            }
        }

        function renderCategoryChips() {
            categoryChips.innerHTML = '';
            const filtered = categories.filter(c => c.is_income === (currentFastType === 'income' ? 1 : 0));

            filtered.forEach(cat => {
                const btn = document.createElement('button');
                btn.className =
                    `flex-none px-3 py-1 rounded-full text-[10px] font-bold transition-all border ${currentFastCategory === cat.name ? 'bg-blue-600 text-white border-blue-600' : 'bg-white text-gray-500 border-gray-200'}`;
                btn.textContent = cat.name;
                btn.onclick = () => {
                    currentFastCategory = currentFastCategory === cat.name ? null : cat.name;
                    renderCategoryChips();
                    updateChatInputPreview();
                };
                categoryChips.appendChild(btn);
            });
        }

        function updateChatInputPreview() {
            const typePrefix = currentFastType === 'income' ? 'Pemasukan' : 'Pengeluaran';
            const categoryPart = currentFastCategory ? `${currentFastCategory} ` : '';

            const year = selectYear.value;
            const month = selectMonth.value;
            const day = selectDay.value;
            const dateStr = `${day}/${month}/${year}`;

            chatInput.value = `${typePrefix} ${categoryPart}${dateStr} `;
        }

        function adjustCustom(amount) {
            customNominalValue += amount;
            if (customNominalValue < 0) customNominalValue = 0;

            let display = '';
            if (customNominalValue >= 1000000) {
                display = (customNominalValue / 1000000) + 'jt';
            } else if (customNominalValue >= 1000) {
                display = (customNominalValue / 1000) + 'rb';
            } else {
                display = customNominalValue;
            }

            customNominalDisplay.textContent = display;
        }

        function selectFastNominal(amount) {
            const typePrefix = currentFastType === 'income' ? 'Pemasukan' : 'Pengeluaran';
            const categoryPart = currentFastCategory ? `${currentFastCategory} ` : '';
            const amountStr = amount >= 1000000 ? (amount / 1000000) + 'jt' : (amount / 1000) + 'rb';

            const year = selectYear.value;
            const month = selectMonth.value;
            const day = selectDay.value;
            const dateStr = `${day}/${month}/${year}`;

            chatInput.value = `${typePrefix} ${categoryPart}${amountStr} ${dateStr} `;
            chatInput.focus();
        }

        function selectCustomNominal() {
            const typePrefix = currentFastType === 'income' ? 'Pemasukan' : 'Pengeluaran';
            const categoryPart = currentFastCategory ? `${currentFastCategory} ` : '';
            const amountStr = customNominalDisplay.textContent;

            const year = selectYear.value;
            const month = selectMonth.value;
            const day = selectDay.value;
            const dateStr = `${day}/${month}/${year}`;

            chatInput.value = `${typePrefix} ${categoryPart}${amountStr} ${dateStr} `;
            chatInput.focus();
        }

        function addMessage(message, type = 'bot') {
            const div = document.createElement('div');
            div.className = type === 'user' ? 'flex items-start justify-end' : 'flex items-start';

            const contentClass = type === 'user' ?
                'bg-blue-600 text-white p-3 rounded-2xl rounded-tr-none text-sm max-w-[85%] shadow-md' :
                'bg-blue-50 text-gray-800 p-3 rounded-2xl rounded-tl-none text-sm max-w-[85%] shadow-sm whitespace-pre-line';

            const icon = type === 'user' ?
                `<div class="w-8 h-8 rounded-full bg-blue-600 flex items-center justify-center ml-2 flex-shrink-0 order-last"><i data-lucide="user" class="w-4 h-4 text-white"></i></div>` :
                `<div class="w-8 h-8 rounded-full bg-blue-100 flex items-center justify-center mr-2 flex-shrink-0"><i data-lucide="bot" class="w-4 h-4 text-blue-600"></i></div>`;

            // Replace markdown-style bold with <strong>
            const formattedMessage = message.replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>');

            div.innerHTML = `${icon}<div class="${contentClass}">${formattedMessage}</div>`;
            chatHistory.appendChild(div);
            chatHistory.scrollTop = chatHistory.scrollHeight;
            lucide.createIcons();
        }

        function setQuickReplies(replies) {
            quickReplies.innerHTML = '';
            if (replies.length > 0) {
                quickReplies.classList.remove('hidden');
                replies.forEach(reply => {
                    const btn = document.createElement('button');
                    btn.className =
                        'bg-white border border-blue-200 text-blue-600 px-4 py-1.5 rounded-full text-xs font-medium shadow-sm hover:bg-blue-50 transition-colors';
                    btn.textContent = reply;
                    btn.onclick = () => {
                        chatInput.value = reply;
                        chatForm.dispatchEvent(new Event('submit'));
                    };
                    quickReplies.appendChild(btn);
                });
            } else {
                quickReplies.classList.add('hidden');
            }
        }

        chatForm.onsubmit = async (e) => {
            e.preventDefault();
            const message = chatInput.value.trim();
            if (!message) return;

            addMessage(message, 'user');
            chatInput.value = '';
            setQuickReplies([]);

            // Show typing indicator
            typingIndicator.classList.remove('hidden');
            chatHistory.scrollTop = chatHistory.scrollHeight;

            try {
                const response = await fetch("{{ route('chat.process') }}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({
                        message
                    })
                });
                const data = await response.json();

                setTimeout(() => {
                    // Hide typing indicator
                    typingIndicator.classList.add('hidden');

                    addMessage(data.message, 'bot');
                    if (data.quick_replies) setQuickReplies(data.quick_replies);
                }, 800);

            } catch (error) {
                typingIndicator.classList.add('hidden');
                addMessage('Maaf, terjadi kesalahan. Coba lagi nanti.', 'bot');
            }
        };
    </script>
@endpush
