@extends('layouts.app')

@section('title', 'Konfigurasi & Jadwal Seminar')

@section('content')
<div class="space-y-6 max-w-4xl mx-auto">
    <!-- Breadcrumb & Back -->
    <div class="flex items-center justify-between">
        <a href="{{ route('dosen-mk.seminars.index') }}" class="inline-flex items-center gap-2 text-sm text-slate-400 hover:text-white transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" /></svg>
            Kembali ke Daftar Seminar MK
        </a>
        @if($seminar)
            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold border {{ $seminar->statusBadgeColor() }}">
                {{ $seminar->statusLabel() }}
            </span>
        @endif
    </div>

    <!-- Student & Course Header Card -->
    <div class="bg-slate-900/60 backdrop-blur-md border border-slate-800 rounded-2xl p-6 shadow-xl">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <span class="text-xs font-bold text-teal-400 uppercase tracking-wider">Mahasiswa Magang</span>
                <h2 class="text-xl font-bold text-white mt-1">{{ $conversion->student?->name ?? 'Mahasiswa' }}</h2>
                <p class="text-xs text-slate-400 mt-0.5">NIM: {{ $conversion->student?->identifier_number ?? '-' }} &bull; {{ $conversion->student?->studyProgram?->name ?? '-' }}</p>
                <p class="text-xs text-slate-400 mt-1">Dosen Pembimbing: <strong class="text-slate-300">{{ $conversion->internshipApplication?->advisor?->name ?? 'Belum ditentukan' }}</strong></p>
            </div>
            <div>
                <span class="text-xs font-bold text-teal-400 uppercase tracking-wider">Mata Kuliah Konversi</span>
                <h2 class="text-xl font-bold text-white mt-1">{{ $conversion->course?->name ?? '-' }}</h2>
                <p class="text-xs text-slate-400 mt-0.5">Kode: {{ $conversion->course?->code ?? '-' }} &bull; SKS: {{ $conversion->course?->credits ?? 0 }}</p>
            </div>
        </div>
    </div>

    @if($seminar && $seminar->isRejected())
        <!-- Alert Wadek Rejection -->
        <div class="bg-rose-950/40 border border-rose-800/60 rounded-2xl p-6">
            <div class="flex items-start gap-4">
                <div class="p-3 rounded-xl bg-rose-900/60 text-rose-300">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>
                </div>
                <div>
                    <h3 class="text-base font-bold text-rose-200">Jadwal Seminar Ditolak oleh Wadek 1</h3>
                    <p class="text-sm text-rose-300/90 mt-1 whitespace-pre-line">{{ $seminar->rejection_reason }}</p>
                    <p class="text-xs text-rose-400/80 mt-3">Silakan gunakan formulir penjadwalan ulang di bawah ini untuk menetapkan waktu atau tempat baru.</p>
                </div>
            </div>
        </div>
    @endif

    @if($seminar && $seminar->isApproved())
        <!-- Wadek 1 Approval & Conduct Action -->
        <div class="bg-emerald-950/40 border border-emerald-800/60 rounded-2xl p-6">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div>
                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-emerald-500/20 text-emerald-300 border border-emerald-500/30 mb-2">
                        Persetujuan Wadek 1 Selesai
                    </span>
                    <h3 class="text-lg font-bold text-white">Seminar Telah Disetujui & Siap Dilaksanakan</h3>
                    <p class="text-xs text-slate-300 mt-1">
                        Jadwal: <strong>{{ $seminar->scheduled_date->format('d/m/Y') }}</strong> pukul <strong>{{ $seminar->scheduled_time }}</strong> ({{ $seminar->location_or_link }}).
                    </p>
                </div>
                <form action="{{ route('dosen-mk.seminars.conduct', $seminar) }}" method="POST">
                    @csrf
                    <button type="submit" class="px-5 py-2.5 rounded-xl font-medium bg-emerald-600 hover:bg-emerald-500 text-white shadow-lg shadow-emerald-600/30 transition text-sm flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                        <span>Tandai Seminar Dilaksanakan</span>
                    </button>
                </form>
            </div>
        </div>
    @elseif($seminar && $seminar->isConducted())
        <div class="bg-teal-950/30 border border-teal-800/40 rounded-2xl p-6">
            <div class="flex items-center gap-3">
                <div class="p-2.5 rounded-xl bg-teal-900/60 text-teal-300">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                </div>
                <div>
                    <h3 class="text-base font-bold text-white">Seminar Telah Dilaksanakan</h3>
                    <p class="text-xs text-slate-400 mt-0.5">Dilaksanakan pada: {{ $seminar->conducted_at?->format('d F Y, H:i') }}</p>
                </div>
            </div>
        </div>
    @endif

    <!-- Form Section -->
    <div class="bg-slate-900/60 backdrop-blur-md border border-slate-800 rounded-2xl p-6 shadow-xl" x-data="{ isRequired: '{{ old('is_required', $seminar && !$seminar->is_required ? '0' : '1') }}' }">
        <h3 class="text-lg font-bold text-white mb-1">
            {{ $seminar && $seminar->is_required ? 'Atur Ulang / Jadwalkan Seminar' : 'Penetapan Kebutuhan Seminar Magang' }}
        </h3>
        <p class="text-xs text-slate-400 mb-6">
            Dosen MK dapat menentukan apakah mahasiswa wajib mengikuti seminar magang untuk mata kuliah ini.
        </p>

        @if($seminar && $seminar->is_required && in_array($seminar->status, [\App\Models\InternshipSeminar::STATUS_SCHEDULED, \App\Models\InternshipSeminar::STATUS_REJECTED, \App\Models\InternshipSeminar::STATUS_ACKNOWLEDGED]))
            <!-- Rescheduling Form -->
            <form action="{{ route('dosen-mk.seminars.reschedule', $seminar) }}" method="POST" class="space-y-5">
                @csrf
                <div class="p-4 bg-teal-500/10 border border-teal-500/20 rounded-xl text-xs text-teal-300">
                    Pembaruan jadwal akan mengatur ulang alur persetujuan dan mengirimkan notifikasi <strong>Jadwal Berubah</strong> ke Mahasiswa, Dosen Pembimbing, Kaprodi, dan Wadek 1.
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-300 mb-2">Tanggal Pelaksanaan *</label>
                        <input type="date" name="scheduled_date" value="{{ old('scheduled_date', $seminar->scheduled_date?->format('Y-m-d')) }}" required
                            class="w-full bg-slate-800/80 border border-slate-700 rounded-xl px-4 py-2.5 text-sm text-white focus:outline-none focus:border-teal-500">
                        @error('scheduled_date') <span class="text-xs text-rose-400 mt-1 block">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-300 mb-2">Waktu Pelaksanaan *</label>
                        <input type="text" name="scheduled_time" placeholder="Contoh: 09:00 - 11:30 WIB" value="{{ old('scheduled_time', $seminar->scheduled_time) }}" required
                            class="w-full bg-slate-800/80 border border-slate-700 rounded-xl px-4 py-2.5 text-sm text-white focus:outline-none focus:border-teal-500">
                        @error('scheduled_time') <span class="text-xs text-rose-400 mt-1 block">{{ $message }}</span> @enderror
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-2">Tempat / Tautan Pertemuan *</label>
                    <input type="text" name="location_or_link" placeholder="Ruang Seminar 204 atau tautan Zoom/Google Meet" value="{{ old('location_or_link', $seminar->location_or_link) }}" required
                        class="w-full bg-slate-800/80 border border-slate-700 rounded-xl px-4 py-2.5 text-sm text-white focus:outline-none focus:border-teal-500">
                    @error('location_or_link') <span class="text-xs text-rose-400 mt-1 block">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-2">Informasi / Petunjuk Seminar (Opsional)</label>
                    <textarea name="information" rows="3" placeholder="Informasi format presentasi, durasi, atau berkas yang harus disiapkan..."
                        class="w-full bg-slate-800/80 border border-slate-700 rounded-xl p-3 text-sm text-white focus:outline-none focus:border-teal-500">{{ old('information', $seminar->information) }}</textarea>
                    @error('information') <span class="text-xs text-rose-400 mt-1 block">{{ $message }}</span> @enderror
                </div>

                <div class="flex items-center justify-end gap-3 pt-3">
                    <button type="submit" class="px-5 py-2.5 rounded-xl font-medium bg-teal-600 hover:bg-teal-500 text-white shadow-lg shadow-teal-600/30 transition text-sm">
                        Simpan Perubahan & Notifikasi
                    </button>
                </div>
            </form>
        @else
            <!-- Initial Decision Form -->
            <form action="{{ route('dosen-mk.seminars.decide') }}" method="POST" class="space-y-6">
                @csrf
                <input type="hidden" name="course_conversion_id" value="{{ $conversion->id }}">

                <!-- YES / NO Radio -->
                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-3">Apakah Seminar Magang Diperlukan? *</label>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <label class="flex items-start gap-3 p-4 rounded-xl border cursor-pointer transition"
                            :class="isRequired === '1' ? 'bg-teal-600/10 border-teal-500 text-white' : 'bg-slate-800/40 border-slate-700 text-slate-400'">
                            <input type="radio" name="is_required" value="1" x-model="isRequired" class="mt-1 text-teal-600 focus:ring-teal-500">
                            <div>
                                <span class="block text-sm font-semibold">YES — Seminar Diperlukan</span>
                                <span class="text-xs text-slate-400">Mahasiswa wajib mengikuti seminar terjadwal untuk MK ini.</span>
                            </div>
                        </label>

                        <label class="flex items-start gap-3 p-4 rounded-xl border cursor-pointer transition"
                            :class="isRequired === '0' ? 'bg-slate-700/30 border-slate-500 text-white' : 'bg-slate-800/40 border-slate-700 text-slate-400'">
                            <input type="radio" name="is_required" value="0" x-model="isRequired" class="mt-1 text-slate-600 focus:ring-slate-500">
                            <div>
                                <span class="block text-sm font-semibold">NO — Tidak Diperlukan</span>
                                <span class="text-xs text-slate-400">Status akan menjadi <em>TIDAK DIPERLUKAN</em>.</span>
                            </div>
                        </label>
                    </div>
                    @error('is_required') <span class="text-xs text-rose-400 mt-1 block">{{ $message }}</span> @enderror
                </div>

                <!-- Fields if YES -->
                <div x-show="isRequired === '1'" x-transition class="space-y-5 pt-4 border-t border-slate-800">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-slate-300 mb-2">Tanggal Pelaksanaan *</label>
                            <input type="date" name="scheduled_date" value="{{ old('scheduled_date', $seminar?->scheduled_date?->format('Y-m-d')) }}"
                                class="w-full bg-slate-800/80 border border-slate-700 rounded-xl px-4 py-2.5 text-sm text-white focus:outline-none focus:border-teal-500">
                            @error('scheduled_date') <span class="text-xs text-rose-400 mt-1 block">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-300 mb-2">Waktu Pelaksanaan *</label>
                            <input type="text" name="scheduled_time" placeholder="Contoh: 10:00 - 12:00 WIB" value="{{ old('scheduled_time', $seminar?->scheduled_time) }}"
                                class="w-full bg-slate-800/80 border border-slate-700 rounded-xl px-4 py-2.5 text-sm text-white focus:outline-none focus:border-teal-500">
                            @error('scheduled_time') <span class="text-xs text-rose-400 mt-1 block">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-300 mb-2">Tempat / Tautan Pertemuan *</label>
                        <input type="text" name="location_or_link" placeholder="Ruang Seminar 3 atau tautan Google Meet / Zoom" value="{{ old('location_or_link', $seminar?->location_or_link) }}"
                            class="w-full bg-slate-800/80 border border-slate-700 rounded-xl px-4 py-2.5 text-sm text-white focus:outline-none focus:border-teal-500">
                        @error('location_or_link') <span class="text-xs text-rose-400 mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-300 mb-2">Informasi / Petunjuk Seminar (Opsional)</label>
                        <textarea name="information" rows="3" placeholder="Informasi bagi mahasiswa dan penguji..."
                            class="w-full bg-slate-800/80 border border-slate-700 rounded-xl p-3 text-sm text-white focus:outline-none focus:border-teal-500">{{ old('information', $seminar?->information) }}</textarea>
                        @error('information') <span class="text-xs text-rose-400 mt-1 block">{{ $message }}</span> @enderror
                    </div>
                </div>

                <div class="flex items-center justify-end gap-3 pt-3">
                    <button type="submit" class="px-5 py-2.5 rounded-xl font-medium bg-teal-600 hover:bg-teal-500 text-white shadow-lg shadow-teal-600/30 transition text-sm">
                        Simpan Keputusan Seminar
                    </button>
                </div>
            </form>
        @endif
    </div>
</div>
@endsection
