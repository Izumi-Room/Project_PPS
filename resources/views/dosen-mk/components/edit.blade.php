@extends('layouts.app')

@section('content')
<div class="max-w-3xl mx-auto space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-xl font-bold text-white tracking-tight flex items-center gap-2">
                <a href="{{ route('dosen-mk.components.index', ['course_id' => $component->course_id]) }}" class="p-1.5 rounded-lg text-slate-400 hover:text-white hover:bg-slate-800 transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                </a>
                Edit Komponen Pengumpulan
            </h2>
            <p class="text-xs text-slate-400 mt-1">
                Perbarui konfigurasi batas waktu, bobot, atau instruksi pengumpulan tugas.
            </p>
        </div>
    </div>

    <form method="POST" action="{{ route('dosen-mk.components.update', $component->id) }}" class="space-y-6">
        @csrf
        @method('PUT')

        <div class="p-6 rounded-2xl bg-slate-900 border border-slate-800 space-y-6">
            <div>
                <label class="block text-xs font-semibold text-slate-400 mb-1">Mata Kuliah</label>
                <div class="p-3 rounded-xl bg-slate-950 border border-slate-800 text-xs font-bold text-white">
                    {{ $component->course->name }} ({{ $component->course->code }})
                </div>
            </div>

            <div>
                <label for="name" class="block text-xs font-semibold text-slate-300 mb-2">
                    Nama Komponen Pengumpulan <span class="text-rose-400">*</span>
                </label>
                <input type="text" name="name" id="name" required
                    value="{{ old('name', $component->name) }}"
                    class="w-full px-4 py-2.5 rounded-xl bg-slate-950 border border-slate-800 text-white text-xs focus:ring-2 focus:ring-teal-500 focus:outline-none">
                @error('name')
                    <p class="text-[11px] text-rose-400 mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="submission_type" class="block text-xs font-semibold text-slate-300 mb-2">
                        Jenis Pengumpulan <span class="text-rose-400">*</span>
                    </label>
                    <select name="submission_type" id="submission_type" required
                        class="w-full px-4 py-2.5 rounded-xl bg-slate-950 border border-slate-800 text-white text-xs focus:ring-2 focus:ring-teal-500 focus:outline-none">
                        <option value="FILE" {{ old('submission_type', $component->submission_type) == 'FILE' ? 'selected' : '' }}>Berkas / Dokumen File (PDF/ZIP/DOCX)</option>
                        <option value="LINK" {{ old('submission_type', $component->submission_type) == 'LINK' ? 'selected' : '' }}>Tautan Link (GitHub / Drive / Video Demo)</option>
                        <option value="FILE_OR_LINK" {{ old('submission_type', $component->submission_type) == 'FILE_OR_LINK' ? 'selected' : '' }}>Fleksibel (File atau Link)</option>
                        <option value="PROJECT_URL" {{ old('submission_type', $component->submission_type) == 'PROJECT_URL' ? 'selected' : '' }}>URL Project Deployment</option>
                    </select>
                </div>

                <div>
                    <label for="weight" class="block text-xs font-semibold text-slate-300 mb-2">
                        Bobot Penilaian (%) <span class="text-rose-400">*</span>
                    </label>
                    <input type="number" name="weight" id="weight" min="1" max="100" required
                        value="{{ old('weight', $component->weight) }}"
                        class="w-full px-4 py-2.5 rounded-xl bg-slate-950 border border-slate-800 text-white text-xs focus:ring-2 focus:ring-teal-500 focus:outline-none font-mono">
                    @error('weight')
                        <p class="text-[11px] text-rose-400 mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="deadline" class="block text-xs font-semibold text-slate-300 mb-2">
                        Batas Waktu (Deadline) <span class="text-rose-400">*</span>
                    </label>
                    <input type="datetime-local" name="deadline" id="deadline" required
                        value="{{ old('deadline', $component->deadline->format('Y-m-d\TH:i')) }}"
                        class="w-full px-4 py-2.5 rounded-xl bg-slate-950 border border-slate-800 text-white text-xs focus:ring-2 focus:ring-teal-500 focus:outline-none font-mono">
                    @error('deadline')
                        <p class="text-[11px] text-rose-400 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex items-center pt-6">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="hidden" name="is_required" value="0">
                        <input type="checkbox" name="is_required" value="1" {{ old('is_required', $component->is_required ? '1' : '0') == '1' ? 'checked' : '' }}
                            class="w-4 h-4 rounded text-teal-600 focus:ring-teal-500 border-slate-700 bg-slate-950">
                        <span class="text-xs font-semibold text-slate-200">Wajib dikumpulkan mahasiswa (Mandatory)</span>
                    </label>
                </div>
            </div>

            <div>
                <label for="instructions" class="block text-xs font-semibold text-slate-300 mb-2">
                    Petunjuk Pengerjaan / Format Pengumpulan (Opsional)
                </label>
                <textarea name="instructions" id="instructions" rows="4"
                    class="w-full px-4 py-3 rounded-xl bg-slate-950 border border-slate-800 text-white text-xs focus:ring-2 focus:ring-teal-500 focus:outline-none leading-relaxed">{{ old('instructions', $component->instructions) }}</textarea>
            </div>

            <div>
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="hidden" name="is_active" value="0">
                    <input type="checkbox" name="is_active" value="1" {{ old('is_active', $component->is_active ? '1' : '0') == '1' ? 'checked' : '' }}
                        class="w-4 h-4 rounded text-emerald-600 focus:ring-emerald-500 border-slate-700 bg-slate-950">
                    <span class="text-xs font-semibold text-slate-200">Komponen Aktif (Dapat dilihat dan dikumpulkan mahasiswa)</span>
                </label>
            </div>
        </div>

        <div class="flex items-center justify-end gap-3">
            <a href="{{ route('dosen-mk.components.index', ['course_id' => $component->course_id]) }}"
                class="px-4 py-2.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 text-xs font-semibold transition">
                Batal
            </a>
            <button type="submit"
                class="px-6 py-2.5 rounded-xl bg-teal-600 hover:bg-teal-500 text-white text-xs font-semibold transition shadow-lg shadow-teal-600/30">
                Perbarui Komponen Tugas
            </button>
        </div>
    </form>
</div>
@endsection
