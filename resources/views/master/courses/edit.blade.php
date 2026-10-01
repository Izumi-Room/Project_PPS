@extends('layouts.app', ['title' => 'Edit Mata Kuliah - ' . $course->code])

@section('content')
<div class="max-w-3xl mx-auto space-y-6">

    <!-- Breadcrumb & Back -->
    <div class="flex items-center gap-2 text-xs text-slate-400">
        <a href="{{ route('master.courses.index') }}" class="hover:text-white transition flex items-center gap-1">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            <span>Kembali ke Daftar Mata Kuliah</span>
        </a>
        <span>/</span>
        <span class="text-white font-medium">Edit: {{ $course->code }}</span>
    </div>

    <!-- Form Card -->
    <div class="rounded-2xl bg-slate-900/80 border border-slate-800 p-6 sm:p-8 shadow-xl">
        <div class="border-b border-slate-800 pb-5 mb-6 flex items-center justify-between">
            <div>
                <h2 class="text-lg font-bold text-white tracking-tight">Edit Mata Kuliah</h2>
                <p class="text-xs text-slate-400 mt-1">Perbarui konfigurasi mata kuliah magang {{ $course->code }} - {{ $course->name }}.</p>
            </div>
            <span class="px-2.5 py-1 rounded-full text-xs font-mono font-semibold {{ $course->is_active ? 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/20' : 'bg-rose-500/10 text-rose-400 border border-rose-500/20' }}">
                {{ $course->is_active ? 'Aktif' : 'Non-Aktif' }}
            </span>
        </div>

        <form action="{{ route('master.courses.update', $course) }}" method="POST" class="space-y-5" onsubmit="this.querySelector('button[type=submit]').disabled=true; this.querySelector('button[type=submit]').innerText='Menyimpan Perubahan...';">
            @csrf
            @method('PUT')

            <!-- Program Studi -->
            <div>
                <label for="study_program_id" class="block text-xs font-semibold text-slate-300 mb-1.5">
                    Program Studi Pengampu <span class="text-rose-400">*</span>
                </label>
                <select id="study_program_id" name="study_program_id" required
                    class="w-full px-3.5 py-2.5 bg-slate-950 border @error('study_program_id') border-rose-500 @else border-slate-800 @enderror rounded-xl text-xs text-white focus:outline-none focus:ring-2 focus:ring-blue-500 transition">
                    @foreach($studyPrograms as $prodi)
                        <option value="{{ $prodi->id }}" {{ old('study_program_id', $course->study_program_id) == $prodi->id ? 'selected' : '' }}>
                            {{ $prodi->code }} - {{ $prodi->name }} ({{ $prodi->degree_level }})
                        </option>
                    @endforeach
                </select>
                @error('study_program_id')
                    <p class="text-[11px] text-rose-400 mt-1 font-medium">{{ $message }}</p>
                @enderror
            </div>

            <!-- Kode & Nama MK -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div>
                    <label for="code" class="block text-xs font-semibold text-slate-300 mb-1.5">
                        Kode Mata Kuliah <span class="text-rose-400">*</span>
                    </label>
                    <input type="text" id="code" name="code" value="{{ old('code', $course->code) }}" required maxlength="20"
                        class="w-full px-3.5 py-2.5 bg-slate-950 border @error('code') border-rose-500 @else border-slate-800 @enderror rounded-xl text-xs text-white font-mono focus:outline-none focus:ring-2 focus:ring-blue-500 transition">
                    @error('code')
                        <p class="text-[11px] text-rose-400 mt-1 font-medium">{{ $message }}</p>
                    @enderror
                </div>

                <div class="sm:col-span-2">
                    <label for="name" class="block text-xs font-semibold text-slate-300 mb-1.5">
                        Nama Mata Kuliah <span class="text-rose-400">*</span>
                    </label>
                    <input type="text" id="name" name="name" value="{{ old('name', $course->name) }}" required
                        class="w-full px-3.5 py-2.5 bg-slate-950 border @error('name') border-rose-500 @else border-slate-800 @enderror rounded-xl text-xs text-white focus:outline-none focus:ring-2 focus:ring-blue-500 transition">
                    @error('name')
                        <p class="text-[11px] text-rose-400 mt-1 font-medium">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- SKS & Semester -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="credits" class="block text-xs font-semibold text-slate-300 mb-1.5">
                        Bobot SKS <span class="text-rose-400">*</span>
                    </label>
                    <input type="number" id="credits" name="credits" value="{{ old('credits', $course->credits) }}" required min="1" max="12"
                        class="w-full px-3.5 py-2.5 bg-slate-950 border @error('credits') border-rose-500 @else border-slate-800 @enderror rounded-xl text-xs text-white font-mono focus:outline-none focus:ring-2 focus:ring-blue-500 transition">
                    @error('credits')
                        <p class="text-[11px] text-rose-400 mt-1 font-medium">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="semester" class="block text-xs font-semibold text-slate-300 mb-1.5">
                        Semester Standar <span class="text-rose-400">*</span>
                    </label>
                    <select id="semester" name="semester" required
                        class="w-full px-3.5 py-2.5 bg-slate-950 border @error('semester') border-rose-500 @else border-slate-800 @enderror rounded-xl text-xs text-white focus:outline-none focus:ring-2 focus:ring-blue-500 transition">
                        @for($s = 1; $s <= 8; $s++)
                            <option value="{{ $s }}" {{ old('semester', $course->semester) == $s ? 'selected' : '' }}>Semester {{ $s }}</option>
                        @endfor
                    </select>
                    @error('semester')
                        <p class="text-[11px] text-rose-400 mt-1 font-medium">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Deskripsi -->
            <div>
                <label for="description" class="block text-xs font-semibold text-slate-300 mb-1.5">
                    Silabus / Deskripsi Ringkas
                </label>
                <textarea id="description" name="description" rows="3"
                    class="w-full px-3.5 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-xs text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-blue-500 transition">{{ old('description', $course->description) }}</textarea>
                @error('description')
                    <p class="text-[11px] text-rose-400 mt-1 font-medium">{{ $message }}</p>
                @enderror
            </div>

            <!-- Status Aktif Checkbox -->
            <div class="flex items-center gap-3 p-3.5 rounded-xl bg-slate-950/60 border border-slate-800">
                <input type="checkbox" id="is_active" name="is_active" value="1" {{ old('is_active', $course->is_active) ? 'checked' : '' }}
                    class="w-4 h-4 rounded text-blue-600 bg-slate-900 border-slate-700 focus:ring-blue-500">
                <div>
                    <label for="is_active" class="text-xs font-semibold text-white block cursor-pointer">
                        Status Aktif
                    </label>
                    <p class="text-[11px] text-slate-400">Centang agar mata kuliah magang ini aktif di semester berjalan.</p>
                </div>
            </div>

            <!-- Buttons -->
            <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-800">
                <a href="{{ route('master.courses.index') }}"
                    class="px-4 py-2.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 text-xs font-semibold transition">
                    Batal
                </a>
                <button type="submit"
                    class="px-5 py-2.5 rounded-xl bg-gradient-to-r from-amber-600 to-orange-600 hover:from-amber-500 hover:to-orange-500 text-white text-xs font-semibold shadow-lg shadow-orange-500/20 transition cursor-pointer">
                    Perbarui Mata Kuliah
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
