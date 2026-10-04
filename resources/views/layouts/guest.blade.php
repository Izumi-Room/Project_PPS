<!DOCTYPE html>
<html lang="id" class="h-full bg-slate-950">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title ?? 'Masuk' }} - SIMAGANG</title>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <!-- Styles & Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        body {
            font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
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
<body class="h-full bg-slate-950 text-slate-100 flex items-center justify-center p-4 sm:p-6 lg:p-8 selection:bg-indigo-500 selection:text-white">
    <div class="w-full max-w-5xl grid grid-cols-1 lg:grid-cols-12 rounded-3xl border border-white/10 bg-slate-900/40 backdrop-blur-2xl shadow-2xl overflow-hidden min-h-[640px]">
        <!-- Visual / Branding Side (Desktop) -->
        <div class="hidden lg:flex lg:col-span-5 bg-gradient-to-br from-indigo-950/40 via-slate-900/40 to-slate-950/60 p-10 flex-col justify-between border-r border-white/5 relative overflow-hidden">
            <div class="absolute -right-20 -top-20 w-72 h-72 bg-indigo-500/10 rounded-full blur-3xl pointer-events-none"></div>
            
            <!-- Brand -->
            <div class="relative z-10 animate-fade-in">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-xl bg-white text-slate-950 flex items-center justify-center font-bold text-sm tracking-tighter shadow-sm">
                        SI
                    </div>
                    <span class="font-bold text-base tracking-tight text-white">SIMAGANG</span>
                </div>
            </div>

            <!-- Value Proposition -->
            <div class="relative z-10 space-y-4 animate-fade-in" style="animation-delay: 150ms;">
                <h3 class="text-2xl font-medium tracking-tight text-white leading-snug">
                    Portal Terpadu Pengelolaan Magang & MBKM Mahasiswa.
                </h3>
                <p class="text-xs text-slate-400 leading-relaxed">
                    Akses pendaftaran, logbook harian, konversi nilai, hingga seminar magang dalam satu sistem terintegrasi.
                </p>
            </div>

            <!-- Footer Badge -->
            <div class="relative z-10 text-[11px] text-slate-500 font-mono animate-fade-in" style="animation-delay: 250ms;">
                Fakultas Ilmu Komputer &bull; &copy; {{ date('Y') }}
            </div>
        </div>

        <!-- Form Side -->
        <div class="lg:col-span-7 p-6 sm:p-12 flex flex-col justify-center">
            <div class="max-w-md w-full mx-auto">
                <!-- Mobile Brand Header -->
                <div class="lg:hidden mb-8 text-center animate-fade-in">
                    <div class="inline-flex w-10 h-10 rounded-xl bg-white text-slate-950 items-center justify-center font-bold text-sm mb-3">
                        SI
                    </div>
                    <h1 class="text-xl font-bold tracking-tight text-white">SIMAGANG</h1>
                </div>

                <!-- Flash Messages -->
                @if (session('success'))
                    <div class="mb-6 p-4 rounded-xl bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 text-xs flex items-center gap-2.5">
                        <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        <span>{{ session('success') }}</span>
                    </div>
                @endif

                @if (session('status'))
                    <div class="mb-6 p-4 rounded-xl bg-indigo-500/10 border border-indigo-500/20 text-indigo-400 text-xs flex items-center gap-2.5">
                        <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <span>{{ session('status') }}</span>
                    </div>
                @endif

                @if ($errors->any())
                    <div class="mb-6 p-4 rounded-xl bg-rose-500/10 border border-rose-500/20 text-rose-400 text-xs space-y-1">
                        @foreach ($errors->all() as $error)
                            <div class="flex items-center gap-2">
                                <span class="w-1 h-1 rounded-full bg-rose-400"></span>
                                <span>{{ $error }}</span>
                            </div>
                        @endforeach
                    </div>
                @endif

                @yield('content')
            </div>
        </div>
    </div>
</body>
</html>
