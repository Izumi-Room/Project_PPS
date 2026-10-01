@extends('layouts.guest', ['title' => 'Atur Ulang Kata Sandi'])

@section('content')
    <div class="mb-6">
        <h2 class="text-xl font-bold text-white tracking-tight">Atur Ulang Kata Sandi</h2>
        <p class="text-xs text-slate-400 mt-1">Masukkan kata sandi baru untuk akun Anda.</p>
    </div>

    <form method="POST" action="{{ route('password.update') }}" class="space-y-5">
        @csrf
        <input type="hidden" name="token" value="{{ $token }}">

        <div>
            <label for="email" class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-2">
                Alamat Email
            </label>
            <input type="email" id="email" name="email" value="{{ old('email', $email) }}" required
                class="block w-full px-4 py-3 bg-slate-950/70 border border-slate-700/80 rounded-xl text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm transition">
        </div>

        <div>
            <label for="password" class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-2">
                Kata Sandi Baru (Minimal 8 Karakter)
            </label>
            <input type="password" id="password" name="password" required
                placeholder="••••••••"
                class="block w-full px-4 py-3 bg-slate-950/70 border border-slate-700/80 rounded-xl text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm transition">
        </div>

        <div>
            <label for="password_confirmation" class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-2">
                Konfirmasi Kata Sandi Baru
            </label>
            <input type="password" id="password_confirmation" name="password_confirmation" required
                placeholder="••••••••"
                class="block w-full px-4 py-3 bg-slate-950/70 border border-slate-700/80 rounded-xl text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm transition">
        </div>

        <button type="submit"
            class="w-full py-3.5 px-4 rounded-xl text-white font-semibold text-sm bg-gradient-to-r from-blue-600 via-indigo-600 to-blue-700 hover:from-blue-500 hover:to-indigo-500 shadow-lg shadow-blue-500/25 transition duration-200 cursor-pointer">
            Simpan Kata Sandi Baru
        </button>

        <div class="text-center pt-2">
            <a href="{{ route('login') }}" class="text-xs text-slate-400 hover:text-white transition">
                Batal dan Kembali ke Masuk
            </a>
        </div>
    </form>
@endsection
