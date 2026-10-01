@extends('layouts.guest', ['title' => 'Masuk ke Sistem'])

@section('content')
    <div class="mb-6">
        <h2 class="text-xl font-bold text-white tracking-tight">Masuk ke Akun Anda</h2>
        <p class="text-xs text-slate-400 mt-1">Gunakan akun kampus untuk mengakses sistem pengelolaan magang.</p>
    </div>

    <form method="POST" action="{{ route('login') }}" class="space-y-5" id="loginForm">
        @csrf

        <!-- Email Field -->
        <div>
            <label for="email" class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-2">
                Alamat Email Kampus
            </label>
            <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-500">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.206" />
                    </svg>
                </div>
                <input type="email" id="email" name="email" value="{{ old('email') }}" required autofocus
                    placeholder="nama@magang.ac.id"
                    class="block w-full pl-11 pr-4 py-3 bg-slate-950/70 border border-slate-700/80 rounded-xl text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm transition">
            </div>
        </div>

        <!-- Password Field -->
        <div>
            <div class="flex items-center justify-between mb-2">
                <label for="password" class="block text-xs font-semibold text-slate-300 uppercase tracking-wider">
                    Kata Sandi
                </label>
                <a href="{{ route('password.request') }}" class="text-xs text-blue-400 hover:text-blue-300 transition">
                    Lupa Kata Sandi?
                </a>
            </div>
            <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-500">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                    </svg>
                </div>
                <input type="password" id="password" name="password" required
                    placeholder="••••••••"
                    class="block w-full pl-11 pr-11 py-3 bg-slate-950/70 border border-slate-700/80 rounded-xl text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm transition">
                <button type="button" onclick="togglePasswordVisibility()" class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-slate-500 hover:text-slate-300">
                    <svg id="eyeIcon" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                    </svg>
                </button>
            </div>
        </div>

        <!-- Remember Me Checkbox -->
        <div class="flex items-center">
            <input id="remember" name="remember" type="checkbox"
                class="w-4 h-4 text-blue-600 bg-slate-950 border-slate-700 rounded focus:ring-blue-500 focus:ring-offset-slate-900">
            <label for="remember" class="ml-2.5 block text-xs text-slate-300">
                Ingat sesi masuk di perangkat ini
            </label>
        </div>

        <!-- Submit Button -->
        <button type="submit" id="btnSubmit"
            class="w-full py-3.5 px-4 rounded-xl text-white font-semibold text-sm bg-gradient-to-r from-blue-600 via-indigo-600 to-blue-700 hover:from-blue-500 hover:to-indigo-500 shadow-lg shadow-blue-500/25 transition duration-200 flex items-center justify-center gap-2 group cursor-pointer">
            <span>Masuk Sekarang</span>
            <svg class="w-4 h-4 transform group-hover:translate-x-1 transition" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
            </svg>
        </button>
    </form>

    <!-- Quick Role Demo / Tester Selector -->
    <div class="mt-8 pt-6 border-t border-slate-800/80">
        <div class="flex items-center justify-between mb-3">
            <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">
                🧪 Akun Uji Coba Role (Klik untuk Mengisi Otomatis)
            </p>
            <span class="text-[10px] bg-blue-500/10 text-blue-400 border border-blue-500/20 px-2 py-0.5 rounded-full font-mono">
                Pass: Password123!
            </span>
        </div>

        <div class="grid grid-cols-2 sm:grid-cols-3 gap-2 text-xs">
            <button type="button" onclick="fillCredentials('superadmin@magang.ac.id')"
                class="px-2.5 py-1.5 rounded-lg bg-rose-500/10 hover:bg-rose-500/20 border border-rose-500/20 text-rose-300 font-medium text-left truncate transition">
                ⚡ Superadmin
            </button>
            <button type="button" onclick="fillCredentials('kaprodi@magang.ac.id')"
                class="px-2.5 py-1.5 rounded-lg bg-purple-500/10 hover:bg-purple-500/20 border border-purple-500/20 text-purple-300 font-medium text-left truncate transition" title="Kaprodi otomatis memiliki hak Superadmin">
                👑 Kaprodi (+Superadmin)
            </button>
            <button type="button" onclick="fillCredentials('mhs@magang.ac.id')"
                class="px-2.5 py-1.5 rounded-lg bg-blue-500/10 hover:bg-blue-500/20 border border-blue-500/20 text-blue-300 font-medium text-left truncate transition">
                🎓 Mahasiswa (MHS)
            </button>
            <button type="button" onclick="fillCredentials('tu@magang.ac.id')"
                class="px-2.5 py-1.5 rounded-lg bg-amber-500/10 hover:bg-amber-500/20 border border-amber-500/20 text-amber-300 font-medium text-left truncate transition">
                📋 Tata Usaha (TU)
            </button>
            <button type="button" onclick="fillCredentials('dosbing@magang.ac.id')"
                class="px-2.5 py-1.5 rounded-lg bg-emerald-500/10 hover:bg-emerald-500/20 border border-emerald-500/20 text-emerald-300 font-medium text-left truncate transition">
                👨‍🏫 Dosen Pembimbing
            </button>
            <button type="button" onclick="fillCredentials('dosenmk@magang.ac.id')"
                class="px-2.5 py-1.5 rounded-lg bg-teal-500/10 hover:bg-teal-500/20 border border-teal-500/20 text-teal-300 font-medium text-left truncate transition">
                📚 Dosen MK
            </button>
            <button type="button" onclick="fillCredentials('wadek1@magang.ac.id')"
                class="px-2.5 py-1.5 rounded-lg bg-indigo-500/10 hover:bg-indigo-500/20 border border-indigo-500/20 text-indigo-300 font-medium text-left truncate transition">
                🏛️ Wadek 1
            </button>
            <button type="button" onclick="fillCredentials('dualrole@magang.ac.id')"
                class="px-2.5 py-1.5 rounded-lg bg-fuchsia-500/10 hover:bg-fuchsia-500/20 border border-fuchsia-500/20 text-fuchsia-300 font-medium text-left truncate transition" title="User dengan 2 role sekaligus: DOSBING dan DOSEN_MK">
                🔄 Dual Role (Dosbing+MK)
            </button>
            <button type="button" onclick="fillCredentials('norole@magang.ac.id')"
                class="px-2.5 py-1.5 rounded-lg bg-slate-800 hover:bg-slate-700 border border-slate-700 text-slate-300 font-medium text-left truncate transition" title="Akun tanpa role aktif untuk uji negatif">
                🚫 Akun Tanpa Role
            </button>
        </div>
    </div>

    <script>
        function fillCredentials(email) {
            document.getElementById('email').value = email;
            document.getElementById('password').value = 'Password123!';
        }

        function togglePasswordVisibility() {
            const pwd = document.getElementById('password');
            pwd.type = pwd.type === 'password' ? 'text' : 'password';
        }
    </script>
@endsection
