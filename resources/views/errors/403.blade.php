<!DOCTYPE html>
<html lang="id" class="h-full bg-slate-950">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>403 - Akses Ditolak (Terlarang) | SIMAGANG</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>body { font-family: 'Plus Jakarta Sans', sans-serif; }</style>
</head>
<body class="h-full bg-slate-950 text-slate-100 flex items-center justify-center p-6">
    <div class="max-w-lg w-full text-center">
        <div class="inline-flex items-center justify-center w-20 h-20 rounded-3xl bg-rose-500/10 border border-rose-500/20 text-rose-400 mb-6 shadow-xl shadow-rose-500/10">
            <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
            </svg>
        </div>
        <span class="inline-block text-xs font-mono font-bold tracking-widest uppercase px-3 py-1 rounded-full bg-rose-500/10 text-rose-400 border border-rose-500/20 mb-3">
            Error 403 • Forbidden
        </span>
        <h1 class="text-3xl sm:text-4xl font-extrabold text-white mb-3">Akses Ditolak</h1>
        <p class="text-slate-400 text-sm mb-6 leading-relaxed">
            {{ $exception->getMessage() ?: 'Anda tidak memiliki hak akses atau role yang sesuai untuk membuka halaman ini. Keamanan backend kami secara aktif memvalidasi setiap permintaan.' }}
        </p>

        @auth
            <div class="bg-slate-900/80 border border-slate-800 rounded-2xl p-4 mb-8 text-left text-xs">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-slate-400">Pengguna Terhubung:</span>
                    <span class="font-semibold text-white">{{ Auth::user()->name }}</span>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-slate-400">Role Terdaftar:</span>
                    <div class="flex flex-wrap gap-1 justify-end">
                        @forelse (Auth::user()->roles as $role)
                            <span class="px-2 py-0.5 rounded bg-blue-500/20 text-blue-300 font-mono text-[10px]">
                                {{ $role->name }}
                            </span>
                        @empty
                            <span class="text-rose-400 italic">Tidak ada role aktif</span>
                        @endforelse
                    </div>
                </div>
            </div>
        @endauth

        <div class="flex flex-col sm:flex-row gap-3 justify-center">
            <a href="{{ route('dashboard') }}" class="px-6 py-3 rounded-xl bg-blue-600 hover:bg-blue-500 text-white font-semibold text-sm shadow-lg shadow-blue-500/20 transition">
                Kembali ke Dashboard
            </a>
            <a href="javascript:history.back()" class="px-6 py-3 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 font-semibold text-sm transition">
                Halaman Sebelumnya
            </a>
        </div>
    </div>
</body>
</html>
