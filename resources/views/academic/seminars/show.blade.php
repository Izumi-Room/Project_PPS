@extends('layouts.app')

@section('title', 'Detail Konfirmasi Seminar - DOSBING')

@section('content')
<div class="space-y-6 max-w-4xl mx-auto">
    <!-- Breadcrumb & Back -->
    <div class="flex items-center justify-between">
        <a href="{{ route('academic.seminars.index') }}" class="inline-flex items-center gap-2 text-sm text-slate-400 hover:text-white transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" /></svg>
            Kembali ke Daftar Seminar Bimbingan
        </a>
        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold border {{ $seminar->statusBadgeColor() }}">
            {{ $seminar->statusLabel() }}
        </span>
    </div>

    <!-- Seminar Details Card -->
    <div class="bg-slate-900/60 backdrop-blur-md border border-slate-800 rounded-2xl p-6 shadow-xl space-y-6">
        <div>
            <span class="text-xs font-bold text-emerald-400 uppercase tracking-wider">Jadwal Seminar Mahasiswa Bimbingan</span>
            <h1 class="text-2xl font-bold text-white mt-1">{{ $seminar->course?->name ?? 'Mata Kuliah Magang' }}</h1>
            <p class="text-sm text-slate-400 mt-1">
                Mahasiswa: <strong class="text-slate-200">{{ $seminar->student?->name }}</strong> (NIM: {{ $seminar->student?->identifier_number }})
            </p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="bg-slate-800/40 border border-slate-700/60 rounded-xl p-4">
                <div class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-1">Tanggal & Waktu</div>
                <div class="text-lg font-bold text-white">{{ $seminar->scheduled_date ? $seminar->scheduled_date->format('l, d F Y') : '-' }}</div>
                <div class="text-sm text-emerald-400 mt-0.5">Pukul: {{ $seminar->scheduled_time ?? '-' }}</div>
            </div>
            <div class="bg-slate-800/40 border border-slate-700/60 rounded-xl p-4">
                <div class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-1">Tempat / Tautan</div>
                <div class="text-base font-semibold text-white break-all">{{ $seminar->location_or_link ?? '-' }}</div>
            </div>
        </div>

        @if($seminar->information)
            <div class="bg-slate-800/30 border border-slate-700/50 rounded-xl p-4">
                <div class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-1">Informasi dari Dosen Pengampu MK</div>
                <p class="text-sm text-slate-300 whitespace-pre-line">{{ $seminar->information }}</p>
            </div>
        @endif

        @if($seminar->dosbing_acknowledged_at)
            <div class="bg-emerald-950/40 border border-emerald-800/60 rounded-xl p-4 text-xs text-emerald-300 flex items-center gap-3">
                <svg class="w-5 h-5 text-emerald-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                <div>
                    <div>Anda telah mengonfirmasi mengetahui jadwal ini pada <strong>{{ $seminar->dosbing_acknowledged_at->format('d F Y, H:i') }}</strong>.</div>
                    @if($seminar->dosbing_notes)
                        <div class="mt-1 text-slate-300">Catatan: {{ $seminar->dosbing_notes }}</div>
                    @endif
                </div>
            </div>
        @else
            <!-- Form Acknowledge -->
            <form action="{{ route('academic.seminars.acknowledge', $seminar) }}" method="POST" class="pt-4 border-t border-slate-800 space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-2">Catatan Dosen Pembimbing (Opsional)</label>
                    <textarea name="notes" rows="2" placeholder="Catatan kesiapan atau pesan untuk mahasiswa/Kaprodi..."
                        class="w-full bg-slate-800/80 border border-slate-700 rounded-xl p-3 text-sm text-white focus:outline-none focus:border-emerald-500"></textarea>
                </div>
                <div class="flex items-center justify-end">
                    <button type="submit" class="px-5 py-2.5 rounded-xl font-medium bg-emerald-600 hover:bg-emerald-500 text-white shadow-lg shadow-emerald-600/30 transition text-sm flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                        <span>Konfirmasi Mengetahui Jadwal</span>
                    </button>
                </div>
            </form>
        @endif
    </div>
</div>
@endsection
