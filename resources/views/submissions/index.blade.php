@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h2 class="text-2xl font-extrabold text-white tracking-tight flex items-center gap-3">
                <span class="w-10 h-10 rounded-xl bg-gradient-to-tr from-cyan-600 to-indigo-600 flex items-center justify-center text-white shadow-lg shadow-cyan-500/20">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                    </svg>
                </span>
                Pengumpulan Tugas Mata Kuliah
            </h2>
            <p class="text-sm text-slate-400 mt-1">
                Kumpulkan laporan, berkas, atau tautan proyek sesuai komponen yang ditentukan oleh Dosen Pengampu MK.
            </p>
        </div>
    </div>

    @if ($conversions->isEmpty())
        <div class="p-8 rounded-2xl bg-slate-900 border border-slate-800 text-center">
            <svg class="w-12 h-12 text-slate-600 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
            </svg>
            <h3 class="text-base font-bold text-white">Belum Ada Mata Kuliah Konversi Aktif</h3>
            <p class="text-xs text-slate-400 mt-1">
                Pengumpulan tugas hanya dapat diakses setelah pengajuan konversi mata kuliah Anda disetujui.
            </p>
        </div>
    @else
        <!-- Converted Course Switcher Tabs -->
        <div class="p-4 rounded-2xl bg-slate-900 border border-slate-800 flex items-center gap-3 overflow-x-auto custom-scrollbar">
            <span class="text-xs font-semibold text-slate-400 flex-shrink-0">Mata Kuliah:</span>
            @foreach ($conversions as $conv)
                <a href="{{ route('submissions.index', ['conversion_id' => $conv->id]) }}"
                    class="px-3.5 py-1.5 rounded-xl text-xs font-semibold flex-shrink-0 transition {{ $selectedConversion?->id === $conv->id ? 'bg-cyan-600 text-white shadow-md shadow-cyan-600/30' : 'bg-slate-950 text-slate-400 border border-slate-800 hover:text-white' }}">
                    {{ $conv->course->name }} ({{ $conv->course->code }})
                </a>
            @endforeach
        </div>

        @if ($selectedConversion)
            <!-- Components Grid / Table -->
            <div class="rounded-2xl bg-slate-900 border border-slate-800 overflow-hidden shadow-xl">
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs text-slate-300">
                        <thead class="bg-slate-950/70 text-slate-400 uppercase font-mono text-[10px] tracking-wider border-b border-slate-800">
                            <tr>
                                <th class="py-3 px-4">Nama Komponen Tugas</th>
                                <th class="py-3 px-4">Jenis & Bobot</th>
                                <th class="py-3 px-4">Batas Waktu (Deadline)</th>
                                <th class="py-3 px-4">Status Pengumpulan</th>
                                <th class="py-3 px-4">Versi Terakhir</th>
                                <th class="py-3 px-4 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-800/60 font-medium">
                            @forelse ($components as $comp)
                                @php
                                    $sub = $submissions->get($comp->id);
                                @endphp
                                <tr class="hover:bg-slate-800/30 transition">
                                    <td class="py-3.5 px-4">
                                        <div class="font-bold text-white text-sm flex items-center gap-2">
                                            <span>{{ $comp->name }}</span>
                                            @if($comp->is_required)
                                                <span class="px-1.5 py-0.2 rounded text-[9px] bg-rose-500/20 text-rose-300 border border-rose-500/30">Wajib</span>
                                            @endif
                                        </div>
                                        @if($comp->instructions)
                                            <div class="text-[11px] text-slate-400 line-clamp-1 mt-0.5">{{ $comp->instructions }}</div>
                                        @endif
                                    </td>
                                    <td class="py-3.5 px-4">
                                        <div class="font-mono text-teal-300 text-[11px]">{{ $comp->submission_type }}</div>
                                        <div class="text-[10px] text-amber-400 font-mono">Bobot: {{ $comp->weight }}%</div>
                                    </td>
                                    <td class="py-3.5 px-4 font-mono">
                                        <span class="{{ $comp->isPassedDeadline() ? 'text-rose-400 font-bold' : 'text-slate-300' }}">
                                            {{ $comp->deadline->format('d/m/Y H:i') }}
                                        </span>
                                        @if($comp->isPassedDeadline())
                                            <span class="block text-[9px] text-rose-400 font-sans">Sudah Berakhir</span>
                                        @endif
                                    </td>
                                    <td class="py-3.5 px-4">
                                        @if ($sub)
                                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[10px] font-bold border {{ $sub->status_badge_classes }}">
                                                {{ $sub->status_label }}
                                            </span>
                                            @if ($sub->latest_feedback)
                                                <p class="text-[10px] text-amber-300/90 line-clamp-1 mt-1 italic">
                                                    "{{ $sub->latest_feedback }}"
                                                </p>
                                            @endif
                                        @else
                                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[10px] font-medium bg-slate-800 text-slate-400 border border-slate-700">
                                                Belum Dikumpulkan
                                            </span>
                                        @endif
                                    </td>
                                    <td class="py-3.5 px-4 font-mono text-slate-400 text-xs">
                                        @if ($sub && $sub->current_version > 0)
                                            <span class="text-cyan-400 font-bold">v{{ $sub->current_version }}</span>
                                        @else
                                            <span>-</span>
                                        @endif
                                    </td>
                                    <td class="py-3.5 px-4 text-right">
                                        <a href="{{ route('submissions.show', [$comp->id, 'conversion_id' => $selectedConversion->id]) }}"
                                            class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg bg-cyan-600/20 hover:bg-cyan-600/30 text-cyan-300 border border-cyan-500/30 text-xs font-semibold transition">
                                            {{ ($sub && $sub->current_version > 0) ? 'Lihat / Revisi' : 'Kumpulkan Tugas' }}
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="py-12 text-center text-slate-500">
                                        <p class="text-sm font-semibold text-slate-400">Belum ada komponen pengumpulan untuk mata kuliah ini.</p>
                                        <p class="text-xs text-slate-500 mt-1">Dosen pengampu akan mengumumkan tugas yang perlu dikumpulkan di halaman ini.</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        @endif
    @endif
</div>
@endsection
