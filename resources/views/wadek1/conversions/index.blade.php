@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h2 class="text-2xl font-extrabold text-white tracking-tight flex items-center gap-3">
                <span class="w-10 h-10 rounded-xl bg-gradient-to-tr from-indigo-600 to-purple-600 flex items-center justify-center text-white shadow-lg shadow-indigo-500/20">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                    </svg>
                </span>
                Approval Konversi Mata Kuliah (Wakil Dekan 1)
            </h2>
            <p class="text-sm text-slate-400 mt-1">
                Persetujuan tingkat fakultas untuk ekuivalensi mata kuliah magang mahasiswa.
            </p>
        </div>

        @if($pendingCount > 0)
            <span class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-amber-500/20 text-amber-300 border border-amber-500/30 text-xs font-bold font-mono">
                <span class="w-2 h-2 rounded-full bg-amber-400 animate-pulse"></span>
                {{ $pendingCount }} Menunggu Approval Dekanat
            </span>
        @endif
    </div>

    <!-- Tabs & Search -->
    <div class="flex flex-col sm:flex-row items-center justify-between gap-4 p-4 rounded-2xl bg-slate-900 border border-slate-800">
        <div class="flex items-center gap-2 w-full sm:w-auto">
            <a href="{{ route('wadek1.conversions.index', ['tab' => 'queue']) }}"
                class="px-4 py-2 rounded-xl text-xs font-semibold transition {{ $tab === 'queue' ? 'bg-indigo-600 text-white shadow-md shadow-indigo-600/30' : 'text-slate-400 hover:text-white hover:bg-slate-800' }}">
                Menunggu Approval ({{ $pendingCount }})
            </a>
            <a href="{{ route('wadek1.conversions.index', ['tab' => 'history']) }}"
                class="px-4 py-2 rounded-xl text-xs font-semibold transition {{ $tab === 'history' ? 'bg-indigo-600 text-white shadow-md shadow-indigo-600/30' : 'text-slate-400 hover:text-white hover:bg-slate-800' }}">
                Riwayat Selesai
            </a>
        </div>

        <form method="GET" action="{{ route('wadek1.conversions.index') }}" class="w-full sm:w-auto flex items-center gap-2">
            <input type="hidden" name="tab" value="{{ $tab }}">
            <input type="text" name="search" value="{{ request('search') }}"
                placeholder="Cari mahasiswa atau prodi..."
                class="px-3 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white text-xs focus:ring-2 focus:ring-indigo-500 focus:outline-none">
            <button type="submit" class="px-3 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-white text-xs font-semibold">
                Cari
            </button>
        </form>
    </div>

    <!-- Table -->
    <div class="rounded-2xl bg-slate-900 border border-slate-800 overflow-hidden shadow-xl">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-300">
                <thead class="bg-slate-950/70 text-slate-400 uppercase font-mono text-[10px] tracking-wider border-b border-slate-800">
                    <tr>
                        <th class="py-3 px-4">Mahasiswa & Program Studi</th>
                        <th class="py-3 px-4">Mata Kuliah Diajukan</th>
                        <th class="py-3 px-4">Verifikator Akademik</th>
                        <th class="py-3 px-4">Status Alur</th>
                        <th class="py-3 px-4 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/60 font-medium">
                    @forelse ($conversions as $conv)
                        <tr class="hover:bg-slate-800/30 transition">
                            <td class="py-3.5 px-4">
                                <div class="font-bold text-white text-sm">{{ $conv->student->name }}</div>
                                <div class="text-[11px] text-slate-400 font-mono">{{ $conv->student->identity_number }} • {{ $conv->internshipApplication->studyProgram->name ?? '-' }}</div>
                            </td>
                            <td class="py-3.5 px-4">
                                <div class="font-semibold text-indigo-300">{{ $conv->course->name }}</div>
                                <div class="text-[11px] text-slate-400 font-mono">{{ $conv->course->code }} • {{ $conv->course->credits }} SKS</div>
                            </td>
                            <td class="py-3.5 px-4">
                                <div class="text-xs text-slate-300">Dosen MK: <span class="text-white">{{ $conv->dosenMk->name ?? '-' }}</span></div>
                                <div class="text-xs text-emerald-400">Dosbing: <span class="font-semibold">{{ $conv->dosbing->name ?? '-' }}</span></div>
                            </td>
                            <td class="py-3.5 px-4">
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[10px] font-bold border {{ $conv->status_badge_classes }}">
                                    {{ $conv->status_label }}
                                </span>
                            </td>
                            <td class="py-3.5 px-4 text-right">
                                <a href="{{ route('wadek1.conversions.show', $conv->id) }}"
                                    class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg bg-indigo-600/20 hover:bg-indigo-600/30 text-indigo-300 border border-indigo-500/30 text-xs font-semibold transition">
                                    {{ $tab === 'queue' ? 'Approval' : 'Detail' }}
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="py-12 text-center text-slate-500">
                                <p class="text-sm font-semibold text-slate-400">Tidak ada pengajuan konversi dalam antrean approval Wadek 1.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($conversions->hasPages())
            <div class="p-4 border-t border-slate-800">
                {{ $conversions->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
