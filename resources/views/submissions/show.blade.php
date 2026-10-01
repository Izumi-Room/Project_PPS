@extends('layouts.app')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div class="flex items-center gap-2">
            <a href="{{ route('submissions.index', ['conversion_id' => $conversion->id]) }}" class="p-1.5 rounded-lg text-slate-400 hover:text-white hover:bg-slate-800 transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
            </a>
            <div>
                <span class="text-xs font-mono text-cyan-400 uppercase tracking-wider">Pengumpulan Tugas • {{ $component->course->name }}</span>
                <h2 class="text-xl font-bold text-white">{{ $component->name }}</h2>
            </div>
        </div>

        @if ($submission)
            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold border {{ $submission->status_badge_classes }}">
                {{ $submission->status_label }}
            </span>
        @else
            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-slate-800 text-slate-400 border border-slate-700">
                Belum Dikumpulkan
            </span>
        @endif
    </div>

    <!-- Component Metadata Card -->
    <div class="p-6 rounded-2xl bg-slate-900 border border-slate-800 space-y-4">
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 pb-3 border-b border-slate-800 text-xs">
            <div>
                <span class="text-slate-400">Jenis Submisi:</span>
                <p class="font-mono text-teal-400 font-bold mt-0.5">{{ $component->submission_type }}</p>
            </div>
            <div>
                <span class="text-slate-400">Batas Waktu (Deadline):</span>
                <p class="font-mono font-bold mt-0.5 {{ $component->isPassedDeadline() ? 'text-rose-400' : 'text-amber-400' }}">
                    {{ $component->deadline->format('d F Y, H:i') }} WIB
                    @if($component->isPassedDeadline())
                        <span class="text-rose-400 text-[10px] block font-sans">(Batas waktu telah berakhir)</span>
                    @endif
                </p>
            </div>
            <div>
                <span class="text-slate-400">Bobot Penilaian:</span>
                <p class="font-mono font-bold text-white mt-0.5">{{ $component->weight }}% {{ $component->is_required ? '(Wajib)' : '(Opsional)' }}</p>
            </div>
        </div>

        @if ($component->instructions)
            <div class="text-xs">
                <span class="text-slate-400 font-medium block mb-1">Petunjuk & Ketentuan Tugas:</span>
                <div class="p-3 rounded-xl bg-slate-950/80 border border-slate-800/80 text-slate-200 whitespace-pre-line leading-relaxed">
                    {{ $component->instructions }}
                </div>
            </div>
        @endif
    </div>

    <!-- Revision Alert if Dosen requested revision -->
    @if ($submission && $submission->needsRevision())
        <div class="p-5 rounded-2xl bg-amber-500/10 border border-amber-500/20 text-amber-300 space-y-2">
            <div class="flex items-center gap-2">
                <svg class="w-5 h-5 text-amber-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
                <h4 class="text-sm font-bold text-white">Dosen Pengampu Meminta Perbaikan Tugas (Revisi)</h4>
            </div>
            <p class="text-xs text-amber-200/90 pl-7 leading-relaxed">
                <strong>Catatan Evaluasi:</strong> "{{ $submission->latest_feedback }}"
            </p>
            <p class="text-[11px] text-amber-400/80 pl-7">
                Silakan lakukan perbaikan dan kirim ulang berkas/link tugas Anda pada form di bawah ini. File versi sebelumnya akan tetap tersimpan aman dalam histori.
            </p>
        </div>
    @endif

    <!-- Upload / Resubmission Form -->
    @if (! $component->isPassedDeadline() && (! $submission || ! $submission->isApproved()))
        <div class="p-6 rounded-2xl bg-gradient-to-br from-slate-900 via-cyan-950/20 to-slate-900 border border-cyan-500/30 space-y-4 shadow-xl">
            <h3 class="text-sm font-bold text-white flex items-center gap-2">
                <svg class="w-5 h-5 text-cyan-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
                </svg>
                {{ $submission && $submission->current_version > 0 ? 'Kirim Ulang Tugas (Resubmission Versi ' . ($submission->current_version + 1) . ')' : 'Form Pengumpulan Tugas (Versi 1)' }}
            </h3>

            <form method="POST" action="{{ route('submissions.submit', $component->id) }}" enctype="multipart/form-data" class="space-y-4">
                @csrf
                <input type="hidden" name="course_conversion_id" value="{{ $conversion->id }}">

                @if (in_array($component->submission_type, ['FILE', 'FILE_OR_LINK', 'DOCUMENT']))
                    <div>
                        <label for="file" class="block text-xs font-semibold text-slate-300 mb-1">
                            Unggah Berkas / Dokumen Tugas {{ $component->submission_type === 'FILE' && $component->is_required ? '*' : '' }}
                        </label>
                        <input type="file" name="file" id="file"
                            accept=".pdf,.zip,.rar,.tar,.gz,.7z,.jpg,.png,.doc,.docx,.ppt,.pptx"
                            class="w-full px-4 py-2 rounded-xl bg-slate-950 border border-slate-800 text-slate-400 text-xs focus:outline-none file:mr-4 file:py-1 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-cyan-600 file:text-white hover:file:bg-cyan-500 cursor-pointer">
                        <p class="text-[11px] text-slate-500 mt-1">Format didukung: PDF, ZIP, RAR, DOCX, PPTX (Maksimal 20MB).</p>
                        @error('file')
                            <p class="text-[11px] text-rose-400 mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                @endif

                @if (in_array($component->submission_type, ['LINK', 'FILE_OR_LINK', 'PROJECT_URL']))
                    <div>
                        <label for="link_url" class="block text-xs font-semibold text-slate-300 mb-1">
                            Tautan Link URL (GitHub / Google Drive / Video Demo) {{ $component->submission_type === 'LINK' && $component->is_required ? '*' : '' }}
                        </label>
                        <input type="url" name="link_url" id="link_url"
                            placeholder="https://github.com/... atau https://drive.google.com/..."
                            value="{{ old('link_url') }}"
                            class="w-full px-4 py-2.5 rounded-xl bg-slate-950 border border-slate-800 text-white text-xs focus:ring-2 focus:ring-cyan-500 focus:outline-none">
                        @error('link_url')
                            <p class="text-[11px] text-rose-400 mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                @endif

                <div>
                    <label for="student_notes" class="block text-xs font-semibold text-slate-300 mb-1">Catatan Tambahan untuk Dosen MK (Opsional)</label>
                    <textarea name="student_notes" id="student_notes" rows="2"
                        placeholder="Catatan pengerjaan atau keterangan khusus..."
                        class="w-full px-3 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white text-xs focus:ring-2 focus:ring-cyan-500 focus:outline-none">{{ old('student_notes') }}</textarea>
                </div>

                <div class="flex justify-end pt-2">
                    <button type="submit"
                        class="px-6 py-2.5 rounded-xl bg-cyan-600 hover:bg-cyan-500 text-white text-xs font-semibold transition shadow-lg shadow-cyan-600/30">
                        {{ $submission && $submission->current_version > 0 ? 'Kirim Ulang Tugas' : 'Kumpulkan Tugas Sekarang' }}
                    </button>
                </div>
            </form>
        </div>
    @elseif ($component->isPassedDeadline())
        <div class="p-4 rounded-xl bg-slate-900 border border-rose-500/20 text-rose-300 text-xs flex items-center gap-3">
            <svg class="w-5 h-5 flex-shrink-0 text-rose-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
            </svg>
            <span>Pengumpulan ditutup karena telah melewati batas waktu (deadline).</span>
        </div>
    @elseif ($submission && $submission->isApproved())
        <div class="p-4 rounded-xl bg-emerald-500/10 border border-emerald-500/20 text-emerald-300 text-xs flex items-center gap-3">
            <svg class="w-5 h-5 flex-shrink-0 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
            </svg>
            <span>Tugas ini telah disetujui resmi oleh Dosen Pengampu MK. Tidak diperlukan pengiriman ulang.</span>
        </div>
    @endif

    <!-- Versioning History (Never deleted on resubmission) -->
    @if ($submission && $submission->versions->isNotEmpty())
        <div class="p-6 rounded-2xl bg-slate-900 border border-slate-800 space-y-4">
            <div class="flex items-center justify-between border-b border-slate-800 pb-3">
                <h3 class="text-xs font-mono uppercase tracking-wider text-cyan-400 font-bold">
                    Riwayat Versi Pengumpulan Tugas
                </h3>
                <span class="text-xs font-mono text-slate-400">Total Versi: {{ $submission->versions->count() }}</span>
            </div>

            <div class="space-y-4">
                @foreach ($submission->versions as $ver)
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
                                    <p class="text-[11px] text-slate-400 italic mt-1">Catatan Anda: "{{ $ver->student_notes }}"</p>
                                @endif
                            </div>

                            @if ($ver->isFile())
                                <a href="{{ route('submissions.download', $ver->id) }}"
                                    class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-cyan-600/20 hover:bg-cyan-600/30 text-cyan-300 border border-cyan-500/30 text-xs font-semibold transition flex-shrink-0">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                                    </svg>
                                    Unduh Berkas
                                </a>
                            @endif
                        </div>

                        @if ($ver->reviewer_feedback)
                            <div class="p-3 rounded-lg bg-slate-900 border border-slate-800 text-[11px] text-slate-300">
                                <span class="text-amber-400 font-semibold block mb-0.5">Catatan Dosen MK:</span>
                                <p class="italic">"{{ $ver->reviewer_feedback }}"</p>
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>
    @endif
</div>
@endsection
