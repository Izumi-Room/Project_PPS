@extends('layouts.app')

@section('content')
<div class="max-w-5xl mx-auto space-y-6">
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-2">
            <a href="{{ route('academic.conversions.index') }}" class="p-1.5 rounded-lg text-slate-400 hover:text-white hover:bg-slate-800 transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
            </a>
            <div>
                <span class="text-xs font-mono text-emerald-400 uppercase tracking-wider">Verifikasi Konversi MK (Dosen Pembimbing)</span>
                <h2 class="text-xl font-bold text-white">{{ $conversion->course->name }} ({{ $conversion->course->code }})</h2>
            </div>
        </div>
        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold border {{ $conversion->status_badge_classes }}">
            {{ $conversion->status_label }}
        </span>
    </div>

    <!-- Verification Form (If pending Dosbing verification) -->
    @if ($conversion->isApprovedByDosenMk())
        <div class="p-6 rounded-2xl bg-gradient-to-br from-slate-900 via-emerald-950/20 to-slate-900 border border-emerald-500/30 space-y-4 shadow-xl">
            <h3 class="text-sm font-bold text-white flex items-center gap-2">
                <svg class="w-5 h-5 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                Keputusan Verifikasi Dosen Pembimbing
            </h3>

            <form method="POST" action="{{ route('academic.conversions.review', $conversion->id) }}" class="space-y-4">
                @csrf

                <div class="space-y-2">
                    <label class="block text-xs font-semibold text-slate-300">Aksi Verifikasi <span class="text-rose-400">*</span></label>
                    <div class="flex gap-4">
                        <label class="flex items-center gap-2 p-3 rounded-xl bg-slate-950 border border-slate-800 hover:border-emerald-500/50 cursor-pointer flex-1">
                            <input type="radio" name="action" value="APPROVE" checked class="text-emerald-500 focus:ring-emerald-500">
                            <div>
                                <p class="text-xs font-bold text-emerald-400">Verifikasi & Teruskan ke Wadek 1</p>
                                <p class="text-[11px] text-slate-400">Aktivitas magang memenuhi bobot kredit SKS mata kuliah.</p>
                            </div>
                        </label>
                        <label class="flex items-center gap-2 p-3 rounded-xl bg-slate-950 border border-slate-800 hover:border-rose-500/50 cursor-pointer flex-1">
                            <input type="radio" name="action" value="REJECT" class="text-rose-500 focus:ring-rose-500">
                            <div>
                                <p class="text-xs font-bold text-rose-400">Tolak Konversi (Reject)</p>
                                <p class="text-[11px] text-slate-400">Wajib sertakan alasan penolakan.</p>
                            </div>
                        </label>
                    </div>
                </div>

                <div>
                    <label for="rejection_reason" class="block text-xs font-semibold text-slate-300 mb-1">
                        Alasan Penolakan <span class="text-xs text-rose-400 font-normal">(Wajib diisi jika memilih Tolak)</span>
                    </label>
                    <textarea name="rejection_reason" id="rejection_reason" rows="2"
                        placeholder="Tuliskan alasan spesifik penolakan verifikasi..."
                        class="w-full px-3 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white text-xs focus:ring-2 focus:ring-rose-500 focus:outline-none">{{ old('rejection_reason') }}</textarea>
                    @error('rejection_reason')
                        <p class="text-[11px] text-rose-400">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="notes" class="block text-xs font-semibold text-slate-300 mb-1">Catatan Tambahan Pembimbing (Opsional)</label>
                    <textarea name="notes" id="notes" rows="2"
                        placeholder="Catatan verifikasi atau rekomendasi pelaksanaan..."
                        class="w-full px-3 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white text-xs focus:ring-2 focus:ring-emerald-500 focus:outline-none">{{ old('notes') }}</textarea>
                </div>

                <div class="flex justify-end gap-3 pt-2">
                    <button type="submit"
                        class="px-5 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-semibold transition shadow-lg shadow-emerald-600/30">
                        Kirim Verifikasi Pembimbing
                    </button>
                </div>
            </form>
        </div>
    @endif

    <!-- Content details -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div class="p-6 rounded-2xl bg-slate-900 border border-slate-800 space-y-4">
            <h3 class="text-xs font-mono uppercase tracking-wider text-slate-400 border-b border-slate-800 pb-2">Data Mahasiswa & Magang</h3>
            <div class="space-y-2 text-xs">
                <div class="flex justify-between py-1 border-b border-slate-800/60">
                    <span class="text-slate-400">Nama Mahasiswa</span>
                    <span class="font-bold text-white">{{ $conversion->student->name }}</span>
                </div>
                <div class="flex justify-between py-1 border-b border-slate-800/60">
                    <span class="text-slate-400">NIM</span>
                    <span class="font-mono text-cyan-400">{{ $conversion->student->identity_number }}</span>
                </div>
                <div class="flex justify-between py-1 border-b border-slate-800/60">
                    <span class="text-slate-400">Instansi Magang</span>
                    <span class="text-white">{{ $conversion->internshipApplication->partnerInstitution->name }}</span>
                </div>
                <div class="flex justify-between py-1 border-b border-slate-800/60">
                    <span class="text-slate-400">Persetujuan Dosen MK</span>
                    <span class="text-emerald-400 font-semibold">{{ $conversion->dosenMk->name ?? '-' }} ({{ $conversion->dosen_mk_approved_at?->format('d/m/Y') }})</span>
                </div>
                @if($conversion->dosen_mk_notes)
                    <div class="py-1">
                        <span class="text-slate-400">Catatan Dosen MK:</span>
                        <p class="text-slate-300 italic mt-0.5">"{{ $conversion->dosen_mk_notes }}"</p>
                    </div>
                @endif
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
