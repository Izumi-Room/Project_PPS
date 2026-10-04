@extends('layouts.app', ['title' => 'Dashboard'])

@section('content')
<div class="max-w-7xl mx-auto space-y-10">
    <!-- Header -->
    <header class="flex items-end justify-between animate-fade-in" style="animation-delay: 100ms;">
        <div>
            <h1 class="text-4xl font-semibold text-white tracking-tighter">Dashboard</h1>
            <p class="text-slate-400 mt-2">Selamat datang kembali, {{ $user->name }}.</p>
        </div>
        <div class="text-right">
            <span class="text-[11px] font-medium text-slate-500 uppercase tracking-widest">Sesi Aktif</span>
            <div class="text-sm font-mono text-emerald-400">{{ now()->format('d M Y') }}</div>
        </div>
    </header>

    <!-- Main Content -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <!-- Role Status Card -->
        <div class="bg-slate-900/50 border border-white/5 rounded-2xl p-6 animate-fade-in" style="animation-delay: 200ms;">
            <h3 class="text-[11px] font-medium text-slate-500 uppercase tracking-widest mb-4">Role Aktif</h3>
            <div class="flex flex-wrap gap-2">
                @forelse ($user->roles as $role)
                    <span class="px-2.5 py-1 rounded-lg bg-white/5 border border-white/10 text-xs text-white">
                        {{ $role->label }}
                    </span>
                @empty
                    <span class="text-sm text-slate-500">Tanpa Role</span>
                @endforelse
            </div>
        </div>

        <!-- RBAC Panel -->
        <div class="bg-slate-900/50 border border-white/5 rounded-2xl p-6 animate-fade-in" style="animation-delay: 300ms;">
            <h3 class="text-[11px] font-medium text-slate-500 uppercase tracking-widest mb-4">Otorisasi</h3>
            <div class="space-y-3">
                <a href="{{ route('admin.superadmin') }}" class="flex items-center justify-between text-sm text-white hover:text-indigo-400 transition">
                    Panel Admin
                    <svg class="w-4 h-4 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </a>
                <a href="{{ route('academic.portal') }}" class="flex items-center justify-between text-sm text-white hover:text-indigo-400 transition">
                    Portal Akademik
                    <svg class="w-4 h-4 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </a>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="bg-slate-900/50 border border-white/5 rounded-2xl p-6 animate-fade-in" style="animation-delay: 400ms;">
            <h3 class="text-[11px] font-medium text-slate-500 uppercase tracking-widest mb-4">Pengaturan</h3>
            <div class="space-y-3">
                <a href="{{ route('profile.show') }}" class="block text-sm text-slate-300 hover:text-white transition">Profil & Keamanan</a>
                <a href="{{ route('notifications.index') }}" class="block text-sm text-slate-300 hover:text-white transition">Pusat Notifikasi</a>
            </div>
        </div>
    </div>

    <!-- System Matrix (Compact) -->
    <section class="animate-fade-in" style="animation-delay: 500ms;">
        <h3 class="text-lg font-medium text-white mb-6">Hak Akses Sistem</h3>
        <div class="bg-slate-900/50 border border-white/5 rounded-2xl overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm text-slate-400">
                    <thead class="bg-white/5 text-[11px] uppercase tracking-widest">
                        <tr>
                            <th class="px-6 py-4">Role</th>
                            <th class="px-6 py-4">Deskripsi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-white/5">
                        @foreach ($allRoles as $roleItem)
                            <tr class="hover:bg-white/5 transition">
                                <td class="px-6 py-4 text-white font-medium">{{ $roleItem->label }}</td>
                                <td class="px-6 py-4">{{ $roleItem->description }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </section>
</div>
@endsection
