@extends('layouts.app', ['title' => 'Persetujuan Dekanat (Wadek 1) - Pendaftaran Magang'])

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-xl sm:text-2xl font-black text-white tracking-tight flex items-center gap-3">
                <span class="p-2 rounded-xl bg-indigo-500/10 text-indigo-400 border border-indigo-500/20">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                    </svg>
                </span>
                <span>Persetujuan Akhir Dekanat (Wakil Dekan 1)</span>
            </h1>
            <p class="text-xs sm:text-sm text-slate-400 mt-1">
                Otorisasi resmi tingkat pimpinan fakultas untuk pelaksanaan magang mahasiswa di instansi mitra.
            </p>
        </div>

        <div class="flex items-center gap-2">
            <span class="px-3.5 py-1.5 rounded-xl bg-indigo-500/10 text-indigo-300 border border-indigo-500/20 text-xs font-bold font-mono">
                {{ $pendingCount }} Menunggu Persetujuan
            </span>
        </div>
    </div>

    <!-- Tabs Navigation -->
    <div class="flex items-center gap-2 border-b border-slate-800 pb-2">
        <a href="{{ route('wadek1.internships.index', ['tab' => 'queue']) }}"
            class="px-4 py-2 rounded-xl text-xs font-bold transition flex items-center gap-2 {{ $activeTab === 'queue' ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-600/20' : 'text-slate-400 hover:text-white hover:bg-slate-800/60' }}">
            <span>Antrean Approval Wadek 1</span>
            <span class="px-1.5 py-0.5 rounded-full text-[10px] {{ $activeTab === 'queue' ? 'bg-black/30 text-white' : 'bg-indigo-500/20 text-indigo-300' }}">
                {{ $pendingCount }}
            </span>
        </a>
        <a href="{{ route('wadek1.internships.index', ['tab' => 'all']) }}"
            class="px-4 py-2 rounded-xl text-xs font-bold transition {{ $activeTab === 'all' ? 'bg-slate-800 text-white' : 'text-slate-400 hover:text-white hover:bg-slate-800/60' }}">
            <span>Seluruh Riwayat Magang Fakultas</span>
        </a>
    </div>

    <!-- Filter Toolbar -->
    <div class="bg-slate-900/60 border border-slate-800 rounded-2xl p-4">
        <form method="GET" action="{{ route('wadek1.internships.index') }}" class="grid grid-cols-1 sm:grid-cols-3 gap-3">
            <input type="hidden" name="tab" value="{{ $activeTab }}">

            <div>
                <label class="block text-xs font-medium text-slate-400 mb-1">Cari Mahasiswa / Instansi</label>
                <input type="text" name="search" value="{{ request('search') }}"
                    placeholder="Nama, NIM, atau instansi..."
                    class="w-full bg-slate-950 border border-slate-800 rounded-xl px-3 py-2 text-xs text-white focus:outline-none focus:border-indigo-500">
            </div>

            <div>
                <label class="block text-xs font-medium text-slate-400 mb-1">Program Studi</label>
                <select name="study_program_id" class="w-full bg-slate-950 border border-slate-800 rounded-xl px-3 py-2 text-xs text-white focus:outline-none focus:border-indigo-500">
                    <option value="">Semua Program Studi</option>
                    @foreach ($studyPrograms as $p)
                        <option value="{{ $p->id }}" {{ request('study_program_id') == $p->id ? 'selected' : '' }}>
                            {{ $p->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="flex items-end gap-2">
                <button type="submit" class="flex-1 px-4 py-2 bg-slate-800 hover:bg-slate-700 text-white rounded-xl text-xs font-semibold transition">
                    Terapkan
                </button>
                <a href="{{ route('wadek1.internships.index', ['tab' => $activeTab]) }}" class="px-3 py-2 bg-slate-950 border border-slate-800 hover:bg-slate-900 text-slate-400 hover:text-white rounded-xl text-xs transition">
                    Reset
                </a>
            </div>
        </form>
    </div>

    <!-- Queue Table -->
    <div class="bg-slate-900/60 border border-slate-800 rounded-2xl overflow-hidden shadow-xl">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-950/70 border-b border-slate-800 text-slate-400 uppercase font-mono text-[10px] tracking-wider">
                    <tr>
                        <th class="px-6 py-4">Mahasiswa / NIM</th>
                        <th class="px-6 py-4">Program Studi</th>
                        <th class="px-6 py-4">Instansi Mitra</th>
                        <th class="px-6 py-4">Periode</th>
                        <th class="px-6 py-4">Status</th>
                        <th class="px-6 py-4 text-right">Otorisasi Wadek 1</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/60">
                    @forelse ($applications as $app)
                        <tr class="hover:bg-slate-800/30 transition">
                            <td class="px-6 py-4">
                                <div class="font-bold text-white text-sm">{{ $app->student_name }}</div>
                                <div class="text-slate-400 font-mono text-[11px]">NIM: {{ $app->student_nim }}</div>
                            </td>
                            <td class="px-6 py-4">
                                <span class="px-2 py-0.5 rounded-lg bg-blue-500/10 text-blue-400 border border-blue-500/20 font-mono text-[11px]">
                                    {{ $app->studyProgram->name ?? '-' }}
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <div class="font-semibold text-white">{{ $app->partnerInstitution->name }}</div>
                                <div class="text-slate-400 text-[11px] truncate max-w-xs">{{ $app->partnerInstitution->address }}</div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-slate-300 font-medium">{{ $app->internshipPeriod->name }}</div>
                                <div class="text-slate-400 font-mono text-[10px] mt-0.5">
                                    {{ $app->start_date->format('d/m/Y') }} - {{ $app->end_date->format('d/m/Y') }}
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[11px] font-semibold border {{ $app->status_badge_classes }}">
                                    {{ $app->status_label }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <a href="{{ route('wadek1.internships.show', $app) }}"
                                    class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white font-semibold text-xs transition shadow-lg shadow-indigo-600/20">
                                    <span>Keputusan Approval</span>
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                    </svg>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center text-slate-500">
                                <svg class="w-12 h-12 mx-auto text-slate-600 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <p class="text-sm font-semibold text-slate-400">Tidak ada pengajuan magang yang memerlukan approval Wadek 1.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($applications->hasPages())
            <div class="p-4 border-t border-slate-800">
                {{ $applications->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
