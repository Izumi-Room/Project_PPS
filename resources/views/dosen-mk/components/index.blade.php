@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h2 class="text-2xl font-extrabold text-white tracking-tight flex items-center gap-3">
                <span class="w-10 h-10 rounded-xl bg-gradient-to-tr from-teal-600 to-cyan-600 flex items-center justify-center text-white shadow-lg shadow-teal-500/20">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
                    </svg>
                </span>
                Komponen Pengumpulan Tugas Mata Kuliah
            </h2>
            <p class="text-sm text-slate-400 mt-1">
                Definisikan komponen pengumpulan tugas, jenis file/link, bobot nilai, dan tenggat waktu (deadline).
            </p>
        </div>

        <a href="{{ route('dosen-mk.components.create', ['course_id' => $selectedCourseId]) }}"
            class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-teal-600 hover:bg-teal-500 text-white font-semibold text-xs transition shadow-lg shadow-teal-600/30">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            <span>Tambah Komponen Baru</span>
        </a>
    </div>

    <!-- Course Filter Tabs -->
    @if ($courses->isNotEmpty())
        <div class="p-4 rounded-2xl bg-slate-900 border border-slate-800 flex items-center gap-3 overflow-x-auto custom-scrollbar">
            <span class="text-xs font-semibold text-slate-400 flex-shrink-0">Mata Kuliah:</span>
            @foreach ($courses as $c)
                <a href="{{ route('dosen-mk.components.index', ['course_id' => $c->id]) }}"
                    class="px-3.5 py-1.5 rounded-xl text-xs font-semibold flex-shrink-0 transition {{ (int)$selectedCourseId === $c->id ? 'bg-teal-600 text-white shadow-md shadow-teal-600/30' : 'bg-slate-950 text-slate-400 border border-slate-800 hover:text-white' }}">
                    {{ $c->name }} ({{ $c->code }})
                </a>
            @endforeach
        </div>
    @endif

    <!-- Components Table -->
    <div class="rounded-2xl bg-slate-900 border border-slate-800 overflow-hidden shadow-xl">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-300">
                <thead class="bg-slate-950/70 text-slate-400 uppercase font-mono text-[10px] tracking-wider border-b border-slate-800">
                    <tr>
                        <th class="py-3 px-4">Nama Komponen</th>
                        <th class="py-3 px-4">Jenis Pengumpulan</th>
                        <th class="py-3 px-4">Bobot</th>
                        <th class="py-3 px-4">Batas Waktu (Deadline)</th>
                        <th class="py-3 px-4">Kewajiban</th>
                        <th class="py-3 px-4">Total Submisi</th>
                        <th class="py-3 px-4 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/60 font-medium">
                    @forelse ($components as $comp)
                        <tr class="hover:bg-slate-800/30 transition">
                            <td class="py-3.5 px-4">
                                <div class="font-bold text-white text-sm">{{ $comp->name }}</div>
                                @if($comp->instructions)
                                    <div class="text-[11px] text-slate-400 line-clamp-1 mt-0.5">{{ $comp->instructions }}</div>
                                @endif
                            </td>
                            <td class="py-3.5 px-4">
                                <span class="px-2 py-0.5 rounded text-[10px] font-mono font-bold bg-teal-500/20 text-teal-300 border border-teal-500/30">
                                    {{ $comp->submission_type }}
                                </span>
                            </td>
                            <td class="py-3.5 px-4 font-mono font-bold text-amber-400">
                                {{ $comp->weight }}%
                            </td>
                            <td class="py-3.5 px-4 font-mono">
                                <span class="{{ $comp->isPassedDeadline() ? 'text-rose-400 font-bold' : 'text-slate-300' }}">
                                    {{ $comp->deadline->format('d/m/Y H:i') }}
                                </span>
                                @if($comp->isPassedDeadline())
                                    <span class="block text-[9px] text-rose-400">Expired</span>
                                @endif
                            </td>
                            <td class="py-3.5 px-4">
                                @if ($comp->is_required)
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-rose-500/20 text-rose-300 border border-rose-500/30">
                                        Wajib
                                    </span>
                                @else
                                    <span class="px-2 py-0.5 rounded text-[10px] font-medium bg-slate-700/30 text-slate-400">
                                        Opsional
                                    </span>
                                @endif
                            </td>
                            <td class="py-3.5 px-4 font-mono text-slate-300">
                                {{ $comp->submissions_count }} Mahasiswa
                            </td>
                            <td class="py-3.5 px-4 text-right space-x-2">
                                <a href="{{ route('dosen-mk.components.edit', $comp->id) }}"
                                    class="inline-flex items-center px-2.5 py-1 rounded-lg bg-blue-600/20 hover:bg-blue-600/30 text-blue-300 border border-blue-500/30 text-xs font-semibold transition">
                                    Edit
                                </a>
                                <form method="POST" action="{{ route('dosen-mk.components.destroy', $comp->id) }}" class="inline-block" onsubmit="return confirm('Hapus / nonaktifkan komponen ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="px-2.5 py-1 rounded-lg bg-rose-500/10 hover:bg-rose-500/20 text-rose-400 text-xs font-semibold transition">
                                        Hapus
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-12 text-center text-slate-500">
                                <p class="text-sm font-semibold text-slate-400">Belum ada komponen pengumpulan pada mata kuliah ini.</p>
                                <p class="text-xs text-slate-500 mt-1">Klik tombol "Tambah Komponen Baru" untuk membuat tugas seperti Laporan Akhir, Project, atau Presentasi.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if(method_exists($components, 'hasPages') && $components->hasPages())
            <div class="p-4 border-t border-slate-800">
                {{ $components->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
