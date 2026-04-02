<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <!-- PWA Meta Tags -->
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="FinanceApp">
    <link rel="manifest" href="/manifest.json">
    <link rel="apple-touch-icon" href="https://coresg-normal.trae.ai/api/ide/v1/text_to_image?prompt=finance%20app%20icon%20modern%20minimalist%20blue%20green&image_size=square">
    <meta name="theme-color" content="#1e40af">

    <title>Finance App - @yield('title')</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap');
        body { font-family: 'Inter', sans-serif; }
        .bottom-nav-active { color: #1e40af; }
        
        /* Loading Screen Style */
        #splash-screen {
            position: fixed;
            inset: 0;
            z-index: 9999;
            background: white;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            transition: opacity 0.5s ease-out, visibility 0.5s;
        }
        
        .loading-dots div {
            animation: loading-dots 1.4s infinite ease-in-out both;
        }
        .loading-dots div:nth-child(1) { animation-delay: -0.32s; }
        .loading-dots div:nth-child(2) { animation-delay: -0.16s; }
        
        @keyframes loading-dots {
            0%, 80%, 100% { transform: scale(0); }
            40% { transform: scale(1.0); }
        }
    </style>
</head>
<body class="bg-gray-50 text-gray-900 pb-20">
    <!-- Global Loading Splash Screen -->
    <div id="splash-screen">
        <div class="w-20 h-20 bg-blue-600 rounded-3xl flex items-center justify-center mb-6 shadow-xl animate-bounce">
            <i data-lucide="wallet" class="w-10 h-10 text-white"></i>
        </div>
        <h1 class="text-xl font-bold text-gray-800 mb-2">Finance App</h1>
        <div class="loading-dots flex gap-1">
            <div class="w-2 h-2 bg-blue-400 rounded-full"></div>
            <div class="w-2 h-2 bg-blue-400 rounded-full"></div>
            <div class="w-2 h-2 bg-blue-400 rounded-full"></div>
        </div>
    </div>

    <div class="max-w-md mx-auto bg-white min-h-screen shadow-lg relative opacity-0 transition-opacity duration-500" id="main-app">
        <!-- Header -->
        <header class="bg-blue-800 text-white p-4 sticky top-0 z-10 flex justify-between items-center">
            <h1 class="text-lg font-bold">@yield('header_title', 'Finance App')</h1>
            <div class="w-8 h-8 rounded-full bg-blue-600 flex items-center justify-center">
                <i data-lucide="user" class="w-5 h-5"></i>
            </div>
        </header>

        <!-- Main Content -->
        <main class="p-4">
            @yield('content')
        </main>

        <!-- Bottom Navigation -->
        <nav class="fixed bottom-0 left-0 right-0 max-w-md mx-auto bg-white border-t border-gray-200 flex justify-around p-2 z-20">
            <a href="{{ route('dashboard') }}" class="flex flex-col items-center p-2 {{ request()->routeIs('dashboard') ? 'text-blue-700' : 'text-gray-400' }}">
                <i data-lucide="layout-dashboard" class="w-6 h-6"></i>
                <span class="text-xs mt-1">Dashboard</span>
            </a>
            <a href="{{ route('chat') }}" class="flex flex-col items-center p-2 {{ request()->routeIs('chat') ? 'text-blue-700' : 'text-gray-400' }}">
                <i data-lucide="message-square" class="w-6 h-6"></i>
                <span class="text-xs mt-1">Chat</span>
            </a>
            <a href="{{ route('reports') }}" class="flex flex-col items-center p-2 {{ request()->routeIs('reports') ? 'text-blue-700' : 'text-gray-400' }}">
                <i data-lucide="bar-chart-3" class="w-6 h-6"></i>
                <span class="text-xs mt-1">Rekap</span>
            </a>
        </nav>
    </div>

    <script>
        // Hide Splash Screen after load
        window.addEventListener('load', () => {
            const splash = document.getElementById('splash-screen');
            const mainApp = document.getElementById('main-app');
            
            setTimeout(() => {
                splash.style.opacity = '0';
                splash.style.visibility = 'hidden';
                mainApp.classList.remove('opacity-0');
                mainApp.classList.add('opacity-100');
            }, 800);
        });

        // Register PWA Service Worker
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', () => {
                navigator.serviceWorker.register('/sw.js').then(registration => {
                    console.log('SW registered: ', registration);
                }).catch(registrationError => {
                    console.log('SW registration failed: ', registrationError);
                });
            });
        }

        lucide.createIcons();
    </script>
    @stack('scripts')
</body>
</html>
