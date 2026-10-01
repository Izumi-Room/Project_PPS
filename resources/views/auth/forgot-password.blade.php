@extends('layouts.guest', ['title' => 'Lupa Kata Sandi'])

@section('content')
    <div class="mb-6">
        <h2 class="text-xl font-bold text-white tracking-tight">Pemulihan Kata Sandi</h2>
        <p class="text-xs text-slate-400 mt-1">Masukkan alamat email Anda untuk menerima tautan pemulihan kata sandi.</p>
    </div>

    @if (session('dev_reset_url'))
        <div class="mb-6 p-4 rounded-xl bg-amber-500/10 border border-amber-500/30 text-amber-300 text-xs">
            <p class="font-semibold mb-1">🔗 Mode Pengujian Lokal:</p>
            <p class="mb-2">Karena ini adalah environment lokal tanpa SMTP publik, Anda dapat langsung mengklik tautan berikut untuk melanjutkan:</p>
            <a href="{{ session('dev_reset_url') }}" class="inline-block px-3 py-1.5 rounded-lg bg-amber-500 text-slate-950 font-bold hover:bg-amber-400 transition">
                Buka Halaman Reset Kata Sandi &rarr;
            </a>
        </div>
    @endif

    <form method="POST" action="{{ route('password.email') }}" class="space-y-5">
        @csrf

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
                    class="block w-full pl-11 pr-4 py-3 bg-slate-950/70 border border-slate-700/80 rounded-xl text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm transition">
            </div>
        </div>

        <button type="submit"
            class="w-full py-3.5 px-4 rounded-xl text-white font-semibold text-sm bg-blue-600 hover:bg-blue-500 shadow-lg shadow-blue-500/25 transition duration-200 cursor-pointer">
            Kirim Tautan Pemulihan
        </button>

        <div class="text-center pt-2">
            <a href="{{ route('login') }}" class="text-xs text-slate-400 hover:text-white transition inline-flex items-center gap-1.5">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                <span>Kembali ke Halaman Masuk</span>
            </a>
        </div>
    </form>
@endsection
