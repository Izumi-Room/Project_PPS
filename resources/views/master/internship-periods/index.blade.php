@extends('layouts.app', ['title' => 'Master Data - Periode Magang'])

@section('content')
<div class="max-w-7xl mx-auto space-y-6">

    @include('master.partials.nav')

    <!-- Page Header & Action -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h2 class="text-xl font-bold text-white tracking-tight flex items-center gap-2">
                <span>Periode Magang Mahasiswa</span>
                <span class="px-2 py-0.5 rounded-full text-xs font-mono bg-blue-500/10 text-blue-400 border border-blue-500/20">
                    {{ $periods->total() }} Total
                </span>
            </h2>
            <p class="text-xs text-slate-400 mt-1">
                Atur kalender semester pelaksanaan magang. <strong class="text-emerald-400">Hanya periode yang aktif yang dapat dipilih untuk proses pendaftaran baru.</strong>
            </p>
        </div>

        <div class="flex items-center gap-3">
            <a href="{{ route('master.internship-periods.create') }}"
                class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-500 hover:to-indigo-500 text-white text-xs font-semibold shadow-lg shadow-blue-500/20 transition cursor-pointer">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                <span>Buka Periode Baru</span>
            </a>
        </div>
    </div>

    <!-- Active Registration Banner Alert -->
    @php
        $activePeriod = $periods->firstWhere('is_active', true);
    @endphp
    @if($activePeriod)
        <div class="p-4 rounded-2xl bg-gradient-to-r from-emerald-500/10 via-teal-500/10 to-transparent border border-emerald-500/30 flex items-center justify-between shadow-lg shadow-emerald-500/5">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-emerald-500/20 flex items-center justify-center text-emerald-400 flex-shrink-0">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <div>
                    <div class="flex items-center gap-2">
                        <span class="text-xs font-bold text-white uppercase tracking-wider font-mono">Periode Pendaftaran Aktif Saat Ini:</span>
                        <span class="px-2 py-0.2 rounded text-[10px] font-mono font-bold bg-emerald-500/20 text-emerald-300">OPEN REGISTRATION</span>
                    </div>
                    <p class="text-sm font-extrabold text-emerald-300 mt-0.5">
                        {{ $activePeriod->name }} (TA {{ $activePeriod->academic_year }} • Semester {{ $activePeriod->semester_type }})
                    </p>
                    <p class="text-xs text-slate-400 mt-0.5 font-mono">
                        Rentang Waktu: {{ $activePeriod->start_date->translatedFormat('d M Y') }} s/d {{ $activePeriod->end_date->translatedFormat('d M Y') }}
                    </p>
                </div>
            </div>
            <a href="{{ route('master.internship-periods.show', $activePeriod) }}" class="hidden sm:inline-flex px-3 py-1.5 rounded-xl bg-emerald-600/30 hover:bg-emerald-600/40 border border-emerald-500/40 text-emerald-200 text-xs font-semibold transition">
                Lihat Detail
            </a>
        </div>
    @else
        <div class="p-4 rounded-2xl bg-amber-500/10 border border-amber-500/30 flex items-center gap-3 text-amber-300 text-xs">
            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
            </svg>
            <div>
                <strong>Perhatian:</strong> Saat ini belum ada periode magang yang berstatus aktif. Mahasiswa tidak dapat melakukan pendaftaran baru hingga salah satu periode diaktifkan.
            </div>
        </div>
    @endif

    <!-- Search & Filter Bar -->
    <div class="p-4 rounded-2xl bg-slate-900/60 border border-slate-800">
        <form method="GET" action="{{ route('master.internship-periods.index') }}" class="grid grid-cols-1 sm:grid-cols-12 gap-3">
            <div class="sm:col-span-5 relative">
                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-500">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </div>
                <input type="text" name="search" value="{{ request('search') }}"
                    placeholder="Cari nama periode atau tahun akademik..."
                    class="w-full pl-10 pr-4 py-2 bg-slate-950 border border-slate-800 rounded-xl text-xs text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-blue-500 transition">
            </div>

            <div class="sm:col-span-3">
                <select name="academic_year" class="w-full px-3 py-2 bg-slate-950 border border-slate-800 rounded-xl text-xs text-slate-300 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">Semua Tahun Akademik</option>
                    @foreach($academicYears as $year)
                        <option value="{{ $year }}" {{ request('academic_year') === $year ? 'selected' : '' }}>Tahun {{ $year }}</option>
                    @endforeach
                </select>
            </div>

            <div class="sm:col-span-2">
                <select name="status" class="w-full px-3 py-2 bg-slate-950 border border-slate-800 rounded-xl text-xs text-slate-300 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">Semua Status</option>
                    <option value="1" {{ request('status') === '1' ? 'selected' : '' }}>Aktif (Dibuka)</option>
                    <option value="0" {{ request('status') === '0' ? 'selected' : '' }}>Non-Aktif (Ditutup)</option>
                </select>
            </div>

            <div class="sm:col-span-2 flex gap-1">
                <button type="submit" class="w-full px-3 py-2 rounded-xl bg-blue-600 hover:bg-blue-500 text-white text-xs font-semibold transition cursor-pointer flex items-center justify-center">
                    Filter
                </button>
                @if(request()->anyFilled(['search', 'academic_year', 'status']))
                    <a href="{{ route('master.internship-periods.index') }}" class="px-2.5 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-400 hover:text-white text-xs transition flex items-center justify-center" title="Reset">
                        &times;
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Data Table Card -->
    <div class="rounded-2xl bg-slate-900/60 border border-slate-800 overflow-hidden shadow-xl">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-950/80 text-slate-400 uppercase font-mono text-[10px] tracking-wider border-b border-slate-800">
                    <tr>
                        <th class="py-3.5 px-4 font-semibold">Nama Periode</th>
                        <th class="py-3.5 px-4 font-semibold">Tahun Akademik</th>
                        <th class="py-3.5 px-4 font-semibold">Semester</th>
                        <th class="py-3.5 px-4 font-semibold">Tanggal Mulai - Selesai</th>
                        <th class="py-3.5 px-4 font-semibold text-center">Status Pendaftaran</th>
                        <th class="py-3.5 px-4 font-semibold text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/60 text-slate-300">
                    @forelse ($periods as $p)
                        <tr class="hover:bg-slate-800/30 transition {{ $p->is_active ? 'bg-emerald-500/5' : '' }}">
                            <td class="py-3.5 px-4 font-semibold text-white">
                                <a href="{{ route('master.internship-periods.show', $p) }}" class="hover:text-blue-400 transition">
                                    {{ $p->name }}
                                </a>
                                @if($p->description)
                                    <p class="text-[11px] text-slate-400 font-normal line-clamp-1 mt-0.5">{{ $p->description }}</p>
                                @endif
                            </td>
                            <td class="py-3.5 px-4 font-mono font-bold text-blue-400">
                                {{ $p->academic_year }}
                            </td>
                            <td class="py-3.5 px-4 font-mono">
                                <span class="px-2 py-0.5 rounded text-[10px] font-semibold bg-slate-800 text-slate-300 border border-slate-700">
                                    {{ $p->semester_type }}
                                </span>
                            </td>
                            <td class="py-3.5 px-4 font-mono text-[11px]">
                                {{ $p->start_date->translatedFormat('d M Y') }} s/d {{ $p->end_date->translatedFormat('d M Y') }}
                            </td>
                            <td class="py-3.5 px-4 text-center">
                                <form action="{{ route('master.internship-periods.toggle', $p) }}" method="POST" class="inline"
                                    onsubmit="return confirm('{{ $p->is_active ? 'Nonaktifkan periode pendaftaran ini?' : 'Jadikan periode ini sebagai SATU-SATUNYA periode pendaftaran aktif baru?' }}')">
                                    @csrf
                                    <button type="submit" class="px-3 py-1 rounded-full text-[10px] font-semibold transition cursor-pointer {{ $p->is_active ? 'bg-emerald-500/20 text-emerald-300 border border-emerald-500/30 hover:bg-emerald-500/30 ring-2 ring-emerald-500/20' : 'bg-slate-800 text-slate-400 border border-slate-700 hover:text-white hover:bg-slate-700' }}" title="Klik untuk toggle status aktif pendaftaran">
                                        {{ $p->is_active ? '● AKTIF (Buka Pendaftaran)' : '○ Non-Aktif' }}
                                    </button>
                                </form>
                            </td>
                            <td class="py-3.5 px-4 text-right">
                                <div class="flex items-center justify-end gap-1.5">
                                    <a href="{{ route('master.internship-periods.show', $p) }}"
                                        class="p-1.5 rounded-lg text-slate-400 hover:text-white hover:bg-slate-800 transition" title="Lihat Detail">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                        </svg>
                                    </a>
                                    <a href="{{ route('master.internship-periods.edit', $p) }}"
                                        class="p-1.5 rounded-lg text-slate-400 hover:text-amber-400 hover:bg-slate-800 transition" title="Edit Periode">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                        </svg>
                                    </a>
                                    <form action="{{ route('master.internship-periods.destroy', $p) }}" method="POST"
                                        onsubmit="return confirm('Apakah Anda yakin ingin menghapus periode magang ini?')" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="p-1.5 rounded-lg text-slate-400 hover:text-rose-400 hover:bg-slate-800 transition cursor-pointer" title="Hapus Periode">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                            </svg>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-12 px-4 text-center">
                                <div class="max-w-sm mx-auto space-y-3">
                                    <div class="w-12 h-12 mx-auto rounded-2xl bg-slate-800/80 flex items-center justify-center text-slate-500">
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                        </svg>
                                    </div>
                                    <p class="text-sm font-semibold text-white">Tidak ada data periode magang</p>
                                    <p class="text-xs text-slate-400">Tidak ditemukan periode magang yang sesuai dengan filter atau kata kunci Anda.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($periods->hasPages())
            <div class="p-4 border-t border-slate-800 bg-slate-950/40">
                {{ $periods->links() }}
            </div>
        @endif
    </div>

</div>
@endsection
