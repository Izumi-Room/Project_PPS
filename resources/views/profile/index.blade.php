@extends('layouts.app', ['title' => 'Profil & Keamanan'])

@section('content')
<div class="max-w-4xl mx-auto space-y-10">
    <!-- Profile Header -->
    <header class="flex flex-col md:flex-row items-center gap-8 animate-fade-in" style="animation-delay: 100ms;">
        <div class="w-24 h-24 rounded-3xl bg-indigo-500/20 text-indigo-300 border border-indigo-500/30 flex items-center justify-center text-3xl font-semibold shadow-2xl">
            {{ strtoupper(substr($user->name, 0, 2)) }}
        </div>
        <div class="text-center md:text-left space-y-2">
            <h1 class="text-3xl font-semibold text-white tracking-tight">{{ $user->name }}</h1>
            <p class="text-slate-400 font-mono text-sm">{{ $user->email }}</p>
            <div class="flex flex-wrap justify-center md:justify-start gap-2 pt-2">
                @foreach ($user->roles as $role)
                    <span class="px-2.5 py-1 rounded-lg bg-white/5 border border-white/10 text-[10px] font-medium text-slate-300 uppercase tracking-wider">
                        {{ $role->label }}
                    </span>
                @endforeach
            </div>
        </div>
    </header>

    <!-- Content Sections -->
    <div class="grid grid-cols-1 gap-8">
        <!-- Account Info -->
        <section class="bg-slate-900/50 border border-white/5 rounded-2xl p-8 animate-fade-in" style="animation-delay: 200ms;">
            <div class="flex items-center justify-between mb-8">
                <h3 class="text-sm font-medium text-white">Informasi Akun</h3>
                <span class="text-[10px] font-mono text-slate-500 bg-white/5 px-2 py-1 rounded-lg">ID #{{ $user->id }}</span>
            </div>
            
            <form method="POST" action="{{ route('profile.update') }}" class="space-y-6">
                @csrf
                @method('PUT')
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="space-y-2">
                        <label for="name" class="block text-[11px] font-medium text-slate-500 uppercase tracking-widest">Nama Lengkap</label>
                        <input type="text" id="name" name="name" value="{{ old('name', $user->name) }}" required
                            class="block w-full px-4 py-3 bg-slate-950/60 border border-white/10 rounded-xl text-sm text-white focus:outline-none focus:border-indigo-500/50 focus:ring-1 focus:ring-indigo-500/50 transition-all">
                        @error('name') <p class="text-[11px] text-rose-400 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div class="space-y-2">
                        <label for="email" class="block text-[11px] font-medium text-slate-500 uppercase tracking-widest">Email Kampus</label>
                        <input type="email" id="email" name="email" value="{{ old('email', $user->email) }}" required
                            class="block w-full px-4 py-3 bg-slate-950/60 border border-white/10 rounded-xl text-sm text-white focus:outline-none focus:border-indigo-500/50 focus:ring-1 focus:ring-indigo-500/50 transition-all">
                        @error('email') <p class="text-[11px] text-rose-400 mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div class="pt-4 flex justify-end">
                    <button type="submit" class="px-6 py-3 rounded-xl bg-white text-slate-950 font-medium text-sm hover:bg-slate-200 active:scale-[0.98] transition-all shadow-lg cursor-pointer">
                        Simpan Perubahan
                    </button>
                </div>
            </form>
        </section>

        <!-- Security -->
        <section class="bg-slate-900/50 border border-white/5 rounded-2xl p-8 animate-fade-in" style="animation-delay: 300ms;">
            <h3 class="text-sm font-medium text-white mb-8">Keamanan & Sandi</h3>
            
            <form method="POST" action="{{ route('profile.password') }}" class="space-y-6">
                @csrf
                @method('PUT')

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div class="space-y-2">
                        <label for="current_password" class="block text-[11px] font-medium text-slate-500 uppercase tracking-widest">Sandi Saat Ini</label>
                        <input type="password" id="current_password" name="current_password" required placeholder="••••••••"
                            class="block w-full px-4 py-3 bg-slate-950/60 border border-white/10 rounded-xl text-sm text-white focus:outline-none focus:border-indigo-500/50 focus:ring-1 focus:ring-indigo-500/50 transition-all">
                        @error('current_password') <p class="text-[11px] text-rose-400 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div class="space-y-2">
                        <label for="password" class="block text-[11px] font-medium text-slate-500 uppercase tracking-widest">Sandi Baru</label>
                        <input type="password" id="password" name="password" required placeholder="Min. 8 Karakter"
                            class="block w-full px-4 py-3 bg-slate-950/60 border border-white/10 rounded-xl text-sm text-white focus:outline-none focus:border-indigo-500/50 focus:ring-1 focus:ring-indigo-500/50 transition-all">
                        @error('password') <p class="text-[11px] text-rose-400 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div class="space-y-2">
                        <label for="password_confirmation" class="block text-[11px] font-medium text-slate-500 uppercase tracking-widest">Konfirmasi Sandi</label>
                        <input type="password" id="password_confirmation" name="password_confirmation" required placeholder="••••••••"
                            class="block w-full px-4 py-3 bg-slate-950/60 border border-white/10 rounded-xl text-sm text-white focus:outline-none focus:border-indigo-500/50 focus:ring-1 focus:ring-indigo-500/50 transition-all">
                    </div>
                </div>

                <div class="pt-4 flex justify-end">
                    <button type="submit" class="px-6 py-3 rounded-xl bg-white/5 border border-white/10 text-white font-medium text-sm hover:bg-white/10 active:scale-[0.98] transition-all cursor-pointer">
                        Perbarui Sandi
                    </button>
                </div>
            </form>
        </section>

        <!-- System Details -->
        <section class="bg-white/5 border border-white/5 rounded-2xl p-8 animate-fade-in" style="animation-delay: 400ms;">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div>
                    <h4 class="text-[11px] font-medium text-slate-500 uppercase tracking-widest mb-1">Riwayat Akun</h4>
                    <p class="text-sm text-slate-300">Bergabung pada {{ $user->created_at ? $user->created_at->translatedFormat('d F Y') : '-' }}</p>
                </div>
                <div class="flex items-center gap-2 text-emerald-400">
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" /></svg>
                    <span class="text-xs font-medium">Email Terverifikasi</span>
                </div>
            </div>
        </section>
    </div>
</div>
@endsection
