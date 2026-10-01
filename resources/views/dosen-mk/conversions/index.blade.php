@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h2 class="text-2xl font-extrabold text-white tracking-tight flex items-center gap-3">
                <span class="w-10 h-10 rounded-xl bg-gradient-to-tr from-teal-600 to-emerald-600 flex items-center justify-center text-white shadow-lg shadow-teal-500/20">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </span>
                Queue Konversi Mata Kuliah (Dosen MK)
            </h2>
            <p class="text-sm text-slate-400 mt-1">
                Persetujuan ekuivalensi kegiatan magang mahasiswa terhadap mata kuliah pengampu.
            </p>
        </div>

        @if($pendingCount > 0)
            <span class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-amber-500/20 text-amber-300 border border-amber-500/30 text-xs font-bold font-mono">
                <span class="w-2 h-2 rounded-full bg-amber-400 animate-pulse"></span>
                {{ $pendingCount }} Pengajuan Menunggu Review
            </span>
        @endif
    </div>

    <!-- Tabs & Search -->
    <div class="flex flex-col sm:flex-row items-center justify-between gap-4 p-4 rounded-2xl bg-slate-900 border border-slate-800">
        <div class="flex items-center gap-2 w-full sm:w-auto">
            <a href="{{ route('dosen-mk.conversions.index', ['tab' => 'queue']) }}"
                class="px-4 py-2 rounded-xl text-xs font-semibold transition {{ $tab === 'queue' ? 'bg-teal-600 text-white shadow-md shadow-teal-600/30' : 'text-slate-400 hover:text-white hover:bg-slate-800' }}">
                Antrean Review ({{ $pendingCount }})
            </a>
            <a href="{{ route('dosen-mk.conversions.index', ['tab' => 'history']) }}"
                class="px-4 py-2 rounded-xl text-xs font-semibold transition {{ $tab === 'history' ? 'bg-teal-600 text-white shadow-md shadow-teal-600/30' : 'text-slate-400 hover:text-white hover:bg-slate-800' }}">
                Riwayat Selesai
            </a>
        </div>

        <form method="GET" action="{{ route('dosen-mk.conversions.index') }}" class="w-full sm:w-auto flex items-center gap-2">
            <input type="hidden" name="tab" value="{{ $tab }}">
            <input type="text" name="search" value="{{ request('search') }}"
                placeholder="Cari mahasiswa atau mata kuliah..."
                class="px-3 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white text-xs focus:ring-2 focus:ring-teal-500 focus:outline-none">
            <button type="submit" class="px-3 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-white text-xs font-semibold">
                Cari
            </button>
        </form>
    </div>

    <!-- Queue Table -->
    <div class="rounded-2xl bg-slate-900 border border-slate-800 overflow-hidden shadow-xl">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-300">
                <thead class="bg-slate-950/70 text-slate-400 uppercase font-mono text-[10px] tracking-wider border-b border-slate-800">
                    <tr>
                        <th class="py-3 px-4">Mahasiswa</th>
                        <th class="py-3 px-4">Mata Kuliah Diajukan</th>
                        <th class="py-3 px-4">Instansi Magang</th>
                        <th class="py-3 px-4">Status</th>
                        <th class="py-3 px-4">Diajukan Pada</th>
                        <th class="py-3 px-4 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/60 font-medium">
                    @forelse ($conversions as $conv)
                        <tr class="hover:bg-slate-800/30 transition">
                            <td class="py-3.5 px-4">
                                <div class="font-bold text-white text-sm">{{ $conv->student->name }}</div>
                                <div class="text-[11px] text-slate-400 font-mono">{{ $conv->student->identity_number }}</div>
                            </td>
                            <td class="py-3.5 px-4">
                                <div class="font-semibold text-teal-300">{{ $conv->course->name }}</div>
                                <div class="text-[11px] text-slate-400 font-mono">{{ $conv->course->code }} • {{ $conv->course->credits }} SKS</div>
                            </td>
                            <td class="py-3.5 px-4 text-slate-300">
                                {{ $conv->internshipApplication->partnerInstitution->name }}
                            </td>
                            <td class="py-3.5 px-4">
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[10px] font-bold border {{ $conv->status_badge_classes }}">
                                    {{ $conv->status_label }}
                                </span>
                            </td>
                            <td class="py-3.5 px-4 font-mono text-slate-400 text-[11px]">
                                {{ $conv->created_at->format('d/m/Y H:i') }}
                            </td>
                            <td class="py-3.5 px-4 text-right">
                                <a href="{{ route('dosen-mk.conversions.show', $conv->id) }}"
                                    class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg bg-teal-600/20 hover:bg-teal-600/30 text-teal-300 border border-teal-500/30 text-xs font-semibold transition">
                                    {{ $tab === 'queue' ? 'Review Pengajuan' : 'Detail' }}
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-12 text-center text-slate-500">
                                <p class="text-sm font-semibold text-slate-400">Tidak ada pengajuan konversi dalam antrean ini.</p>
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
