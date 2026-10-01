@extends('layouts.app', ['title' => 'Formulir Pendaftaran Magang'])

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <a href="{{ route('internships.index') }}" class="inline-flex items-center gap-1.5 text-xs text-slate-400 hover:text-white mb-2 transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                <span>Kembali ke Daftar Magang</span>
            </a>
            <h1 class="text-xl sm:text-2xl font-black text-white tracking-tight">Formulir Pendaftaran Magang Mahasiswa</h1>
            <p class="text-xs sm:text-sm text-slate-400 mt-1">
                Lengkapi seluruh formulir data diri, instansi tujuan, periode, rencana magang, serta unggah dokumen persyaratan.
            </p>
        </div>
    </div>

    @if ($errors->any())
        <div class="p-4 rounded-2xl bg-rose-500/10 border border-rose-500/20 text-rose-300 text-xs shadow-lg shadow-rose-500/5 space-y-1">
            <div class="font-bold flex items-center gap-2 text-sm text-rose-400">
                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                </svg>
                <span>Terdapat kesalahan pengisian formulir:</span>
            </div>
            <ul class="list-disc list-inside space-y-0.5 pl-2">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('internships.store') }}" enctype="multipart/form-data" class="space-y-6">
        @csrf

        <!-- 1. DATA DIRI MAHASISWA -->
        <div class="bg-slate-900/60 border border-slate-800 rounded-2xl p-5 sm:p-6 shadow-xl">
            <h2 class="text-sm font-bold text-white uppercase tracking-wider mb-4 flex items-center gap-2 pb-3 border-b border-slate-800">
                <span class="w-2 h-2 rounded-full bg-blue-500"></span>
                <span>1. Data Diri Mahasiswa</span>
            </h2>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1">Nama Lengkap <span class="text-rose-400">*</span></label>
                    <input type="text" name="student_name" value="{{ old('student_name', $user->name) }}" required
                        class="w-full bg-slate-950 border border-slate-800 rounded-xl px-3.5 py-2.5 text-xs text-white focus:outline-none focus:border-blue-500">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1">Nomor Induk Mahasiswa (NIM) <span class="text-rose-400">*</span></label>
                    <input type="text" name="student_nim" value="{{ old('student_nim', $user->identifier_number) }}" required
                        class="w-full bg-slate-950 border border-slate-800 rounded-xl px-3.5 py-2.5 text-xs text-white focus:outline-none focus:border-blue-500">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1">Program Studi <span class="text-rose-400">*</span></label>
                    <select name="study_program_id" required
                        class="w-full bg-slate-950 border border-slate-800 rounded-xl px-3.5 py-2.5 text-xs text-white focus:outline-none focus:border-blue-500">
                        <option value="">Pilih Program Studi</option>
                        @foreach ($studyPrograms as $prodi)
                            <option value="{{ $prodi->id }}" {{ old('study_program_id', $user->study_program_id) == $prodi->id ? 'selected' : '' }}>
                                {{ $prodi->name }} ({{ $prodi->degree_level }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1">Nomor WhatsApp / HP Aktif <span class="text-rose-400">*</span></label>
                    <input type="text" name="student_phone" value="{{ old('student_phone', $user->phone) }}" required
                        placeholder="Contoh: 081234567890"
                        class="w-full bg-slate-950 border border-slate-800 rounded-xl px-3.5 py-2.5 text-xs text-white focus:outline-none focus:border-blue-500">
                </div>
            </div>
        </div>

        <!-- 2. INSTANSI TUJUAN & PERIODE -->
        <div class="bg-slate-900/60 border border-slate-800 rounded-2xl p-5 sm:p-6 shadow-xl">
            <h2 class="text-sm font-bold text-white uppercase tracking-wider mb-4 flex items-center gap-2 pb-3 border-b border-slate-800">
                <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                <span>2. Instansi Tujuan & Periode Magang</span>
            </h2>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="sm:col-span-2">
                    <label class="block text-xs font-semibold text-slate-300 mb-1">Pilih Instansi Mitra <span class="text-rose-400">*</span></label>
                    <select name="partner_institution_id" required
                        class="w-full bg-slate-950 border border-slate-800 rounded-xl px-3.5 py-2.5 text-xs text-white focus:outline-none focus:border-blue-500">
                        <option value="">Pilih Instansi Mitra dari Katalog Master Data</option>
                        @foreach ($partnerInstitutions as $inst)
                            <option value="{{ $inst->id }}" {{ old('partner_institution_id') == $inst->id ? 'selected' : '' }}>
                                {{ $inst->name }} ({{ $inst->sector }}) — {{ $inst->address }}
                            </option>
                        @endforeach
                    </select>
                    <p class="text-[11px] text-slate-400 mt-1">Hanya instansi berstatus aktif yang dapat dipilih.</p>
                </div>

                <div class="sm:col-span-2">
                    <label class="block text-xs font-semibold text-slate-300 mb-1">Periode Akademik Magang <span class="text-rose-400">*</span></label>
                    <select name="internship_period_id" required
                        class="w-full bg-slate-950 border border-slate-800 rounded-xl px-3.5 py-2.5 text-xs text-white focus:outline-none focus:border-blue-500">
                        <option value="">Pilih Periode Magang Aktif</option>
                        @foreach ($activePeriods as $period)
                            <option value="{{ $period->id }}" {{ old('internship_period_id') == $period->id ? 'selected' : '' }}>
                                {{ $period->name }} (TA {{ $period->academic_year }} {{ $period->semester_type }}) — Pelaksanaan: {{ $period->start_date->format('d M Y') }} s/d {{ $period->end_date->format('d M Y') }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1">Tanggal Mulai Magang <span class="text-rose-400">*</span></label>
                    <input type="date" name="start_date" value="{{ old('start_date') }}" required
                        class="w-full bg-slate-950 border border-slate-800 rounded-xl px-3.5 py-2.5 text-xs text-white focus:outline-none focus:border-blue-500">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1">Tanggal Selesai Magang <span class="text-rose-400">*</span></label>
                    <input type="date" name="end_date" value="{{ old('end_date') }}" required
                        class="w-full bg-slate-950 border border-slate-800 rounded-xl px-3.5 py-2.5 text-xs text-white focus:outline-none focus:border-blue-500">
                </div>
            </div>
        </div>

        <!-- 3. RENCANA KEGIATAN MAGANG -->
        <div class="bg-slate-900/60 border border-slate-800 rounded-2xl p-5 sm:p-6 shadow-xl">
            <h2 class="text-sm font-bold text-white uppercase tracking-wider mb-4 flex items-center gap-2 pb-3 border-b border-slate-800">
                <span class="w-2 h-2 rounded-full bg-purple-500"></span>
                <span>3. Rencana Kegiatan Magang</span>
            </h2>

            <div class="space-y-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1">Judul / Topik Rencana Magang</label>
                    <input type="text" name="proposal_title" value="{{ old('proposal_title') }}"
                        placeholder="Contoh: Pengembangan Modul Sistem Informasi Monitoring Logistik di Divisi IT"
                        class="w-full bg-slate-950 border border-slate-800 rounded-xl px-3.5 py-2.5 text-xs text-white focus:outline-none focus:border-blue-500">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1">Deskripsi Rencana Magang & Penugasan <span class="text-rose-400">*</span></label>
                    <textarea name="internship_plan" rows="4" required
                        placeholder="Uraikan ruang lingkup kegiatan, teknologi atau bidang pekerjaan yang direncanakan di instansi magang..."
                        class="w-full bg-slate-950 border border-slate-800 rounded-xl p-3.5 text-xs text-white focus:outline-none focus:border-blue-500">{{ old('internship_plan') }}</textarea>
                </div>
            </div>
        </div>

        <!-- 4. DOKUMEN PERSYARATAN -->
        <div class="bg-slate-900/60 border border-slate-800 rounded-2xl p-5 sm:p-6 shadow-xl">
            <h2 class="text-sm font-bold text-white uppercase tracking-wider mb-2 flex items-center gap-2 pb-3 border-b border-slate-800">
                <span class="w-2 h-2 rounded-full bg-amber-500"></span>
                <span>4. Dokumen Persyaratan Magang</span>
            </h2>
            <p class="text-[11px] text-slate-400 mb-4">
                Format yang diizinkan: <strong>PDF, DOC, DOCX, PNG, JPG</strong>. Ukuran maksimum: <strong>10MB</strong> per file. Dokumen tersimpan aman pada private storage.
            </p>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <!-- Transkrip Nilai -->
                <div class="p-3.5 rounded-xl bg-slate-950/60 border border-slate-800">
                    <label class="block text-xs font-bold text-white mb-1">
                        Transkrip Nilai Akademik Terakhir <span class="text-rose-400">*</span>
                    </label>
                    <p class="text-[10px] text-slate-400 mb-2">Wajib saat pengajuan resmi (PDF/Gambar)</p>
                    <input type="file" name="document_transcript" accept=".pdf,.png,.jpg,.jpeg"
                        class="w-full text-xs text-slate-400 file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-blue-600 file:text-white hover:file:bg-blue-500 cursor-pointer">
                </div>

                <!-- Proposal Magang -->
                <div class="p-3.5 rounded-xl bg-slate-950/60 border border-slate-800">
                    <label class="block text-xs font-bold text-white mb-1">
                        Proposal / Kerangka Rencana Magang <span class="text-rose-400">*</span>
                    </label>
                    <p class="text-[10px] text-slate-400 mb-2">Wajib saat pengajuan resmi (PDF/DOC/DOCX)</p>
                    <input type="file" name="document_proposal" accept=".pdf,.doc,.docx"
                        class="w-full text-xs text-slate-400 file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-blue-600 file:text-white hover:file:bg-blue-500 cursor-pointer">
                </div>

                <!-- CV -->
                <div class="p-3.5 rounded-xl bg-slate-950/60 border border-slate-800">
                    <label class="block text-xs font-bold text-white mb-1">
                        Curriculum Vitae (CV) <span class="text-slate-500 font-normal">(Opsional)</span>
                    </label>
                    <p class="text-[10px] text-slate-400 mb-2">Resume / riwayat hidup mahasiswa (PDF/DOC)</p>
                    <input type="file" name="document_cv" accept=".pdf,.doc,.docx"
                        class="w-full text-xs text-slate-400 file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-slate-800 file:text-slate-300 hover:file:bg-slate-700 cursor-pointer">
                </div>

                <!-- Surat Izin Orang Tua -->
                <div class="p-3.5 rounded-xl bg-slate-950/60 border border-slate-800">
                    <label class="block text-xs font-bold text-white mb-1">
                        Surat Persetujuan Orang Tua <span class="text-slate-500 font-normal">(Opsional)</span>
                    </label>
                    <p class="text-[10px] text-slate-400 mb-2">Surat pernyataan persetujuan wali/ortu (PDF/Gambar)</p>
                    <input type="file" name="document_parent_consent" accept=".pdf,.png,.jpg,.jpeg"
                        class="w-full text-xs text-slate-400 file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-slate-800 file:text-slate-300 hover:file:bg-slate-700 cursor-pointer">
                </div>

                <!-- Dokumen Pendukung Lainnya -->
                <div class="sm:col-span-2 p-3.5 rounded-xl bg-slate-950/60 border border-slate-800">
                    <label class="block text-xs font-bold text-white mb-1">
                        Dokumen Pendukung Lainnya <span class="text-slate-500 font-normal">(Opsional)</span>
                    </label>
                    <p class="text-[10px] text-slate-400 mb-2">Sertifikat keahlian, portofolio, dsb (PDF/Gambar/DOC)</p>
                    <input type="file" name="document_other" accept=".pdf,.doc,.docx,.png,.jpg,.jpeg"
                        class="w-full text-xs text-slate-400 file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-slate-800 file:text-slate-300 hover:file:bg-slate-700 cursor-pointer">
                </div>
            </div>
        </div>

        <!-- FORM ACTION BUTTONS -->
        <div class="flex items-center justify-end gap-3 pt-3">
            <button type="submit" name="action" value="draft"
                class="px-5 py-2.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 hover:text-white font-semibold text-xs transition cursor-pointer">
                Simpan sebagai Draft
            </button>
            <button type="submit" name="action" value="submit"
                class="px-6 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-500 text-white font-bold text-xs transition shadow-lg shadow-blue-600/30 cursor-pointer flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>
                <span>Ajukan Pendaftaran Magang</span>
            </button>
        </div>
    </form>
</div>
@endsection
