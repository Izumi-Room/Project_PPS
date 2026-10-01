@extends('layouts.app', ['title' => 'Detail Role - ' . $role->name])

@section('content')
<div class="max-w-5xl mx-auto space-y-6">

    @php
        $isSystemRole = in_array($role->name, ['MHS', 'TU', 'KAPRODI', 'DOSBING', 'DOSEN_MK', 'WADEK1', 'SUPERADMIN']);
    @endphp

    <!-- Breadcrumb & Back -->
    <div class="flex items-center justify-between text-xs text-slate-400">
        <div class="flex items-center gap-2">
            <a href="{{ route('master.roles.index') }}" class="hover:text-white transition flex items-center gap-1">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                <span>Daftar Role</span>
            </a>
            <span>/</span>
            <span class="text-white font-medium">{{ $role->name }}</span>
        </div>

        <a href="{{ route('master.roles.edit', $role) }}"
            class="px-3.5 py-1.5 rounded-xl bg-amber-500/10 hover:bg-amber-500/20 border border-amber-500/20 text-amber-300 font-semibold text-xs transition flex items-center gap-1.5">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
            </svg>
            <span>Edit Role</span>
        </a>
    </div>

    <!-- Main Detail Card -->
    <div class="rounded-2xl bg-slate-900/80 border border-slate-800 p-6 sm:p-8 shadow-xl space-y-6">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-slate-800 pb-6">
            <div>
                <div class="flex items-center gap-2">
                    <span class="px-2.5 py-0.5 rounded text-xs font-mono font-bold bg-blue-500/10 text-blue-400 border border-blue-500/20">
                        {{ $role->name }}
                    </span>
                    @if($isSystemRole)
                        <span class="px-2.5 py-0.5 rounded text-xs font-mono font-bold bg-rose-500/10 text-rose-300 border border-rose-500/20">
                            CORE SYSTEM ROLE
                        </span>
                    @endif
                </div>
                <h2 class="text-xl sm:text-2xl font-extrabold text-white mt-2">{{ $role->label }}</h2>
                <p class="text-xs text-slate-400 mt-1">{{ $role->description ?? 'Tidak ada deskripsi tambahan.' }}</p>
            </div>

            <div class="flex items-center gap-3">
                <div class="p-3 rounded-xl bg-slate-950 border border-slate-800 text-center min-w-[120px]">
                    <span class="block text-xl font-bold font-mono text-white">{{ $role->permissions->count() }}</span>
                    <span class="text-[10px] text-slate-400 uppercase font-mono">Hak Akses</span>
                </div>
                <div class="p-3 rounded-xl bg-slate-950 border border-slate-800 text-center min-w-[120px]">
                    <span class="block text-xl font-bold font-mono text-white">{{ $role->users->count() }}</span>
                    <span class="text-[10px] text-slate-400 uppercase font-mono">Pengguna Aktif</span>
                </div>
            </div>
        </div>

        <!-- Permissions List -->
        <div class="space-y-3">
            <h4 class="text-xs font-bold text-white uppercase tracking-wider font-mono">Daftar Hak Akses Terpasang (Permissions)</h4>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-2.5">
                @forelse($role->permissions as $perm)
                    <div class="p-3 rounded-xl bg-slate-950/60 border border-slate-800">
                        <span class="block font-mono text-xs font-bold text-blue-400">{{ $perm->name }}</span>
                        <span class="block text-xs font-semibold text-slate-200 mt-0.5">{{ $perm->label }}</span>
                        <span class="block text-[11px] text-slate-400 mt-0.5">{{ $perm->description }}</span>
                    </div>
                @empty
                    <div class="col-span-3 text-center py-4 text-xs text-slate-400">
                        Belum ada hak akses yang ditetapkan untuk role ini.
                    </div>
                @endforelse
            </div>
        </div>

        <!-- Users List -->
        <div class="space-y-3 pt-4 border-t border-slate-800">
            <h4 class="text-xs font-bold text-white uppercase tracking-wider font-mono">Pengguna dengan Role {{ $role->name }} ({{ $role->users->count() }})</h4>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-2.5">
                @forelse($role->users as $u)
                    <a href="{{ route('master.users.show', $u) }}" class="p-3 rounded-xl bg-slate-950/60 border border-slate-800 hover:border-slate-700 transition flex items-center gap-3">
                        <div class="w-8 h-8 rounded-full bg-gradient-to-tr from-blue-600 to-indigo-600 flex items-center justify-center font-bold text-white text-xs flex-shrink-0">
                            {{ strtoupper(substr($u->name, 0, 2)) }}
                        </div>
                        <div class="overflow-hidden">
                            <span class="block text-xs font-bold text-white truncate">{{ $u->name }}</span>
                            <span class="block text-[10px] text-slate-400 font-mono truncate">{{ $u->email }}</span>
                        </div>
                    </a>
                @empty
                    <div class="col-span-3 text-center py-4 text-xs text-slate-400">
                        Belum ada pengguna yang ditugaskan pada role ini.
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection
