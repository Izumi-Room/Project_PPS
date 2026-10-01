@extends('layouts.app')

@section('title', 'Kelola Seminar Magang - Dosen Pengampu MK')

@section('content')
<div class="space-y-6">
    <div class="bg-gradient-to-r from-slate-900 via-teal-950 to-slate-900 border border-slate-800 p-6 rounded-2xl shadow-xl">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-teal-500/10 text-teal-400 border border-teal-500/20 mb-2">
                    Portal Dosen Pengampu MK
                </span>
                <h1 class="text-2xl font-bold text-white tracking-tight">Pengelolaan Seminar Magang</h1>
                <p class="text-sm text-slate-400 mt-1">
                    Tentukan apakah seminar diperlukan (YES/NO) untuk mata kuliah konversi magang, jadwalkan waktu/tempat temu, atau lakukan penjadwalan ulang.
                </p>
            </div>
        </div>
    </div>

    <!-- Conversion & Seminar Queue -->
    <div class="bg-slate-900/60 backdrop-blur-md border border-slate-800 rounded-2xl overflow-hidden shadow-xl">
        <div class="p-5 border-b border-slate-800 flex items-center justify-between">
            <div>
                <h2 class="text-base font-semibold text-white">Daftar Mahasiswa & Konversi MK</h2>
                <p class="text-xs text-slate-400 mt-0.5">Pilih mahasiswa untuk menentukan kebutuhan seminar dan jadwalnya.</p>
            </div>
        </div>

        @if($conversions->isEmpty())
            <div class="p-12 text-center">
                <div class="w-16 h-16 bg-slate-800/80 rounded-2xl flex items-center justify-center mx-auto mb-4 text-slate-500 border border-slate-700/50">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                    </svg>
                </div>
                <h3 class="text-base font-medium text-white mb-1">Belum Ada Mahasiswa Aktif</h3>
                <p class="text-sm text-slate-400 max-w-sm mx-auto">
                    Belum ada pengajuan konversi MK magang yang disetujui untuk dikelola seminarnya.
                </p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-sm">
                    <thead>
                        <tr class="border-b border-slate-800 text-xs uppercase tracking-wider text-slate-400 bg-slate-800/30">
                            <th class="py-3 px-5">Mahasiswa</th>
                            <th class="py-3 px-5">Mata Kuliah</th>
                            <th class="py-3 px-5">Dosen Pembimbing</th>
                            <th class="py-3 px-5">Status Seminar</th>
                            <th class="py-3 px-5">Jadwal & Lokasi</th>
                            <th class="py-3 px-5 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-800/60 text-slate-300">
                        @foreach($conversions as $conversion)
                            @php
                                $seminar = $conversion->seminars->first();
                            @endphp
                            <tr class="hover:bg-slate-800/40 transition">
                                <td class="py-4 px-5">
                                    <div class="font-medium text-white">{{ $conversion->student?->name ?? 'Mahasiswa' }}</div>
                                    <div class="text-xs text-slate-500">NIM: {{ $conversion->student?->identifier_number ?? '-' }}</div>
                                </td>
                                <td class="py-4 px-5">
                                    <div class="font-medium text-white">{{ $conversion->course?->name ?? '-' }}</div>
                                    <div class="text-xs text-slate-500">{{ $conversion->course?->code ?? '-' }} &bull; {{ $conversion->course?->credits ?? 0 }} SKS</div>
                                </td>
                                <td class="py-4 px-5">
                                    <div class="text-slate-300">{{ $conversion->internshipApplication?->advisor?->name ?? 'Belum ditentukan' }}</div>
                                </td>
                                <td class="py-4 px-5">
                                    @if($seminar)
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium border {{ $seminar->statusBadgeColor() }}">
                                            {{ $seminar->statusLabel() }}
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-amber-500/10 text-amber-400 border border-amber-500/20">
                                            Menunggu Keputusan
                                        </span>
                                    @endif
                                </td>
                                <td class="py-4 px-5">
                                    @if($seminar && $seminar->scheduled_date)
                                        <div class="font-medium text-white">{{ $seminar->scheduled_date->format('d/m/Y') }} ({{ $seminar->scheduled_time }})</div>
                                        <div class="text-xs text-slate-400 truncate max-w-[180px]">{{ $seminar->location_or_link }}</div>
                                    @else
                                        <span class="text-slate-500 italic">-</span>
                                    @endif
                                </td>
                                <td class="py-4 px-5 text-center">
                                    <a href="{{ route('dosen-mk.seminars.show', $conversion) }}" class="inline-flex items-center px-3 py-1.5 rounded-lg text-xs font-medium bg-teal-600/20 hover:bg-teal-600/30 text-teal-300 border border-teal-500/30 transition">
                                        {{ $seminar ? 'Kelola / Ubah' : 'Tentukan Kebutuhan' }} &rarr;
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="p-4 border-t border-slate-800">
                {{ $conversions->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
