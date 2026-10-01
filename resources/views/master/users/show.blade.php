@extends('layouts.app', ['title' => 'Detail Pengguna - ' . $user->name])

@section('content')
<div class="max-w-4xl mx-auto space-y-6">

    <!-- Breadcrumb & Back -->
    <div class="flex items-center justify-between text-xs text-slate-400">
        <div class="flex items-center gap-2">
            <a href="{{ route('master.users.index') }}" class="hover:text-white transition flex items-center gap-1">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                <span>Daftar Pengguna</span>
            </a>
            <span>/</span>
            <span class="text-white font-medium">{{ $user->name }}</span>
        </div>

        <div class="flex items-center gap-2">
            <a href="{{ route('master.users.edit', $user) }}"
                class="px-3.5 py-1.5 rounded-xl bg-amber-500/10 hover:bg-amber-500/20 border border-amber-500/20 text-amber-300 font-semibold text-xs transition flex items-center gap-1.5">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                </svg>
                <span>Edit Profil</span>
            </a>
            <a href="{{ route('master.user-roles.edit', $user) }}"
                class="px-3.5 py-1.5 rounded-xl bg-purple-500/10 hover:bg-purple-500/20 border border-purple-500/20 text-purple-300 font-semibold text-xs transition flex items-center gap-1.5">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                </svg>
                <span>Kelola Role</span>
            </a>
        </div>
    </div>

    <!-- Main Detail Card -->
    <div class="rounded-2xl bg-slate-900/80 border border-slate-800 p-6 sm:p-8 shadow-xl space-y-6">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-slate-800 pb-6">
            <div class="flex items-center gap-4">
                <div class="w-16 h-16 rounded-2xl bg-gradient-to-tr from-blue-600 to-indigo-600 flex items-center justify-center font-extrabold text-white text-xl shadow-lg shadow-blue-500/20">
                    {{ strtoupper(substr($user->name, 0, 2)) }}
                </div>
                <div>
                    <div class="flex items-center gap-2">
                        <h2 class="text-xl font-extrabold text-white">{{ $user->name }}</h2>
                        <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold {{ $user->is_active ? 'bg-emerald-500/10 text-emerald-300 border border-emerald-500/20' : 'bg-rose-500/10 text-rose-300 border border-rose-500/20' }}">
                            {{ $user->is_active ? '● Aktif' : '○ Non-Aktif' }}
                        </span>
                    </div>
                    <p class="text-xs font-mono text-slate-400 mt-1">{{ $user->email }}</p>
                </div>
            </div>

            <div class="text-right">
                <span class="block text-[10px] text-slate-400 uppercase font-mono tracking-wider">Terdaftar Sejak</span>
                <span class="block text-xs font-mono text-slate-200 mt-0.5">{{ $user->created_at->translatedFormat('d M Y, H:i') }}</span>
            </div>
        </div>

        <!-- Meta Grid -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div class="p-4 rounded-xl bg-slate-950/70 border border-slate-800">
                <span class="block text-[10px] text-slate-400 uppercase font-mono tracking-wider">Nomor Identitas (NIM/NIP)</span>
                <span class="block text-sm font-bold font-mono text-white mt-1">{{ $user->identifier_number ?? '-' }}</span>
            </div>
            <div class="p-4 rounded-xl bg-slate-950/70 border border-slate-800">
                <span class="block text-[10px] text-slate-400 uppercase font-mono tracking-wider">Program Studi</span>
                @if($user->studyProgram)
                    <span class="block text-xs font-bold text-white mt-1">{{ $user->studyProgram->name }}</span>
                    <span class="text-[11px] font-mono text-indigo-400">{{ $user->studyProgram->code }} ({{ $user->studyProgram->degree_level }})</span>
                @else
                    <span class="block text-xs text-slate-500 italic mt-1">Non-Prodi / Umum</span>
                @endif
            </div>
            <div class="p-4 rounded-xl bg-slate-950/70 border border-slate-800">
                <span class="block text-[10px] text-slate-400 uppercase font-mono tracking-wider">Telepon / WhatsApp</span>
                <span class="block text-xs font-mono text-slate-200 mt-1">{{ $user->phone ?? '-' }}</span>
            </div>
        </div>

        <!-- Assigned Roles Section -->
        <div class="p-5 rounded-2xl bg-slate-950/40 border border-slate-800 space-y-3">
            <div class="flex items-center justify-between">
                <h4 class="text-xs font-bold text-white uppercase tracking-wider font-mono">Peran Aktif Pengguna (Roles)</h4>
                <a href="{{ route('master.user-roles.edit', $user) }}" class="text-xs text-blue-400 hover:underline">
                    Kelola Penugasan Role &rarr;
                </a>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 pt-1">
                @forelse($user->roles as $role)
                    <div class="p-3.5 rounded-xl bg-slate-900 border border-slate-800">
                        <div class="flex items-center justify-between">
                            <span class="font-mono font-bold text-xs text-blue-400">{{ $role->name }}</span>
                            <span class="text-[10px] font-mono text-slate-400">{{ $role->permissions->count() }} permissions</span>
                        </div>
                        <p class="text-xs font-semibold text-white mt-1">{{ $role->label }}</p>
                        <p class="text-[11px] text-slate-400 mt-0.5">{{ $role->description }}</p>
                    </div>
                @empty
                    <div class="col-span-2 text-center py-4 text-xs text-rose-400">
                        Pengguna ini belum memiliki role terpasang.
                    </div>
                @endforelse
            </div>
        </div>

        <!-- Audit Trail Timeline for this User -->
        <div class="p-5 rounded-2xl bg-slate-950/40 border border-slate-800 space-y-3">
            <div class="flex items-center justify-between">
                <h4 class="text-xs font-bold text-white uppercase tracking-wider font-mono">Riwayat Audit Akun Ini (Audit Trail)</h4>
                <a href="{{ route('master.audit-logs.index') }}?search={{ urlencode($user->email) }}" class="text-xs text-purple-400 hover:underline">
                    Lihat Semua Audit Log &rarr;
                </a>
            </div>

            <div class="divide-y divide-slate-800/60 text-xs">
                @forelse($user->auditLogs as $log)
                    <div class="py-2.5 flex items-start justify-between gap-4">
                        <div class="space-y-0.5">
                            <div class="flex items-center gap-2">
                                <span class="px-1.5 py-0.2 rounded text-[9px] font-mono font-bold
                                    {{ $log->action === 'ROLES_SYNCED' ? 'bg-purple-500/20 text-purple-300' : '' }}
                                    {{ $log->action === 'ROLE_REMOVED' ? 'bg-rose-500/20 text-rose-300' : '' }}
                                    {{ $log->action === 'STATUS_TOGGLED' ? 'bg-amber-500/20 text-amber-300' : '' }}
                                    {{ $log->action === 'USER_CREATED' ? 'bg-emerald-500/20 text-emerald-300' : '' }}
                                    {{ $log->action === 'USER_UPDATED' ? 'bg-blue-500/20 text-blue-300' : '' }}">
                                    {{ $log->action }}
                                </span>
                                <span class="text-slate-300 font-medium">{{ $log->description }}</span>
                            </div>
                            <span class="text-[10px] text-slate-500 font-mono block">Oleh: {{ $log->actor_name ?? 'System' }} • IP: {{ $log->ip_address }}</span>
                        </div>
                        <span class="text-[10px] text-slate-400 font-mono whitespace-nowrap">{{ $log->created_at->diffForHumans() }}</span>
                    </div>
                @empty
                    <p class="py-3 text-center text-xs text-slate-500">Belum ada riwayat audit log tercatat untuk pengguna ini.</p>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection
