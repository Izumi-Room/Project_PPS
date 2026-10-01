@extends('layouts.app')

@section('content')
<div class="max-w-5xl mx-auto space-y-6">
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-2">
            <a href="{{ route('wadek1.conversions.index') }}" class="p-1.5 rounded-lg text-slate-400 hover:text-white hover:bg-slate-800 transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
            </a>
            <div>
                <span class="text-xs font-mono text-indigo-400 uppercase tracking-wider">Approval Konversi MK (Wakil Dekan 1)</span>
                <h2 class="text-xl font-bold text-white">{{ $conversion->course->name }} ({{ $conversion->course->code }})</h2>
            </div>
        </div>
        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold border {{ $conversion->status_badge_classes }}">
            {{ $conversion->status_label }}
        </span>
    </div>

    <!-- Wadek 1 Approval Form (If pending Wadek 1) -->
    @if ($conversion->isVerifiedByDosbing())
        <div class="p-6 rounded-2xl bg-gradient-to-br from-slate-900 via-indigo-950/20 to-slate-900 border border-indigo-500/30 space-y-4 shadow-xl">
            <h3 class="text-sm font-bold text-white flex items-center gap-2">
                <svg class="w-5 h-5 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                Keputusan Approval Wakil Dekan 1
            </h3>

            <form method="POST" action="{{ route('wadek1.conversions.review', $conversion->id) }}" class="space-y-4">
                @csrf

                <div class="space-y-2">
                    <label class="block text-xs font-semibold text-slate-300">Aksi Approval <span class="text-rose-400">*</span></label>
                    <div class="flex gap-4">
                        <label class="flex items-center gap-2 p-3 rounded-xl bg-slate-950 border border-slate-800 hover:border-emerald-500/50 cursor-pointer flex-1">
                            <input type="radio" name="action" value="APPROVE" checked class="text-emerald-500 focus:ring-emerald-500">
                            <div>
                                <p class="text-xs font-bold text-emerald-400">Setujui Konversi (Approve)</p>
                                <p class="text-[11px] text-slate-400">Pengajuan konversi disahkan tingkat dekanat dan diteruskan ke Kaprodi.</p>
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
                        placeholder="Tuliskan alasan spesifik penolakan Wadek 1..."
                        class="w-full px-3 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white text-xs focus:ring-2 focus:ring-rose-500 focus:outline-none">{{ old('rejection_reason') }}</textarea>
                    @error('rejection_reason')
                        <p class="text-[11px] text-rose-400">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="notes" class="block text-xs font-semibold text-slate-300 mb-1">Catatan SK / Dekanat (Opsional)</label>
                    <textarea name="notes" id="notes" rows="2"
                        placeholder="Catatan surat keputusan atau ketentuan akademik lainnya..."
                        class="w-full px-3 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white text-xs focus:ring-2 focus:ring-indigo-500 focus:outline-none">{{ old('notes') }}</textarea>
                </div>

                <div class="flex justify-end gap-3 pt-2">
                    <button type="submit"
                        class="px-5 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-semibold transition shadow-lg shadow-indigo-600/30">
                        Kirim Keputusan Wadek 1
                    </button>
                </div>
            </form>
        </div>
    @endif

    <!-- Content details -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div class="p-6 rounded-2xl bg-slate-900 border border-slate-800 space-y-4">
            <h3 class="text-xs font-mono uppercase tracking-wider text-slate-400 border-b border-slate-800 pb-2">Data Mahasiswa & Riwayat Verifikasi</h3>
            <div class="space-y-2 text-xs">
                <div class="flex justify-between py-1 border-b border-slate-800/60">
                    <span class="text-slate-400">Mahasiswa</span>
                    <span class="font-bold text-white">{{ $conversion->student->name }} ({{ $conversion->student->identity_number }})</span>
                </div>
                <div class="flex justify-between py-1 border-b border-slate-800/60">
                    <span class="text-slate-400">Program Studi</span>
                    <span class="text-white">{{ $conversion->internshipApplication->studyProgram->name ?? '-' }}</span>
                </div>
                <div class="flex justify-between py-1 border-b border-slate-800/60">
                    <span class="text-slate-400">Instansi Magang</span>
                    <span class="text-white">{{ $conversion->internshipApplication->partnerInstitution->name }}</span>
                </div>
                <div class="flex justify-between py-1 border-b border-slate-800/60">
                    <span class="text-slate-400">1. Persetujuan Dosen MK</span>
                    <span class="text-teal-400 font-semibold">{{ $conversion->dosenMk->name ?? '-' }}</span>
                </div>
                <div class="flex justify-between py-1">
                    <span class="text-slate-400">2. Verifikasi Dosen Pembimbing</span>
                    <span class="text-emerald-400 font-semibold">{{ $conversion->dosbing->name ?? '-' }}</span>
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
