@extends('layouts.app')

@section('content')
<div class="max-w-3xl mx-auto space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-xl font-bold text-white tracking-tight flex items-center gap-2">
                <a href="{{ route('logbooks.index') }}" class="p-1.5 rounded-lg text-slate-400 hover:text-white hover:bg-slate-800 transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                </a>
                Catat Logbook Mingguan Magang
            </h2>
            <p class="text-xs text-slate-400 mt-1">
                Dokumentasikan aktivitas harian / mingguan serta lampirkan bukti kegiatan pelaksanaan magang.
            </p>
        </div>
    </div>

    <form method="POST" action="{{ route('logbooks.store') }}" enctype="multipart/form-data" class="space-y-6">
        @csrf

        <div class="p-6 rounded-2xl bg-slate-900 border border-slate-800 space-y-6">
            <!-- 1. Pendaftaran Magang -->
            <div>
                <label for="internship_application_id" class="block text-xs font-semibold text-slate-300 mb-2">
                    Program Magang Terkait <span class="text-rose-400">*</span>
                </label>
                <select name="internship_application_id" id="internship_application_id" required
                    class="w-full px-4 py-2.5 rounded-xl bg-slate-950 border border-slate-800 text-white text-xs focus:ring-2 focus:ring-amber-500 focus:outline-none">
                    @foreach ($applications as $app)
                        <option value="{{ $app->id }}" {{ (old('internship_application_id', $defaultAppId) == $app->id) ? 'selected' : '' }}>
                            {{ $app->partnerInstitution->name }} (Dosbing: {{ $app->advisor->name ?? 'Belum Ditentukan' }})
                        </option>
                    @endforeach
                </select>
                @error('internship_application_id')
                    <p class="text-[11px] text-rose-400 mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- 2. Minggu & Tanggal -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="week_number" class="block text-xs font-semibold text-slate-300 mb-2">
                        Minggu Ke- <span class="text-rose-400">*</span>
                    </label>
                    <input type="number" name="week_number" id="week_number" min="1" max="52" required
                        value="{{ old('week_number', $nextWeek) }}"
                        class="w-full px-4 py-2.5 rounded-xl bg-slate-950 border border-slate-800 text-white text-xs focus:ring-2 focus:ring-amber-500 focus:outline-none font-mono">
                    @error('week_number')
                        <p class="text-[11px] text-rose-400 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="activity_date" class="block text-xs font-semibold text-slate-300 mb-2">
                        Tanggal Aktivitas <span class="text-rose-400">*</span>
                    </label>
                    <input type="date" name="activity_date" id="activity_date" required
                        value="{{ old('activity_date', date('Y-m-d')) }}"
                        class="w-full px-4 py-2.5 rounded-xl bg-slate-950 border border-slate-800 text-white text-xs focus:ring-2 focus:ring-amber-500 focus:outline-none font-mono">
                    @error('activity_date')
                        <p class="text-[11px] text-rose-400 mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- 3. Judul Kegiatan -->
            <div>
                <label for="activity_title" class="block text-xs font-semibold text-slate-300 mb-2">
                    Kegiatan / Topik Aktivitas <span class="text-rose-400">*</span>
                </label>
                <input type="text" name="activity_title" id="activity_title" required
                    placeholder="Contoh: Analisis Kebutuhan Sistem & Deployment Modul Otentikasi"
                    value="{{ old('activity_title') }}"
                    class="w-full px-4 py-2.5 rounded-xl bg-slate-950 border border-slate-800 text-white text-xs focus:ring-2 focus:ring-amber-500 focus:outline-none">
                @error('activity_title')
                    <p class="text-[11px] text-rose-400 mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- 4. Rincian Deskripsi -->
            <div>
                <label for="description" class="block text-xs font-semibold text-slate-300 mb-2">
                    Deskripsi Rincian Pekerjaan / Tugas <span class="text-rose-400">*</span>
                </label>
                <textarea name="description" id="description" rows="6" required
                    placeholder="Uraikan langkah-langkah tugas, kendala yang dihadapi, solusi yang dilakukan, serta capaian selama minggu ini..."
                    class="w-full px-4 py-3 rounded-xl bg-slate-950 border border-slate-800 text-white text-xs focus:ring-2 focus:ring-amber-500 focus:outline-none leading-relaxed">{{ old('description') }}</textarea>
                @error('description')
                    <p class="text-[11px] text-rose-400 mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- 5. Lampiran Bukti / Attachment -->
            <div>
                <label for="attachment" class="block text-xs font-semibold text-slate-300 mb-2">
                    Lampiran Bukti Kegiatan (Dokumentasi / Screenshot / File Laporan)
                </label>
                <input type="file" name="attachment" id="attachment"
                    accept=".pdf,.jpg,.jpeg,.png,.doc,.docx"
                    class="w-full px-4 py-2 rounded-xl bg-slate-950 border border-slate-800 text-slate-400 text-xs focus:outline-none file:mr-4 file:py-1 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-amber-600 file:text-white hover:file:bg-amber-500 cursor-pointer">
                <p class="text-[11px] text-slate-500 mt-1">Format: PDF, JPG, PNG, DOC, DOCX (Maksimal 5MB).</p>
                @error('attachment')
                    <p class="text-[11px] text-rose-400 mt-1">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <div class="flex items-center justify-end gap-3">
            <a href="{{ route('logbooks.index') }}"
                class="px-4 py-2.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 text-xs font-semibold transition">
                Batal
            </a>
            <button type="submit"
                class="px-6 py-2.5 rounded-xl bg-amber-600 hover:bg-amber-500 text-white text-xs font-semibold transition shadow-lg shadow-amber-600/30">
                Simpan Logbook Mingguan
            </button>
        </div>
    </form>
</div>
@endsection
