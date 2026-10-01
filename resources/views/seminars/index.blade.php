@extends('layouts.app')

@section('title', 'Seminar Magang Mahasiswa')

@section('content')
<div class="space-y-6">
    <div class="bg-gradient-to-r from-slate-900 via-indigo-950 to-slate-900 border border-slate-800 p-6 rounded-2xl shadow-xl">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-indigo-500/10 text-indigo-400 border border-indigo-500/20 mb-2">
                    Phase 6 — Seminar Magang
                </span>
                <h1 class="text-2xl font-bold text-white tracking-tight">Seminar Magang</h1>
                <p class="text-sm text-slate-400 mt-1">
                    Pantau status penetapan seminar, jadwal pelaksanaan, lokasi/tautan temu, dan progres persetujuan berjenjang.
                </p>
            </div>
        </div>
    </div>

    <!-- Seminar List Table -->
    <div class="bg-slate-900/60 backdrop-blur-md border border-slate-800 rounded-2xl overflow-hidden shadow-xl">
        <div class="p-5 border-b border-slate-800">
            <h2 class="text-base font-semibold text-white">Daftar Status Seminar Magang</h2>
            <p class="text-xs text-slate-400 mt-0.5">Penetapan seminar dikonfigurasi oleh Dosen Pengampu MK terkait.</p>
        </div>

        @if($seminars->isEmpty())
            <div class="p-12 text-center">
                <div class="w-16 h-16 bg-slate-800/80 rounded-2xl flex items-center justify-center mx-auto mb-4 text-slate-500 border border-slate-700/50">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                </div>
                <h3 class="text-base font-medium text-white mb-1">Belum Ada Seminar Terdaftar</h3>
                <p class="text-sm text-slate-400 max-w-sm mx-auto">
                    Dosen Pengampu MK belum menetapkan kebutuhan seminar untuk mata kuliah magang Anda.
                </p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-sm">
                    <thead>
                        <tr class="border-b border-slate-800 text-xs uppercase tracking-wider text-slate-400 bg-slate-800/30">
                            <th class="py-3 px-5">Mata Kuliah</th>
                            <th class="py-3 px-5">Dosen Pengampu MK</th>
                            <th class="py-3 px-5">Status Seminar</th>
                            <th class="py-3 px-5">Jadwal & Waktu</th>
                            <th class="py-3 px-5">Tempat / Link</th>
                            <th class="py-3 px-5 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-800/60 text-slate-300">
                        @foreach($seminars as $seminar)
                            <tr class="hover:bg-slate-800/40 transition">
                                <td class="py-4 px-5">
                                    <div class="font-medium text-white">{{ $seminar->course?->name ?? 'Mata Kuliah Magang' }}</div>
                                    <div class="text-xs text-slate-500">{{ $seminar->course?->code ?? '-' }} &bull; {{ $seminar->course?->credits ?? 0 }} SKS</div>
                                </td>
                                <td class="py-4 px-5">
                                    <div class="text-slate-300">{{ $seminar->dosenMk?->name ?? '-' }}</div>
                                </td>
                                <td class="py-4 px-5">
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium border {{ $seminar->statusBadgeColor() }}">
                                        {{ $seminar->statusLabel() }}
                                    </span>
                                </td>
                                <td class="py-4 px-5">
                                    @if($seminar->scheduled_date)
                                        <div class="font-medium text-white">{{ $seminar->scheduled_date->format('d M Y') }}</div>
                                        <div class="text-xs text-slate-400">{{ $seminar->scheduled_time ?? '-' }}</div>
                                    @else
                                        <span class="text-slate-500 italic">-</span>
                                    @endif
                                </td>
                                <td class="py-4 px-5">
                                    @if($seminar->location_or_link)
                                        @if(filter_var($seminar->location_or_link, FILTER_VALIDATE_URL))
                                            <a href="{{ $seminar->location_or_link }}" target="_blank" rel="noopener noreferrer" class="inline-flex items-center gap-1 text-indigo-400 hover:text-indigo-300 underline font-mono text-xs">
                                                <span>Buka Tautan</span>
                                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" /></svg>
                                            </a>
                                        @else
                                            <span class="text-slate-300 text-xs">{{ $seminar->location_or_link }}</span>
                                        @endif
                                    @else
                                        <span class="text-slate-500 italic">-</span>
                                    @endif
                                </td>
                                <td class="py-4 px-5 text-center">
                                    <a href="{{ route('seminars.show', $seminar) }}" class="inline-flex items-center px-3 py-1.5 rounded-lg text-xs font-medium bg-slate-800 hover:bg-slate-700 text-indigo-300 border border-slate-700 transition">
                                        Detail & Approval &rarr;
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="p-4 border-t border-slate-800">
                {{ $seminars->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
