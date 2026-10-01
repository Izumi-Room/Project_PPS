@extends('layouts.app', ['title' => 'Detail Pendaftaran Magang - ' . $internship->student_name])

@section('content')
<div class="max-w-5xl mx-auto space-y-6">
    <!-- Top Action Nav -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 pb-2 border-b border-slate-800">
        <div>
            <a href="{{ route('internships.index') }}" class="inline-flex items-center gap-1.5 text-xs text-slate-400 hover:text-white transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                <span>Kembali ke Daftar Magang</span>
            </a>
            <h1 class="text-xl sm:text-2xl font-black text-white tracking-tight mt-1">Detail Pendaftaran Magang</h1>
        </div>

        <div class="flex items-center gap-2">
            @if ($internship->isEditableByStudent() && (Auth::id() === $internship->user_id || Auth::user()->hasRole('SUPERADMIN')))
                <a href="{{ route('internships.edit', $internship) }}"
                    class="px-4 py-2 rounded-xl bg-amber-600 hover:bg-amber-500 text-white font-semibold text-xs transition shadow-lg shadow-amber-600/20 flex items-center gap-1.5">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                    </svg>
                    <span>{{ $internship->status === 'TIDAK_LENGKAP' ? 'Perbaiki & Ajukan Ulang' : 'Edit Pengajuan' }}</span>
                </a>
            @endif

            @if (Auth::user()->hasAnyRole(['TU', 'SUPERADMIN']) && $internship->status === 'DIAJUKAN')
                <a href="{{ route('tu.internships.show', $internship) }}"
                    class="px-4 py-2 rounded-xl bg-blue-600 hover:bg-blue-500 text-white font-semibold text-xs transition shadow-lg shadow-blue-600/20">
                    Proses Validasi TU
                </a>
            @endif

            @if (Auth::user()->hasAnyRole(['KAPRODI', 'SUPERADMIN']) && $internship->status === 'LOLOS_TU')
                <a href="{{ route('kaprodi.internships.show', $internship) }}"
                    class="px-4 py-2 rounded-xl bg-purple-600 hover:bg-purple-500 text-white font-semibold text-xs transition shadow-lg shadow-purple-600/20">
                    Proses Verifikasi Kaprodi
                </a>
            @endif

            @if (Auth::user()->hasAnyRole(['WADEK1', 'SUPERADMIN']) && $internship->status === 'VERIFIKASI_KAPRODI')
                <a href="{{ route('wadek1.internships.show', $internship) }}"
                    class="px-4 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white font-semibold text-xs transition shadow-lg shadow-indigo-600/20">
                    Proses Approval Wadek 1
                </a>
            @endif
        </div>
    </div>

    <!-- Status Highlight Card -->
    <div class="bg-slate-900/80 border border-slate-800 rounded-2xl p-5 sm:p-6 shadow-xl relative overflow-hidden">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <span class="text-xs text-slate-400 font-mono uppercase tracking-wider block mb-1">Status Pengajuan</span>
                <div class="flex items-center gap-3">
                    <span class="px-3.5 py-1.5 rounded-full text-xs font-bold border {{ $internship->status_badge_classes }}">
                        {{ $internship->status_label }}
                    </span>
                    <span class="text-xs text-slate-400 font-mono">ID: #APP-{{ str_pad($internship->id, 5, '0', STR_PAD_LEFT) }}</span>
                </div>
            </div>

            <!-- Workflow Progress Tracker -->
            <div class="flex items-center gap-2 overflow-x-auto py-2">
                @php
                    $steps = [
                        ['label' => 'Diajukan', 'reached' => in_array($internship->status, ['DIAJUKAN', 'LOLOS_TU', 'VERIFIKASI_KAPRODI', 'DISETUJUI'])],
                        ['label' => 'Validasi TU', 'reached' => in_array($internship->status, ['LOLOS_TU', 'VERIFIKASI_KAPRODI', 'DISETUJUI'])],
                        ['label' => 'Kaprodi', 'reached' => in_array($internship->status, ['VERIFIKASI_KAPRODI', 'DISETUJUI'])],
                        ['label' => 'Wadek 1', 'reached' => $internship->status === 'DISETUJUI'],
                    ];
                @endphp
                @foreach ($steps as $index => $step)
                    <div class="flex items-center gap-2 flex-shrink-0">
                        <div class="flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-[10px] font-bold font-mono {{ $step['reached'] ? 'bg-emerald-500/20 text-emerald-300 border border-emerald-500/30' : 'bg-slate-800 text-slate-500' }}">
                            @if ($step['reached'])
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                </svg>
                            @else
                                <span class="w-3.5 h-3.5 rounded-full border border-slate-600 flex items-center justify-center text-[9px]">{{ $index + 1 }}</span>
                            @endif
                            <span>{{ $step['label'] }}</span>
                        </div>
                        @if ($index < count($steps) - 1)
                            <div class="w-4 h-0.5 {{ $step['reached'] ? 'bg-emerald-500/40' : 'bg-slate-800' }}"></div>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>

        @if ($internship->status === 'TIDAK_LENGKAP')
            <div class="mt-4 p-3.5 rounded-xl bg-rose-500/10 border border-rose-500/20 text-rose-300 text-xs">
                <strong>Catatan Perbaikan Dokumen:</strong> {{ $internship->review_notes }}
            </div>
        @elseif ($internship->status === 'DITOLAK')
            <div class="mt-4 p-3.5 rounded-xl bg-rose-700/10 border border-rose-600/30 text-rose-300 text-xs">
                <strong>Alasan Penolakan:</strong> {{ $internship->review_notes }}
            </div>
        @endif
    </div>

    <!-- Main Application Information Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- 1. Data Diri & Akademik -->
        <div class="bg-slate-900/60 border border-slate-800 rounded-2xl p-5 shadow-xl">
            <h2 class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-4 pb-2 border-b border-slate-800 flex items-center gap-2">
                <svg class="w-4 h-4 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                </svg>
                <span>Informasi Mahasiswa</span>
            </h2>

            <dl class="space-y-3 text-xs">
                <div class="flex justify-between">
                    <dt class="text-slate-400">Nama Mahasiswa</dt>
                    <dd class="font-bold text-white text-right">{{ $internship->student_name }}</dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-slate-400">Nomor Induk Mahasiswa (NIM)</dt>
                    <dd class="font-mono text-slate-200 text-right">{{ $internship->student_nim }}</dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-slate-400">Program Studi</dt>
                    <dd class="text-blue-400 font-medium text-right">{{ $internship->studyProgram->name ?? '-' }} ({{ $internship->studyProgram->degree_level ?? 'S1' }})</dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-slate-400">Nomor Kontak / WhatsApp</dt>
                    <dd class="font-mono text-slate-200 text-right">{{ $internship->student_phone }}</dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-slate-400">Email Akun</dt>
                    <dd class="font-mono text-slate-400 text-right">{{ $internship->student->email }}</dd>
                </div>
            </dl>
        </div>

        <!-- 2. Instansi & Periode Magang -->
        <div class="bg-slate-900/60 border border-slate-800 rounded-2xl p-5 shadow-xl">
            <h2 class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-4 pb-2 border-b border-slate-800 flex items-center gap-2">
                <svg class="w-4 h-4 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                </svg>
                <span>Instansi Mitra & Waktu Pelaksanaan</span>
            </h2>

            <dl class="space-y-3 text-xs">
                <div>
                    <dt class="text-slate-400">Nama Instansi</dt>
                    <dd class="font-bold text-white text-sm mt-0.5">{{ $internship->partnerInstitution->name }}</dd>
                </div>
                <div>
                    <dt class="text-slate-400">Alamat Instansi</dt>
                    <dd class="text-slate-300 mt-0.5">{{ $internship->partnerInstitution->address }}</dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-slate-400">Periode Akademik</dt>
                    <dd class="text-amber-400 font-semibold text-right">{{ $internship->internshipPeriod->name }}</dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-slate-400">Jadwal Magang</dt>
                    <dd class="font-mono text-slate-200 text-right">{{ $internship->start_date->format('d M Y') }} s/d {{ $internship->end_date->format('d M Y') }}</dd>
                </div>
            </dl>
        </div>
    </div>

    <!-- Rencana Kegiatan Magang -->
    <div class="bg-slate-900/60 border border-slate-800 rounded-2xl p-5 sm:p-6 shadow-xl">
        <h2 class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-3 pb-2 border-b border-slate-800 flex items-center gap-2">
            <svg class="w-4 h-4 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
            </svg>
            <span>Rencana Penugasan & Kegiatan Magang</span>
        </h2>

        @if ($internship->proposal_title)
            <div class="mb-3">
                <span class="text-xs text-slate-400 block font-medium">Judul / Topik Magang:</span>
                <p class="text-sm font-bold text-white mt-0.5">{{ $internship->proposal_title }}</p>
            </div>
        @endif

        <div>
            <span class="text-xs text-slate-400 block font-medium">Deskripsi Kegiatan:</span>
            <div class="mt-1.5 p-4 rounded-xl bg-slate-950/70 border border-slate-800/80 text-xs text-slate-300 leading-relaxed whitespace-pre-line font-sans">
                {{ $internship->internship_plan }}
            </div>
        </div>
    </div>

    <!-- Dosen Pembimbing Magang Card (Phase 3) -->
    <div class="bg-slate-900/60 border border-slate-800 rounded-2xl p-5 sm:p-6 shadow-xl">
        <h2 class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-4 pb-2 border-b border-slate-800 flex items-center justify-between">
            <span class="flex items-center gap-2">
                <svg class="w-4 h-4 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                </svg>
                <span>Dosen Pembimbing Magang (DOSBING)</span>
            </span>
            <span class="px-2.5 py-1 rounded-full text-[11px] font-semibold border {{ $internship->advisor_status_badge_classes }}">
                {{ $internship->advisor_status_label }}
            </span>
        </h2>

        @if ($internship->advisor)
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 p-4 rounded-xl bg-slate-950/70 border border-slate-800">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 rounded-2xl bg-gradient-to-tr from-emerald-600 to-teal-600 flex items-center justify-center font-bold text-white text-base shadow-lg shadow-emerald-600/20 flex-shrink-0">
                        {{ strtoupper(substr($internship->advisor->name, 0, 2)) }}
                    </div>
                    <div>
                        <h3 class="text-sm font-bold text-white">{{ $internship->advisor->name }}</h3>
                        <p class="text-xs text-slate-400 font-mono mt-0.5">
                            NIDN / Identitas: <strong class="text-slate-200">{{ $internship->advisor->identifier_number ?? '-' }}</strong>
                        </p>
                        <p class="text-[11px] text-slate-500 font-mono">
                            Email: {{ $internship->advisor->email }} &bull; Kontak: {{ $internship->advisor->phone ?? '-' }}
                        </p>
                    </div>
                </div>

                <div class="text-right">
                    @if ($internship->advisor_status === 'DITERIMA')
                        <div class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-emerald-500/10 text-emerald-400 border border-emerald-500/20 text-xs font-semibold">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                            <span>Bimbingan Aktif</span>
                        </div>
                    @elseif ($internship->advisor_status === 'DIAJUKAN')
                        <div class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-amber-500/10 text-amber-400 border border-amber-500/20 text-xs font-semibold">
                            <span class="w-2 h-2 rounded-full bg-amber-400 animate-pulse"></span>
                            <span>Menunggu Konfirmasi Dosen</span>
                        </div>
                    @elseif ($internship->advisor_status === 'DITOLAK')
                        <div class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-rose-500/10 text-rose-400 border border-rose-500/20 text-xs font-semibold">
                            <span>Ditolak Dosen (Perlu Re-assign)</span>
                        </div>
                    @endif
                </div>
            </div>
        @else
            <div class="p-4 rounded-xl bg-slate-950/60 border border-slate-800 text-xs text-slate-400 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                <div>
                    @if ($internship->status === 'DISETUJUI')
                        <p class="text-slate-300 font-medium">Pendaftaran magang Anda telah disetujui resmi oleh Dekanat.</p>
                        <p class="text-[11px] text-slate-500 mt-0.5">
                            {{ $internship->advisor_status === 'DITOLAK' ? 'Dosen pembimbing sebelumnya berhalangan. Kaprodi sedang menentukan dosen pembimbing pengganti.' : 'Ketua Program Studi (Kaprodi) akan segera menentukan Dosen Pembimbing untuk Anda.' }}
                        </p>
                    @else
                        <p class="text-slate-400">
                            Dosen pembimbing akan ditentukan oleh Kaprodi setelah pendaftaran magang disetujui penuh oleh Wakil Dekan 1.
                        </p>
                    @endif
                </div>
                @if (Auth::user()->hasAnyRole(['KAPRODI', 'SUPERADMIN']) && $internship->isReadyForAdvisorAssignment())
                    <a href="{{ route('kaprodi.advisors.show', $internship) }}"
                        class="px-3.5 py-1.5 rounded-xl bg-purple-600 hover:bg-purple-500 text-white font-semibold text-xs transition flex-shrink-0">
                        Tentukan Dospem Sekarang
                    </a>
                @endif
            </div>
        @endif
    </div>

    <!-- Surat Pengantar & Surat Balasan Instansi Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- Surat Pengantar Card -->
        <div class="bg-slate-900/60 border border-slate-800 rounded-2xl p-5 shadow-xl flex flex-col justify-between">
            <div>
                <h3 class="text-xs font-bold text-amber-400 uppercase tracking-wider flex items-center gap-2 mb-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                    </svg>
                    <span>Surat Pengantar Fakultas (TU)</span>
                </h3>

                @if ($internship->canDownloadReferenceLetter())
                    <p class="text-xs text-slate-300 mt-2">
                        Nomor Surat: <strong class="text-white font-mono">{{ $internship->reference_letter_number ?? 'Resmi Fakultas' }}</strong>
                    </p>
                    <p class="text-[11px] text-slate-400 mt-0.5">
                        Diterbitkan: {{ $internship->reference_letter_issued_at?->format('d M Y, H:i') ?? '-' }}
                    </p>
                @else
                    <p class="text-xs text-slate-400 mt-2">
                        Surat pengantar belum diterbitkan. Tata Usaha (TU) akan membuat dan menerbitkan surat setelah memvalidasi kelengkapan berkas Anda.
                    </p>
                @endif
            </div>

            <div class="mt-4 pt-3 border-t border-slate-800">
                @if ($internship->canDownloadReferenceLetter())
                    <a href="{{ route('internships.reference-letter.download', $internship) }}" target="_blank"
                        class="w-full inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl bg-amber-600 hover:bg-amber-500 text-white font-semibold text-xs transition shadow-lg shadow-amber-600/20">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        <span>Unduh / Cetak Surat Pengantar</span>
                    </a>
                @else
                    <button disabled class="w-full px-4 py-2.5 rounded-xl bg-slate-800 text-slate-500 text-xs font-semibold cursor-not-allowed">
                        Menunggu Penerbitan TU
                    </button>
                @endif
            </div>
        </div>

        <!-- Surat Balasan Instansi Card -->
        <div class="bg-slate-900/60 border border-slate-800 rounded-2xl p-5 shadow-xl flex flex-col justify-between">
            <div>
                <h3 class="text-xs font-bold text-teal-400 uppercase tracking-wider flex items-center gap-2 mb-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span>Surat Balasan / Penerimaan Instansi</span>
                </h3>

                @if ($internship->acceptance_letter_path)
                    <p class="text-xs text-emerald-400 font-semibold mt-2 flex items-center gap-1.5">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                        <span>Surat balasan telah diunggah</span>
                    </p>
                    <p class="text-[11px] text-slate-400 mt-0.5">
                        Waktu unggah: {{ $internship->acceptance_letter_uploaded_at?->format('d M Y, H:i') }}
                    </p>
                @else
                    <p class="text-xs text-slate-400 mt-2">
                        Setelah menyerahkan surat pengantar ke instansi mitra, unggah scan surat balasan resmi (diterima/konfirmasi) di sini.
                    </p>
                @endif
            </div>

            <div class="mt-4 pt-3 border-t border-slate-800 space-y-2">
                @if ($internship->acceptance_letter_path)
                    <a href="{{ route('internships.acceptance-letter.download', $internship) }}" target="_blank"
                        class="w-full inline-flex items-center justify-center gap-2 px-4 py-2 rounded-xl bg-teal-600/20 hover:bg-teal-600/30 text-teal-300 border border-teal-500/30 font-semibold text-xs transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                        </svg>
                        <span>Unduh Surat Balasan Instansi</span>
                    </a>
                @endif

                @if ($internship->canUploadAcceptanceLetter() && (Auth::id() === $internship->user_id || Auth::user()->hasRole('SUPERADMIN')))
                    <form method="POST" action="{{ route('internships.acceptance.upload', $internship) }}" enctype="multipart/form-data" class="flex items-center gap-2">
                        @csrf
                        <input type="file" name="acceptance_letter_file" accept=".pdf,.png,.jpg,.jpeg" required
                            class="text-xs text-slate-400 file:mr-2 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-blue-600 file:text-white hover:file:bg-blue-500 cursor-pointer flex-1">
                        <button type="submit" class="px-3.5 py-1.5 rounded-lg bg-teal-600 hover:bg-teal-500 text-white font-bold text-xs transition cursor-pointer">
                            Unggah
                        </button>
                    </form>
                @endif
            </div>
        </div>
    </div>

    <!-- Dokumen Persyaratan -->
    <div class="bg-slate-900/60 border border-slate-800 rounded-2xl p-5 sm:p-6 shadow-xl">
        <h2 class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-4 pb-2 border-b border-slate-800 flex items-center justify-between">
            <span class="flex items-center gap-2">
                <svg class="w-4 h-4 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13" />
                </svg>
                <span>Dokumen Persyaratan yang Diunggah</span>
            </span>
            <span class="text-[11px] font-mono text-slate-500">{{ $internship->documents->count() }} Dokumen Terlampir</span>
        </h2>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
            @forelse ($internship->documents as $doc)
                <div class="p-3.5 rounded-xl bg-slate-950/60 border border-slate-800/80 flex items-center justify-between gap-3">
                    <div class="flex items-center gap-3 overflow-hidden">
                        <div class="p-2 rounded-lg bg-blue-500/10 text-blue-400 border border-blue-500/20 flex-shrink-0">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                            </svg>
                        </div>
                        <div class="overflow-hidden">
                            <p class="text-xs font-bold text-white truncate" title="{{ $doc->document_name }}">{{ $doc->document_name }}</p>
                            <p class="text-[10px] text-slate-400 font-mono">{{ $doc->document_type }} &bull; {{ $doc->formatted_file_size }}</p>
                        </div>
                    </div>

                    <a href="{{ route('internships.documents.download', [$internship, $doc]) }}" target="_blank"
                        class="px-3 py-1.5 rounded-lg bg-slate-800 hover:bg-slate-700 text-slate-300 hover:text-white font-medium text-xs transition flex-shrink-0">
                        Unduh
                    </a>
                </div>
            @empty
                <div class="sm:col-span-2 text-center py-6 text-slate-500 text-xs">
                    Belum ada dokumen yang diunggah.
                </div>
            @endforelse
        </div>
    </div>

    <!-- Status Audit Trail Timeline -->
    <div class="bg-slate-900/60 border border-slate-800 rounded-2xl p-5 sm:p-6 shadow-xl">
        <h2 class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-4 pb-2 border-b border-slate-800 flex items-center gap-2">
            <svg class="w-4 h-4 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <span>Riwayat Perubahan Status (Status History & Audit Trail)</span>
        </h2>

        <div class="relative pl-6 space-y-6 before:absolute before:left-2 before:top-2 before:bottom-2 before:w-0.5 before:bg-slate-800">
            @forelse ($internship->statusHistories as $history)
                <div class="relative">
                    <div class="absolute -left-[27px] top-1.5 w-3.5 h-3.5 rounded-full border-2 border-slate-950 bg-blue-500"></div>
                    <div class="bg-slate-950/70 border border-slate-800/80 rounded-xl p-3.5 text-xs">
                        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-1 mb-1">
                            <span class="font-bold text-white text-xs">
                                {{ $history->getStatusLabel($history->new_status) }}
                            </span>
                            <span class="text-[11px] text-slate-500 font-mono">
                                {{ $history->created_at->format('d M Y, H:i:s') }}
                            </span>
                        </div>
                        <p class="text-slate-400 text-[11px]">
                            Diproses oleh: <strong class="text-slate-200">{{ $history->actor->name ?? 'Sistem' }}</strong>
                            @if ($history->actor && $history->actor->roles->isNotEmpty())
                                ({{ $history->actor->roles->pluck('name')->join(', ') }})
                            @endif
                        </p>
                        @if ($history->reason)
                            <div class="mt-2 p-2 rounded-lg bg-slate-900 border border-slate-800/90 text-slate-300 text-xs italic">
                                &ldquo;{{ $history->reason }}&rdquo;
                            </div>
                        @endif
                    </div>
                </div>
            @empty
                <p class="text-slate-500 text-xs italic">Belum ada riwayat perubahan status.</p>
            @endforelse
        </div>
    </div>
</div>
@endsection
