@extends('layouts.app', ['title' => 'Penentuan Dosen Pembimbing - Kaprodi'])

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-xl sm:text-2xl font-black text-white tracking-tight flex items-center gap-3">
                <span class="p-2 rounded-xl bg-purple-500/10 text-purple-400 border border-purple-500/20">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                </span>
                <span>Penentuan Dosen Pembimbing Magang (Kaprodi)</span>
            </h1>
            <p class="text-xs sm:text-sm text-slate-400 mt-1">
                Tugaskan dosen pembimbing akademik (DOSBING) bagi mahasiswa yang pendaftarannya telah <strong>Disetujui Resmi oleh Wakil Dekan 1</strong>.
            </p>
        </div>

        <div class="flex items-center gap-2">
            <span class="px-3.5 py-1.5 rounded-xl bg-purple-500/10 text-purple-300 border border-purple-500/20 text-xs font-bold font-mono">
                {{ $readyCount }} Siap Ditentukan Dospem
            </span>
        </div>
    </div>

    <!-- Prerequisite Banner -->
    <div class="p-3.5 rounded-2xl bg-slate-900/80 border border-slate-800 text-xs text-slate-300 flex items-center gap-3">
        <span class="p-1.5 rounded-lg bg-emerald-500/20 text-emerald-400 flex-shrink-0">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
        </span>
        <span>
            <strong>Prasyarat Otorisasi:</strong> Antrean di bawah ini secara otomatis hanya memuat mahasiswa yang pendaftarannya telah berstatus <strong>DISETUJUI (Approved oleh Wadek 1)</strong>.
        </span>
    </div>

    <!-- Tabs Navigation -->
    <div class="flex items-center gap-2 border-b border-slate-800 pb-2">
        <a href="{{ route('kaprodi.advisors.index', ['tab' => 'ready']) }}"
            class="px-4 py-2 rounded-xl text-xs font-bold transition flex items-center gap-2 {{ $activeTab === 'ready' ? 'bg-purple-600 text-white shadow-lg shadow-purple-600/20' : 'text-slate-400 hover:text-white hover:bg-slate-800/60' }}">
            <span>Mahasiswa yang Siap Ditentukan Dosen Pembimbing</span>
            <span class="px-1.5 py-0.5 rounded-full text-[10px] {{ $activeTab === 'ready' ? 'bg-black/30 text-white' : 'bg-purple-500/20 text-purple-300' }}">
                {{ $readyCount }}
            </span>
        </a>
        <a href="{{ route('kaprodi.advisors.index', ['tab' => 'assigned']) }}"
            class="px-4 py-2 rounded-xl text-xs font-bold transition {{ $activeTab === 'assigned' ? 'bg-slate-800 text-white' : 'text-slate-400 hover:text-white hover:bg-slate-800/60' }}">
            <span>Dosen Pembimbing yang Sudah Ditentukan</span>
        </a>
    </div>

    <!-- Filter & Search Toolbar -->
    <div class="bg-slate-900/60 border border-slate-800 rounded-2xl p-4">
        <form method="GET" action="{{ route('kaprodi.advisors.index') }}" class="grid grid-cols-1 sm:grid-cols-3 gap-3">
            <input type="hidden" name="tab" value="{{ $activeTab }}">

            <div>
                <label class="block text-xs font-medium text-slate-400 mb-1">Cari Mahasiswa / Instansi</label>
                <input type="text" name="search" value="{{ request('search') }}"
                    placeholder="Nama, NIM, atau instansi..."
                    class="w-full bg-slate-950 border border-slate-800 rounded-xl px-3 py-2 text-xs text-white focus:outline-none focus:border-purple-500">
            </div>

            <div>
                <label class="block text-xs font-medium text-slate-400 mb-1">Program Studi</label>
                <select name="study_program_id" class="w-full bg-slate-950 border border-slate-800 rounded-xl px-3 py-2 text-xs text-white focus:outline-none focus:border-purple-500">
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
                <a href="{{ route('kaprodi.advisors.index', ['tab' => $activeTab]) }}" class="px-3 py-2 bg-slate-950 border border-slate-800 hover:bg-slate-900 text-slate-400 hover:text-white rounded-xl text-xs transition">
                    Reset
                </a>
            </div>
        </form>
    </div>

    <!-- Applications Table -->
    <div class="bg-slate-900/60 border border-slate-800 rounded-2xl overflow-hidden shadow-xl">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-950/70 border-b border-slate-800 text-slate-400 uppercase font-mono text-[10px] tracking-wider">
                    <tr>
                        <th class="px-6 py-4">Mahasiswa / NIM</th>
                        <th class="px-6 py-4">Program Studi</th>
                        <th class="px-6 py-4">Instansi Mitra</th>
                        <th class="px-6 py-4">Periode Magang</th>
                        <th class="px-6 py-4">Dosen Pembimbing</th>
                        <th class="px-6 py-4 text-right">Aksi Penugasan</th>
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
                                @if ($app->advisor)
                                    <div class="font-semibold text-white">{{ $app->advisor->name }}</div>
                                    <div class="text-slate-400 font-mono text-[10px]">NIDN: {{ $app->advisor->identifier_number ?? '-' }}</div>
                                @endif
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold border mt-1 {{ $app->advisor_status_badge_classes }}">
                                    {{ $app->advisor_status_label }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <a href="{{ route('kaprodi.advisors.show', $app) }}"
                                    class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-xl bg-purple-600 hover:bg-purple-500 text-white font-semibold text-xs transition shadow-lg shadow-purple-600/20">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
                                    </svg>
                                    <span>{{ $app->isReadyForAdvisorAssignment() ? 'Tentukan Dosen' : 'Detail Penugasan' }}</span>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center text-slate-500">
                                <svg class="w-12 h-12 mx-auto text-slate-600 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                                </svg>
                                <p class="text-sm font-semibold text-slate-400">
                                    {{ $activeTab === 'ready' ? 'Tidak ada mahasiswa yang menunggu penentuan dosen pembimbing.' : 'Belum ada data penugasan dosen pembimbing.' }}
                                </p>
                                <p class="text-xs text-slate-500 mt-1">
                                    Mahasiswa akan otomatis muncul di antrean ini setelah permohonan magang disetujui penuh oleh Dekanat.
                                </p>
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
