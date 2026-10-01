@extends('layouts.app', ['title' => 'Master Data - Penugasan Role Pengguna'])

@section('content')
<div class="max-w-7xl mx-auto space-y-6">

    @include('master.partials.nav')

    <!-- Page Header & Action -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h2 class="text-xl font-bold text-white tracking-tight flex items-center gap-2">
                <span>Penugasan Peran Pengguna (User Roles)</span>
                <span class="px-2 py-0.5 rounded-full text-xs font-mono bg-purple-500/10 text-purple-400 border border-purple-500/20">
                    Many-to-Many RBAC
                </span>
            </h2>
            <p class="text-xs text-slate-400 mt-1">
                Atur relasi peran ganda pengguna melalui tabel relasi <code class="text-indigo-400 font-mono">user_roles</code>. Kaprodi otomatis mewarisi akses Superadmin.
            </p>
        </div>

        <div class="p-3 rounded-xl bg-purple-500/10 border border-purple-500/20 text-purple-300 text-xs">
            <span class="font-bold">Multi-Role Rule:</span> Satu pengguna dapat memiliki lebih dari satu role sekaligus.
        </div>
    </div>

    <!-- Search & Filter Bar -->
    <div class="p-4 rounded-2xl bg-slate-900/60 border border-slate-800">
        <form method="GET" action="{{ route('master.user-roles.index') }}" class="grid grid-cols-1 sm:grid-cols-12 gap-3">
            <div class="sm:col-span-8 relative">
                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-500">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </div>
                <input type="text" name="search" value="{{ request('search') }}"
                    placeholder="Cari pengguna berdasarkan nama atau email..."
                    class="w-full pl-10 pr-4 py-2 bg-slate-950 border border-slate-800 rounded-xl text-xs text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-blue-500 transition">
            </div>

            <div class="sm:col-span-3">
                <select name="role" class="w-full px-3 py-2 bg-slate-950 border border-slate-800 rounded-xl text-xs text-slate-300 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">Semua Peran (Role)</option>
                    @foreach($roles as $r)
                        <option value="{{ $r->name }}" {{ request('role') === $r->name ? 'selected' : '' }}>
                            {{ $r->name }} ({{ $r->label }})
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="sm:col-span-1 flex gap-1">
                <button type="submit" class="w-full px-3 py-2 rounded-xl bg-blue-600 hover:bg-blue-500 text-white text-xs font-semibold transition cursor-pointer flex items-center justify-center">
                    Filter
                </button>
                @if(request()->anyFilled(['search', 'role']))
                    <a href="{{ route('master.user-roles.index') }}" class="px-2.5 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-400 hover:text-white text-xs transition flex items-center justify-center" title="Reset">
                        &times;
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Data Table Card -->
    <div class="rounded-2xl bg-slate-900/60 border border-slate-800 overflow-hidden shadow-xl">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-950/80 text-slate-400 uppercase font-mono text-[10px] tracking-wider border-b border-slate-800">
                    <tr>
                        <th class="py-3.5 px-4 font-semibold">Nama Pengguna</th>
                        <th class="py-3.5 px-4 font-semibold">Email & Prodi</th>
                        <th class="py-3.5 px-4 font-semibold">Peran Terdaftar (Explicit)</th>
                        <th class="py-3.5 px-4 font-semibold">Peran Efektif (Inherited)</th>
                        <th class="py-3.5 px-4 font-semibold text-right">Kelola Role</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/60 text-slate-300">
                    @forelse ($users as $u)
                        @php
                            $isKaprodi = $u->roles->contains('name', 'KAPRODI');
                            $hasSuperadmin = $u->roles->contains('name', 'SUPERADMIN');
                        @endphp
                        <tr class="hover:bg-slate-800/30 transition">
                            <td class="py-3.5 px-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-full bg-gradient-to-tr from-purple-600 to-indigo-600 flex items-center justify-center font-bold text-white text-xs flex-shrink-0">
                                        {{ strtoupper(substr($u->name, 0, 2)) }}
                                    </div>
                                    <span class="font-bold text-white">{{ $u->name }}</span>
                                </div>
                            </td>
                            <td class="py-3.5 px-4">
                                <span class="font-mono text-slate-300 block">{{ $u->email }}</span>
                                @if($u->studyProgram)
                                    <span class="text-[10px] text-slate-400 font-mono">{{ $u->studyProgram->code }} - {{ $u->studyProgram->name }}</span>
                                @endif
                            </td>
                            <td class="py-3.5 px-4">
                                <div class="flex flex-wrap gap-1.5 items-center">
                                    @forelse($u->roles as $role)
                                        <div class="inline-flex items-center gap-1 pl-2 pr-1 py-0.5 rounded text-[10px] font-mono font-semibold bg-slate-800 border border-slate-700">
                                            <span class="{{ $role->name === 'SUPERADMIN' ? 'text-rose-300' : ($role->name === 'KAPRODI' ? 'text-purple-300' : 'text-slate-300') }}">
                                                {{ $role->name }}
                                            </span>
                                            @if($u->roles->count() > 1)
                                                <form action="{{ route('master.user-roles.detach', [$u, $role]) }}" method="POST"
                                                    onsubmit="return confirm('Lepas role {{ $role->name }} dari {{ $u->name }}?')" class="inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="text-slate-500 hover:text-rose-400 p-0.5 rounded" title="Lepas role ini">
                                                        &times;
                                                    </button>
                                                </form>
                                            @endif
                                        </div>
                                    @empty
                                        <span class="text-rose-400 text-[10px] italic">Belum Ada Role</span>
                                    @endforelse
                                </div>
                            </td>
                            <td class="py-3.5 px-4">
                                <div class="flex flex-wrap gap-1">
                                    @foreach($u->roles as $role)
                                        <span class="px-1.5 py-0.5 rounded text-[9px] font-mono font-semibold bg-slate-900 text-slate-300 border border-slate-800">
                                            {{ $role->name }}
                                        </span>
                                    @endforeach
                                    @if($isKaprodi && !$hasSuperadmin)
                                        <span class="px-1.5 py-0.5 rounded text-[9px] font-mono font-semibold bg-rose-500/20 text-rose-300 border border-rose-500/30" title="Inherited automatically from KAPRODI">
                                            +SUPERADMIN*
                                        </span>
                                    @endif
                                </div>
                            </td>
                            <td class="py-3.5 px-4 text-right">
                                <a href="{{ route('master.user-roles.edit', $u) }}"
                                    class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-purple-600/20 hover:bg-purple-600/30 border border-purple-500/30 text-purple-300 font-semibold text-xs transition">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                    </svg>
                                    <span>Ubah Role</span>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="py-12 px-4 text-center">
                                <div class="max-w-sm mx-auto space-y-3">
                                    <p class="text-sm font-semibold text-white">Tidak ada data penugasan role</p>
                                    <p class="text-xs text-slate-400">Tidak ditemukan pengguna yang sesuai dengan filter.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($users->hasPages())
            <div class="p-4 border-t border-slate-800 bg-slate-950/40">
                {{ $users->links() }}
            </div>
        @endif
    </div>

</div>
@endsection
