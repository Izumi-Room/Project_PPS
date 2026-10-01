@extends('layouts.app')

@section('content')
<div class="max-w-6xl mx-auto space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div class="flex items-center gap-3">
            <a href="{{ route('academic.advisor-assignments.index') }}" class="p-2 rounded-xl bg-slate-900 border border-slate-800 text-slate-400 hover:text-white transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
            </a>
            <div>
                <span class="text-xs font-mono text-emerald-400 uppercase tracking-wider">Monitoring Logbook Mahasiswa Bimbingan</span>
                <h2 class="text-2xl font-extrabold text-white tracking-tight">
                    {{ $internship->student->name }} ({{ $internship->student->identity_number }})
                </h2>
                <p class="text-xs text-slate-400 mt-0.5">
                    {{ $internship->partnerInstitution->name }} • {{ $internship->studyProgram->name ?? 'Program Studi' }}
                </p>
            </div>
        </div>
    </div>

    <!-- Logbook Entries -->
    <div class="space-y-4">
        @forelse ($logbooks as $log)
            <div class="p-6 rounded-2xl bg-slate-900 border border-slate-800 space-y-4 shadow-lg">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 border-b border-slate-800 pb-3">
                    <div class="flex items-center gap-3">
                        <span class="px-3 py-1 rounded-xl bg-amber-500/20 text-amber-300 border border-amber-500/30 text-xs font-mono font-bold">
                            Minggu ke-{{ $log->week_number }}
                        </span>
                        <h3 class="text-base font-bold text-white">{{ $log->activity_title }}</h3>
                    </div>
                    <span class="text-xs font-mono text-slate-400">{{ $log->activity_date->format('d F Y') }}</span>
                </div>

                <div class="text-xs text-slate-300 whitespace-pre-line leading-relaxed">
                    {{ $log->description }}
                </div>

                @if ($log->attachment_path)
                    <div class="pt-2">
                        <a href="{{ route('logbooks.attachment.download', $log->id) }}"
                            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-blue-600/20 hover:bg-blue-600/30 text-blue-300 border border-blue-500/30 text-xs font-semibold transition">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                            Unduh Lampiran Bukti
                        </a>
                    </div>
                @endif

                <!-- Dosbing Feedback Box -->
                <div class="p-4 rounded-xl bg-slate-950 border border-slate-800/80 space-y-3">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-mono text-emerald-400 font-bold">Catatan Review Pembimbing:</span>
                        @if ($log->hasFeedback())
                            <span class="text-[10px] text-slate-500 font-mono">
                                Terakhir dinilai: {{ $log->dosbing_reviewed_at?->format('d/m/Y H:i') }}
                            </span>
                        @endif
                    </div>

                    <form method="POST" action="{{ route('academic.logbooks.feedback', $log->id) }}" class="space-y-2">
                        @csrf
                        <textarea name="dosbing_feedback" rows="2" required
                            placeholder="Tuliskan arahan atau feedback evaluasi logbook..."
                            class="w-full px-3 py-2 rounded-xl bg-slate-900 border border-slate-800 text-white text-xs focus:ring-2 focus:ring-emerald-500 focus:outline-none">{{ old('dosbing_feedback', $log->dosbing_feedback) }}</textarea>
                        @error('dosbing_feedback')
                            <p class="text-[11px] text-rose-400">{{ $message }}</p>
                        @enderror
                        <div class="flex justify-end">
                            <button type="submit"
                                class="px-4 py-1.5 rounded-lg bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-semibold transition">
                                {{ $log->hasFeedback() ? 'Perbarui Catatan' : 'Kirim Catatan Review' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        @empty
            <div class="p-12 rounded-2xl bg-slate-900 border border-slate-800 text-center text-slate-500">
                <svg class="w-12 h-12 mx-auto text-slate-600 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                </svg>
                <p class="text-sm font-semibold text-slate-400">Mahasiswa ini belum mengisi catatan logbook.</p>
            </div>
        @endforelse

        @if($logbooks->hasPages())
            <div class="p-4 border-t border-slate-800">
                {{ $logbooks->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
