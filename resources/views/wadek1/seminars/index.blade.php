@extends('layouts.app')

@section('title', 'Approval Seminar Magang - Wakil Dekan 1')

@section('content')
<div class="space-y-6">
    <div class="bg-gradient-to-r from-slate-900 via-indigo-950 to-slate-900 border border-slate-800 p-6 rounded-2xl shadow-xl">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-indigo-500/10 text-indigo-400 border border-indigo-500/20 mb-2">
                    Dekanat (Wakil Dekan 1)
                </span>
                <h1 class="text-2xl font-bold text-white tracking-tight">Approval Seminar Magang</h1>
                <p class="text-sm text-slate-400 mt-1">
                    Seminar magang tidak boleh dilaksanakan sebelum mendapatkan persetujuan resmi dari Wakil Dekan 1.
                </p>
            </div>
        </div>
    </div>

    <!-- Approval Queue Table -->
    <div class="bg-slate-900/60 backdrop-blur-md border border-slate-800 rounded-2xl overflow-hidden shadow-xl">
        <div class="p-5 border-b border-slate-800 flex items-center justify-between">
            <div>
                <h2 class="text-base font-semibold text-white">Antrean Seminar Menunggu Persetujuan Wadek 1</h2>
                <p class="text-xs text-slate-400 mt-0.5">Telah diketahui oleh Dosen Pembimbing dan Kaprodi.</p>
            </div>
            <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-indigo-500/20 text-indigo-300 border border-indigo-500/30">
                {{ $seminars->total() }} Menunggu
            </span>
        </div>

        @if($seminars->isEmpty())
            <div class="p-12 text-center">
                <div class="w-16 h-16 bg-slate-800/80 rounded-2xl flex items-center justify-center mx-auto mb-4 text-slate-500 border border-slate-700/50">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <h3 class="text-base font-medium text-white mb-1">Tidak Ada Antrean Approval</h3>
                <p class="text-sm text-slate-400 max-w-sm mx-auto">
                    Seluruh pengajuan jadwal seminar telah diproses atau belum ada jadwal baru yang diajukan oleh Kaprodi.
                </p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-sm">
                    <thead>
                        <tr class="border-b border-slate-800 text-xs uppercase tracking-wider text-slate-400 bg-slate-800/30">
                            <th class="py-3 px-5">Mahasiswa</th>
                            <th class="py-3 px-5">Mata Kuliah</th>
                            <th class="py-3 px-5">Jadwal & Lokasi</th>
                            <th class="py-3 px-5">Tahap Pengesahan</th>
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
                                    <div class="text-xs text-slate-500">Dosen MK: {{ $seminar->dosenMk?->name ?? '-' }}</div>
                                </td>
                                <td class="py-4 px-5">
                                    <div class="font-medium text-white">{{ $seminar->scheduled_date?->format('d/m/Y') }} ({{ $seminar->scheduled_time }})</div>
                                    <div class="text-xs text-slate-400 truncate max-w-[200px]">{{ $seminar->location_or_link }}</div>
                                </td>
                                <td class="py-4 px-5">
                                    <div class="flex items-center gap-1.5 text-xs text-emerald-400">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                                        <span>Dosbing & Kaprodi Mengetahui</span>
                                    </div>
                                </td>
                                <td class="py-4 px-5 text-center">
                                    <a href="{{ route('wadek1.seminars.show', $seminar) }}" class="inline-flex items-center px-3 py-1.5 rounded-lg text-xs font-medium bg-indigo-600 hover:bg-indigo-500 text-white shadow-md shadow-indigo-600/30 transition">
                                        Review & Putuskan &rarr;
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

    <!-- History Table -->
    @if(!$history->isEmpty())
        <div class="bg-slate-900/60 backdrop-blur-md border border-slate-800 rounded-2xl overflow-hidden shadow-xl">
            <div class="p-5 border-b border-slate-800">
                <h3 class="text-sm font-semibold text-white">Riwayat Keputusan Wadek 1 Terkini</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-sm">
                    <thead>
                        <tr class="border-b border-slate-800 text-xs uppercase tracking-wider text-slate-400 bg-slate-800/30">
                            <th class="py-3 px-5">Mahasiswa</th>
                            <th class="py-3 px-5">Mata Kuliah</th>
                            <th class="py-3 px-5">Jadwal</th>
                            <th class="py-3 px-5">Keputusan</th>
                            <th class="py-3 px-5">Tanggal Keputusan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-800/60 text-slate-300">
                        @foreach($history as $item)
                            <tr class="hover:bg-slate-800/30 transition">
                                <td class="py-3 px-5 font-medium text-white">{{ $item->student?->name }}</td>
                                <td class="py-3 px-5 text-slate-300">{{ $item->course?->name }}</td>
                                <td class="py-3 px-5 text-slate-400">{{ $item->scheduled_date?->format('d/m/Y') }}</td>
                                <td class="py-3 px-5">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium border {{ $item->statusBadgeColor() }}">
                                        {{ $item->statusLabel() }}
                                    </span>
                                </td>
                                <td class="py-3 px-5 text-xs text-slate-400">
                                    {{ ($item->wadek1_approved_at ?? $item->wadek1_rejected_at)?->format('d/m/Y H:i') ?? '-' }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif
</div>
@endsection
