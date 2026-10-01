@extends('layouts.app', ['title' => 'Master Data - Pengguna'])

@section('content')
<div class="max-w-7xl mx-auto space-y-6">

    @include('master.partials.nav')

    <!-- Page Header & Action -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h2 class="text-xl font-bold text-white tracking-tight flex items-center gap-2">
                <span>Data Pengguna Sistem (Users)</span>
                <span class="px-2 py-0.5 rounded-full text-xs font-mono bg-blue-500/10 text-blue-400 border border-blue-500/20">
                    {{ $users->total() }} Total
                </span>
            </h2>
            <p class="text-xs text-slate-400 mt-1">
                Kelola seluruh akun pengguna, prodi asal, identitas (NIM/NIP), dan role akses aplikasi.
            </p>
        </div>

        <div class="flex items-center gap-3">
            <a href="{{ route('master.users.create') }}"
                class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-500 hover:to-indigo-500 text-white text-xs font-semibold shadow-lg shadow-blue-500/20 transition cursor-pointer">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
                </svg>
                <span>Tambah Pengguna Baru</span>
            </a>
        </div>
    </div>

    <!-- Search & Filter Bar -->
    <div class="p-4 rounded-2xl bg-slate-900/60 border border-slate-800">
        <form method="GET" action="{{ route('master.users.index') }}" class="grid grid-cols-1 sm:grid-cols-12 gap-3">
            <div class="sm:col-span-4 relative">
                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-500">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </div>
                <input type="text" name="search" value="{{ request('search') }}"
                    placeholder="Cari nama, email, NIM, atau NIP..."
                    class="w-full pl-10 pr-4 py-2 bg-slate-950 border border-slate-800 rounded-xl text-xs text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-blue-500 transition">
            </div>

            <div class="sm:col-span-3">
                <select name="role" class="w-full px-3 py-2 bg-slate-950 border border-slate-800 rounded-xl text-xs text-slate-300 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">Semua Role</option>
                    @foreach($roles as $r)
                        <option value="{{ $r->name }}" {{ request('role') === $r->name ? 'selected' : '' }}>
                            {{ $r->name }} ({{ $r->label }})
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="sm:col-span-2">
                <select name="study_program_id" class="w-full px-3 py-2 bg-slate-950 border border-slate-800 rounded-xl text-xs text-slate-300 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">Semua Prodi</option>
                    @foreach($studyPrograms as $prodi)
                        <option value="{{ $prodi->id }}" {{ request('study_program_id') == $prodi->id ? 'selected' : '' }}>
                            {{ $prodi->code }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="sm:col-span-2">
                <select name="status" class="w-full px-3 py-2 bg-slate-950 border border-slate-800 rounded-xl text-xs text-slate-300 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">Semua Status</option>
                    <option value="1" {{ request('status') === '1' ? 'selected' : '' }}>Aktif</option>
                    <option value="0" {{ request('status') === '0' ? 'selected' : '' }}>Non-Aktif</option>
                </select>
            </div>

            <div class="sm:col-span-1 flex gap-1">
                <button type="submit" class="w-full px-3 py-2 rounded-xl bg-blue-600 hover:bg-blue-500 text-white text-xs font-semibold transition cursor-pointer flex items-center justify-center">
                    Filter
                </button>
                @if(request()->anyFilled(['search', 'role', 'study_program_id', 'status']))
                    <a href="{{ route('master.users.index') }}" class="px-2.5 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-400 hover:text-white text-xs transition flex items-center justify-center" title="Reset">
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
                        <th class="py-3.5 px-4 font-semibold">Pengguna & Identitas</th>
                        <th class="py-3.5 px-4 font-semibold">Email & Kontak</th>
                        <th class="py-3.5 px-4 font-semibold">Program Studi</th>
                        <th class="py-3.5 px-4 font-semibold">Role Terpasang</th>
                        <th class="py-3.5 px-4 font-semibold text-center">Status</th>
                        <th class="py-3.5 px-4 font-semibold text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/60 text-slate-300">
                    @forelse ($users as $u)
                        <tr class="hover:bg-slate-800/30 transition">
                            <td class="py-3.5 px-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-full bg-gradient-to-tr from-blue-600 to-indigo-600 flex items-center justify-center font-bold text-white text-xs flex-shrink-0">
                                        {{ strtoupper(substr($u->name, 0, 2)) }}
                                    </div>
                                    <div>
                                        <a href="{{ route('master.users.show', $u) }}" class="font-bold text-white hover:text-blue-400 transition">
                                            {{ $u->name }}
                                        </a>
                                        @if($u->identifier_number)
                                            <span class="block text-[11px] font-mono text-slate-400 mt-0.5">ID: {{ $u->identifier_number }}</span>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td class="py-3.5 px-4 font-mono text-[11px]">
                                <span class="block text-slate-300">{{ $u->email }}</span>
                                <span class="block text-slate-400 mt-0.5">{{ $u->phone ?? '-' }}</span>
                            </td>
                            <td class="py-3.5 px-4">
                                @if($u->studyProgram)
                                    <span class="px-2 py-0.5 rounded text-[10px] font-mono font-semibold bg-indigo-500/10 text-indigo-300 border border-indigo-500/20">
                                        {{ $u->studyProgram->code }}
                                    </span>
                                    <span class="text-slate-400 text-[11px] block mt-0.5 truncate max-w-[140px]">{{ $u->studyProgram->name }}</span>
                                @else
                                    <span class="text-slate-500 italic text-[11px]">Umum / Non-Prodi</span>
                                @endif
                            </td>
                            <td class="py-3.5 px-4">
                                <div class="flex flex-wrap gap-1">
                                    @forelse($u->roles as $role)
                                        <span class="px-1.5 py-0.5 rounded text-[9px] font-mono font-semibold
                                            {{ $role->name === 'SUPERADMIN' ? 'bg-rose-500/20 text-rose-300 border border-rose-500/30' : '' }}
                                            {{ $role->name === 'KAPRODI' ? 'bg-purple-500/20 text-purple-300 border border-purple-500/30' : '' }}
                                            {{ $role->name === 'MHS' ? 'bg-blue-500/20 text-blue-300 border border-blue-500/30' : '' }}
                                            {{ $role->name === 'TU' ? 'bg-amber-500/20 text-amber-300 border border-amber-500/30' : '' }}
                                            {{ $role->name === 'DOSBING' ? 'bg-emerald-500/20 text-emerald-300 border border-emerald-500/30' : '' }}
                                            {{ $role->name === 'DOSEN_MK' ? 'bg-teal-500/20 text-teal-300 border border-teal-500/30' : '' }}
                                            {{ $role->name === 'WADEK1' ? 'bg-indigo-500/20 text-indigo-300 border border-indigo-500/30' : '' }}">
                                            {{ $role->name }}
                                        </span>
                                    @empty
                                        <span class="text-rose-400 text-[10px] italic">Tanpa Role</span>
                                    @endforelse
                                </div>
                            </td>
                            <td class="py-3.5 px-4 text-center">
                                <form action="{{ route('master.users.toggle', $u) }}" method="POST" class="inline">
                                    @csrf
                                    <button type="submit" class="px-2.5 py-1 rounded-full text-[10px] font-semibold transition cursor-pointer {{ $u->is_active ? 'bg-emerald-500/10 text-emerald-300 border border-emerald-500/20 hover:bg-emerald-500/20' : 'bg-rose-500/10 text-rose-300 border border-rose-500/20 hover:bg-rose-500/20' }}" title="Ubah Status Pengguna">
                                        {{ $u->is_active ? '● Aktif' : '○ Non-Aktif' }}
                                    </button>
                                </form>
                            </td>
                            <td class="py-3.5 px-4 text-right">
                                <div class="flex items-center justify-end gap-1.5">
                                    <a href="{{ route('master.users.show', $u) }}"
                                        class="p-1.5 rounded-lg text-slate-400 hover:text-white hover:bg-slate-800 transition" title="Lihat Detail">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                        </svg>
                                    </a>
                                    <a href="{{ route('master.users.edit', $u) }}"
                                        class="p-1.5 rounded-lg text-slate-400 hover:text-amber-400 hover:bg-slate-800 transition" title="Edit Akun">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                        </svg>
                                    </a>
                                    <a href="{{ route('master.user-roles.edit', $u) }}"
                                        class="p-1.5 rounded-lg text-slate-400 hover:text-purple-400 hover:bg-slate-800 transition" title="Kelola Role">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                                        </svg>
                                    </a>
                                    @if($u->id !== auth()->id())
                                        <form action="{{ route('master.users.destroy', $u) }}" method="POST"
                                            onsubmit="return confirm('Apakah Anda yakin ingin menghapus akun pengguna ini?')" class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="p-1.5 rounded-lg text-slate-400 hover:text-rose-400 hover:bg-slate-800 transition cursor-pointer" title="Hapus Pengguna">
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
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                                        </svg>
                                    </div>
                                    <p class="text-sm font-semibold text-white">Tidak ada data pengguna</p>
                                    <p class="text-xs text-slate-400">Tidak ditemukan akun pengguna yang cocok dengan pencarian Anda.</p>
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
