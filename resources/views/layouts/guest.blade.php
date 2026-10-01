<!DOCTYPE html>
<html lang="id" class="h-full bg-slate-950">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title ?? 'Autentikasi' }} - SIMAGANG (Sistem Informasi Magang Mahasiswa)</title>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Styles & Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
        }
        .mesh-gradient {
            background-color: #090d16;
            background-image: 
                radial-gradient(at 0% 0%, rgba(59, 130, 246, 0.18) 0px, transparent 50%),
                radial-gradient(at 100% 0%, rgba(99, 102, 241, 0.18) 0px, transparent 50%),
                radial-gradient(at 50% 100%, rgba(14, 165, 233, 0.15) 0px, transparent 50%);
        }
    </style>
</head>
<body class="h-full mesh-gradient text-slate-100 flex flex-col justify-center items-center py-12 px-4 sm:px-6 lg:px-8">
    <div class="w-full max-w-md sm:max-w-xl">
        <!-- Logo & Brand Header -->
        <div class="text-center mb-8">
            <div class="inline-flex items-center justify-center w-16 h-16 rounded-2xl bg-gradient-to-tr from-blue-600 via-indigo-600 to-cyan-500 shadow-xl shadow-blue-500/20 ring-1 ring-white/20 mb-4">
                <svg class="w-9 h-9 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                </svg>
            </div>
            <h1 class="text-3xl font-extrabold tracking-tight text-white sm:text-4xl">
                SIMAGANG
            </h1>
            <p class="mt-2 text-sm text-slate-400">
                Sistem Pendaftaran & Pengelolaan Magang Mahasiswa
            </p>
        </div>

        <!-- Flash Messages -->
        @if (session('success'))
            <div class="mb-4 p-4 rounded-xl bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 text-sm flex items-center gap-3">
                <svg class="w-5 h-5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                </svg>
                <span>{{ session('success') }}</span>
            </div>
        @endif

        @if (session('info'))
            <div class="mb-4 p-4 rounded-xl bg-blue-500/10 border border-blue-500/20 text-blue-400 text-sm flex items-center gap-3">
                <svg class="w-5 h-5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                </svg>
                <span>{{ session('info') }}</span>
            </div>
        @endif

        @if (session('status'))
            <div class="mb-4 p-4 rounded-xl bg-indigo-500/10 border border-indigo-500/20 text-indigo-400 text-sm flex items-center gap-3">
                <svg class="w-5 h-5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                </svg>
                <span>{{ session('status') }}</span>
            </div>
        @endif

        @if ($errors->any())
            <div class="mb-4 p-4 rounded-xl bg-rose-500/10 border border-rose-500/20 text-rose-400 text-sm">
                <div class="flex items-center gap-2 font-medium mb-1">
                    <svg class="w-5 h-5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                    </svg>
                    <span>Perhatikan kesalahan berikut:</span>
                </div>
                <ul class="list-disc list-inside space-y-1 text-xs text-rose-300">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <!-- Content Card -->
        <div class="bg-slate-900/80 backdrop-blur-xl border border-slate-800 rounded-3xl p-6 sm:p-10 shadow-2xl ring-1 ring-white/10">
            @yield('content')
        </div>

        <!-- Footer Copyright -->
        <p class="mt-8 text-center text-xs text-slate-500">
            &copy; {{ date('Y') }} SIMAGANG Mahasiswa. Hak Cipta Dilindungi Undang-Undang.
        </p>
    </div>
</body>
</html>
