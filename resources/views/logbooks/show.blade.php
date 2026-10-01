@extends('layouts.app')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div class="flex items-center gap-2">
            <a href="{{ $isDosbing ? route('academic.logbooks.student', $logbook->internship_application_id) : route('logbooks.index') }}"
                class="p-1.5 rounded-lg text-slate-400 hover:text-white hover:bg-slate-800 transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
            </a>
            <div>
                <span class="text-xs font-mono text-amber-400 uppercase tracking-wider">Logbook Minggu Ke-{{ $logbook->week_number }}</span>
                <h2 class="text-xl font-bold text-white">{{ $logbook->activity_title }}</h2>
            </div>
        </div>

        <div class="flex items-center gap-2">
            @if ($isOwner)
                <a href="{{ route('logbooks.edit', $logbook->id) }}"
                    class="px-3 py-1.5 rounded-xl bg-blue-600 hover:bg-blue-500 text-white text-xs font-semibold transition">
                    Edit Logbook
                </a>
            @endif
        </div>
    </div>

    <!-- Details Card -->
    <div class="p-6 rounded-2xl bg-slate-900 border border-slate-800 space-y-6">
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 pb-4 border-b border-slate-800/80 text-xs">
            <div>
                <span class="text-slate-400">Mahasiswa</span>
                <p class="font-bold text-white mt-0.5">{{ $logbook->student->name }} ({{ $logbook->student->identity_number }})</p>
            </div>
            <div>
                <span class="text-slate-400">Tanggal Pelaksanaan</span>
                <p class="font-mono text-cyan-400 mt-0.5">{{ $logbook->activity_date->format('d F Y') }}</p>
            </div>
            <div>
                <span class="text-slate-400">Instansi Magang</span>
                <p class="font-semibold text-slate-200 mt-0.5">{{ $logbook->internshipApplication->partnerInstitution->name }}</p>
            </div>
        </div>

        <div>
            <h4 class="text-xs font-mono uppercase tracking-wider text-slate-400 mb-2">Deskripsi Rincian Pekerjaan</h4>
            <div class="p-4 rounded-xl bg-slate-950 border border-slate-800 text-xs text-slate-200 whitespace-pre-line leading-relaxed">
                {{ $logbook->description }}
            </div>
        </div>

        <!-- Attachment -->
        <div>
            <h4 class="text-xs font-mono uppercase tracking-wider text-slate-400 mb-2">Lampiran Bukti Pelaksanaan</h4>
            @if ($logbook->attachment_path)
                <div class="p-4 rounded-xl bg-slate-950 border border-slate-800 flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-lg bg-blue-500/10 border border-blue-500/20 flex items-center justify-center text-blue-400">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                        </div>
                        <div>
                            <p class="text-xs font-bold text-white">Dokumen Lampiran Logbook</p>
                            <p class="text-[10px] text-slate-400 font-mono">{{ basename($logbook->attachment_path) }}</p>
                        </div>
                    </div>
                    <a href="{{ route('logbooks.attachment.download', $logbook->id) }}"
                        class="px-3 py-1.5 rounded-lg bg-blue-600/20 hover:bg-blue-600/30 text-blue-300 border border-blue-500/30 text-xs font-semibold transition">
                        Unduh File
                    </a>
                </div>
            @else
                <p class="text-xs text-slate-500 italic">Tidak ada file lampiran yang diunggah.</p>
            @endif
        </div>

        <!-- Dosen Pembimbing Feedback Section -->
        <div class="pt-4 border-t border-slate-800/80 space-y-4">
            <h4 class="text-xs font-mono uppercase tracking-wider text-emerald-400 flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z" />
                </svg>
                Evaluasi / Catatan Dosen Pembimbing
            </h4>

            @if ($logbook->hasFeedback())
                <div class="p-4 rounded-xl bg-emerald-500/10 border border-emerald-500/20 text-xs text-emerald-200 leading-relaxed">
                    <p class="font-medium">"{{ $logbook->dosbing_feedback }}"</p>
                    <div class="mt-2 text-[10px] text-emerald-400 font-mono">
                        Direview oleh: {{ $logbook->reviewer->name ?? 'Dosen Pembimbing' }} ({{ $logbook->dosbing_reviewed_at?->format('d M Y H:i') }})
                    </div>
                </div>
            @else
                <p class="text-xs text-slate-500 italic">Dosen pembimbing belum memberikan catatan evaluasi.</p>
            @endif

            <!-- Form for Dosbing to add or update feedback -->
            @if ($isDosbing || Auth::user()->hasRole('SUPERADMIN'))
                <form method="POST" action="{{ route('academic.logbooks.feedback', $logbook->id) }}" class="space-y-3 pt-2">
                    @csrf
                    <label for="dosbing_feedback" class="block text-xs font-semibold text-slate-300">
                        {{ $logbook->hasFeedback() ? 'Perbarui Catatan Review' : 'Beri Catatan Evaluasi untuk Mahasiswa' }}
                    </label>
                    <textarea name="dosbing_feedback" id="dosbing_feedback" rows="3" required
                        placeholder="Tuliskan catatan evaluasi, arahan, atau verifikasi progres magang..."
                        class="w-full px-3 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white text-xs focus:ring-2 focus:ring-emerald-500 focus:outline-none">{{ old('dosbing_feedback', $logbook->dosbing_feedback) }}</textarea>
                    @error('dosbing_feedback')
                        <p class="text-[11px] text-rose-400">{{ $message }}</p>
                    @enderror
                    <div class="flex justify-end">
                        <button type="submit"
                            class="px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-semibold transition shadow-md shadow-emerald-600/30">
                            Simpan Evaluasi Pembimbing
                        </button>
                    </div>
                </form>
            @endif
        </div>
    </div>
</div>
@endsection
