@extends('layouts.app', ['title' => 'Detail Program Studi - ' . $studyProgram->name])

@section('content')
<div class="max-w-5xl mx-auto space-y-6">

    <!-- Breadcrumb & Back -->
    <div class="flex items-center justify-between text-xs text-slate-400">
        <div class="flex items-center gap-2">
            <a href="{{ route('master.study-programs.index') }}" class="hover:text-white transition flex items-center gap-1">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                <span>Daftar Prodi</span>
            </a>
            <span>/</span>
            <span class="text-white font-medium">{{ $studyProgram->name }} ({{ $studyProgram->code }})</span>
        </div>

        <div class="flex items-center gap-2">
            <a href="{{ route('master.study-programs.edit', $studyProgram) }}"
                class="px-3.5 py-1.5 rounded-xl bg-amber-500/10 hover:bg-amber-500/20 border border-amber-500/20 text-amber-300 font-semibold text-xs transition flex items-center gap-1.5">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                </svg>
                <span>Edit Prodi</span>
            </a>
        </div>
    </div>

    <!-- Detail Header Card -->
    <div class="rounded-2xl bg-slate-900/80 border border-slate-800 p-6 shadow-xl">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-slate-800 pb-5">
            <div>
                <div class="flex items-center gap-2">
                    <span class="px-2 py-0.5 rounded text-xs font-mono font-bold bg-blue-500/10 text-blue-400 border border-blue-500/20">
                        {{ $studyProgram->code }}
                    </span>
                    <span class="px-2 py-0.5 rounded text-xs font-mono font-bold bg-indigo-500/10 text-indigo-300 border border-indigo-500/20">
                        {{ $studyProgram->degree_level }}
                    </span>
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold {{ $studyProgram->is_active ? 'bg-emerald-500/10 text-emerald-300 border border-emerald-500/20' : 'bg-rose-500/10 text-rose-300 border border-rose-500/20' }}">
                        {{ $studyProgram->is_active ? '● Aktif' : '○ Non-Aktif' }}
                    </span>
                </div>
                <h2 class="text-xl font-extrabold text-white mt-2">{{ $studyProgram->name }}</h2>
                <p class="text-xs text-slate-400 mt-1">{{ $studyProgram->faculty }}</p>
            </div>

            <div class="flex items-center gap-3">
                <div class="p-3 rounded-xl bg-slate-950 border border-slate-800 text-center min-w-[100px]">
                    <span class="block text-lg font-bold text-white font-mono">{{ $studyProgram->courses->count() }}</span>
                    <span class="text-[10px] text-slate-400 uppercase tracking-wider font-mono">Mata Kuliah</span>
                </div>
                <div class="p-3 rounded-xl bg-slate-950 border border-slate-800 text-center min-w-[100px]">
                    <span class="block text-lg font-bold text-white font-mono">{{ $studyProgram->users->count() }}</span>
                    <span class="text-[10px] text-slate-400 uppercase tracking-wider font-mono">Pengguna/Mhs</span>
                </div>
            </div>
        </div>

        @if($studyProgram->description)
            <div class="pt-4 text-xs text-slate-300 leading-relaxed">
                <p class="font-semibold text-slate-400 mb-1">Deskripsi Program Studi:</p>
                <p>{{ $studyProgram->description }}</p>
            </div>
        @endif
    </div>

    <!-- Related Courses Section -->
    <div class="rounded-2xl bg-slate-900/60 border border-slate-800 overflow-hidden shadow-xl">
        <div class="p-4 sm:p-5 border-b border-slate-800 flex items-center justify-between">
            <div>
                <h3 class="text-sm font-bold text-white">Mata Kuliah Terdaftar di {{ $studyProgram->name }}</h3>
                <p class="text-xs text-slate-400">Daftar mata kuliah yang menjadi syarat atau ekuivalensi pelaksanaan magang.</p>
            </div>
            <a href="{{ route('master.courses.create') }}?study_program_id={{ $studyProgram->id }}"
                class="px-3 py-1.5 rounded-xl bg-blue-600/20 hover:bg-blue-600/30 border border-blue-500/30 text-blue-300 text-xs font-semibold transition">
                + Tambah MK
            </a>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-950/80 text-slate-400 uppercase font-mono text-[10px] tracking-wider border-b border-slate-800">
                    <tr>
                        <th class="py-3 px-4">Kode MK</th>
                        <th class="py-3 px-4">Nama Mata Kuliah</th>
                        <th class="py-3 px-4 text-center">SKS</th>
                        <th class="py-3 px-4 text-center">Semester</th>
                        <th class="py-3 px-4 text-center">Status</th>
                        <th class="py-3 px-4 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/60 text-slate-300">
                    @forelse($studyProgram->courses as $course)
                        <tr class="hover:bg-slate-800/30 transition">
                            <td class="py-3 px-4 font-mono font-bold text-blue-400">{{ $course->code }}</td>
                            <td class="py-3 px-4 font-medium text-white">{{ $course->name }}</td>
                            <td class="py-3 px-4 text-center font-mono">{{ $course->credits }} SKS</td>
                            <td class="py-3 px-4 text-center font-mono">Semester {{ $course->semester }}</td>
                            <td class="py-3 px-4 text-center">
                                <span class="px-2 py-0.5 rounded text-[10px] font-semibold {{ $course->is_active ? 'bg-emerald-500/10 text-emerald-300' : 'bg-rose-500/10 text-rose-300' }}">
                                    {{ $course->is_active ? 'Aktif' : 'Non-Aktif' }}
                                </span>
                            </td>
                            <td class="py-3 px-4 text-right">
                                <a href="{{ route('master.courses.edit', $course) }}" class="text-slate-400 hover:text-amber-400 transition font-medium">
                                    Edit
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-8 px-4 text-center text-slate-400">
                                Belum ada mata kuliah magang yang didaftarkan untuk program studi ini.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
