@extends('layouts.app')

@section('content')
<div class="max-w-5xl mx-auto space-y-6">
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-2">
            <a href="{{ route('dosen-mk.submissions.index') }}" class="p-1.5 rounded-lg text-slate-400 hover:text-white hover:bg-slate-800 transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
            </a>
            <div>
                <span class="text-xs font-mono text-cyan-400 uppercase tracking-wider">Review Submisi Tugas Mahasiswa</span>
                <h2 class="text-xl font-bold text-white">{{ $submission->component->name }}</h2>
            </div>
        </div>
        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold border {{ $submission->status_badge_classes }}">
            {{ $submission->status_label }}
        </span>
    </div>

    <!-- Review Form Card -->
    <div class="p-6 rounded-2xl bg-gradient-to-br from-slate-900 via-cyan-950/20 to-slate-900 border border-cyan-500/30 space-y-4 shadow-xl">
        <h3 class="text-sm font-bold text-white flex items-center gap-2">
            <svg class="w-5 h-5 text-cyan-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            Keputusan Review Dosen Pengampu MK
        </h3>

        <form method="POST" action="{{ route('dosen-mk.submissions.review', $submission->id) }}" class="space-y-4">
            @csrf

            <div class="space-y-2">
                <label class="block text-xs font-semibold text-slate-300">Aksi Penilaian <span class="text-rose-400">*</span></label>
                <div class="flex gap-4">
                    <label class="flex items-center gap-2 p-3 rounded-xl bg-slate-950 border border-slate-800 hover:border-emerald-500/50 cursor-pointer flex-1">
                        <input type="radio" name="action" value="APPROVE" {{ old('action', 'APPROVE') == 'APPROVE' ? 'checked' : '' }} class="text-emerald-500 focus:ring-emerald-500">
                        <div>
                            <p class="text-xs font-bold text-emerald-400">Setujui Tugas (Approve)</p>
                            <p class="text-[11px] text-slate-400">Tugas telah memenuhi kriteria dan silabus mata kuliah.</p>
                        </div>
                    </label>
                    <label class="flex items-center gap-2 p-3 rounded-xl bg-slate-950 border border-slate-800 hover:border-amber-500/50 cursor-pointer flex-1">
                        <input type="radio" name="action" value="REVISION" {{ old('action') == 'REVISION' ? 'checked' : '' }} class="text-amber-500 focus:ring-amber-500">
                        <div>
                            <p class="text-xs font-bold text-amber-400">Minta Revisi (Request Revision)</p>
                            <p class="text-[11px] text-slate-400">Wajib sertakan catatan instruksi perbaikan.</p>
                        </div>
                    </label>
                </div>
            </div>

            <div>
                <label for="feedback" class="block text-xs font-semibold text-slate-300 mb-1">
                    Catatan Review & Arahan Perbaikan <span class="text-xs text-amber-400 font-normal">(Wajib diisi jika meminta revisi)</span>
                </label>
                <textarea name="feedback" id="feedback" rows="3"
                    placeholder="Tuliskan catatan evaluasi, kekurangan tugas, atau instruksi revisi untuk mahasiswa..."
                    class="w-full px-3 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white text-xs focus:ring-2 focus:ring-cyan-500 focus:outline-none">{{ old('feedback', $submission->latest_feedback) }}</textarea>
                @error('feedback')
                    <p class="text-[11px] text-rose-400 mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex justify-end pt-2">
                <button type="submit"
                    class="px-5 py-2.5 rounded-xl bg-cyan-600 hover:bg-cyan-500 text-white text-xs font-semibold transition shadow-lg shadow-cyan-600/30">
                    Kirim Keputusan Review
                </button>
            </div>
        </form>
    </div>

    <!-- Student & Component Info -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div class="p-6 rounded-2xl bg-slate-900 border border-slate-800 space-y-4">
            <h3 class="text-xs font-mono uppercase tracking-wider text-slate-400 border-b border-slate-800 pb-2">Informasi Mahasiswa & Magang</h3>
            <div class="space-y-2 text-xs">
                <div class="flex justify-between py-1 border-b border-slate-800/60">
                    <span class="text-slate-400">Nama Mahasiswa</span>
                    <span class="font-bold text-white">{{ $submission->student->name }}</span>
                </div>
                <div class="flex justify-between py-1 border-b border-slate-800/60">
                    <span class="text-slate-400">NIM</span>
                    <span class="font-mono text-cyan-400">{{ $submission->student->identity_number }}</span>
                </div>
                <div class="flex justify-between py-1 border-b border-slate-800/60">
                    <span class="text-slate-400">Mata Kuliah</span>
                    <span class="text-white">{{ $submission->component->course->name }} ({{ $submission->component->course->code }})</span>
                </div>
                <div class="flex justify-between py-1 border-b border-slate-800/60">
                    <span class="text-slate-400">Instansi Magang</span>
                    <span class="text-white">{{ $submission->courseConversion->internshipApplication->partnerInstitution->name }}</span>
                </div>
                <div class="flex justify-between py-1">
                    <span class="text-slate-400">Dosen Pembimbing</span>
                    <span class="text-emerald-400 font-semibold">{{ $submission->courseConversion->internshipApplication->advisor->name ?? 'Belum Ditentukan' }}</span>
                </div>
            </div>
        </div>

        <div class="p-6 rounded-2xl bg-slate-900 border border-slate-800 space-y-4">
            <h3 class="text-xs font-mono uppercase tracking-wider text-slate-400 border-b border-slate-800 pb-2">Ketentuan Komponen Tugas</h3>
            <div class="space-y-2 text-xs">
                <div class="flex justify-between py-1 border-b border-slate-800/60">
                    <span class="text-slate-400">Jenis Submisi</span>
                    <span class="font-mono text-teal-300 font-bold">{{ $submission->component->submission_type }}</span>
                </div>
                <div class="flex justify-between py-1 border-b border-slate-800/60">
                    <span class="text-slate-400">Bobot Penilaian</span>
                    <span class="font-mono text-amber-400 font-bold">{{ $submission->component->weight }}%</span>
                </div>
                <div class="flex justify-between py-1 border-b border-slate-800/60">
                    <span class="text-slate-400">Batas Waktu (Deadline)</span>
                    <span class="font-mono {{ $submission->component->isPassedDeadline() ? 'text-rose-400' : 'text-slate-300' }}">
                        {{ $submission->component->deadline->format('d/m/Y H:i') }}
                    </span>
                </div>
                @if($submission->component->instructions)
                    <div class="py-1">
                        <span class="text-slate-400">Instruksi Tugas:</span>
                        <p class="text-slate-300 italic mt-0.5 whitespace-pre-line">{{ $submission->component->instructions }}</p>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Complete Versioning History (Files never deleted on resubmission) -->
    <div class="p-6 rounded-2xl bg-slate-900 border border-slate-800 space-y-4">
        <div class="flex items-center justify-between border-b border-slate-800 pb-3">
            <h3 class="text-xs font-mono uppercase tracking-wider text-cyan-400 font-bold">
                Histori Seluruh Versi Pengumpulan (Versioning)
            </h3>
            <span class="text-xs font-mono text-slate-400">Total: {{ $submission->versions->count() }} Versi Disimpan</span>
        </div>

        <div class="space-y-4">
            @forelse ($submission->versions as $ver)
                <div class="p-4 rounded-xl bg-slate-950 border border-slate-800/80 space-y-3">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 border-b border-slate-800/60 pb-2">
                        <div class="flex items-center gap-2">
                            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold font-mono bg-cyan-500/20 text-cyan-300 border border-cyan-500/30">
                                Versi {{ $ver->version_number }}
                            </span>
                            <span class="text-[11px] text-slate-400 font-mono">{{ $ver->submitted_at->format('d M Y, H:i') }}</span>
                        </div>
                        <span class="text-[10px] font-bold px-2 py-0.5 rounded border 
                            {{ $ver->status === 'DISETUJUI' ? 'bg-emerald-500/20 text-emerald-300 border-emerald-500/30' : ($ver->status === 'PERLU_PERBAIKAN' ? 'bg-amber-500/20 text-amber-300 border-amber-500/30' : 'bg-blue-500/20 text-blue-300 border-blue-500/30') }}">
                            {{ $ver->status }}
                        </span>
                    </div>

                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 text-xs">
                        <div class="space-y-1">
                            @if ($ver->isFile())
                                <div class="flex items-center gap-2">
                                    <svg class="w-4 h-4 text-cyan-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                    </svg>
                                    <span class="font-medium text-white">{{ $ver->file_name }}</span>
                                    <span class="text-slate-500 font-mono text-[10px]">({{ $ver->formattedFileSize() }})</span>
                                </div>
                            @endif

                            @if ($ver->isLink())
                                <div class="flex items-center gap-2">
                                    <svg class="w-4 h-4 text-emerald-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1" />
                                    </svg>
                                    <a href="{{ $ver->link_url }}" target="_blank" rel="noopener noreferrer" class="text-cyan-400 hover:underline truncate max-w-md">
                                        {{ $ver->link_url }}
                                    </a>
                                </div>
                            @endif

                            @if ($ver->student_notes)
                                <p class="text-[11px] text-slate-400 italic mt-1">Catatan Mahasiswa: "{{ $ver->student_notes }}"</p>
                            @endif
                        </div>

                        @if ($ver->isFile())
                            <a href="{{ route('dosen-mk.submissions.version.download', $ver->id) }}"
                                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-cyan-600/20 hover:bg-cyan-600/30 text-cyan-300 border border-cyan-500/30 text-xs font-semibold transition flex-shrink-0">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                                </svg>
                                Unduh File Versi Ini
                            </a>
                        @endif
                    </div>

                    @if ($ver->reviewer_feedback)
                        <div class="p-3 rounded-lg bg-slate-900 border border-slate-800 text-[11px] text-slate-300">
                            <span class="text-amber-400 font-semibold block mb-0.5">Catatan Reviewer:</span>
                            <p class="italic">"{{ $ver->reviewer_feedback }}"</p>
                        </div>
                    @endif
                </div>
            @empty
                <p class="text-xs text-slate-500">Belum ada versi pengumpulan.</p>
            @endforelse
        </div>
    </div>
</div>
@endsection
