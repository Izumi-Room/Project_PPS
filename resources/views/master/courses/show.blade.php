@extends('layouts.app', ['title' => 'Detail Mata Kuliah - ' . $course->code])

@section('content')
<div class="max-w-4xl mx-auto space-y-6">

    <!-- Breadcrumb & Back -->
    <div class="flex items-center justify-between text-xs text-slate-400">
        <div class="flex items-center gap-2">
            <a href="{{ route('master.courses.index') }}" class="hover:text-white transition flex items-center gap-1">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                <span>Daftar Mata Kuliah</span>
            </a>
            <span>/</span>
            <span class="text-white font-medium">{{ $course->code }}</span>
        </div>

        <a href="{{ route('master.courses.edit', $course) }}"
            class="px-3.5 py-1.5 rounded-xl bg-amber-500/10 hover:bg-amber-500/20 border border-amber-500/20 text-amber-300 font-semibold text-xs transition flex items-center gap-1.5">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
            </svg>
            <span>Edit MK</span>
        </a>
    </div>

    <!-- Main Detail Card -->
    <div class="rounded-2xl bg-slate-900/80 border border-slate-800 p-6 sm:p-8 shadow-xl space-y-6">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-slate-800 pb-6">
            <div>
                <div class="flex items-center gap-2">
                    <span class="px-2.5 py-0.5 rounded text-xs font-mono font-bold bg-blue-500/10 text-blue-400 border border-blue-500/20">
                        {{ $course->code }}
                    </span>
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold {{ $course->is_active ? 'bg-emerald-500/10 text-emerald-300 border border-emerald-500/20' : 'bg-rose-500/10 text-rose-300 border border-rose-500/20' }}">
                        {{ $course->is_active ? '● Aktif' : '○ Non-Aktif' }}
                    </span>
                </div>
                <h2 class="text-xl sm:text-2xl font-extrabold text-white mt-2">{{ $course->name }}</h2>
            </div>

            <div class="text-right">
                <span class="text-2xl font-extrabold font-mono text-white">{{ $course->credits }}</span>
                <span class="text-xs text-slate-400 font-mono block">SKS Magang</span>
            </div>
        </div>

        <!-- Academic Info Grid -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div class="p-4 rounded-xl bg-slate-950/70 border border-slate-800">
                <span class="block text-[10px] text-slate-400 uppercase font-mono tracking-wider">Program Studi</span>
                <span class="block text-sm font-bold text-white mt-1">{{ $course->studyProgram?->name ?? '-' }}</span>
                <span class="text-xs font-mono text-indigo-400">{{ $course->studyProgram?->code }} ({{ $course->studyProgram?->degree_level }})</span>
            </div>
            <div class="p-4 rounded-xl bg-slate-950/70 border border-slate-800">
                <span class="block text-[10px] text-slate-400 uppercase font-mono tracking-wider">Semester Rekomendasi</span>
                <span class="block text-sm font-bold text-white font-mono mt-1">Semester {{ $course->semester }}</span>
                <span class="text-xs text-slate-400">Tingkat akademik</span>
            </div>
            <div class="p-4 rounded-xl bg-slate-950/70 border border-slate-800">
                <span class="block text-[10px] text-slate-400 uppercase font-mono tracking-wider">Fakultas</span>
                <span class="block text-xs font-medium text-slate-200 mt-1">{{ $course->studyProgram?->faculty ?? 'Fakultas Teknik' }}</span>
            </div>
        </div>

        <!-- Description -->
        @if($course->description)
            <div class="p-4 rounded-xl bg-slate-950/40 border border-slate-800">
                <h4 class="text-xs font-bold text-slate-300 uppercase tracking-wider font-mono mb-1.5">Deskripsi / Silabus</h4>
                <p class="text-xs text-slate-300 leading-relaxed">{{ $course->description }}</p>
            </div>
        @endif
    </div>
</div>
@endsection
