@extends('layouts.app')

@section('content')
<div class="max-w-5xl mx-auto space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 mb-1">
                <a href="{{ route('conversions.index') }}" class="p-1.5 rounded-lg text-slate-400 hover:text-white hover:bg-slate-800 transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                </a>
                <span class="text-xs font-mono text-cyan-400 uppercase tracking-wider">Detail Konversi MK #{{ $conversion->id }}</span>
            </div>
            <h2 class="text-2xl font-extrabold text-white tracking-tight">
                {{ $conversion->course->name }} ({{ $conversion->course->code }})
            </h2>
        </div>

        <div class="flex items-center gap-3">
            <span class="inline-flex items-center px-3 py-1.5 rounded-full text-xs font-bold border {{ $conversion->status_badge_classes }}">
                {{ $conversion->status_label }}
            </span>
        </div>
    </div>

    <!-- Rejection Alert & Resubmit Form -->
    @if ($conversion->isRejected())
        <div class="p-5 rounded-2xl bg-rose-500/10 border border-rose-500/20 text-rose-300 space-y-3">
            <div class="flex items-start gap-3">
                <svg class="w-5 h-5 text-rose-400 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
                <div>
                    <h3 class="text-sm font-bold text-white">Pengajuan Konversi Ditolak pada Tahap: {{ $conversion->rejection_stage ?? 'Reviewer' }}</h3>
                    <p class="text-xs text-rose-200/90 mt-1">
                        <strong>Alasan Penolakan:</strong> {{ $conversion->rejection_reason }}
                    </p>
                </div>
            </div>

            @if (Auth::id() === $conversion->user_id)
                <div class="pt-3 border-t border-rose-500/20">
                    <h4 class="text-xs font-bold text-white mb-2">Form Perbaikan & Pengajuan Ulang (Resubmission)</h4>
                    <form method="POST" action="{{ route('conversions.resubmit', $conversion->id) }}" class="space-y-3">
                        @csrf
                        <textarea name="activity_plan" rows="4" required
                            class="w-full px-4 py-2.5 rounded-xl bg-slate-950 border border-slate-800 text-white text-xs focus:ring-2 focus:ring-blue-500 focus:outline-none">{{ old('activity_plan', $conversion->activity_plan) }}</textarea>
                        @error('activity_plan')
                            <p class="text-[11px] text-rose-400">{{ $message }}</p>
                        @enderror
                        <button type="submit"
                            class="px-4 py-2 rounded-xl bg-blue-600 hover:bg-blue-500 text-white text-xs font-semibold transition shadow-md shadow-blue-600/20">
                            Ajukan Ulang ke Dosen MK
                        </button>
                    </form>
                </div>
            @endif
        </div>
    @endif

    <!-- Workflow Progression Stepper -->
    <div class="p-6 rounded-2xl bg-slate-900 border border-slate-800">
        <h3 class="text-xs font-mono uppercase tracking-wider text-slate-400 mb-6">Alur Persetujuan Bertingkat</h3>
        
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <!-- Step 1: Dosen MK -->
            <div class="p-4 rounded-xl border {{ in_array($conversion->status, ['DISETUJUI_DOSEN_MK', 'DIVERIFIKASI_DOSBING', 'DISETUJUI_WADEK1', 'DIKETAHUI_KAPRODI']) ? 'bg-emerald-500/10 border-emerald-500/30' : ($conversion->status === 'DIAJUKAN' ? 'bg-amber-500/10 border-amber-500/30' : 'bg-slate-950/60 border-slate-800') }}">
                <div class="flex items-center justify-between text-xs font-bold mb-1">
                    <span>1. Dosen Pengampu MK</span>
                    @if (in_array($conversion->status, ['DISETUJUI_DOSEN_MK', 'DIVERIFIKASI_DOSBING', 'DISETUJUI_WADEK1', 'DIKETAHUI_KAPRODI']))
                        <span class="text-emerald-400">✓ Disetujui</span>
                    @elseif ($conversion->status === 'DIAJUKAN')
                        <span class="text-amber-400">Menunggu</span>
                    @elseif ($conversion->rejection_stage === 'DOSEN_MK')
                        <span class="text-rose-400">✗ Ditolak</span>
                    @endif
                </div>
                <p class="text-[11px] text-slate-400">Review kesesuaian materi & CPMK mata kuliah.</p>
                @if ($conversion->dosen_mk_approved_at)
                    <p class="text-[10px] text-emerald-400 font-mono mt-2">
                        Oleh: {{ $conversion->dosenMk->name ?? 'Dosen MK' }} ({{ $conversion->dosen_mk_approved_at->format('d/m/Y') }})
                    </p>
                @endif
            </div>

            <!-- Step 2: Dosen Pembimbing -->
            <div class="p-4 rounded-xl border {{ in_array($conversion->status, ['DIVERIFIKASI_DOSBING', 'DISETUJUI_WADEK1', 'DIKETAHUI_KAPRODI']) ? 'bg-emerald-500/10 border-emerald-500/30' : ($conversion->status === 'DISETUJUI_DOSEN_MK' ? 'bg-amber-500/10 border-amber-500/30' : 'bg-slate-950/60 border-slate-800') }}">
                <div class="flex items-center justify-between text-xs font-bold mb-1">
                    <span>2. Dosen Pembimbing</span>
                    @if (in_array($conversion->status, ['DIVERIFIKASI_DOSBING', 'DISETUJUI_WADEK1', 'DIKETAHUI_KAPRODI']))
                        <span class="text-emerald-400">✓ Diverifikasi</span>
                    @elseif ($conversion->status === 'DISETUJUI_DOSEN_MK')
                        <span class="text-amber-400">Menunggu</span>
                    @elseif ($conversion->rejection_stage === 'DOSBING')
                        <span class="text-rose-400">✗ Ditolak</span>
                    @endif
                </div>
                <p class="text-[11px] text-slate-400">Verifikasi beban aktivitas magang mahasiswa.</p>
                @if ($conversion->dosbing_verified_at)
                    <p class="text-[10px] text-emerald-400 font-mono mt-2">
                        Oleh: {{ $conversion->dosbing->name ?? 'Dosbing' }} ({{ $conversion->dosbing_verified_at->format('d/m/Y') }})
                    </p>
                @endif
            </div>

            <!-- Step 3: Wadek 1 -->
            <div class="p-4 rounded-xl border {{ in_array($conversion->status, ['DISETUJUI_WADEK1', 'DIKETAHUI_KAPRODI']) ? 'bg-emerald-500/10 border-emerald-500/30' : ($conversion->status === 'DIVERIFIKASI_DOSBING' ? 'bg-amber-500/10 border-amber-500/30' : 'bg-slate-950/60 border-slate-800') }}">
                <div class="flex items-center justify-between text-xs font-bold mb-1">
                    <span>3. Wakil Dekan 1</span>
                    @if (in_array($conversion->status, ['DISETUJUI_WADEK1', 'DIKETAHUI_KAPRODI']))
                        <span class="text-emerald-400">✓ Disetujui</span>
                    @elseif ($conversion->status === 'DIVERIFIKASI_DOSBING')
                        <span class="text-amber-400">Menunggu</span>
                    @elseif ($conversion->rejection_stage === 'WADEK1')
                        <span class="text-rose-400">✗ Ditolak</span>
                    @endif
                </div>
                <p class="text-[11px] text-slate-400">Approval tingkat fakultas akademik.</p>
                @if ($conversion->wadek1_approved_at)
                    <p class="text-[10px] text-emerald-400 font-mono mt-2">
                        Oleh: {{ $conversion->wadek1->name ?? 'Wadek 1' }} ({{ $conversion->wadek1_approved_at->format('d/m/Y') }})
                    </p>
                @endif
            </div>

            <!-- Step 4: Kaprodi -->
            <div class="p-4 rounded-xl border {{ $conversion->status === 'DIKETAHUI_KAPRODI' ? 'bg-emerald-500/10 border-emerald-500/30' : ($conversion->status === 'DISETUJUI_WADEK1' ? 'bg-purple-500/10 border-purple-500/30' : 'bg-slate-950/60 border-slate-800') }}">
                <div class="flex items-center justify-between text-xs font-bold mb-1">
                    <span>4. Kaprodi</span>
                    @if ($conversion->status === 'DIKETAHUI_KAPRODI')
                        <span class="text-emerald-400">✓ Diketahui</span>
                    @elseif ($conversion->status === 'DISETUJUI_WADEK1')
                        <span class="text-purple-400">Menunggu</span>
                    @endif
                </div>
                <p class="text-[11px] text-slate-400">Pengesahan tanda "Diketahui" akhir.</p>
                @if ($conversion->kaprodi_acknowledged_at)
                    <p class="text-[10px] text-emerald-400 font-mono mt-2">
                        Oleh: {{ $conversion->kaprodi->name ?? 'Kaprodi' }} ({{ $conversion->kaprodi_acknowledged_at->format('d/m/Y') }})
                    </p>
                @endif
            </div>
        </div>
    </div>

    <!-- Details Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- Info Mata Kuliah & Magang -->
        <div class="p-6 rounded-2xl bg-slate-900 border border-slate-800 space-y-4">
            <h3 class="text-xs font-mono uppercase tracking-wider text-slate-400 border-b border-slate-800 pb-2">Informasi Mata Kuliah</h3>
            <div class="space-y-2 text-xs">
                <div class="flex justify-between py-1 border-b border-slate-800/60">
                    <span class="text-slate-400">Nama Mata Kuliah</span>
                    <span class="font-bold text-white">{{ $conversion->course->name }}</span>
                </div>
                <div class="flex justify-between py-1 border-b border-slate-800/60">
                    <span class="text-slate-400">Kode MK</span>
                    <span class="font-mono text-cyan-400">{{ $conversion->course->code }}</span>
                </div>
                <div class="flex justify-between py-1 border-b border-slate-800/60">
                    <span class="text-slate-400">Bobot SKS / Semester</span>
                    <span class="text-white">{{ $conversion->course->credits }} SKS (Semester {{ $conversion->course->semester }})</span>
                </div>
                <div class="flex justify-between py-1 border-b border-slate-800/60">
                    <span class="text-slate-400">Instansi Magang</span>
                    <span class="text-white">{{ $conversion->internshipApplication->partnerInstitution->name }}</span>
                </div>
                <div class="flex justify-between py-1 border-b border-slate-800/60">
                    <span class="text-slate-400">Dosen Pembimbing</span>
                    <span class="text-emerald-400 font-semibold">{{ $conversion->internshipApplication->advisor->name ?? 'Belum Ditentukan' }}</span>
                </div>
                <div class="flex justify-between py-1">
                    <span class="text-slate-400">Mahasiswa Pemohon</span>
                    <span class="text-white font-medium">{{ $conversion->student->name }} ({{ $conversion->student->identity_number }})</span>
                </div>
            </div>
        </div>

        <!-- Rencana Kegiatan -->
        <div class="p-6 rounded-2xl bg-slate-900 border border-slate-800 space-y-4">
            <h3 class="text-xs font-mono uppercase tracking-wider text-slate-400 border-b border-slate-800 pb-2">Rencana / Relevansi Kegiatan</h3>
            <div class="p-4 rounded-xl bg-slate-950 border border-slate-800/80 text-xs text-slate-200 whitespace-pre-line leading-relaxed">
                {{ $conversion->activity_plan }}
            </div>
        </div>
    </div>

    <!-- Audit Timeline Trail -->
    <div class="p-6 rounded-2xl bg-slate-900 border border-slate-800 space-y-4">
        <h3 class="text-xs font-mono uppercase tracking-wider text-slate-400 border-b border-slate-800 pb-2">Riwayat Audit Perubahan Status</h3>
        <div class="space-y-4">
            @forelse ($conversion->statusHistories as $history)
                <div class="flex items-start gap-4 text-xs">
                    <div class="w-8 h-8 rounded-full bg-slate-800 border border-slate-700 flex items-center justify-center font-bold text-slate-300 text-[10px] flex-shrink-0">
                        {{ $loop->iteration }}
                    </div>
                    <div class="flex-1">
                        <div class="flex items-center gap-2">
                            <span class="font-bold text-white">{{ $history->action }}</span>
                            <span class="text-[10px] text-slate-500 font-mono">{{ $history->created_at->format('d M Y, H:i:s') }}</span>
                        </div>
                        <p class="text-slate-300 text-xs mt-0.5">{{ $history->notes }}</p>
                        <p class="text-[10px] text-slate-500 font-mono mt-0.5">Oleh: {{ $history->actor->name ?? 'Sistem' }}</p>
                    </div>
                </div>
            @empty
                <p class="text-xs text-slate-500">Belum ada riwayat audit.</p>
            @endforelse
        </div>
    </div>
</div>
@endsection
