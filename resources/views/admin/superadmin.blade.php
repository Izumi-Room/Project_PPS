@extends('layouts.app', ['title' => 'Panel Super Administrator'])

@section('content')
<div class="space-y-8 max-w-7xl mx-auto animate-fade-in" style="animation-delay: 100ms;">
    <!-- Header -->
    <header class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-semibold text-white">Panel Super Administrator</h1>
            <p class="text-sm text-slate-400 mt-1">Audit otorisasi RBAC dan distribusi hak akses pengguna.</p>
        </div>
        <div class="flex items-center gap-2">
            <span class="text-xs text-slate-400">Status Akses:</span>
            <span class="px-2.5 py-1 rounded-lg text-xs font-mono font-medium {{ Auth::user()->hasRole('SUPERADMIN') ? 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/20' : 'bg-rose-500/10 text-rose-400 border border-rose-500/20' }}">
                {{ Auth::user()->hasRole('SUPERADMIN') ? 'AUTHORIZED' : 'DENIED' }}
            </span>
        </div>
    </header>

    <!-- Metrics Overview -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">
        <div class="bg-slate-900/40 border border-white/5 rounded-2xl p-6 space-y-2">
            <span class="text-[11px] font-medium text-slate-500 uppercase tracking-widest">Total Akun</span>
            <div class="text-3xl font-semibold text-white tracking-tight">{{ $users->total() }}</div>
            <p class="text-xs text-slate-400">Pengguna terdaftar di sistem</p>
        </div>
        <div class="bg-slate-900/40 border border-white/5 rounded-2xl p-6 space-y-2">
            <span class="text-[11px] font-medium text-slate-500 uppercase tracking-widest">Model RBAC</span>
            <div class="text-3xl font-semibold text-white tracking-tight">Many-to-Many</div>
            <p class="text-xs text-slate-400">Multi-role authorization</p>
        </div>
        <div class="bg-slate-900/40 border border-white/5 rounded-2xl p-6 space-y-2">
            <span class="text-[11px] font-medium text-slate-500 uppercase tracking-widest">Inheritance</span>
            <div class="text-3xl font-semibold text-white tracking-tight">Kaprodi &rarr; Superadmin</div>
            <p class="text-xs text-slate-400">Pewarisan hak akses otomatis</p>
        </div>
    </div>

    <!-- User Management Table with Multi-Role Display -->
    <div class="bg-slate-900/40 border border-white/5 rounded-2xl overflow-hidden">
        <div class="p-6 border-b border-white/5 flex items-center justify-between">
            <div>
                <h3 class="text-sm font-medium text-white">Daftar Pengguna & Otorisasi</h3>
                <p class="text-xs text-slate-400 mt-1">Audit penetapan peran langsung dari sistem.</p>
            </div>
        </div>

        <div class="overflow-x-auto custom-scrollbar">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="border-b border-white/5 text-[11px] uppercase tracking-widest text-slate-500">
                        <th class="py-4 px-6 font-medium">Pengguna</th>
                        <th class="py-4 px-6 font-medium">Email</th>
                        <th class="py-4 px-6 font-medium">Role Aktif</th>
                        <th class="py-4 px-6 font-medium text-right">ID</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-white/5 text-sm text-slate-300">
                    @foreach ($users as $u)
                        <tr class="hover:bg-white/5 transition">
                            <td class="py-4 px-6 font-medium text-white">{{ $u->name }}</td>
                            <td class="py-4 px-6 text-slate-400">{{ $u->email }}</td>
                            <td class="py-4 px-6">
                                <div class="flex flex-wrap gap-1">
                                    @forelse ($u->roles as $r)
                                        <span class="px-2 py-0.5 rounded-md bg-white/5 text-[10px] text-slate-300">
                                            {{ $r->name }}
                                        </span>
                                    @empty
                                        <span class="text-slate-500 text-xs italic">Tanpa Role</span>
                                    @endforelse
                                    @if ($u->hasRole('KAPRODI') && !$u->roles->contains('name', 'SUPERADMIN'))
                                        <span class="px-2 py-0.5 rounded-md bg-white/5 text-[10px] text-indigo-400">
                                            +SUPERADMIN
                                        </span>
                                    @endif
                                </div>
                            </td>
                            <td class="py-4 px-6 text-right font-mono text-xs text-slate-500">
                                #{{ $u->id }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        @if($users->hasPages())
            <div class="p-6 border-t border-white/5">
                {{ $users->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
