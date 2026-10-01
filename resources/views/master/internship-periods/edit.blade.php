@extends('layouts.app', ['title' => 'Edit Periode - ' . $internshipPeriod->name])

@section('content')
<div class="max-w-3xl mx-auto space-y-6">

    <!-- Breadcrumb & Back -->
    <div class="flex items-center gap-2 text-xs text-slate-400">
        <a href="{{ route('master.internship-periods.index') }}" class="hover:text-white transition flex items-center gap-1">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            <span>Kembali ke Daftar Periode</span>
        </a>
        <span>/</span>
        <span class="text-white font-medium">Edit: {{ $internshipPeriod->name }}</span>
    </div>

    <!-- Form Card -->
    <div class="rounded-2xl bg-slate-900/80 border border-slate-800 p-6 sm:p-8 shadow-xl">
        <div class="border-b border-slate-800 pb-5 mb-6 flex items-center justify-between">
            <div>
                <h2 class="text-lg font-bold text-white tracking-tight">Edit Periode Magang</h2>
                <p class="text-xs text-slate-400 mt-1">Perbarui jadwal tanggal pelaksanaan atau status buka pendaftaran.</p>
            </div>
            <span class="px-2.5 py-1 rounded-full text-xs font-mono font-semibold {{ $internshipPeriod->is_active ? 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/20' : 'bg-rose-500/10 text-rose-400 border border-rose-500/20' }}">
                {{ $internshipPeriod->is_active ? '● Pendaftaran Dibuka' : '○ Non-Aktif' }}
            </span>
        </div>

        <form action="{{ route('master.internship-periods.update', $internshipPeriod) }}" method="POST" class="space-y-5" onsubmit="this.querySelector('button[type=submit]').disabled=true; this.querySelector('button[type=submit]').innerText='Menyimpan Perubahan...';">
            @csrf
            @method('PUT')

            <!-- Nama Periode -->
            <div>
                <label for="name" class="block text-xs font-semibold text-slate-300 mb-1.5">
                    Nama Periode Magang <span class="text-rose-400">*</span>
                </label>
                <input type="text" id="name" name="name" value="{{ old('name', $internshipPeriod->name) }}" required
                    class="w-full px-3.5 py-2.5 bg-slate-950 border @error('name') border-rose-500 @else border-slate-800 @enderror rounded-xl text-xs text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-blue-500 transition">
                @error('name')
                    <p class="text-[11px] text-rose-400 mt-1 font-medium">{{ $message }}</p>
                @enderror
            </div>

            <!-- Tahun Akademik & Jenis Semester -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="academic_year" class="block text-xs font-semibold text-slate-300 mb-1.5">
                        Tahun Akademik <span class="text-rose-400">*</span>
                    </label>
                    <input type="text" id="academic_year" name="academic_year" value="{{ old('academic_year', $internshipPeriod->academic_year) }}" required
                        class="w-full px-3.5 py-2.5 bg-slate-950 border @error('academic_year') border-rose-500 @else border-slate-800 @enderror rounded-xl text-xs text-white font-mono focus:outline-none focus:ring-2 focus:ring-blue-500 transition">
                    @error('academic_year')
                        <p class="text-[11px] text-rose-400 mt-1 font-medium">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="semester_type" class="block text-xs font-semibold text-slate-300 mb-1.5">
                        Jenis Semester <span class="text-rose-400">*</span>
                    </label>
                    <select id="semester_type" name="semester_type" required
                        class="w-full px-3.5 py-2.5 bg-slate-950 border @error('semester_type') border-rose-500 @else border-slate-800 @enderror rounded-xl text-xs text-white focus:outline-none focus:ring-2 focus:ring-blue-500 transition">
                        <option value="GENAP" {{ old('semester_type', $internshipPeriod->semester_type) === 'GENAP' ? 'selected' : '' }}>Semester GENAP</option>
                        <option value="GANJIL" {{ old('semester_type', $internshipPeriod->semester_type) === 'GANJIL' ? 'selected' : '' }}>Semester GANJIL</option>
                    </select>
                    @error('semester_type')
                        <p class="text-[11px] text-rose-400 mt-1 font-medium">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Tanggal Mulai & Tanggal Selesai -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="start_date" class="block text-xs font-semibold text-slate-300 mb-1.5">
                        Tanggal Mulai Pelaksanaan <span class="text-rose-400">*</span>
                    </label>
                    <input type="date" id="start_date" name="start_date" value="{{ old('start_date', $internshipPeriod->start_date->format('Y-m-d')) }}" required
                        class="w-full px-3.5 py-2.5 bg-slate-950 border @error('start_date') border-rose-500 @else border-slate-800 @enderror rounded-xl text-xs text-white font-mono focus:outline-none focus:ring-2 focus:ring-blue-500 transition">
                    @error('start_date')
                        <p class="text-[11px] text-rose-400 mt-1 font-medium">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="end_date" class="block text-xs font-semibold text-slate-300 mb-1.5">
                        Tanggal Selesai Pelaksanaan <span class="text-rose-400">*</span>
                    </label>
                    <input type="date" id="end_date" name="end_date" value="{{ old('end_date', $internshipPeriod->end_date->format('Y-m-d')) }}" required
                        class="w-full px-3.5 py-2.5 bg-slate-950 border @error('end_date') border-rose-500 @else border-slate-800 @enderror rounded-xl text-xs text-white font-mono focus:outline-none focus:ring-2 focus:ring-blue-500 transition">
                    @error('end_date')
                        <p class="text-[11px] text-rose-400 mt-1 font-medium">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Deskripsi -->
            <div>
                <label for="description" class="block text-xs font-semibold text-slate-300 mb-1.5">
                    Keterangan & Petunjuk Pendaftaran
                </label>
                <textarea id="description" name="description" rows="3"
                    class="w-full px-3.5 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-xs text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-blue-500 transition">{{ old('description', $internshipPeriod->description) }}</textarea>
                @error('description')
                    <p class="text-[11px] text-rose-400 mt-1 font-medium">{{ $message }}</p>
                @enderror
            </div>

            <!-- Status Aktif Checkbox -->
            <div class="flex items-center gap-3 p-3.5 rounded-xl bg-slate-950/60 border border-slate-800">
                <input type="checkbox" id="is_active" name="is_active" value="1" {{ old('is_active', $internshipPeriod->is_active) ? 'checked' : '' }}
                    class="w-4 h-4 rounded text-emerald-600 bg-slate-900 border-slate-700 focus:ring-emerald-500">
                <div>
                    <label for="is_active" class="text-xs font-semibold text-emerald-400 block cursor-pointer">
                        Jadikan Sebagai Periode Pendaftaran Aktif (Open Registration)
                    </label>
                    <p class="text-[11px] text-slate-400">Jika aktif, periode ini akan menjadi satu-satunya periode yang dapat dipilih mahasiswa baru saat registrasi.</p>
                </div>
            </div>

            <!-- Buttons -->
            <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-800">
                <a href="{{ route('master.internship-periods.index') }}"
                    class="px-4 py-2.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 text-xs font-semibold transition">
                    Batal
                </a>
                <button type="submit"
                    class="px-5 py-2.5 rounded-xl bg-gradient-to-r from-amber-600 to-orange-600 hover:from-amber-500 hover:to-orange-500 text-white text-xs font-semibold shadow-lg shadow-orange-500/20 transition cursor-pointer">
                    Perbarui Periode
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
