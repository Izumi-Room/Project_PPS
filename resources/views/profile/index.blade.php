@extends('layouts.app', ['title' => 'Profil & Keamanan Akun'])

@section('content')
<div class="space-y-8 max-w-5xl mx-auto">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h2 class="text-2xl font-extrabold text-white tracking-tight">Pengaturan Profil & Keamanan</h2>
            <p class="text-xs text-slate-400 mt-1">Kelola informasi pribadi akun Anda dan perbarui kata sandi.</p>
        </div>
        <div class="flex items-center gap-2">
            <span class="text-xs text-slate-400">ID Akun:</span>
            <span class="font-mono text-xs bg-slate-800 text-slate-200 px-2 py-1 rounded-lg">#{{ $user->id }}</span>
        </div>
    </div>

    <!-- User Role & RBAC Overview Card -->
    <div class="bg-slate-900/70 border border-slate-800 rounded-3xl p-6 sm:p-8">
        <h3 class="text-base font-bold text-white mb-1">Status Role & Hak Akses Akun</h3>
        <p class="text-xs text-slate-400 mb-4">Role ditentukan oleh administrator dan diterapkan langsung oleh sistem RBAC.</p>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div class="p-4 rounded-2xl bg-slate-950/60 border border-slate-800">
                <span class="text-xs text-slate-400 block mb-2">Role Terpasang (Many-to-Many):</span>
                <div class="flex flex-wrap gap-1.5">
                    @forelse ($user->roles as $role)
                        <span class="px-2.5 py-1 rounded-lg text-xs font-mono font-bold
                            {{ $role->name === 'SUPERADMIN' ? 'bg-rose-500/20 text-rose-300 border border-rose-500/30' : '' }}
                            {{ $role->name === 'KAPRODI' ? 'bg-purple-500/20 text-purple-300 border border-purple-500/30' : '' }}
                            {{ $role->name === 'MHS' ? 'bg-blue-500/20 text-blue-300 border border-blue-500/30' : '' }}
                            {{ $role->name === 'TU' ? 'bg-amber-500/20 text-amber-300 border border-amber-500/30' : '' }}
                            {{ $role->name === 'DOSBING' ? 'bg-emerald-500/20 text-emerald-300 border border-emerald-500/30' : '' }}
                            {{ $role->name === 'DOSEN_MK' ? 'bg-teal-500/20 text-teal-300 border border-teal-500/30' : '' }}
                            {{ $role->name === 'WADEK1' ? 'bg-indigo-500/20 text-indigo-300 border border-indigo-500/30' : '' }}">
                            {{ $role->name }} - {{ $role->label }}
                        </span>
                    @empty
                        <span class="text-rose-400 text-xs italic">Tidak ada role terdaftar</span>
                    @endforelse

                    @if ($user->hasRole('KAPRODI') && !$user->roles->contains('name', 'SUPERADMIN'))
                        <span class="px-2.5 py-1 rounded-lg text-xs font-mono font-bold bg-rose-500/20 text-rose-300 border border-rose-500/30">
                            + SUPERADMIN (Inherited from KAPRODI)
                        </span>
                    @endif
                </div>
            </div>

            <div class="p-4 rounded-2xl bg-slate-950/60 border border-slate-800">
                <span class="text-xs text-slate-400 block mb-2">Tanggal Akun Dibuat:</span>
                <p class="text-sm font-semibold text-white">
                    {{ $user->created_at ? $user->created_at->translatedFormat('d F Y, H:i') : '-' }} WIB
                </p>
                <span class="text-xs text-slate-400 block mt-3 mb-1">Verifikasi Email:</span>
                <span class="inline-flex items-center gap-1.5 text-xs text-emerald-400">
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" /></svg>
                    Email Terverifikasi
                </span>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
        <!-- Update General Profile Information Form -->
        <div class="bg-slate-900/70 border border-slate-800 rounded-3xl p-6 sm:p-8">
            <h3 class="text-base font-bold text-white mb-1">Informasi Umum</h3>
            <p class="text-xs text-slate-400 mb-6">Perbarui nama tampilan dan alamat email Anda.</p>

            <form method="POST" action="{{ route('profile.update') }}" class="space-y-5">
                @csrf
                @method('PUT')

                <div>
                    <label for="name" class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-2">
                        Nama Lengkap
                    </label>
                    <input type="text" id="name" name="name" value="{{ old('name', $user->name) }}" required
                        class="block w-full px-4 py-3 bg-slate-950/70 border border-slate-700/80 rounded-xl text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm transition">
                    @error('name')
                        <p class="text-xs text-rose-400 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="email" class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-2">
                        Alamat Email Kampus
                    </label>
                    <input type="email" id="email" name="email" value="{{ old('email', $user->email) }}" required
                        class="block w-full px-4 py-3 bg-slate-950/70 border border-slate-700/80 rounded-xl text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm transition">
                    @error('email')
                        <p class="text-xs text-rose-400 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <button type="submit"
                    class="w-full py-3 px-4 rounded-xl text-white font-semibold text-xs bg-blue-600 hover:bg-blue-500 shadow-lg shadow-blue-500/25 transition cursor-pointer">
                    Simpan Perubahan Profil
                </button>
            </form>
        </div>

        <!-- Update Password Form -->
        <div class="bg-slate-900/70 border border-slate-800 rounded-3xl p-6 sm:p-8">
            <h3 class="text-base font-bold text-white mb-1">Ubah Kata Sandi</h3>
            <p class="text-xs text-slate-400 mb-6">Pastikan akun Anda menggunakan kata sandi yang aman.</p>

            <form method="POST" action="{{ route('profile.password') }}" class="space-y-5">
                @csrf
                @method('PUT')

                <div>
                    <label for="current_password" class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-2">
                        Kata Sandi Saat Ini
                    </label>
                    <input type="password" id="current_password" name="current_password" required
                        placeholder="••••••••"
                        class="block w-full px-4 py-3 bg-slate-950/70 border border-slate-700/80 rounded-xl text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm transition">
                    @error('current_password')
                        <p class="text-xs text-rose-400 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="password" class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-2">
                        Kata Sandi Baru
                    </label>
                    <input type="password" id="password" name="password" required
                        placeholder="Minimal 8 karakter"
                        class="block w-full px-4 py-3 bg-slate-950/70 border border-slate-700/80 rounded-xl text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm transition">
                    @error('password')
                        <p class="text-xs text-rose-400 mt-1">{{ $message }}</p>
                    @enderror
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
                    class="w-full py-3 px-4 rounded-xl text-white font-semibold text-xs bg-slate-800 hover:bg-slate-700 border border-slate-700 text-slate-200 transition cursor-pointer">
                    Perbarui Kata Sandi
                </button>
            </form>
        </div>
    </div>
</div>
@endsection
