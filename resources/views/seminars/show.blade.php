@extends('layouts.app')

@section('title', 'Detail Seminar Magang')

@section('content')
<div class="space-y-6 max-w-5xl mx-auto">
    <!-- Header Back -->
    <div class="flex items-center justify-between">
        <a href="{{ route('seminars.index') }}" class="inline-flex items-center gap-2 text-sm text-slate-400 hover:text-white transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" /></svg>
            Kembali ke Daftar Seminar
        </a>
        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold border {{ $seminar->statusBadgeColor() }}">
            {{ $seminar->statusLabel() }}
        </span>
    </div>

    <!-- Main Card -->
    <div class="bg-slate-900/60 backdrop-blur-md border border-slate-800 rounded-2xl p-6 shadow-xl space-y-6">
        <div>
            <span class="text-xs font-bold text-indigo-400 uppercase tracking-wider">Mata Kuliah Magang</span>
            <h1 class="text-2xl font-bold text-white mt-1">{{ $seminar->course?->name ?? 'Mata Kuliah Magang' }}</h1>
            <p class="text-sm text-slate-400 mt-1">
                Kode MK: <span class="font-mono text-slate-300">{{ $seminar->course?->code ?? '-' }}</span> &bull; {{ $seminar->course?->credits ?? 0 }} SKS
            </p>
        </div>

        @if($seminar->isNotRequired())
            <div class="bg-slate-800/40 border border-slate-700/60 rounded-xl p-5 flex items-start gap-4">
                <div class="p-2.5 rounded-lg bg-slate-700/50 text-slate-400">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                </div>
                <div>
                    <h3 class="text-base font-semibold text-white">Seminar Tidak Diperlukan</h3>
                    <p class="text-sm text-slate-400 mt-1">
                        Dosen Pengampu MK telah menetapkan bahwa pelaksanaan seminar <span class="text-slate-300 font-medium">TIDAK DIPERLUKAN</span> untuk mata kuliah ini. Anda dapat fokus menyelesaikan logbook dan pengumpulan tugas MK.
                    </p>
                </div>
            </div>
        @else
            <!-- Jadwal & Tempat / Link Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="bg-slate-800/40 border border-slate-700/60 rounded-xl p-5">
                    <div class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-2 flex items-center gap-1.5">
                        <svg class="w-4 h-4 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                        Jadwal & Waktu
                    </div>
                    <div class="text-xl font-bold text-white">
                        {{ $seminar->scheduled_date ? $seminar->scheduled_date->format('l, d F Y') : '-' }}
                    </div>
                    <div class="text-sm text-slate-300 mt-1 flex items-center gap-2">
                        <span class="inline-block w-2 h-2 rounded-full bg-emerald-400"></span>
                        Pukul: <span class="font-semibold text-white">{{ $seminar->scheduled_time ?? '-' }}</span>
                    </div>
                </div>

                <div class="bg-slate-800/40 border border-slate-700/60 rounded-xl p-5">
                    <div class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-2 flex items-center gap-1.5">
                        <svg class="w-4 h-4 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                        Tempat / Tautan Temu
                    </div>
                    <div class="text-base font-semibold text-white break-all">
                        @if($seminar->location_or_link)
                            @if(filter_var($seminar->location_or_link, FILTER_VALIDATE_URL))
                                <a href="{{ $seminar->location_or_link }}" target="_blank" rel="noopener noreferrer" class="inline-flex items-center gap-1.5 text-indigo-400 hover:text-indigo-300 underline font-mono text-sm">
                                    <span>{{ $seminar->location_or_link }}</span>
                                    <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" /></svg>
                                </a>
                            @else
                                <span class="text-slate-200">{{ $seminar->location_or_link }}</span>
                            @endif
                        @else
                            <span class="text-slate-500 italic">-</span>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Informasi Seminar -->
            @if($seminar->information)
                <div class="bg-slate-800/30 border border-slate-700/50 rounded-xl p-5">
                    <h3 class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Petunjuk & Informasi Tambahan</h3>
                    <p class="text-sm text-slate-300 whitespace-pre-line leading-relaxed">{{ $seminar->information }}</p>
                </div>
            @endif

            <!-- Rejection Notice if any -->
            @if($seminar->isRejected())
                <div class="bg-rose-950/40 border border-rose-800/60 rounded-xl p-5">
                    <div class="flex items-start gap-3">
                        <div class="p-2 rounded-lg bg-rose-900/60 text-rose-400">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>
                        </div>
                        <div>
                            <h4 class="text-sm font-semibold text-rose-300">Catatan Penolakan Wadek 1</h4>
                            <p class="text-xs text-rose-200/90 mt-1 whitespace-pre-line">{{ $seminar->rejection_reason }}</p>
                            <p class="text-[11px] text-rose-400/80 mt-2">Dosen Pengampu MK akan menjadwalkan ulang seminar ini.</p>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Reschedule Notice if any -->
            @if($seminar->reschedule_count > 0)
                <div class="bg-amber-950/30 border border-amber-800/40 rounded-xl p-4 text-xs text-amber-300 flex items-center gap-2">
                    <svg class="w-4 h-4 text-amber-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                    <span>Jadwal telah diperbarui sebanyak <strong>{{ $seminar->reschedule_count }} kali</strong> (terakhir {{ $seminar->rescheduled_at?->diffForHumans() }}).</span>
                </div>
            @endif

            <!-- Approval Status Timeline -->
            <div class="pt-4 border-t border-slate-800">
                <h3 class="text-sm font-semibold text-white mb-4">Approval Status Berjenjang</h3>
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <!-- Step 1: Dosen MK Scheduled -->
                    <div class="bg-slate-800/40 border border-slate-700/60 rounded-xl p-4">
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-xs font-semibold text-slate-400">1. Dosen Pengampu MK</span>
                            <span class="w-2.5 h-2.5 rounded-full bg-emerald-400"></span>
                        </div>
                        <div class="text-xs font-medium text-white">{{ $seminar->dosenMk?->name ?? 'Dosen MK' }}</div>
                        <div class="text-[11px] text-emerald-400 mt-1">Terjadwal</div>
                        <div class="text-[10px] text-slate-500 mt-0.5">{{ $seminar->created_at->format('d/m/Y H:i') }}</div>
                    </div>

                    <!-- Step 2: Dosbing Mengetahui -->
                    <div class="bg-slate-800/40 border border-slate-700/60 rounded-xl p-4">
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-xs font-semibold text-slate-400">2. Dosen Pembimbing</span>
                            <span class="w-2.5 h-2.5 rounded-full {{ $seminar->dosbing_acknowledged_at ? 'bg-emerald-400' : 'bg-slate-600' }}"></span>
                        </div>
                        <div class="text-xs font-medium text-white">{{ $seminar->dosbing?->name ?? $seminar->internshipApplication?->advisor?->name ?? 'Dosen Pembimbing' }}</div>
                        @if($seminar->dosbing_acknowledged_at)
                            <div class="text-[11px] text-emerald-400 mt-1">Mengetahui</div>
                            <div class="text-[10px] text-slate-500 mt-0.5">{{ $seminar->dosbing_acknowledged_at->format('d/m/Y H:i') }}</div>
                        @else
                            <div class="text-[11px] text-amber-400 mt-1">Menunggu Konfirmasi</div>
                        @endif
                    </div>

                    <!-- Step 3: Kaprodi Mengetahui -->
                    <div class="bg-slate-800/40 border border-slate-700/60 rounded-xl p-4">
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-xs font-semibold text-slate-400">3. Kaprodi</span>
                            <span class="w-2.5 h-2.5 rounded-full {{ $seminar->kaprodi_acknowledged_at ? 'bg-emerald-400' : 'bg-slate-600' }}"></span>
                        </div>
                        <div class="text-xs font-medium text-white">{{ $seminar->kaprodi?->name ?? 'Ketua Prodi' }}</div>
                        @if($seminar->kaprodi_acknowledged_at)
                            <div class="text-[11px] text-emerald-400 mt-1">Mengetahui</div>
                            <div class="text-[10px] text-slate-500 mt-0.5">{{ $seminar->kaprodi_acknowledged_at->format('d/m/Y H:i') }}</div>
                        @else
                            <div class="text-[11px] text-slate-500 mt-1">Menunggu Tahap 2</div>
                        @endif
                    </div>

                    <!-- Step 4: Wadek 1 Approval -->
                    <div class="bg-slate-800/40 border border-slate-700/60 rounded-xl p-4">
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-xs font-semibold text-slate-400">4. Wadek 1 (Dekanat)</span>
                            <span class="w-2.5 h-2.5 rounded-full {{ $seminar->wadek1_approved_at ? 'bg-emerald-400' : ($seminar->isRejected() ? 'bg-rose-500' : 'bg-slate-600') }}"></span>
                        </div>
                        <div class="text-xs font-medium text-white">{{ $seminar->wadek1?->name ?? 'Wakil Dekan 1' }}</div>
                        @if($seminar->wadek1_approved_at)
                            <div class="text-[11px] text-emerald-400 mt-1">Disetujui</div>
                            <div class="text-[10px] text-slate-500 mt-0.5">{{ $seminar->wadek1_approved_at->format('d/m/Y H:i') }}</div>
                        @elseif($seminar->isRejected())
                            <div class="text-[11px] text-rose-400 mt-1">Ditolak</div>
                            <div class="text-[10px] text-slate-500 mt-0.5">{{ $seminar->wadek1_rejected_at?->format('d/m/Y H:i') }}</div>
                        @else
                            <div class="text-[11px] text-slate-500 mt-1">Menunggu Approval</div>
                        @endif
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>
@endsection
