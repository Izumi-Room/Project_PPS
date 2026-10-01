@extends('layouts.app')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-xl font-bold text-white tracking-tight flex items-center gap-2">
                <a href="{{ route('conversions.index') }}" class="p-1.5 rounded-lg text-slate-400 hover:text-white hover:bg-slate-800 transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                </a>
                Form Pengajuan Konversi Mata Kuliah
            </h2>
            <p class="text-xs text-slate-400 mt-1">
                Pilih pendaftaran magang dan mata kuliah yang akan dikonversi.
            </p>
        </div>
    </div>

    <form method="POST" action="{{ route('conversions.store') }}" class="space-y-6">
        @csrf

        <div class="p-6 rounded-2xl bg-slate-900 border border-slate-800 space-y-6">
            <!-- 1. Pendaftaran Magang -->
            <div>
                <label for="internship_application_id" class="block text-xs font-semibold text-slate-300 mb-2">
                    Pendaftaran Magang yang Disetujui <span class="text-rose-400">*</span>
                </label>
                <select name="internship_application_id" id="internship_application_id" required
                    class="w-full px-4 py-2.5 rounded-xl bg-slate-950 border border-slate-800 text-white text-xs focus:ring-2 focus:ring-blue-500 focus:outline-none">
                    @foreach ($applications as $app)
                        <option value="{{ $app->id }}" {{ old('internship_application_id') == $app->id ? 'selected' : '' }}>
                            {{ $app->partnerInstitution->name }} (Periode: {{ $app->internshipPeriod->name ?? '-' }}) — Dosbing: {{ $app->advisor->name ?? '-' }}
                        </option>
                    @endforeach
                </select>
                @error('internship_application_id')
                    <p class="text-[11px] text-rose-400 mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- 2. Mata Kuliah Tujuan -->
            <div>
                <label for="course_id" class="block text-xs font-semibold text-slate-300 mb-2">
                    Mata Kuliah Tujuan Konversi <span class="text-rose-400">*</span>
                </label>
                <select name="course_id" id="course_id" required
                    class="w-full px-4 py-2.5 rounded-xl bg-slate-950 border border-slate-800 text-white text-xs focus:ring-2 focus:ring-blue-500 focus:outline-none">
                    <option value="">-- Pilih Mata Kuliah --</option>
                    @foreach ($courses as $c)
                        <option value="{{ $c->id }}" {{ old('course_id') == $c->id ? 'selected' : '' }}>
                            [{{ $c->code }}] {{ $c->name }} ({{ $c->credits }} SKS - Semester {{ $c->semester }})
                        </option>
                    @endforeach
                </select>
                @error('course_id')
                    <p class="text-[11px] text-rose-400 mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- 3. Rencana Kegiatan & Relevansi -->
            <div>
                <label for="activity_plan" class="block text-xs font-semibold text-slate-300 mb-2">
                    Kegiatan / Rencana Kegiatan yang Relevan dengan Mata Kuliah <span class="text-rose-400">*</span>
                </label>
                <textarea name="activity_plan" id="activity_plan" rows="5" required
                    placeholder="Uraikan keterkaitan rencana aktivitas proyek atau pekerjaan magang Anda dengan capaian pembelajaran (CPMK) mata kuliah ini..."
                    class="w-full px-4 py-3 rounded-xl bg-slate-950 border border-slate-800 text-white text-xs focus:ring-2 focus:ring-blue-500 focus:outline-none leading-relaxed">{{ old('activity_plan') }}</textarea>
                <p class="text-[11px] text-slate-500 mt-1">
                    Jelaskan secara rinci modul kerja, tools, teknologi, atau tugas lapangan yang sesuai dengan materi mata kuliah terkait.
                </p>
                @error('activity_plan')
                    <p class="text-[11px] text-rose-400 mt-1">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <div class="flex items-center justify-end gap-3">
            <a href="{{ route('conversions.index') }}"
                class="px-4 py-2.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 text-xs font-semibold transition">
                Batal
            </a>
            <button type="submit"
                class="px-6 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-500 text-white text-xs font-semibold transition shadow-lg shadow-blue-600/30">
                Kirim Pengajuan Konversi
            </button>
        </div>
    </form>
</div>
@endsection
