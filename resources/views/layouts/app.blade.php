<!DOCTYPE html>
<html lang="id" class="h-full bg-slate-950">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title ?? 'Dashboard' }} - SIMAGANG</title>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=JetBrains+Mono:wght@400;500;600&display=swap" rel="stylesheet">

    <!-- Scripts & Vite -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        body {
            font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
        }
        .custom-scrollbar::-webkit-scrollbar {
            width: 6px;
        }
        .custom-scrollbar::-webkit-scrollbar-track {
            background: transparent;
        }
        .custom-scrollbar::-webkit-scrollbar-thumb {
            background: rgba(255, 255, 255, 0.1);
            border-radius: 9999px;
            border: 1px solid transparent;
            background-clip: content-box;
        }
        .custom-scrollbar::-webkit-scrollbar-thumb:hover {
            background: rgba(255, 255, 255, 0.2);
            background-clip: content-box;
        }
        @media (prefers-reduced-motion: reduce) {
            .animate-fade-in {
                animation: none !important;
                opacity: 1 !important;
                transform: none !important;
            }
        }
    </style>
</head>
<body class="h-full bg-slate-950 text-slate-100 flex flex-col antialiased selection:bg-indigo-500 selection:text-white">
    <div class="flex h-screen overflow-hidden">
        <!-- Sidebar Backdrop for Mobile -->
        <div id="sidebarBackdrop" onclick="toggleSidebar()" class="fixed inset-0 bg-slate-950/80 backdrop-blur-md z-40 lg:hidden hidden transition-all duration-300"></div>

        <!-- Sidebar -->
        @include('layouts.sidebar')

        <!-- Main Content Area -->
        <div class="flex-1 flex flex-col min-w-0 overflow-hidden">
            <!-- Top Navbar -->
            <header class="h-16 bg-slate-950/60 backdrop-blur-2xl border-b border-white/5 px-4 sm:px-8 flex items-center justify-between z-10 transition-colors">
                <div class="flex items-center gap-3">
                    <button type="button" onclick="toggleSidebar()" aria-label="Buka Navigasi Menu" class="lg:hidden p-2 rounded-xl text-slate-400 hover:text-white hover:bg-white/5 focus:outline-none focus:ring-1 focus:ring-white/20 transition cursor-pointer">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        </svg>
                    </button>
                    <div>
                        <h1 class="text-sm font-semibold text-white tracking-tight">
                            {{ $title ?? 'Dashboard' }}
                        </h1>
                    </div>
                </div>

                <!-- Right Header Actions -->
                <div class="flex items-center gap-2 sm:gap-3">
                    <!-- Quick API / State Pill -->
                    <a href="{{ route('api.me') }}" target="_blank" rel="noopener"
                        class="hidden md:inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-white/5 hover:bg-white/10 border border-white/5 text-slate-400 hover:text-slate-200 text-xs font-mono transition"
                        title="Status JSON API">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse" aria-hidden="true"></span>
                        <span>API Status</span>
                    </a>

                    <!-- Notification Bell Icon -->
                    @php
                        $unreadNotifsCount = Auth::user()->appNotifications()->unread()->count();
                    @endphp
                    <a href="{{ route('notifications.index') }}" aria-label="Pusat Notifikasi"
                        class="relative p-2 rounded-xl text-slate-400 hover:text-white hover:bg-white/5 transition flex items-center justify-center cursor-pointer">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                        </svg>
                        @if ($unreadNotifsCount > 0)
                            <span class="absolute top-1.5 right-1.5 flex h-2 w-2" aria-hidden="true">
                                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-indigo-400 opacity-75"></span>
                                <span class="relative inline-flex rounded-full h-2 w-2 bg-indigo-500"></span>
                            </span>
                        @endif
                    </a>

                    <!-- User Quick Info -->
                    <div class="flex items-center gap-3 pl-2 sm:pl-3 border-l border-white/5">
                        <div class="text-right hidden sm:block">
                            <p class="text-xs font-medium text-white truncate max-w-[140px]">{{ Auth::user()->name }}</p>
                            <p class="text-[10px] text-slate-500 truncate max-w-[140px]">{{ Auth::user()->email }}</p>
                        </div>
                        <a href="{{ route('profile.show') }}" aria-label="Profil Pengguna" class="w-8 h-8 rounded-xl bg-white/10 hover:bg-white/20 border border-white/10 flex items-center justify-center font-semibold text-white text-xs transition cursor-pointer">
                            {{ strtoupper(substr(Auth::user()->name, 0, 2)) }}
                        </a>
                    </div>
                </div>
            </header>

            <!-- Main Body Scrollable -->
            <main class="flex-1 overflow-y-auto custom-scrollbar p-4 sm:p-8 bg-slate-950 animate-fade-in">
                <!-- Flash Notification Messages -->
                @if (session('success'))
                    <div role="alert" class="mb-6 p-4 rounded-2xl bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 text-xs flex items-center justify-between">
                        <div class="flex items-center gap-2.5">
                            <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            <span>{{ session('success') }}</span>
                        </div>
                        <button type="button" aria-label="Tutup Notifikasi" onclick="this.parentElement.remove()" class="text-emerald-400/60 hover:text-emerald-400 p-1 cursor-pointer">
                            &times;
                        </button>
                    </div>
                @endif

                @if (session('error'))
                    <div role="alert" class="mb-6 p-4 rounded-2xl bg-rose-500/10 border border-rose-500/20 text-rose-400 text-xs flex items-center justify-between">
                        <div class="flex items-center gap-2.5">
                            <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                            <span>{{ session('error') }}</span>
                        </div>
                        <button type="button" aria-label="Tutup Notifikasi" onclick="this.parentElement.remove()" class="text-rose-400/60 hover:text-rose-400 p-1 cursor-pointer">
                            &times;
                        </button>
                    </div>
                @endif

                @yield('content')
            </main>
        </div>
    </div>

    <script>
        function toggleSidebar() {
            const sidebar = document.getElementById('appSidebar');
            const backdrop = document.getElementById('sidebarBackdrop');
            if (sidebar.classList.contains('hidden')) {
                sidebar.classList.remove('hidden');
                sidebar.classList.add('fixed', 'inset-y-0', 'left-0', 'z-50', 'flex', 'w-64', 'shadow-2xl');
                backdrop.classList.remove('hidden');
            } else {
                sidebar.classList.add('hidden');
                sidebar.classList.remove('fixed', 'inset-y-0', 'left-0', 'z-50', 'flex', 'w-64', 'shadow-2xl');
                backdrop.classList.add('hidden');
            }
        }
    </script>
</body>
</html>
