@extends('layouts.app', ['title' => 'Master Data - Mata Kuliah'])

@section('content')
<div class="max-w-7xl mx-auto space-y-6">

    @include('master.partials.nav')

    <!-- Page Header & Action -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h2 class="text-xl font-bold text-white tracking-tight flex items-center gap-2">
                <span>Mata Kuliah Magang</span>
                <span class="px-2 py-0.5 rounded-full text-xs font-mono bg-blue-500/10 text-blue-400 border border-blue-500/20">
                    {{ $courses->total() }} Total
                </span>
            </h2>
            <p class="text-xs text-slate-400 mt-1">
                Mata kuliah terkait magang (Kerja Praktik, PKL, Seminar Magang) per program studi dan semester.
            </p>
        </div>

        <div class="flex items-center gap-3">
            <a href="{{ route('master.courses.create') }}"
                class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-500 hover:to-indigo-500 text-white text-xs font-semibold shadow-lg shadow-blue-500/20 transition cursor-pointer">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                <span>Tambah Mata Kuliah</span>
            </a>
        </div>
    </div>

    <!-- Search & Filter Bar -->
    <div class="p-4 rounded-2xl bg-slate-900/60 border border-slate-800">
        <form method="GET" action="{{ route('master.courses.index') }}" class="grid grid-cols-1 sm:grid-cols-12 gap-3">
            <div class="sm:col-span-4 relative">
                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-500">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </div>
                <input type="text" name="search" value="{{ request('search') }}"
                    placeholder="Cari kode atau nama mata kuliah..."
                    class="w-full pl-10 pr-4 py-2 bg-slate-950 border border-slate-800 rounded-xl text-xs text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-blue-500 transition">
            </div>

            <div class="sm:col-span-3">
                <select name="study_program_id" class="w-full px-3 py-2 bg-slate-950 border border-slate-800 rounded-xl text-xs text-slate-300 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">Semua Program Studi</option>
                    @foreach($studyPrograms as $p)
                        <option value="{{ $p->id }}" {{ request('study_program_id') == $p->id ? 'selected' : '' }}>
                            {{ $p->code }} - {{ $p->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="sm:col-span-2">
                <select name="semester" class="w-full px-3 py-2 bg-slate-950 border border-slate-800 rounded-xl text-xs text-slate-300 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">Semua Semester</option>
                    @for($s = 1; $s <= 8; $s++)
                        <option value="{{ $s }}" {{ request('semester') == $s ? 'selected' : '' }}>Semester {{ $s }}</option>
                    @endfor
                </select>
            </div>

            <div class="sm:col-span-2">
                <select name="status" class="w-full px-3 py-2 bg-slate-950 border border-slate-800 rounded-xl text-xs text-slate-300 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">Semua Status</option>
                    <option value="1" {{ request('status') === '1' ? 'selected' : '' }}>Aktif</option>
                    <option value="0" {{ request('status') === '0' ? 'selected' : '' }}>Non-Aktif</option>
                </select>
            </div>

            <div class="sm:col-span-1 flex gap-1">
                <button type="submit" class="w-full px-3 py-2 rounded-xl bg-blue-600 hover:bg-blue-500 text-white text-xs font-semibold transition cursor-pointer flex items-center justify-center">
                    Filter
                </button>
                @if(request()->anyFilled(['search', 'study_program_id', 'semester', 'status']))
                    <a href="{{ route('master.courses.index') }}" class="px-2.5 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-400 hover:text-white text-xs transition flex items-center justify-center" title="Reset">
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
                        <th class="py-3.5 px-4 font-semibold">Kode MK</th>
                        <th class="py-3.5 px-4 font-semibold">Nama Mata Kuliah</th>
                        <th class="py-3.5 px-4 font-semibold">Program Studi</th>
                        <th class="py-3.5 px-4 font-semibold text-center">SKS</th>
                        <th class="py-3.5 px-4 font-semibold text-center">Semester</th>
                        <th class="py-3.5 px-4 font-semibold text-center">Status</th>
                        <th class="py-3.5 px-4 font-semibold text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/60 text-slate-300">
                    @forelse ($courses as $c)
                        <tr class="hover:bg-slate-800/30 transition">
                            <td class="py-3.5 px-4 font-mono font-bold text-blue-400">
                                {{ $c->code }}
                            </td>
                            <td class="py-3.5 px-4 font-semibold text-white">
                                <a href="{{ route('master.courses.show', $c) }}" class="hover:text-blue-400 transition">
                                    {{ $c->name }}
                                </a>
                                @if($c->description)
                                    <p class="text-[11px] text-slate-400 font-normal line-clamp-1 mt-0.5">{{ $c->description }}</p>
                                @endif
                            </td>
                            <td class="py-3.5 px-4">
                                <span class="px-2 py-0.5 rounded text-[10px] font-mono font-semibold bg-indigo-500/10 text-indigo-300 border border-indigo-500/20">
                                    {{ $c->studyProgram?->code ?? '-' }}
                                </span>
                                <span class="text-slate-400 text-[11px] ml-1.5">{{ $c->studyProgram?->name }}</span>
                            </td>
                            <td class="py-3.5 px-4 text-center font-mono font-semibold text-white">
                                {{ $c->credits }} SKS
                            </td>
                            <td class="py-3.5 px-4 text-center font-mono">
                                Semester {{ $c->semester }}
                            </td>
                            <td class="py-3.5 px-4 text-center">
                                <form action="{{ route('master.courses.toggle', $c) }}" method="POST" class="inline">
                                    @csrf
                                    <button type="submit" class="px-2.5 py-1 rounded-full text-[10px] font-semibold transition cursor-pointer {{ $c->is_active ? 'bg-emerald-500/10 text-emerald-300 border border-emerald-500/20 hover:bg-emerald-500/20' : 'bg-rose-500/10 text-rose-300 border border-rose-500/20 hover:bg-rose-500/20' }}" title="Ubah Status">
                                        {{ $c->is_active ? '● Aktif' : '○ Non-Aktif' }}
                                    </button>
                                </form>
                            </td>
                            <td class="py-3.5 px-4 text-right">
                                <div class="flex items-center justify-end gap-1.5">
                                    <a href="{{ route('master.courses.show', $c) }}"
                                        class="p-1.5 rounded-lg text-slate-400 hover:text-white hover:bg-slate-800 transition" title="Lihat Detail">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                        </svg>
                                    </a>
                                    <a href="{{ route('master.courses.edit', $c) }}"
                                        class="p-1.5 rounded-lg text-slate-400 hover:text-amber-400 hover:bg-slate-800 transition" title="Edit MK">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                        </svg>
                                    </a>
                                    <form action="{{ route('master.courses.destroy', $c) }}" method="POST"
                                        onsubmit="return confirm('Apakah Anda yakin ingin menghapus mata kuliah ini?')" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="p-1.5 rounded-lg text-slate-400 hover:text-rose-400 hover:bg-slate-800 transition cursor-pointer" title="Hapus MK">
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
                            <td colspan="7" class="py-12 px-4 text-center">
                                <div class="max-w-sm mx-auto space-y-3">
                                    <div class="w-12 h-12 mx-auto rounded-2xl bg-slate-800/80 flex items-center justify-center text-slate-500">
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                                        </svg>
                                    </div>
                                    <p class="text-sm font-semibold text-white">Tidak ada data mata kuliah</p>
                                    <p class="text-xs text-slate-400">Tidak ditemukan mata kuliah magang yang sesuai dengan filter atau kata kunci Anda.</p>
                                    @if(request()->anyFilled(['search', 'study_program_id', 'semester', 'status']))
                                        <a href="{{ route('master.courses.index') }}" class="inline-block text-xs text-blue-400 hover:underline">
                                            Reset filter pencarian
                                        </a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($courses->hasPages())
            <div class="p-4 border-t border-slate-800 bg-slate-950/40">
                {{ $courses->links() }}
            </div>
        @endif
    </div>

</div>
@endsection
