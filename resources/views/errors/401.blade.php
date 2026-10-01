<!DOCTYPE html>
<html lang="id" class="h-full bg-slate-950">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>401 - Tidak Terautentikasi | SIMAGANG</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>body { font-family: 'Plus Jakarta Sans', sans-serif; }</style>
</head>
<body class="h-full bg-slate-950 text-slate-100 flex items-center justify-center p-6">
    <div class="max-w-md w-full text-center">
        <div class="inline-flex items-center justify-center w-20 h-20 rounded-3xl bg-amber-500/10 border border-amber-500/20 text-amber-400 mb-6">
            <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
            </svg>
        </div>
        <span class="inline-block text-xs font-mono font-bold tracking-widest uppercase px-3 py-1 rounded-full bg-amber-500/10 text-amber-400 border border-amber-500/20 mb-3">
            Error 401
        </span>
        <h1 class="text-3xl font-extrabold text-white mb-2">Autentikasi Diperlukan</h1>
        <p class="text-slate-400 text-sm mb-8 leading-relaxed">
            Sesi Anda belum terautentikasi atau telah berakhir. Silakan masuk terlebih dahulu untuk mengakses sumber daya ini.
        </p>
        <div class="flex flex-col sm:flex-row gap-3 justify-center">
            <a href="{{ route('login') }}" class="px-6 py-3 rounded-xl bg-blue-600 hover:bg-blue-500 text-white font-semibold text-sm shadow-lg shadow-blue-500/20 transition">
                Masuk ke Sistem
            </a>
            <a href="javascript:history.back()" class="px-6 py-3 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 font-semibold text-sm transition">
                Kembali
            </a>
        </div>
    </div>
</body>
</html>
