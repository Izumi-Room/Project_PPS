@extends('layouts.guest', ['title' => 'Masuk'])

@section('content')
    <div class="mb-8 animate-fade-in" style="animation-delay: 100ms;">
        <h2 class="text-2xl font-semibold text-white tracking-tight">Selamat Datang Kembali</h2>
        <p class="text-xs text-slate-400 mt-1.5">Masukkan akun Anda untuk melanjutkan ke portal magang.</p>
    </div>

    <form method="POST" action="{{ route('login') }}" class="space-y-5 animate-fade-in" style="animation-delay: 200ms;" id="loginForm">
        @csrf

        <!-- Email Field -->
        <div>
            <label for="email" class="block text-[11px] font-semibold text-slate-300 uppercase tracking-wider mb-2">
                Email Kampus
            </label>
            <div class="relative">
                <input type="email" id="email" name="email" value="{{ old('email') }}" required autofocus
                    placeholder="nama@magang.ac.id"
                    class="block w-full px-4 py-3 bg-slate-950/60 border border-white/10 rounded-xl text-sm text-white placeholder-slate-500 focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 transition-colors">
            </div>
        </div>

        <!-- Password Field -->
        <div>
            <div class="flex items-center justify-between mb-2">
                <label for="password" class="block text-[11px] font-semibold text-slate-300 uppercase tracking-wider">
                    Kata Sandi
                </label>
                <a href="{{ route('password.request') }}" class="text-[11px] text-slate-400 hover:text-white transition-colors">
                    Lupa sandi?
                </a>
            </div>
            <div class="relative">
                <input type="password" id="password" name="password" required
                    placeholder="••••••••"
                    class="block w-full pl-4 pr-11 py-3 bg-slate-950/60 border border-white/10 rounded-xl text-sm text-white placeholder-slate-500 focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 transition-colors">
                <button type="button" onclick="togglePasswordVisibility()" aria-label="Tampilkan atau sembunyikan kata sandi" class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-slate-500 hover:text-slate-300 transition-colors cursor-pointer">
                    <svg id="eyeIcon" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                    </svg>
                </button>
            </div>
        </div>

        <!-- Remember Me Checkbox -->
        <div class="flex items-center">
            <input id="remember" name="remember" type="checkbox"
                class="w-4 h-4 text-indigo-600 bg-slate-950 border-white/10 rounded focus:ring-indigo-500 focus:ring-offset-slate-900 cursor-pointer">
            <label for="remember" class="ml-2 block text-xs text-slate-400 cursor-pointer select-none">
                Ingat saya di perangkat ini
            </label>
        </div>

        <!-- Submit Button -->
        <button type="submit" id="btnSubmit"
            class="w-full py-3.5 px-4 rounded-xl text-slate-950 bg-white font-semibold text-sm hover:bg-slate-200 active:scale-[0.98] transition-all shadow-md flex items-center justify-center gap-2 cursor-pointer">
            <span>Masuk ke Akun</span>
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
        </button>
    </form>

    <!-- Quick Role Tester -->
    <div class="mt-8 pt-6 border-t border-white/5 animate-fade-in" style="animation-delay: 300ms;">
        <p class="text-[10px] font-semibold text-slate-500 uppercase tracking-wider mb-3">Akun Uji Coba Cepat</p>
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-2">
            <button type="button" onclick="fillCredentials('superadmin@magang.ac.id')" class="px-2.5 py-1.5 rounded-lg bg-white/5 hover:bg-white/10 border border-white/5 text-[11px] text-slate-300 font-medium transition-colors text-center cursor-pointer">Superadmin</button>
            <button type="button" onclick="fillCredentials('kaprodi@magang.ac.id')" class="px-2.5 py-1.5 rounded-lg bg-white/5 hover:bg-white/10 border border-white/5 text-[11px] text-slate-300 font-medium transition-colors text-center cursor-pointer">Kaprodi</button>
            <button type="button" onclick="fillCredentials('mhs@magang.ac.id')" class="px-2.5 py-1.5 rounded-lg bg-white/5 hover:bg-white/10 border border-white/5 text-[11px] text-slate-300 font-medium transition-colors text-center cursor-pointer">Mahasiswa</button>
            <button type="button" onclick="fillCredentials('tu@magang.ac.id')" class="px-2.5 py-1.5 rounded-lg bg-white/5 hover:bg-white/10 border border-white/5 text-[11px] text-slate-300 font-medium transition-colors text-center cursor-pointer">Tata Usaha</button>
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
