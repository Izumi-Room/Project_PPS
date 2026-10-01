@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h2 class="text-2xl font-extrabold text-white tracking-tight flex items-center gap-3">
                <span class="w-10 h-10 rounded-xl bg-gradient-to-tr from-amber-600 to-orange-600 flex items-center justify-center text-white shadow-lg shadow-amber-500/20">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                    </svg>
                </span>
                Logbook Mingguan Magang
            </h2>
            <p class="text-sm text-slate-400 mt-1">
                Catatan aktivitas, deskripsi mingguan, dan evaluasi berkala dari Dosen Pembimbing.
            </p>
        </div>

        @if ($applications->isNotEmpty())
            <a href="{{ route('logbooks.create', ['application_id' => $selectedApplication?->id]) }}"
                class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-amber-600 hover:bg-amber-500 text-white font-semibold text-xs transition shadow-lg shadow-amber-600/30">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                <span>Tulis Logbook Baru</span>
            </a>
        @endif
    </div>

    @if ($applications->isEmpty())
        <div class="p-8 rounded-2xl bg-slate-900 border border-slate-800 text-center">
            <svg class="w-12 h-12 text-slate-600 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
            </svg>
            <h3 class="text-base font-bold text-white">Belum Ada Magang Aktif</h3>
            <p class="text-xs text-slate-400 mt-1">
                Logbook hanya dapat diisi jika pendaftaran magang Anda telah berstatus Disetujui resmi oleh Wakil Dekan 1.
            </p>
        </div>
    @else
        <!-- Application Switcher if student has multiple -->
        @if ($applications->count() > 1)
            <div class="p-4 rounded-2xl bg-slate-900 border border-slate-800 flex items-center gap-4">
                <span class="text-xs font-semibold text-slate-400">Pilih Magang:</span>
                <div class="flex flex-wrap gap-2">
                    @foreach ($applications as $app)
                        <a href="{{ route('logbooks.index', ['application_id' => $app->id]) }}"
                            class="px-3 py-1.5 rounded-xl text-xs font-semibold transition {{ $selectedApplication?->id === $app->id ? 'bg-amber-600 text-white' : 'bg-slate-950 text-slate-400 border border-slate-800 hover:text-white' }}">
                            {{ $app->partnerInstitution->name }}
                        </a>
                    @endforeach
                </div>
            </div>
        @endif

        @if ($selectedApplication)
            <!-- Info Banner -->
            <div class="p-4 rounded-2xl bg-slate-900 border border-slate-800 flex flex-col sm:flex-row sm:items-center justify-between gap-3 text-xs">
                <div>
                    <span class="text-slate-400">Instansi Magang:</span>
                    <span class="font-bold text-white ml-1">{{ $selectedApplication->partnerInstitution->name }}</span>
                </div>
                <div>
                    <span class="text-slate-400">Dosen Pembimbing:</span>
                    <span class="font-bold text-emerald-400 ml-1">{{ $selectedApplication->advisor->name ?? 'Belum Ditugaskan' }}</span>
                </div>
                <div>
                    <span class="text-slate-400">Total Logbook:</span>
                    <span class="font-mono font-bold text-amber-400 ml-1">{{ $logbooks->total() }} Catatan</span>
                </div>
            </div>

            <!-- Logbook Table -->
            <div class="rounded-2xl bg-slate-900 border border-slate-800 overflow-hidden shadow-xl">
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs text-slate-300">
                        <thead class="bg-slate-950/70 text-slate-400 uppercase font-mono text-[10px] tracking-wider border-b border-slate-800">
                            <tr>
                                <th class="py-3 px-4">Minggu</th>
                                <th class="py-3 px-4">Tanggal</th>
                                <th class="py-3 px-4">Aktivitas Kegiatan</th>
                                <th class="py-3 px-4">Lampiran</th>
                                <th class="py-3 px-4">Feedback Pembimbing</th>
                                <th class="py-3 px-4 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-800/60 font-medium">
                            @forelse ($logbooks as $log)
                                <tr class="hover:bg-slate-800/30 transition">
                                    <td class="py-3.5 px-4 font-mono font-bold text-amber-400">
                                        Minggu ke-{{ $log->week_number }}
                                    </td>
                                    <td class="py-3.5 px-4 font-mono text-slate-400">
                                        {{ $log->activity_date->format('d/m/Y') }}
                                    </td>
                                    <td class="py-3.5 px-4">
                                        <div class="font-bold text-white text-sm">{{ $log->activity_title }}</div>
                                        <div class="text-[11px] text-slate-400 line-clamp-1 mt-0.5">{{ $log->description }}</div>
                                    </td>
                                    <td class="py-3.5 px-4">
                                        @if ($log->attachment_path)
                                            <a href="{{ route('logbooks.attachment.download', $log->id) }}"
                                                class="inline-flex items-center gap-1 text-[11px] text-blue-400 hover:text-blue-300 font-medium underline">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                                </svg>
                                                Unduh File
                                            </a>
                                        @else
                                            <span class="text-slate-500 text-[11px]">-</span>
                                        @endif
                                    </td>
                                    <td class="py-3.5 px-4">
                                        @if ($log->hasFeedback())
                                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-500/20 text-emerald-300 border border-emerald-500/30">
                                                ✓ Direview
                                            </span>
                                            <p class="text-[10px] text-slate-400 line-clamp-1 mt-0.5 italic">"{{ $log->dosbing_feedback }}"</p>
                                        @else
                                            <span class="text-slate-500 text-[10px]">Belum direview</span>
                                        @endif
                                    </td>
                                    <td class="py-3.5 px-4 text-right space-x-1">
                                        <a href="{{ route('logbooks.show', $log->id) }}"
                                            class="inline-flex items-center px-2.5 py-1 rounded-lg bg-slate-800 hover:bg-slate-700 text-slate-200 text-xs font-semibold transition">
                                            Detail
                                        </a>
                                        <a href="{{ route('logbooks.edit', $log->id) }}"
                                            class="inline-flex items-center px-2.5 py-1 rounded-lg bg-blue-600/20 hover:bg-blue-600/30 text-blue-300 border border-blue-500/30 text-xs font-semibold transition">
                                            Edit
                                        </a>
                                        <form method="POST" action="{{ route('logbooks.destroy', $log->id) }}" class="inline-block" onsubmit="return confirm('Hapus catatan logbook ini?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="px-2.5 py-1 rounded-lg bg-rose-500/10 hover:bg-rose-500/20 text-rose-400 text-xs font-semibold transition">
                                                Hapus
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="py-12 text-center text-slate-500">
                                        <p class="text-sm font-semibold text-slate-400">Belum ada catatan logbook yang diisi.</p>
                                        <p class="text-xs text-slate-500 mt-1">Klik tombol "Tulis Logbook Baru" di atas untuk menambahkan aktivitas magang mingguan Anda.</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if($logbooks->hasPages())
                    <div class="p-4 border-t border-slate-800">
                        {{ $logbooks->links() }}
                    </div>
                @endif
            </div>
        @endif
    @endif
</div>
@endsection
