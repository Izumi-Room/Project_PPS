@extends('layouts.app')

@section('title', 'Persetujuan & Mengetahui Seminar Magang - Kaprodi')

@section('content')
<div class="space-y-6">
    <div class="bg-gradient-to-r from-slate-900 via-purple-950 to-slate-900 border border-slate-800 p-6 rounded-2xl shadow-xl">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-purple-500/10 text-purple-400 border border-purple-500/20 mb-2">
                    Pimpinan Prodi
                </span>
                <h1 class="text-2xl font-bold text-white tracking-tight">Pengesahan Mengetahui Seminar Magang</h1>
                <p class="text-sm text-slate-400 mt-1">
                    Verifikasi dan konfirmasi bahwa Kaprodi mengetahui jadwal seminar magang mahasiswa program studi sebelum diteruskan ke Wakil Dekan 1.
                </p>
            </div>
        </div>
    </div>

    <!-- Queue Table -->
    <div class="bg-slate-900/60 backdrop-blur-md border border-slate-800 rounded-2xl overflow-hidden shadow-xl">
        <div class="p-5 border-b border-slate-800">
            <h2 class="text-base font-semibold text-white">Daftar Seminar Magang Mahasiswa Prodi</h2>
            <p class="text-xs text-slate-400 mt-0.5">Seminar yang telah dikonfirmasi oleh Dosen Pembimbing.</p>
        </div>

        @if($seminars->isEmpty())
            <div class="p-12 text-center">
                <div class="w-16 h-16 bg-slate-800/80 rounded-2xl flex items-center justify-center mx-auto mb-4 text-slate-500 border border-slate-700/50">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <h3 class="text-base font-medium text-white mb-1">Tidak Ada Antrean Mengetahui</h3>
                <p class="text-sm text-slate-400 max-w-sm mx-auto">
                    Seluruh jadwal seminar magang yang telah dikonfirmasi Dosbing telah Anda proses atau belum ada jadwal baru.
                </p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-sm">
                    <thead>
                        <tr class="border-b border-slate-800 text-xs uppercase tracking-wider text-slate-400 bg-slate-800/30">
                            <th class="py-3 px-5">Mahasiswa</th>
                            <th class="py-3 px-5">Mata Kuliah</th>
                            <th class="py-3 px-5">Dospem & Dosen MK</th>
                            <th class="py-3 px-5">Jadwal & Tempat</th>
                            <th class="py-3 px-5">Status Kaprodi</th>
                            <th class="py-3 px-5 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-800/60 text-slate-300">
                        @foreach($seminars as $seminar)
                            <tr class="hover:bg-slate-800/40 transition">
                                <td class="py-4 px-5">
                                    <div class="font-medium text-white">{{ $seminar->student?->name ?? 'Mahasiswa' }}</div>
                                    <div class="text-xs text-slate-500">NIM: {{ $seminar->student?->identifier_number ?? '-' }}</div>
                                </td>
                                <td class="py-4 px-5">
                                    <div class="font-medium text-white">{{ $seminar->course?->name ?? '-' }}</div>
                                    <div class="text-xs text-slate-500">{{ $seminar->course?->code ?? '-' }}</div>
                                </td>
                                <td class="py-4 px-5">
                                    <div class="text-xs text-slate-300">Dospem: <span class="text-emerald-400 font-medium">{{ $seminar->dosbing?->name ?? $seminar->internshipApplication?->advisor?->name ?? '-' }}</span></div>
                                    <div class="text-xs text-slate-400">Dosen MK: {{ $seminar->dosenMk?->name ?? '-' }}</div>
                                </td>
                                <td class="py-4 px-5">
                                    @if($seminar->scheduled_date)
                                        <div class="font-medium text-white">{{ $seminar->scheduled_date->format('d/m/Y') }} ({{ $seminar->scheduled_time }})</div>
                                        <div class="text-xs text-slate-400 truncate max-w-[180px]">{{ $seminar->location_or_link }}</div>
                                    @else
                                        <span class="text-slate-500 italic">-</span>
                                    @endif
                                </td>
                                <td class="py-4 px-5">
                                    @if($seminar->kaprodi_acknowledged_at)
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">
                                            Diketahui
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-purple-500/10 text-purple-400 border border-purple-500/20">
                                            Menunggu Konfirmasi
                                        </span>
                                    @endif
                                </td>
                                <td class="py-4 px-5 text-center">
                                    <a href="{{ route('kaprodi.seminars.show', $seminar) }}" class="inline-flex items-center px-3 py-1.5 rounded-lg text-xs font-medium bg-slate-800 hover:bg-slate-700 text-purple-300 border border-slate-700 transition">
                                        Detail & Pengesahan &rarr;
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
