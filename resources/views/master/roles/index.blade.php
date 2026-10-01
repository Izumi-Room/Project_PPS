@extends('layouts.app', ['title' => 'Master Data - Role'])

@section('content')
<div class="max-w-7xl mx-auto space-y-6">

    @include('master.partials.nav')

    <!-- Page Header & Action -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h2 class="text-xl font-bold text-white tracking-tight flex items-center gap-2">
                <span>Konfigurasi Role & Hak Akses</span>
                <span class="px-2 py-0.5 rounded-full text-xs font-mono bg-blue-500/10 text-blue-400 border border-blue-500/20">
                    {{ $roles->total() }} Roles
                </span>
            </h2>
            <p class="text-xs text-slate-400 mt-1">
                Kelola daftar peran pengguna dalam aplikasi dan mapping hak akses (permissions) granular.
            </p>
        </div>

        <div class="flex items-center gap-3">
            <a href="{{ route('master.roles.create') }}"
                class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-500 hover:to-indigo-500 text-white text-xs font-semibold shadow-lg shadow-blue-500/20 transition cursor-pointer">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                <span>Tambah Role Baru</span>
            </a>
        </div>
    </div>

    <!-- Search Bar -->
    <div class="p-4 rounded-2xl bg-slate-900/60 border border-slate-800">
        <form method="GET" action="{{ route('master.roles.index') }}" class="flex gap-3">
            <div class="flex-1 relative">
                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-500">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </div>
                <input type="text" name="search" value="{{ request('search') }}"
                    placeholder="Cari kode role, label, atau deskripsi..."
                    class="w-full pl-10 pr-4 py-2 bg-slate-950 border border-slate-800 rounded-xl text-xs text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-blue-500 transition">
            </div>

            <button type="submit" class="px-4 py-2 rounded-xl bg-blue-600 hover:bg-blue-500 text-white text-xs font-semibold transition cursor-pointer">
                Cari
            </button>
            @if(request()->filled('search'))
                <a href="{{ route('master.roles.index') }}" class="px-3 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-400 hover:text-white text-xs transition flex items-center justify-center" title="Reset">
                    &times;
                </a>
            @endif
        </form>
    </div>

    <!-- Data Table Card -->
    <div class="rounded-2xl bg-slate-900/60 border border-slate-800 overflow-hidden shadow-xl">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-950/80 text-slate-400 uppercase font-mono text-[10px] tracking-wider border-b border-slate-800">
                    <tr>
                        <th class="py-3.5 px-4 font-semibold">Kode Role</th>
                        <th class="py-3.5 px-4 font-semibold">Nama / Label Role</th>
                        <th class="py-3.5 px-4 font-semibold">Deskripsi</th>
                        <th class="py-3.5 px-4 font-semibold text-center">Jumlah Permission</th>
                        <th class="py-3.5 px-4 font-semibold text-center">Pengguna Terdaftar</th>
                        <th class="py-3.5 px-4 font-semibold text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/60 text-slate-300">
                    @forelse ($roles as $r)
                        @php
                            $isSystemRole = in_array($r->name, ['MHS', 'TU', 'KAPRODI', 'DOSBING', 'DOSEN_MK', 'WADEK1', 'SUPERADMIN']);
                        @endphp
                        <tr class="hover:bg-slate-800/30 transition">
                            <td class="py-3.5 px-4">
                                <span class="px-2 py-0.5 rounded text-[10px] font-mono font-bold
                                    {{ $r->name === 'SUPERADMIN' ? 'bg-rose-500/20 text-rose-300 border border-rose-500/30' : '' }}
                                    {{ $r->name === 'KAPRODI' ? 'bg-purple-500/20 text-purple-300 border border-purple-500/30' : '' }}
                                    {{ $r->name === 'MHS' ? 'bg-blue-500/20 text-blue-300 border border-blue-500/30' : '' }}
                                    {{ $r->name === 'TU' ? 'bg-amber-500/20 text-amber-300 border border-amber-500/30' : '' }}
                                    {{ $r->name === 'DOSBING' ? 'bg-emerald-500/20 text-emerald-300 border border-emerald-500/30' : '' }}
                                    {{ $r->name === 'DOSEN_MK' ? 'bg-teal-500/20 text-teal-300 border border-teal-500/30' : '' }}
                                    {{ $r->name === 'WADEK1' ? 'bg-indigo-500/20 text-indigo-300 border border-indigo-500/30' : 'bg-slate-800 text-slate-300' }}">
                                    {{ $r->name }}
                                </span>
                                @if($isSystemRole)
                                    <span class="text-[9px] text-slate-500 font-mono ml-1">SYSTEM</span>
                                @endif
                            </td>
                            <td class="py-3.5 px-4 font-bold text-white">
                                <a href="{{ route('master.roles.show', $r) }}" class="hover:text-blue-400 transition">
                                    {{ $r->label }}
                                </a>
                            </td>
                            <td class="py-3.5 px-4 text-slate-400 max-w-xs">
                                <p class="line-clamp-1 text-[11px]">{{ $r->description ?? '-' }}</p>
                            </td>
                            <td class="py-3.5 px-4 text-center">
                                <span class="px-2 py-0.5 rounded text-[10px] font-mono font-semibold bg-blue-500/10 text-blue-300 border border-blue-500/20">
                                    {{ $r->permissions_count }} Hak Akses
                                </span>
                            </td>
                            <td class="py-3.5 px-4 text-center font-mono">
                                <a href="{{ route('master.users.index') }}?role={{ $r->name }}" class="text-slate-300 hover:text-white underline">
                                    {{ $r->users_count }} user
                                </a>
                            </td>
                            <td class="py-3.5 px-4 text-right">
                                <div class="flex items-center justify-end gap-1.5">
                                    <a href="{{ route('master.roles.show', $r) }}"
                                        class="p-1.5 rounded-lg text-slate-400 hover:text-white hover:bg-slate-800 transition" title="Lihat Detail">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                        </svg>
                                    </a>
                                    <a href="{{ route('master.roles.edit', $r) }}"
                                        class="p-1.5 rounded-lg text-slate-400 hover:text-amber-400 hover:bg-slate-800 transition" title="Edit Role & Permissions">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                        </svg>
                                    </a>
                                    @if(!$isSystemRole)
                                        <form action="{{ route('master.roles.destroy', $r) }}" method="POST"
                                            onsubmit="return confirm('Apakah Anda yakin ingin menghapus role kustom ini?')" class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="p-1.5 rounded-lg text-slate-400 hover:text-rose-400 hover:bg-slate-800 transition cursor-pointer" title="Hapus Role">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                </svg>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-12 px-4 text-center">
                                <div class="max-w-sm mx-auto space-y-3">
                                    <div class="w-12 h-12 mx-auto rounded-2xl bg-slate-800/80 flex items-center justify-center text-slate-500">
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                                        </svg>
                                    </div>
                                    <p class="text-sm font-semibold text-white">Tidak ada data role</p>
                                    <p class="text-xs text-slate-400">Tidak ada role yang cocok dengan pencarian Anda.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($roles->hasPages())
            <div class="p-4 border-t border-slate-800 bg-slate-950/40">
                {{ $roles->links() }}
            </div>
        @endif
    </div>

</div>
@endsection
