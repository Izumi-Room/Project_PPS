@extends('layouts.app', ['title' => 'Validasi Berkas TU - ' . $internship->student_name])

@section('content')
<div class="max-w-5xl mx-auto space-y-6">
    <!-- Top Nav -->
    <div class="flex items-center justify-between pb-2 border-b border-slate-800">
        <div>
            <a href="{{ route('tu.internships.index') }}" class="inline-flex items-center gap-1.5 text-xs text-slate-400 hover:text-white transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                <span>Kembali ke Antrean TU</span>
            </a>
            <h1 class="text-xl sm:text-2xl font-black text-white tracking-tight mt-1">
                Validasi Administrasi & Dokumen Magang
            </h1>
        </div>

        <div class="flex items-center gap-2">
            <span class="px-3 py-1 rounded-full text-xs font-bold border {{ $internship->status_badge_classes }}">
                {{ $internship->status_label }}
            </span>
            <a href="{{ route('tu.internships.print-letter', $internship) }}" target="_blank"
                class="px-3.5 py-1.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 hover:text-white text-xs font-semibold transition flex items-center gap-1.5">
                <svg class="w-4 h-4 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                </svg>
                <span>Cetak / Pratinjau Surat Pengantar</span>
            </a>
        </div>
    </div>

    @if ($errors->any())
        <div class="p-4 rounded-2xl bg-rose-500/10 border border-rose-500/20 text-rose-300 text-xs shadow-lg space-y-1">
            <div class="font-bold flex items-center gap-2 text-sm text-rose-400">
                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                </svg>
                <span>Terdapat kesalahan validasi:</span>
            </div>
            <ul class="list-disc list-inside space-y-0.5 pl-2">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- Informasi Mahasiswa & Instansi Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div class="bg-slate-900/60 border border-slate-800 rounded-2xl p-5 shadow-xl">
            <h2 class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-3 pb-2 border-b border-slate-800">
                Profil Mahasiswa Pemohon
            </h2>
            <dl class="space-y-2.5 text-xs">
                <div class="flex justify-between">
                    <dt class="text-slate-400">Nama</dt>
                    <dd class="font-bold text-white text-right">{{ $internship->student_name }}</dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-slate-400">NIM</dt>
                    <dd class="font-mono text-slate-200 text-right">{{ $internship->student_nim }}</dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-slate-400">Program Studi</dt>
                    <dd class="text-blue-400 font-medium text-right">{{ $internship->studyProgram->name ?? '-' }}</dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-slate-400">Kontak WhatsApp</dt>
                    <dd class="font-mono text-slate-200 text-right">{{ $internship->student_phone }}</dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-slate-400">Email</dt>
                    <dd class="font-mono text-slate-400 text-right">{{ $internship->student->email }}</dd>
                </div>
            </dl>
        </div>

        <div class="bg-slate-900/60 border border-slate-800 rounded-2xl p-5 shadow-xl">
            <h2 class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-3 pb-2 border-b border-slate-800">
                Instansi Tujuan & Waktu Pelaksanaan
            </h2>
            <dl class="space-y-2.5 text-xs">
                <div>
                    <dt class="text-slate-400">Instansi</dt>
                    <dd class="font-bold text-white mt-0.5">{{ $internship->partnerInstitution->name }}</dd>
                </div>
                <div>
                    <dt class="text-slate-400">Alamat</dt>
                    <dd class="text-slate-300 mt-0.5">{{ $internship->partnerInstitution->address }}</dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-slate-400">Periode Magang</dt>
                    <dd class="text-amber-400 font-semibold text-right">{{ $internship->internshipPeriod->name }}</dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-slate-400">Jadwal</dt>
                    <dd class="font-mono text-slate-200 text-right">{{ $internship->start_date->format('d M Y') }} s/d {{ $internship->end_date->format('d M Y') }}</dd>
                </div>
            </dl>
        </div>
    </div>

    <!-- Rencana Magang -->
    <div class="bg-slate-900/60 border border-slate-800 rounded-2xl p-5 shadow-xl">
        <h2 class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Rencana Kegiatan Magang Mahasiswa</h2>
        @if ($internship->proposal_title)
            <p class="text-xs font-bold text-white mb-2">Topik: {{ $internship->proposal_title }}</p>
        @endif
        <div class="p-3.5 rounded-xl bg-slate-950/70 border border-slate-800 text-xs text-slate-300 whitespace-pre-line leading-relaxed">
            {{ $internship->internship_plan }}
        </div>
    </div>

    <!-- Dokumen Viewer & Download -->
    <div class="bg-slate-900/60 border border-slate-800 rounded-2xl p-5 shadow-xl">
        <h2 class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-4 pb-2 border-b border-slate-800 flex items-center justify-between">
            <span>Pemeriksaan Dokumen Persyaratan ({{ $internship->documents->count() }} Dokumen)</span>
            <span class="text-[11px] text-amber-400 font-mono">Wajib Diperiksa oleh TU</span>
        </h2>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
            @forelse ($internship->documents as $doc)
                <div class="p-4 rounded-xl bg-slate-950 border border-slate-800 flex items-center justify-between gap-3">
                    <div class="flex items-center gap-3 overflow-hidden">
                        <div class="p-2 rounded-lg bg-amber-500/10 text-amber-400 border border-amber-500/20 flex-shrink-0">
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
                        class="px-3.5 py-1.5 rounded-lg bg-blue-600 hover:bg-blue-500 text-white font-semibold text-xs transition flex-shrink-0">
                        Buka / Unduh
                    </a>
                </div>
            @empty
                <div class="sm:col-span-2 text-center py-6 text-slate-500 text-xs">
                    Tidak ada berkas yang diunggah.
                </div>
            @endforelse
        </div>
    </div>

    <!-- ACTION DECISION PANEL FOR TU -->
    @if ($internship->status === 'DIAJUKAN')
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- 1. Form Loloskan & Terbitkan Surat Pengantar -->
            <div class="bg-gradient-to-b from-slate-900/90 to-slate-950 border border-emerald-500/30 rounded-2xl p-5 shadow-xl">
                <h3 class="text-sm font-bold text-emerald-400 uppercase tracking-wider mb-2 flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span>Opsi 1: Loloskan & Terbitkan Surat</span>
                </h3>
                <p class="text-xs text-slate-400 mb-4">
                    Jika berkas lengkap dan sesuai ketentuan, berikan nomor surat pengantar dan teruskan ke Ketua Program Studi (Kaprodi).
                </p>

                <form method="POST" action="{{ route('tu.internships.review', $internship) }}" enctype="multipart/form-data" class="space-y-3">
                    @csrf
                    <input type="hidden" name="decision" value="PASS">

                    <div>
                        <label class="block text-xs font-semibold text-slate-300 mb-1">Nomor Surat Pengantar Fakultas</label>
                        <input type="text" name="reference_letter_number"
                            value="{{ old('reference_letter_number', '421/FT-TU/MAGANG/' . date('Y')) }}"
                            placeholder="Contoh: 421/FT-TU/MAGANG/2026"
                            class="w-full bg-slate-950 border border-slate-800 rounded-xl px-3 py-2 text-xs text-white focus:outline-none focus:border-emerald-500">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-300 mb-1">Upload File Surat Pengantar (Opsional, PDF)</label>
                        <input type="file" name="reference_letter_file" accept=".pdf"
                            class="w-full text-xs text-slate-400 file:mr-2 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-slate-800 file:text-slate-300 hover:file:bg-slate-700 cursor-pointer">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-300 mb-1">Catatan Validasi TU (Opsional)</label>
                        <textarea name="reason" rows="2" placeholder="Catatan kelengkapan berkas..."
                            class="w-full bg-slate-950 border border-slate-800 rounded-xl p-2.5 text-xs text-white focus:outline-none focus:border-emerald-500">{{ old('reason') }}</textarea>
                    </div>

                    <button type="submit"
                        class="w-full py-2.5 px-4 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-xs transition shadow-lg shadow-emerald-600/20 cursor-pointer flex items-center justify-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                        <span>Loloskan Berkas & Teruskan ke Kaprodi</span>
                    </button>
                </form>
            </div>

            <!-- 2. Form Kembalikan untuk Perbaikan (Tidak Lengkap) -->
            <div class="bg-gradient-to-b from-slate-900/90 to-slate-950 border border-rose-500/30 rounded-2xl p-5 shadow-xl">
                <h3 class="text-sm font-bold text-rose-400 uppercase tracking-wider mb-2 flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                    <span>Opsi 2: Kembalikan Berkas (Tidak Lengkap)</span>
                </h3>
                <p class="text-xs text-slate-400 mb-4">
                    Jika terdapat berkas yang salah, tidak terbaca, atau kurang lengkap. Mahasiswa akan mendapatkan notifikasi untuk revisi.
                </p>

                <form method="POST" action="{{ route('tu.internships.review', $internship) }}" class="space-y-3">
                    @csrf
                    <input type="hidden" name="decision" value="RETURN">

                    <div>
                        <label class="block text-xs font-semibold text-rose-300 mb-1">
                            Alasan Pengembalian / Bagian yang Perlu Diperbaiki <span class="text-rose-400">*</span>
                        </label>
                        <textarea name="reason" rows="5" required
                            placeholder="Jelaskan secara rinci dokumen apa yang belum lengkap atau perlu diperbaiki oleh mahasiswa..."
                            class="w-full bg-slate-950 border border-rose-500/30 rounded-xl p-2.5 text-xs text-white focus:outline-none focus:border-rose-500">{{ old('reason') }}</textarea>
                    </div>

                    <button type="submit"
                        onclick="return confirm('Apakah Anda yakin ingin mengembalikan berkas pendaftaran ini ke mahasiswa untuk diperbaiki?');"
                        class="w-full py-2.5 px-4 rounded-xl bg-rose-600 hover:bg-rose-500 text-white font-bold text-xs transition shadow-lg shadow-rose-600/20 cursor-pointer flex items-center justify-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6" />
                        </svg>
                        <span>Kembalikan Berkas ke Mahasiswa</span>
                    </button>
                </form>
            </div>
        </div>
    @else
        <div class="p-4 rounded-2xl bg-slate-900 border border-slate-800 text-center text-xs text-slate-400">
            Aplikasi ini sudah diproses dan saat ini berstatus: <strong class="text-white">{{ $internship->status_label }}</strong>.
        </div>
    @endif
</div>
@endsection
