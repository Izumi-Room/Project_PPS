@extends('layouts.app', ['title' => 'Portal Akademik Dosen'])

@section('content')
<div class="space-y-8 max-w-7xl mx-auto">
    <!-- Header Card -->
    <div class="rounded-3xl bg-gradient-to-r from-emerald-950/70 via-slate-900 to-slate-900 border border-emerald-900/40 p-6 sm:p-8 shadow-xl">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-emerald-500/20 text-emerald-300 border border-emerald-500/30 mb-3">
                    <span class="w-2 h-2 rounded-full bg-emerald-400"></span>
                    Protected Route • DOSBING & DOSEN_MK
                </span>
                <h2 class="text-2xl sm:text-3xl font-extrabold text-white tracking-tight">
                    Portal Akademik Dosen Pembimbing & Dosen MK
                </h2>
                <p class="mt-1 text-sm text-slate-300">
                    Akses terverifikasi untuk dosen pembimbing magang dan dosen pengampu mata kuliah.
                </p>
            </div>
            <div class="flex items-center gap-2">
                @if (Auth::user()->hasRole('DOSBING'))
                    <span class="px-3 py-1 rounded-xl bg-emerald-500/20 text-emerald-300 border border-emerald-500/30 text-xs font-mono font-bold">
                        DOSBING Aktif
                    </span>
                @endif
                @if (Auth::user()->hasRole('DOSEN_MK'))
                    <span class="px-3 py-1 rounded-xl bg-teal-500/20 text-teal-300 border border-teal-500/30 text-xs font-mono font-bold">
                        DOSEN_MK Aktif
                    </span>
                @endif
            </div>
        </div>
    </div>

    <!-- Multi-Role Verification Content -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- Dosbing Capabilities -->
        <div class="bg-slate-900/70 border border-slate-800 rounded-3xl p-6">
            <div class="flex items-center gap-3 mb-4">
                <div class="w-10 h-10 rounded-xl bg-emerald-500/10 text-emerald-400 flex items-center justify-center font-bold">
                    👨‍🏫
                </div>
                <div>
                    <h3 class="font-bold text-white text-base">Modul Dosen Pembimbing (DOSBING)</h3>
                    <p class="text-xs text-slate-400">Bimbingan logbook dan evaluasi lapangan mahasiswa.</p>
                </div>
            </div>
            <p class="text-xs text-slate-300 leading-relaxed mb-4">
                Status Otorisasi Akun Anda: 
                @if (Auth::user()->hasRole('DOSBING') || Auth::user()->hasRole('SUPERADMIN'))
                    <span class="text-emerald-400 font-semibold">&#10003; Terverifikasi Memiliki Hak Akses</span>
                @else
                    <span class="text-rose-400 font-semibold">&#10007; Tidak Memiliki Role DOSBING</span>
                @endif
            </p>
            <div class="p-3 bg-slate-950/60 rounded-xl border border-slate-800/80 text-xs text-slate-400">
                Fitur pendaftaran magang dan bimbingan akan diaktifkan pada phase berikutnya sesuai timeline.
            </div>
        </div>

        <!-- Dosen MK Capabilities -->
        <div class="bg-slate-900/70 border border-slate-800 rounded-3xl p-6">
            <div class="flex items-center gap-3 mb-4">
                <div class="w-10 h-10 rounded-xl bg-teal-500/10 text-teal-400 flex items-center justify-center font-bold">
                    📚
                </div>
                <div>
                    <h3 class="font-bold text-white text-base">Modul Dosen Mata Kuliah (DOSEN_MK)</h3>
                    <p class="text-xs text-slate-400">Konversi SKS dan penilaian akhir mata kuliah magang.</p>
                </div>
            </div>
            <p class="text-xs text-slate-300 leading-relaxed mb-4">
                Status Otorisasi Akun Anda: 
                @if (Auth::user()->hasRole('DOSEN_MK') || Auth::user()->hasRole('SUPERADMIN'))
                    <span class="text-teal-400 font-semibold">&#10003; Terverifikasi Memiliki Hak Akses</span>
                @else
                    <span class="text-rose-400 font-semibold">&#10007; Tidak Memiliki Role DOSEN_MK</span>
                @endif
            </p>
            <div class="p-3 bg-slate-950/60 rounded-xl border border-slate-800/80 text-xs text-slate-400">
                Fitur penilaian nilai akhir mata kuliah akan diaktifkan pada phase berikutnya sesuai timeline.
            </div>
        </div>
    </div>
</div>
@endsection
