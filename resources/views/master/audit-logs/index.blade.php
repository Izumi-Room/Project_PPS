@extends('layouts.app', ['title' => 'Audit Log - Keamanan & Peran'])

@section('content')
<div class="max-w-7xl mx-auto space-y-6">

    @include('master.partials.nav')

    <!-- Page Header & Action -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h2 class="text-xl font-bold text-white tracking-tight flex items-center gap-2">
                <span>Security Audit Log (Catatan Perubahan Peran & Pengguna)</span>
                <span class="px-2 py-0.5 rounded-full text-xs font-mono bg-purple-500/10 text-purple-400 border border-purple-500/20">
                    {{ $logs->total() }} Log
                </span>
            </h2>
            <p class="text-xs text-slate-400 mt-1">
                Rekam jejak setiap perubahan penting pada akun pengguna, penugasan role, pencopotan role, dan status aktif untuk akuntabilitas sistem.
            </p>
        </div>

        <div class="p-3 rounded-xl bg-slate-900 border border-slate-800 text-xs text-slate-300">
            <span class="font-bold text-white">Immutable Audit Trail:</span> Seluruh riwayat perubahan tersimpan permanen.
        </div>
    </div>

    <!-- Search & Filter Bar -->
    <div class="p-4 rounded-2xl bg-slate-900/60 border border-slate-800">
        <form method="GET" action="{{ route('master.audit-logs.index') }}" class="grid grid-cols-1 sm:grid-cols-12 gap-3">
            <div class="sm:col-span-8 relative">
                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-500">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </div>
                <input type="text" name="search" value="{{ request('search') }}"
                    placeholder="Cari berdasarkan nama aktor, target pengguna, atau keterangan..."
                    class="w-full pl-10 pr-4 py-2 bg-slate-950 border border-slate-800 rounded-xl text-xs text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-blue-500 transition">
            </div>

            <div class="sm:col-span-3">
                <select name="action" class="w-full px-3 py-2 bg-slate-950 border border-slate-800 rounded-xl text-xs text-slate-300 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">Semua Tipe Aksi</option>
                    @foreach($actions as $act)
                        <option value="{{ $act }}" {{ request('action') === $act ? 'selected' : '' }}>{{ $act }}</option>
                    @endforeach
                </select>
            </div>

            <div class="sm:col-span-1 flex gap-1">
                <button type="submit" class="w-full px-3 py-2 rounded-xl bg-blue-600 hover:bg-blue-500 text-white text-xs font-semibold transition cursor-pointer flex items-center justify-center">
                    Filter
                </button>
                @if(request()->anyFilled(['search', 'action']))
                    <a href="{{ route('master.audit-logs.index') }}" class="px-2.5 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-400 hover:text-white text-xs transition flex items-center justify-center" title="Reset">
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
                        <th class="py-3.5 px-4 font-semibold">Waktu & IP</th>
                        <th class="py-3.5 px-4 font-semibold">Aktor Pelaksana</th>
                        <th class="py-3.5 px-4 font-semibold">Aksi</th>
                        <th class="py-3.5 px-4 font-semibold">Target Pengguna</th>
                        <th class="py-3.5 px-4 font-semibold">Perubahan (Old &rarr; New)</th>
                        <th class="py-3.5 px-4 font-semibold">Keterangan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/60 text-slate-300">
                    @forelse ($logs as $log)
                        <tr class="hover:bg-slate-800/30 transition">
                            <td class="py-3.5 px-4 font-mono text-[11px] whitespace-nowrap">
                                <span class="block text-slate-200">{{ $log->created_at->translatedFormat('d M Y, H:i:s') }}</span>
                                <span class="block text-slate-500 text-[10px] mt-0.5">IP: {{ $log->ip_address ?? '127.0.0.1' }}</span>
                            </td>
                            <td class="py-3.5 px-4">
                                <span class="font-bold text-white block">{{ $log->actor_name ?? 'System' }}</span>
                                @if($log->actor)
                                    <span class="text-[10px] font-mono text-slate-400">{{ $log->actor->email }}</span>
                                @endif
                            </td>
                            <td class="py-3.5 px-4">
                                <span class="px-2 py-0.5 rounded text-[10px] font-mono font-bold
                                    {{ $log->action === 'ROLES_SYNCED' ? 'bg-purple-500/20 text-purple-300 border border-purple-500/30' : '' }}
                                    {{ $log->action === 'ROLE_REMOVED' ? 'bg-rose-500/20 text-rose-300 border border-rose-500/30' : '' }}
                                    {{ $log->action === 'STATUS_TOGGLED' ? 'bg-amber-500/20 text-amber-300 border border-amber-500/30' : '' }}
                                    {{ $log->action === 'USER_CREATED' ? 'bg-emerald-500/20 text-emerald-300 border border-emerald-500/30' : '' }}
                                    {{ $log->action === 'USER_UPDATED' ? 'bg-blue-500/20 text-blue-300 border border-blue-500/30' : '' }}
                                    {{ $log->action === 'USER_DELETED' ? 'bg-rose-500/30 text-rose-200 border border-rose-500/40' : '' }}">
                                    {{ $log->action }}
                                </span>
                            </td>
                            <td class="py-3.5 px-4">
                                @if($log->user_id)
                                    <a href="{{ route('master.users.show', $log->user_id) }}" class="font-bold text-white hover:text-blue-400 transition block">
                                        {{ $log->user_name }}
                                    </a>
                                @else
                                    <span class="font-bold text-white">{{ $log->user_name ?? '-' }}</span>
                                @endif
                            </td>
                            <td class="py-3.5 px-4 font-mono text-[11px] max-w-xs">
                                @if($log->old_values || $log->new_values)
                                    <div class="space-y-1">
                                        @if($log->old_values)
                                            <div class="text-rose-400/90 text-[10px] truncate" title="{{ json_encode($log->old_values) }}">
                                                <span class="text-slate-500">Old:</span> {{ is_array($log->old_values) ? implode(', ', array_map(fn($k, $v) => is_array($v) ? implode(',', $v) : "$k: $v", array_keys($log->old_values), $log->old_values)) : $log->old_values }}
                                            </div>
                                        @endif
                                        @if($log->new_values)
                                            <div class="text-emerald-400/90 text-[10px] truncate" title="{{ json_encode($log->new_values) }}">
                                                <span class="text-slate-500">New:</span> {{ is_array($log->new_values) ? implode(', ', array_map(fn($k, $v) => is_array($v) ? implode(',', $v) : "$k: $v", array_keys($log->new_values), $log->new_values)) : $log->new_values }}
                                            </div>
                                        @endif
                                    </div>
                                @else
                                    <span class="text-slate-500">-</span>
                                @endif
                            </td>
                            <td class="py-3.5 px-4 text-slate-300 text-[11px] max-w-sm">
                                {{ $log->description ?? '-' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-12 px-4 text-center">
                                <div class="max-w-sm mx-auto space-y-3">
                                    <p class="text-sm font-semibold text-white">Tidak ada data audit log</p>
                                    <p class="text-xs text-slate-400">Belum ada aktivitas perubahan peran atau pengguna yang tercatat.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($logs->hasPages())
            <div class="p-4 border-t border-slate-800 bg-slate-950/40">
                {{ $logs->links() }}
            </div>
        @endif
    </div>

</div>
@endsection
