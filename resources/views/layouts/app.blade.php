<!DOCTYPE html>
<html lang="id" class="h-full bg-slate-950">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title ?? 'Dashboard' }} - SIMAGANG (Pendaftaran & Pengelolaan Magang)</title>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=JetBrains+Mono:wght@400;500;600&display=swap" rel="stylesheet">

    <!-- Styles & Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
        }
        code, pre {
            font-family: 'JetBrains Mono', monospace;
        }
        .custom-scrollbar::-webkit-scrollbar {
            width: 4px;
        }
        .custom-scrollbar::-webkit-scrollbar-track {
            background: rgba(15, 23, 42, 0.6);
        }
        .custom-scrollbar::-webkit-scrollbar-thumb {
            background: rgba(51, 65, 85, 0.8);
            border-radius: 4px;
        }
    </style>
</head>
<body class="h-full bg-slate-950 text-slate-100 flex flex-col antialiased">
    <div class="flex h-screen overflow-hidden">
        <!-- Sidebar -->
        @include('layouts.sidebar')

        <!-- Main Content Area -->
        <div class="flex-1 flex flex-col min-w-0 overflow-hidden">
            <!-- Top Navbar -->
            <header class="h-16 bg-slate-900/80 backdrop-blur-md border-b border-slate-800 px-4 sm:px-8 flex items-center justify-between z-10">
                <div class="flex items-center gap-3">
                    <button type="button" onclick="toggleSidebar()" class="lg:hidden p-2 rounded-xl text-slate-400 hover:text-white hover:bg-slate-800 focus:outline-none">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        </svg>
                    </button>
                    <div>
                        <h1 class="text-sm sm:text-base font-bold text-white tracking-tight">
                            {{ $title ?? 'Sistem Pengelolaan Magang' }}
                        </h1>
                        <p class="text-[11px] text-slate-400 hidden sm:block">
                            Portal Resmi Pendaftaran & Monitoring Magang Mahasiswa
                        </p>
                    </div>
                </div>

                <!-- Right Header Actions -->
                <div class="flex items-center gap-3 sm:gap-4">
                    <!-- Quick API / State Pill -->
                    <a href="{{ route('api.me') }}" target="_blank"
                        class="hidden md:inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-blue-500/10 hover:bg-blue-500/20 border border-blue-500/20 text-blue-400 text-xs font-mono transition"
                        title="Buka status JSON API untuk pengujian state autentikasi">
                        <span class="w-2 h-2 rounded-full bg-blue-400 animate-pulse"></span>
                        <span>GET /api/me</span>
                    </a>

                    <!-- Notification Bell Icon -->
                    @php
                        $unreadNotifsCount = Auth::user()->appNotifications()->unread()->count();
                    @endphp
                    <a href="{{ route('notifications.index') }}"
                        class="relative p-2 rounded-xl text-slate-400 hover:text-white hover:bg-slate-800 transition"
                        title="Pusat Notifikasi">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                        </svg>
                        @if ($unreadNotifsCount > 0)
                            <span class="absolute top-1.5 right-1.5 flex h-4 w-4">
                                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-rose-400 opacity-75"></span>
                                <span class="relative inline-flex rounded-full h-4 w-4 bg-rose-500 text-[9px] font-bold text-white items-center justify-center font-mono">
                                    {{ $unreadNotifsCount > 9 ? '9+' : $unreadNotifsCount }}
                                </span>
                            </span>
                        @endif
                    </a>

                    <!-- User Quick Info -->
                    <div class="flex items-center gap-2 pl-3 border-l border-slate-800">
                        <div class="text-right hidden sm:block">
                            <p class="text-xs font-bold text-white truncate max-w-[150px]">{{ Auth::user()->name }}</p>
                            <p class="text-[10px] text-slate-400 font-mono">{{ Auth::user()->email }}</p>
                        </div>
                        <a href="{{ route('profile.show') }}" class="w-9 h-9 rounded-xl bg-gradient-to-tr from-blue-600 to-indigo-600 flex items-center justify-center font-bold text-white text-xs ring-2 ring-blue-500/20 hover:ring-blue-400 transition" title="Profil Pengguna">
                            {{ strtoupper(substr(Auth::user()->name, 0, 2)) }}
                        </a>
                    </div>
                </div>
            </header>

            <!-- Main Body Scrollable -->
            <main class="flex-1 overflow-y-auto custom-scrollbar p-4 sm:p-8 bg-slate-950">
                <!-- Flash Notification Messages -->
                @if (session('success'))
                    <div class="mb-6 p-4 rounded-2xl bg-emerald-500/10 border border-emerald-500/20 text-emerald-300 text-sm flex items-center justify-between shadow-lg shadow-emerald-500/5">
                        <div class="flex items-center gap-3">
                            <svg class="w-5 h-5 flex-shrink-0 text-emerald-400" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                            </svg>
                            <span>{{ session('success') }}</span>
                        </div>
                        <button type="button" onclick="this.parentElement.remove()" class="text-emerald-400/60 hover:text-emerald-300">
                            &times;
                        </button>
                    </div>
                @endif

                @if (session('error'))
                    <div class="mb-6 p-4 rounded-2xl bg-rose-500/10 border border-rose-500/20 text-rose-300 text-sm flex items-center justify-between shadow-lg shadow-rose-500/5">
                        <div class="flex items-center gap-3">
                            <svg class="w-5 h-5 flex-shrink-0 text-rose-400" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                            </svg>
                            <span>{{ session('error') }}</span>
                        </div>
                        <button type="button" onclick="this.parentElement.remove()" class="text-rose-400/60 hover:text-rose-300">
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
            sidebar.classList.toggle('hidden');
        }
    </script>
</body>
</html>
