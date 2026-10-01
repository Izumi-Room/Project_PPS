@extends('layouts.app')

@section('title', 'Review & Keputusan Seminar - Wakil Dekan 1')

@section('content')
<div class="space-y-6 max-w-4xl mx-auto">
    <!-- Breadcrumb & Back -->
    <div class="flex items-center justify-between">
        <a href="{{ route('wadek1.seminars.index') }}" class="inline-flex items-center gap-2 text-sm text-slate-400 hover:text-white transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" /></svg>
            Kembali ke Antrean Wadek 1
        </a>
        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold border {{ $seminar->statusBadgeColor() }}">
            {{ $seminar->statusLabel() }}
        </span>
    </div>

    <!-- Seminar Details Card -->
    <div class="bg-slate-900/60 backdrop-blur-md border border-slate-800 rounded-2xl p-6 shadow-xl space-y-6">
        <div>
            <span class="text-xs font-bold text-indigo-400 uppercase tracking-wider">Permohonan Persetujuan Pelaksanaan Seminar</span>
            <h1 class="text-2xl font-bold text-white mt-1">{{ $seminar->course?->name ?? 'Mata Kuliah Magang' }}</h1>
            <p class="text-sm text-slate-400 mt-1">
                Mahasiswa: <strong class="text-slate-200">{{ $seminar->student?->name }}</strong> (NIM: {{ $seminar->student?->identifier_number }}) &bull; {{ $seminar->student?->studyProgram?->name ?? '-' }}
            </p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="bg-slate-800/40 border border-slate-700/60 rounded-xl p-4">
                <div class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-1">Rencana Jadwal</div>
                <div class="text-lg font-bold text-white">{{ $seminar->scheduled_date ? $seminar->scheduled_date->format('l, d F Y') : '-' }}</div>
                <div class="text-sm text-indigo-400 mt-0.5">Pukul: {{ $seminar->scheduled_time ?? '-' }}</div>
            </div>
            <div class="bg-slate-800/40 border border-slate-700/60 rounded-xl p-4">
                <div class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-1">Tempat / Tautan Pertemuan</div>
                <div class="text-base font-semibold text-white break-all">{{ $seminar->location_or_link ?? '-' }}</div>
            </div>
        </div>

        @if($seminar->information)
            <div class="bg-slate-800/30 border border-slate-700/50 rounded-xl p-4">
                <div class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-1">Informasi Seminar</div>
                <p class="text-sm text-slate-300 whitespace-pre-line">{{ $seminar->information }}</p>
            </div>
        @endif

        <!-- Verification Track Record -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 pt-2">
            <div class="bg-slate-800/30 border border-slate-700/50 rounded-xl p-4 text-xs">
                <div class="font-semibold text-white flex items-center gap-1.5 mb-1">
                    <svg class="w-4 h-4 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                    Dosen Pembimbing:
                </div>
                <div class="text-slate-300">{{ $seminar->dosbing?->name ?? $seminar->internshipApplication?->advisor?->name ?? '-' }}</div>
                <div class="text-[11px] text-slate-500 mt-0.5">{{ $seminar->dosbing_acknowledged_at?->format('d/m/Y H:i') ?? '-' }}</div>
                @if($seminar->dosbing_notes)
                    <div class="mt-1 text-slate-400 italic">"{{ $seminar->dosbing_notes }}"</div>
                @endif
            </div>

            <div class="bg-slate-800/30 border border-slate-700/50 rounded-xl p-4 text-xs">
                <div class="font-semibold text-white flex items-center gap-1.5 mb-1">
                    <svg class="w-4 h-4 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                    Ketua Program Studi:
                </div>
                <div class="text-slate-300">{{ $seminar->kaprodi?->name ?? 'Kaprodi' }}</div>
                <div class="text-[11px] text-slate-500 mt-0.5">{{ $seminar->kaprodi_acknowledged_at?->format('d/m/Y H:i') ?? '-' }}</div>
                @if($seminar->kaprodi_notes)
                    <div class="mt-1 text-slate-400 italic">"{{ $seminar->kaprodi_notes }}"</div>
                @endif
            </div>
        </div>

        @if($seminar->isApproved())
            <div class="bg-emerald-950/40 border border-emerald-800/60 rounded-xl p-4 text-xs text-emerald-300 flex items-center gap-3">
                <svg class="w-5 h-5 text-emerald-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                <div>
                    Jadwal seminar ini telah Anda setujui pada <strong>{{ $seminar->wadek1_approved_at?->format('d F Y, H:i') }}</strong>.
                </div>
            </div>
        @elseif($seminar->isRejected())
            <div class="bg-rose-950/40 border border-rose-800/60 rounded-xl p-4 text-xs text-rose-300">
                <div class="font-semibold flex items-center gap-2 mb-1">
                    <svg class="w-4 h-4 text-rose-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                    Jadwal seminar telah ditolak pada {{ $seminar->wadek1_rejected_at?->format('d F Y, H:i') }}
                </div>
                <div class="mt-1 text-slate-300">Alasan Penolakan: <em>"{{ $seminar->rejection_reason }}"</em></div>
            </div>
        @else
            <!-- Action Forms (Approve / Reject) -->
            <div class="pt-4 border-t border-slate-800 space-y-6" x-data="{ showRejectModal: false }">
                <div class="flex items-center justify-end gap-3">
                    <button type="button" @click="showRejectModal = true" class="px-5 py-2.5 rounded-xl font-medium bg-rose-600/20 hover:bg-rose-600/30 text-rose-300 border border-rose-500/30 transition text-sm">
                        Tolak Jadwal Seminar
                    </button>
                    <form action="{{ route('wadek1.seminars.approve', $seminar) }}" method="POST">
                        @csrf
                        <button type="submit" class="px-6 py-2.5 rounded-xl font-medium bg-indigo-600 hover:bg-indigo-500 text-white shadow-lg shadow-indigo-600/30 transition text-sm flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                            <span>Setujui Seminar Magang</span>
                        </button>
                    </form>
                </div>

                <!-- Rejection Modal / Form -->
                <div x-show="showRejectModal" x-cloak class="p-5 bg-rose-950/30 border border-rose-800/60 rounded-xl space-y-4">
                    <h4 class="text-sm font-bold text-rose-300">Penolakan Jadwal Seminar Magang</h4>
                    <p class="text-xs text-slate-400">
                        Penolakan wajib menyertakan alasan. Dosen Pengampu MK akan menerima notifikasi beserta catatan penolakan untuk melakukan penjadwalan ulang.
                    </p>
                    <form action="{{ route('wadek1.seminars.reject', $seminar) }}" method="POST" class="space-y-4">
                        @csrf
                        <div>
                            <label class="block text-xs font-semibold text-slate-300 mb-2">Alasan / Catatan Penolakan *</label>
                            <textarea name="rejection_reason" rows="3" required placeholder="Jelaskan alasan penolakan jadwal seminar ini..."
                                class="w-full bg-slate-800/80 border border-slate-700 rounded-xl p-3 text-sm text-white focus:outline-none focus:border-rose-500">{{ old('rejection_reason') }}</textarea>
                            @error('rejection_reason') <span class="text-xs text-rose-400 mt-1 block">{{ $message }}</span> @enderror
                        </div>
                        <div class="flex items-center justify-end gap-3">
                            <button type="button" @click="showRejectModal = false" class="px-4 py-2 rounded-xl text-xs text-slate-400 hover:text-white transition">
                                Batal
                            </button>
                            <button type="submit" class="px-5 py-2 rounded-xl text-xs font-medium bg-rose-600 hover:bg-rose-500 text-white transition">
                                Konfirmasi Tolak Jadwal
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        @endif
    </div>
</div>
@endsection
