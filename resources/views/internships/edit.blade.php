@extends('layouts.app', ['title' => 'Ubah Pendaftaran Magang'])

@section('content')
<div class="max-w-3xl mx-auto space-y-8 animate-fade-in" style="animation-delay: 100ms;">
    <!-- Header -->
    <header class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-semibold text-white">Ubah Pendaftaran Magang</h1>
            <p class="text-sm text-slate-400 mt-1">Status: <span class="text-white font-medium">{{ $internship->status_label }}</span></p>
        </div>
        <a href="{{ route('internships.show', $internship) }}" class="text-sm text-slate-400 hover:text-white transition">
            Batal
        </a>
    </header>

    @if ($internship->status === 'TIDAK_LENGKAP' && $internship->review_notes)
        <div class="p-4 rounded-xl bg-amber-500/10 border border-amber-500/20 text-amber-300 text-xs space-y-1">
            <p class="font-medium text-amber-200">Catatan Perbaikan Tata Usaha:</p>
            <p class="text-slate-300">{{ $internship->review_notes }}</p>
        </div>
    @endif

    @if ($errors->any())
        <div class="p-4 rounded-xl bg-rose-500/10 border border-rose-500/20 text-rose-400 text-xs space-y-1">
            <p class="font-medium">Perhatikan kesalahan berikut:</p>
            <ul class="list-disc list-inside space-y-0.5">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('internships.update', $internship) }}" enctype="multipart/form-data" class="space-y-6">
        @csrf
        @method('PUT')

        <!-- 1. DATA DIRI MAHASISWA -->
        <div class="bg-slate-900/40 border border-white/5 rounded-2xl p-6 space-y-6">
            <h3 class="text-sm font-medium text-white">1. Data Diri Mahasiswa</h3>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="space-y-2">
                    <label class="block text-[11px] font-medium text-slate-400 uppercase tracking-widest">Nama Lengkap</label>
                    <input type="text" name="student_name" value="{{ old('student_name', $internship->student_name) }}" required
                        class="w-full px-4 py-2.5 bg-slate-950 border border-white/10 rounded-xl text-sm text-white focus:outline-none focus:border-indigo-500/50 transition">
                </div>

                <div class="space-y-2">
                    <label class="block text-[11px] font-medium text-slate-400 uppercase tracking-widest">NIM</label>
                    <input type="text" name="student_nim" value="{{ old('student_nim', $internship->student_nim) }}" required
                        class="w-full px-4 py-2.5 bg-slate-950 border border-white/10 rounded-xl text-sm text-white focus:outline-none focus:border-indigo-500/50 transition">
                </div>

                <div class="space-y-2">
                    <label class="block text-[11px] font-medium text-slate-400 uppercase tracking-widest">Program Studi</label>
                    <select name="study_program_id" required
                        class="w-full px-4 py-2.5 bg-slate-950 border border-white/10 rounded-xl text-sm text-slate-300 focus:outline-none focus:border-indigo-500/50 transition">
                        @foreach ($studyPrograms as $prodi)
                            <option value="{{ $prodi->id }}" {{ old('study_program_id', $internship->study_program_id) == $prodi->id ? 'selected' : '' }}>
                                {{ $prodi->name }} ({{ $prodi->degree_level }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="space-y-2">
                    <label class="block text-[11px] font-medium text-slate-400 uppercase tracking-widest">Nomor WhatsApp</label>
                    <input type="text" name="student_phone" value="{{ old('student_phone', $internship->student_phone) }}" required
                        class="w-full px-4 py-2.5 bg-slate-950 border border-white/10 rounded-xl text-sm text-white focus:outline-none focus:border-indigo-500/50 transition">
                </div>
            </div>
        </div>

        <!-- 2. INSTANSI TUJUAN & PERIODE -->
        <div class="bg-slate-900/40 border border-white/5 rounded-2xl p-6 space-y-6">
            <h3 class="text-sm font-medium text-white">2. Instansi & Periode</h3>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="sm:col-span-2 space-y-2">
                    <label class="block text-[11px] font-medium text-slate-400 uppercase tracking-widest">Instansi Mitra</label>
                    <select name="partner_institution_id" required
                        class="w-full px-4 py-2.5 bg-slate-950 border border-white/10 rounded-xl text-sm text-slate-300 focus:outline-none focus:border-indigo-500/50 transition">
                        @foreach ($partnerInstitutions as $inst)
                            <option value="{{ $inst->id }}" {{ old('partner_institution_id', $internship->partner_institution_id) == $inst->id ? 'selected' : '' }}>
                                {{ $inst->name }} ({{ $inst->sector }}) — {{ $inst->address }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="sm:col-span-2 space-y-2">
                    <label class="block text-[11px] font-medium text-slate-400 uppercase tracking-widest">Periode Magang</label>
                    <select name="internship_period_id" required
                        class="w-full px-4 py-2.5 bg-slate-950 border border-white/10 rounded-xl text-sm text-slate-300 focus:outline-none focus:border-indigo-500/50 transition">
                        @foreach ($activePeriods as $period)
                            <option value="{{ $period->id }}" {{ old('internship_period_id', $internship->internship_period_id) == $period->id ? 'selected' : '' }}>
                                {{ $period->name }} (TA {{ $period->academic_year }} {{ $period->semester_type }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="space-y-2">
                    <label class="block text-[11px] font-medium text-slate-400 uppercase tracking-widest">Mulai</label>
                    <input type="date" name="start_date" value="{{ old('start_date', $internship->start_date ? $internship->start_date->format('Y-m-d') : '') }}" required
                        class="w-full px-4 py-2.5 bg-slate-950 border border-white/10 rounded-xl text-sm text-slate-300 focus:outline-none focus:border-indigo-500/50 transition">
                </div>

                <div class="space-y-2">
                    <label class="block text-[11px] font-medium text-slate-400 uppercase tracking-widest">Selesai</label>
                    <input type="date" name="end_date" value="{{ old('end_date', $internship->end_date ? $internship->end_date->format('Y-m-d') : '') }}" required
                        class="w-full px-4 py-2.5 bg-slate-950 border border-white/10 rounded-xl text-sm text-slate-300 focus:outline-none focus:border-indigo-500/50 transition">
                </div>
            </div>
        </div>

        <!-- 3. RENCANA KEGIATAN MAGANG -->
        <div class="bg-slate-900/40 border border-white/5 rounded-2xl p-6 space-y-6">
            <h3 class="text-sm font-medium text-white">3. Rencana Kegiatan</h3>

            <div class="space-y-4">
                <div class="space-y-2">
                    <label class="block text-[11px] font-medium text-slate-400 uppercase tracking-widest">Judul / Topik Magang</label>
                    <input type="text" name="proposal_title" value="{{ old('proposal_title', $internship->proposal_title) }}"
                        class="w-full px-4 py-2.5 bg-slate-950 border border-white/10 rounded-xl text-sm text-white focus:outline-none focus:border-indigo-500/50 transition">
                </div>

                <div class="space-y-2">
                    <label class="block text-[11px] font-medium text-slate-400 uppercase tracking-widest">Deskripsi Rencana Magang</label>
                    <textarea name="internship_plan" rows="3" required
                        class="w-full p-4 bg-slate-950 border border-white/10 rounded-xl text-sm text-white focus:outline-none focus:border-indigo-500/50 transition">{{ old('internship_plan', $internship->internship_plan) }}</textarea>
                </div>
            </div>
        </div>

        <!-- 4. DOKUMEN PERSYARATAN -->
        <div class="bg-slate-900/40 border border-white/5 rounded-2xl p-6 space-y-6">
            <h3 class="text-sm font-medium text-white">4. Unggah Ulang Dokumen (Opsional)</h3>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="p-4 rounded-xl bg-slate-950 border border-white/5 space-y-2">
                    <label class="block text-[11px] font-medium text-slate-300 uppercase tracking-widest">Transkrip Nilai</label>
                    <input type="file" name="document_transcript" accept=".pdf,.png,.jpg,.jpeg"
                        class="w-full text-xs text-slate-400 file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-medium file:bg-white/10 file:text-white hover:file:bg-white/20 transition cursor-pointer">
                </div>

                <div class="p-4 rounded-xl bg-slate-950 border border-white/5 space-y-2">
                    <label class="block text-[11px] font-medium text-slate-300 uppercase tracking-widest">Proposal Magang</label>
                    <input type="file" name="document_proposal" accept=".pdf,.doc,.docx"
                        class="w-full text-xs text-slate-400 file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-medium file:bg-white/10 file:text-white hover:file:bg-white/20 transition cursor-pointer">
                </div>
            </div>
        </div>

        <!-- ACTION BUTTONS -->
        <div class="flex items-center justify-end gap-3 pt-4">
            <button type="submit" name="action" value="draft"
                class="px-5 py-2.5 rounded-xl bg-white/5 hover:bg-white/10 text-slate-300 text-sm font-medium transition cursor-pointer">
                Simpan Perubahan
            </button>
            <button type="submit" name="action" value="submit"
                class="px-6 py-2.5 rounded-xl bg-white text-slate-950 text-sm font-medium hover:bg-slate-200 transition active:scale-[0.98] shadow-lg cursor-pointer">
                Ajukan Ulang
            </button>
        </div>
    </form>
</div>
@endsection
