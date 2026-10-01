@extends('layouts.app')

@section('content')
<div class="max-w-5xl mx-auto space-y-6">
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-2">
            <a href="{{ route('kaprodi.conversions.index') }}" class="p-1.5 rounded-lg text-slate-400 hover:text-white hover:bg-slate-800 transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
            </a>
            <div>
                <span class="text-xs font-mono text-purple-400 uppercase tracking-wider">Pengesahan Konversi MK (Kaprodi)</span>
                <h2 class="text-xl font-bold text-white">{{ $conversion->course->name }} ({{ $conversion->course->code }})</h2>
            </div>
        </div>
        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold border {{ $conversion->status_badge_classes }}">
            {{ $conversion->status_label }}
        </span>
    </div>

    <!-- Kaprodi Acknowledge Form (If pending Kaprodi) -->
    @if ($conversion->isApprovedByWadek1())
        <div class="p-6 rounded-2xl bg-gradient-to-br from-slate-900 via-purple-950/20 to-slate-900 border border-purple-500/30 space-y-4 shadow-xl">
            <h3 class="text-sm font-bold text-white flex items-center gap-2">
                <svg class="w-5 h-5 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                Tandai "Diketahui" & Selesaikan Konversi Mata Kuliah
            </h3>
            <p class="text-xs text-slate-300">
                Pengajuan telah disetujui oleh Dosen MK, diverifikasi oleh Dosen Pembimbing, dan di-approve oleh Wakil Dekan 1. Dengan menandai <strong>"Diketahui"</strong>, mata kuliah ini resmi tercatat sebagai konversi magang.
            </p>

            <form method="POST" action="{{ route('kaprodi.conversions.acknowledge', $conversion->id) }}" class="space-y-4">
                @csrf

                <div>
                    <label for="notes" class="block text-xs font-semibold text-slate-300 mb-1">Catatan Tambahan Kaprodi (Opsional)</label>
                    <textarea name="notes" id="notes" rows="2"
                        placeholder="Catatan pengesahan kurikulum atau arsip program studi..."
                        class="w-full px-3 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white text-xs focus:ring-2 focus:ring-purple-500 focus:outline-none">{{ old('notes') }}</textarea>
                </div>

                <div class="flex justify-end gap-3 pt-2">
                    <button type="submit"
                        class="px-5 py-2.5 rounded-xl bg-purple-600 hover:bg-purple-500 text-white text-xs font-semibold transition shadow-lg shadow-purple-600/30">
                        Tandai "Diketahui" (Selesai)
                    </button>
                </div>
            </form>
        </div>
    @endif

    <!-- Content details -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div class="p-6 rounded-2xl bg-slate-900 border border-slate-800 space-y-4">
            <h3 class="text-xs font-mono uppercase tracking-wider text-slate-400 border-b border-slate-800 pb-2">Data Mahasiswa & Verifikasi Bertingkat</h3>
            <div class="space-y-2 text-xs">
                <div class="flex justify-between py-1 border-b border-slate-800/60">
                    <span class="text-slate-400">Mahasiswa</span>
                    <span class="font-bold text-white">{{ $conversion->student->name }} ({{ $conversion->student->identity_number }})</span>
                </div>
                <div class="flex justify-between py-1 border-b border-slate-800/60">
                    <span class="text-slate-400">Instansi Magang</span>
                    <span class="text-white">{{ $conversion->internshipApplication->partnerInstitution->name }}</span>
                </div>
                <div class="flex justify-between py-1 border-b border-slate-800/60">
                    <span class="text-slate-400">1. Dosen Pengampu MK</span>
                    <span class="text-teal-400 font-semibold">{{ $conversion->dosenMk->name ?? '-' }}</span>
                </div>
                <div class="flex justify-between py-1 border-b border-slate-800/60">
                    <span class="text-slate-400">2. Dosen Pembimbing</span>
                    <span class="text-emerald-400 font-semibold">{{ $conversion->dosbing->name ?? '-' }}</span>
                </div>
                <div class="flex justify-between py-1">
                    <span class="text-slate-400">3. Wakil Dekan 1</span>
                    <span class="text-indigo-400 font-semibold">{{ $conversion->wadek1->name ?? '-' }}</span>
                </div>
            </div>
        </div>

        <div class="p-6 rounded-2xl bg-slate-900 border border-slate-800 space-y-4">
            <h3 class="text-xs font-mono uppercase tracking-wider text-slate-400 border-b border-slate-800 pb-2">Uraian Rencana / Relevansi Kegiatan</h3>
            <div class="p-4 rounded-xl bg-slate-950 border border-slate-800 text-xs text-slate-200 whitespace-pre-line leading-relaxed">
                {{ $conversion->activity_plan }}
            </div>
        </div>
    </div>
</div>
@endsection
