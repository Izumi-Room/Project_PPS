@extends('layouts.app', ['title' => 'Panel Super Administrator'])

@section('content')
<div class="space-y-8 max-w-7xl mx-auto">
    <!-- Header Card -->
    <div class="rounded-3xl bg-gradient-to-r from-rose-950/70 via-slate-900 to-slate-900 border border-rose-900/40 p-6 sm:p-8 shadow-xl">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-rose-500/20 text-rose-300 border border-rose-500/30 mb-3">
                    <span class="w-2 h-2 rounded-full bg-rose-400"></span>
                    Restricted Access • Superadmin & Kaprodi Only
                </span>
                <h2 class="text-2xl sm:text-3xl font-extrabold text-white tracking-tight">
                    Panel Kontrol Super Administrator
                </h2>
                <p class="mt-1 text-sm text-slate-300">
                    Manajemen pengguna sistem, matriks role many-to-many, dan audit otorisasi.
                </p>
            </div>
            <div class="text-right">
                <span class="text-xs text-slate-400 block">Status Akses Anda:</span>
                <span class="font-mono text-sm font-bold text-rose-400">
                    {{ Auth::user()->hasRole('SUPERADMIN') ? 'AUTHORIZED' : 'DENIED' }}
                </span>
            </div>
        </div>
    </div>

    <!-- User Management Table with Multi-Role Display -->
    <div class="bg-slate-900/70 border border-slate-800 rounded-3xl p-6 sm:p-8">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
            <div>
                <h3 class="text-base font-bold text-white">Daftar Pengguna & Penetapan Role</h3>
                <p class="text-xs text-slate-400 mt-0.5">Membuktikan relasi Many-to-Many (users &rarr; user_roles &rarr; roles).</p>
            </div>
            <span class="text-xs font-mono text-slate-400 bg-slate-800 px-3 py-1 rounded-xl">
                Total {{ $users->total() }} Akun Terdaftar
            </span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="border-b border-slate-800 text-slate-400 font-semibold uppercase tracking-wider text-[11px]">
                        <th class="py-3 px-4">Nama Pengguna</th>
                        <th class="py-3 px-4">Email Kampus</th>
                        <th class="py-3 px-4">Role Terpasang (Many-to-Many)</th>
                        <th class="py-3 px-4">Status Hak Akses</th>
                        <th class="py-3 px-4 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/60 text-slate-300">
                    @foreach ($users as $u)
                        <tr class="hover:bg-slate-800/30 transition">
                            <td class="py-3.5 px-4 font-semibold text-white">
                                {{ $u->name }}
                            </td>
                            <td class="py-3.5 px-4 font-mono text-slate-400">
                                {{ $u->email }}
                            </td>
                            <td class="py-3.5 px-4">
                                <div class="flex flex-wrap gap-1">
                                    @forelse ($u->roles as $r)
                                        <span class="px-2 py-0.5 rounded text-[10px] font-mono font-semibold
                                            {{ $r->name === 'SUPERADMIN' ? 'bg-rose-500/20 text-rose-300 border border-rose-500/30' : '' }}
                                            {{ $r->name === 'KAPRODI' ? 'bg-purple-500/20 text-purple-300 border border-purple-500/30' : '' }}
                                            {{ $r->name === 'MHS' ? 'bg-blue-500/20 text-blue-300 border border-blue-500/30' : '' }}
                                            {{ $r->name === 'TU' ? 'bg-amber-500/20 text-amber-300 border border-amber-500/30' : '' }}
                                            {{ $r->name === 'DOSBING' ? 'bg-emerald-500/20 text-emerald-300 border border-emerald-500/30' : '' }}
                                            {{ $r->name === 'DOSEN_MK' ? 'bg-teal-500/20 text-teal-300 border border-teal-500/30' : '' }}
                                            {{ $r->name === 'WADEK1' ? 'bg-indigo-500/20 text-indigo-300 border border-indigo-500/30' : '' }}">
                                            {{ $r->name }}
                                        </span>
                                    @empty
                                        <span class="text-rose-400 italic text-[10px]">Tanpa Role</span>
                                    @endforelse

                                    @if ($u->hasRole('KAPRODI') && !$u->roles->contains('name', 'SUPERADMIN'))
                                        <span class="px-1.5 py-0.5 rounded text-[10px] font-mono font-semibold bg-rose-500/20 text-rose-300 border border-rose-500/30" title="Kaprodi otomatis memiliki hak Superadmin">
                                            +SUPERADMIN (Inherited)
                                        </span>
                                    @endif
                                </div>
                            </td>
                            <td class="py-3.5 px-4">
                                @if ($u->hasRole('SUPERADMIN'))
                                    <span class="inline-flex items-center gap-1 text-[11px] text-emerald-400 font-semibold">
                                        <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" /></svg>
                                        Full Superadmin
                                    </span>
                                @elseif ($u->roles->isNotEmpty())
                                    <span class="text-slate-400 text-[11px]">Role Standar</span>
                                @else
                                    <span class="text-rose-400 text-[11px] font-semibold">Terkunci (No Role)</span>
                                @endif
                            </td>
                            <td class="py-3.5 px-4 text-right">
                                <span class="text-[10px] text-slate-500 font-mono">ID: #{{ $u->id }}</span>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="mt-4">
            {{ $users->links() }}
        </div>
    </div>
</div>
@endsection
