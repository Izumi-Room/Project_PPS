@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h2 class="text-2xl font-extrabold text-white tracking-tight flex items-center gap-3">
                <span class="w-10 h-10 rounded-xl bg-gradient-to-tr from-cyan-600 to-blue-600 flex items-center justify-center text-white shadow-lg shadow-cyan-500/20">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                </span>
                Review Pengumpulan Tugas Mahasiswa
            </h2>
            <p class="text-sm text-slate-400 mt-1">
                Pemeriksaan berkas, persetujuan tugas, atau permintaan revisi perbaikan kepada mahasiswa.
            </p>
        </div>

        @if($pendingCount > 0)
            <span class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-blue-500/20 text-blue-300 border border-blue-500/30 text-xs font-bold font-mono">
                <span class="w-2 h-2 rounded-full bg-blue-400 animate-pulse"></span>
                {{ $pendingCount }} Tugas Menunggu Review
            </span>
        @endif
    </div>

    <!-- Tabs & Filter -->
    <div class="flex flex-col sm:flex-row items-center justify-between gap-4 p-4 rounded-2xl bg-slate-900 border border-slate-800">
        <div class="flex items-center gap-2 w-full sm:w-auto">
            <a href="{{ route('dosen-mk.submissions.index', ['tab' => 'queue']) }}"
                class="px-4 py-2 rounded-xl text-xs font-semibold transition {{ $tab === 'queue' ? 'bg-cyan-600 text-white shadow-md shadow-cyan-600/30' : 'text-slate-400 hover:text-white hover:bg-slate-800' }}">
                Antrean Periksa ({{ $pendingCount }})
            </a>
            <a href="{{ route('dosen-mk.submissions.index', ['tab' => 'history']) }}"
                class="px-4 py-2 rounded-xl text-xs font-semibold transition {{ $tab === 'history' ? 'bg-cyan-600 text-white shadow-md shadow-cyan-600/30' : 'text-slate-400 hover:text-white hover:bg-slate-800' }}">
                Riwayat Selesai & Revisi
            </a>
        </div>

        <form method="GET" action="{{ route('dosen-mk.submissions.index') }}" class="w-full sm:w-auto flex items-center gap-2">
            <input type="hidden" name="tab" value="{{ $tab }}">
            <select name="course_id" class="px-3 py-2 rounded-xl bg-slate-950 border border-slate-800 text-slate-300 text-xs focus:ring-2 focus:ring-cyan-500 focus:outline-none">
                <option value="">Semua MK</option>
                @foreach ($courses as $c)
                    <option value="{{ $c->id }}" {{ request('course_id') == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                @endforeach
            </select>
            <input type="text" name="search" value="{{ request('search') }}"
                placeholder="Cari mahasiswa/tugas..."
                class="px-3 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white text-xs focus:ring-2 focus:ring-cyan-500 focus:outline-none">
            <button type="submit" class="px-3 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-white text-xs font-semibold">
                Cari
            </button>
        </form>
    </div>

    <!-- Table -->
    <div class="rounded-2xl bg-slate-900 border border-slate-800 overflow-hidden shadow-xl">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-300">
                <thead class="bg-slate-950/70 text-slate-400 uppercase font-mono text-[10px] tracking-wider border-b border-slate-800">
                    <tr>
                        <th class="py-3 px-4">Mahasiswa</th>
                        <th class="py-3 px-4">Komponen & Mata Kuliah</th>
                        <th class="py-3 px-4">Versi Terakhir</th>
                        <th class="py-3 px-4">Status</th>
                        <th class="py-3 px-4">Waktu Dikumpulkan</th>
                        <th class="py-3 px-4 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/60 font-medium">
                    @forelse ($submissions as $sub)
                        <tr class="hover:bg-slate-800/30 transition">
                            <td class="py-3.5 px-4">
                                <div class="font-bold text-white text-sm">{{ $sub->student->name }}</div>
                                <div class="text-[11px] text-slate-400 font-mono">{{ $sub->student->identity_number }}</div>
                            </td>
                            <td class="py-3.5 px-4">
                                <div class="font-semibold text-cyan-300">{{ $sub->component->name }}</div>
                                <div class="text-[11px] text-slate-400 font-mono">{{ $sub->component->course->name }} ({{ $sub->component->course->code }})</div>
                            </td>
                            <td class="py-3.5 px-4 font-mono">
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-blue-500/20 text-blue-300 border border-blue-500/30">
                                    Versi {{ $sub->current_version }}
                                </span>
                            </td>
                            <td class="py-3.5 px-4">
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[10px] font-bold border {{ $sub->status_badge_classes }}">
                                    {{ $sub->status_label }}
                                </span>
                            </td>
                            <td class="py-3.5 px-4 font-mono text-slate-400 text-[11px]">
                                {{ $sub->latest_submitted_at?->format('d/m/Y H:i') ?? '-' }}
                            </td>
                            <td class="py-3.5 px-4 text-right">
                                <a href="{{ route('dosen-mk.submissions.show', $sub->id) }}"
                                    class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg bg-cyan-600/20 hover:bg-cyan-600/30 text-cyan-300 border border-cyan-500/30 text-xs font-semibold transition">
                                    {{ $tab === 'queue' ? 'Review Tugas' : 'Detail' }}
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-12 text-center text-slate-500">
                                <p class="text-sm font-semibold text-slate-400">Tidak ada pengumpulan tugas dalam antrean ini.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($submissions->hasPages())
            <div class="p-4 border-t border-slate-800">
                {{ $submissions->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
