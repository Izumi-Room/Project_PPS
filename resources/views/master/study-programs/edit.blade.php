@extends('layouts.app', ['title' => 'Edit Program Studi - ' . $studyProgram->code])

@section('content')
<div class="max-w-3xl mx-auto space-y-6">

    <!-- Breadcrumb & Back -->
    <div class="flex items-center gap-2 text-xs text-slate-400">
        <a href="{{ route('master.study-programs.index') }}" class="hover:text-white transition flex items-center gap-1">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            <span>Kembali ke Daftar Prodi</span>
        </a>
        <span>/</span>
        <span class="text-white font-medium">Edit: {{ $studyProgram->name }}</span>
    </div>

    <!-- Form Card -->
    <div class="rounded-2xl bg-slate-900/80 border border-slate-800 p-6 sm:p-8 shadow-xl">
        <div class="border-b border-slate-800 pb-5 mb-6 flex items-center justify-between">
            <div>
                <h2 class="text-lg font-bold text-white tracking-tight">Edit Program Studi</h2>
                <p class="text-xs text-slate-400 mt-1">Perbarui informasi data program studi {{ $studyProgram->code }}.</p>
            </div>
            <span class="px-2.5 py-1 rounded-full text-xs font-mono font-semibold {{ $studyProgram->is_active ? 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/20' : 'bg-rose-500/10 text-rose-400 border border-rose-500/20' }}">
                {{ $studyProgram->is_active ? 'Aktif' : 'Non-Aktif' }}
            </span>
        </div>

        <form action="{{ route('master.study-programs.update', $studyProgram) }}" method="POST" class="space-y-5" onsubmit="this.querySelector('button[type=submit]').disabled=true; this.querySelector('button[type=submit]').innerText='Menyimpan Perubahan...';">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <!-- Kode Prodi -->
                <div>
                    <label for="code" class="block text-xs font-semibold text-slate-300 mb-1.5">
                        Kode Program Studi <span class="text-rose-400">*</span>
                    </label>
                    <input type="text" id="code" name="code" value="{{ old('code', $studyProgram->code) }}" required maxlength="20"
                        class="w-full px-3.5 py-2.5 bg-slate-950 border @error('code') border-rose-500 @else border-slate-800 @enderror rounded-xl text-xs text-white placeholder-slate-500 font-mono focus:outline-none focus:ring-2 focus:ring-blue-500 transition">
                    @error('code')
                        <p class="text-[11px] text-rose-400 mt-1 font-medium">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Jenjang Pendidikan -->
                <div>
                    <label for="degree_level" class="block text-xs font-semibold text-slate-300 mb-1.5">
                        Jenjang Pendidikan <span class="text-rose-400">*</span>
                    </label>
                    <select id="degree_level" name="degree_level" required
                        class="w-full px-3.5 py-2.5 bg-slate-950 border @error('degree_level') border-rose-500 @else border-slate-800 @enderror rounded-xl text-xs text-white focus:outline-none focus:ring-2 focus:ring-blue-500 transition">
                        <option value="S1" {{ old('degree_level', $studyProgram->degree_level) === 'S1' ? 'selected' : '' }}>S1 - Sarjana</option>
                        <option value="D4" {{ old('degree_level', $studyProgram->degree_level) === 'D4' ? 'selected' : '' }}>D4 - Sarjana Terapan</option>
                        <option value="D3" {{ old('degree_level', $studyProgram->degree_level) === 'D3' ? 'selected' : '' }}>D3 - Ahli Madya</option>
                        <option value="S2" {{ old('degree_level', $studyProgram->degree_level) === 'S2' ? 'selected' : '' }}>S2 - Magister</option>
                    </select>
                    @error('degree_level')
                        <p class="text-[11px] text-rose-400 mt-1 font-medium">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Nama Program Studi -->
            <div>
                <label for="name" class="block text-xs font-semibold text-slate-300 mb-1.5">
                    Nama Program Studi <span class="text-rose-400">*</span>
                </label>
                <input type="text" id="name" name="name" value="{{ old('name', $studyProgram->name) }}" required
                    class="w-full px-3.5 py-2.5 bg-slate-950 border @error('name') border-rose-500 @else border-slate-800 @enderror rounded-xl text-xs text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-blue-500 transition">
                @error('name')
                    <p class="text-[11px] text-rose-400 mt-1 font-medium">{{ $message }}</p>
                @enderror
            </div>

            <!-- Fakultas -->
            <div>
                <label for="faculty" class="block text-xs font-semibold text-slate-300 mb-1.5">
                    Fakultas <span class="text-rose-400">*</span>
                </label>
                <input type="text" id="faculty" name="faculty" value="{{ old('faculty', $studyProgram->faculty) }}" required
                    class="w-full px-3.5 py-2.5 bg-slate-950 border @error('faculty') border-rose-500 @else border-slate-800 @enderror rounded-xl text-xs text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-blue-500 transition">
                @error('faculty')
                    <p class="text-[11px] text-rose-400 mt-1 font-medium">{{ $message }}</p>
                @enderror
            </div>

            <!-- Deskripsi -->
            <div>
                <label for="description" class="block text-xs font-semibold text-slate-300 mb-1.5">
                    Deskripsi / Catatan Singkat
                </label>
                <textarea id="description" name="description" rows="3"
                    class="w-full px-3.5 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-xs text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-blue-500 transition">{{ old('description', $studyProgram->description) }}</textarea>
                @error('description')
                    <p class="text-[11px] text-rose-400 mt-1 font-medium">{{ $message }}</p>
                @enderror
            </div>

            <!-- Status Aktif Checkbox -->
            <div class="flex items-center gap-3 p-3.5 rounded-xl bg-slate-950/60 border border-slate-800">
                <input type="checkbox" id="is_active" name="is_active" value="1" {{ old('is_active', $studyProgram->is_active) ? 'checked' : '' }}
                    class="w-4 h-4 rounded text-blue-600 bg-slate-900 border-slate-700 focus:ring-blue-500">
                <div>
                    <label for="is_active" class="text-xs font-semibold text-white block cursor-pointer">
                        Status Aktif
                    </label>
                    <p class="text-[11px] text-slate-400">Centang untuk mengaktifkan prodi dalam pemilihan pendaftaran dan mata kuliah magang.</p>
                </div>
            </div>

            <!-- Submit Button Bar -->
            <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-800">
                <a href="{{ route('master.study-programs.index') }}"
                    class="px-4 py-2.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 text-xs font-semibold transition">
                    Batal
                </a>
                <button type="submit"
                    class="px-5 py-2.5 rounded-xl bg-gradient-to-r from-amber-600 to-orange-600 hover:from-amber-500 hover:to-orange-500 text-white text-xs font-semibold shadow-lg shadow-orange-500/20 transition cursor-pointer">
                    Perbarui Data Prodi
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
