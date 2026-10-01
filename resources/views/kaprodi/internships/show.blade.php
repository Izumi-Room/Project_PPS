@extends('layouts.app', ['title' => 'Verifikasi Kaprodi - ' . $internship->student_name])

@section('content')
<div class="max-w-5xl mx-auto space-y-6">
    <!-- Top Nav -->
    <div class="flex items-center justify-between pb-2 border-b border-slate-800">
        <div>
            <a href="{{ route('kaprodi.internships.index') }}" class="inline-flex items-center gap-1.5 text-xs text-slate-400 hover:text-white transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                <span>Kembali ke Antrean Kaprodi</span>
            </a>
            <h1 class="text-xl sm:text-2xl font-black text-white tracking-tight mt-1">
                Verifikasi Akademik Pendaftaran Magang (Kaprodi)
            </h1>
        </div>

        <div>
            <span class="px-3 py-1 rounded-full text-xs font-bold border {{ $internship->status_badge_classes }}">
                {{ $internship->status_label }}
            </span>
        </div>
    </div>

    @if ($errors->any())
        <div class="p-4 rounded-2xl bg-rose-500/10 border border-rose-500/20 text-rose-300 text-xs shadow-lg space-y-1">
            <div class="font-bold flex items-center gap-2 text-sm text-rose-400">
                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                </svg>
                <span>Terdapat kesalahan:</span>
            </div>
            <ul class="list-disc list-inside space-y-0.5 pl-2">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- Data Overview Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div class="bg-slate-900/60 border border-slate-800 rounded-2xl p-5 shadow-xl">
            <h2 class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-3 pb-2 border-b border-slate-800">
                Data Mahasiswa
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
                    <dd class="text-purple-400 font-medium text-right">{{ $internship->studyProgram->name ?? '-' }}</dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-slate-400">Kontak</dt>
                    <dd class="font-mono text-slate-200 text-right">{{ $internship->student_phone }}</dd>
                </div>
            </dl>
        </div>

        <div class="bg-slate-900/60 border border-slate-800 rounded-2xl p-5 shadow-xl">
            <h2 class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-3 pb-2 border-b border-slate-800">
                Instansi Tujuan & Surat Pengantar TU
            </h2>
            <dl class="space-y-2.5 text-xs">
                <div>
                    <dt class="text-slate-400">Instansi</dt>
                    <dd class="font-bold text-white mt-0.5">{{ $internship->partnerInstitution->name }}</dd>
                </div>
                <div>
                    <dt class="text-slate-400">No. Surat Pengantar TU</dt>
                    <dd class="font-mono text-amber-400 font-bold mt-0.5">{{ $internship->reference_letter_number ?? 'Resmi Fakultas' }}</dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-slate-400">Periode Magang</dt>
                    <dd class="text-slate-200 font-medium text-right">{{ $internship->internshipPeriod->name }}</dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-slate-400">Durasi</dt>
                    <dd class="font-mono text-slate-200 text-right">{{ $internship->start_date->format('d M Y') }} s/d {{ $internship->end_date->format('d M Y') }}</dd>
                </div>
            </dl>
        </div>
    </div>

    <!-- Rencana Kegiatan Magang Mahasiswa -->
    <div class="bg-slate-900/60 border border-slate-800 rounded-2xl p-5 shadow-xl">
        <h2 class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Rencana Penugasan Magang</h2>
        @if ($internship->proposal_title)
            <p class="text-xs font-bold text-white mb-2">Topik: {{ $internship->proposal_title }}</p>
        @endif
        <div class="p-3.5 rounded-xl bg-slate-950/70 border border-slate-800 text-xs text-slate-300 whitespace-pre-line leading-relaxed">
            {{ $internship->internship_plan }}
        </div>
    </div>

    <!-- Dokumen Terlampir -->
    <div class="bg-slate-900/60 border border-slate-800 rounded-2xl p-5 shadow-xl">
        <h2 class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-4 pb-2 border-b border-slate-800">
            Berkas Persyaratan yang Telah Lolos Validasi TU
        </h2>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
            @foreach ($internship->documents as $doc)
                <div class="p-3.5 rounded-xl bg-slate-950 border border-slate-800 flex items-center justify-between gap-3">
                    <div class="overflow-hidden">
                        <p class="text-xs font-bold text-white truncate">{{ $doc->document_name }}</p>
                        <p class="text-[10px] text-slate-400 font-mono">{{ $doc->document_type }} &bull; {{ $doc->formatted_file_size }}</p>
                    </div>
                    <a href="{{ route('internships.documents.download', [$internship, $doc]) }}" target="_blank"
                        class="px-3 py-1.5 rounded-lg bg-blue-600/20 hover:bg-blue-600/30 text-blue-400 border border-blue-500/30 font-semibold text-xs transition flex-shrink-0">
                        Unduh
                    </a>
                </div>
            @endforeach
        </div>
    </div>

    <!-- ACTION DECISION PANEL FOR KAPRODI -->
    @if ($internship->status === 'LOLOS_TU')
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- 1. Form Setujui & Teruskan ke Wadek 1 -->
            <div class="bg-gradient-to-b from-slate-900/90 to-slate-950 border border-purple-500/30 rounded-2xl p-5 shadow-xl">
                <h3 class="text-sm font-bold text-purple-400 uppercase tracking-wider mb-2 flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                    </svg>
                    <span>Verifikasi & Setujui Akademik</span>
                </h3>
                <p class="text-xs text-slate-400 mb-4">
                    Menyatakan rencana magang mahasiswa layak secara kurikulum program studi dan meneruskan pengajuan ke Wakil Dekan 1 (Wadek 1).
                </p>

                <form method="POST" action="{{ route('kaprodi.internships.review', $internship) }}" class="space-y-3">
                    @csrf
                    <input type="hidden" name="decision" value="VERIFY">

                    <div>
                        <label class="block text-xs font-semibold text-slate-300 mb-1">Catatan Persetujuan Kaprodi (Opsional)</label>
                        <textarea name="reason" rows="3" placeholder="Catatan atau arahan akademik untuk mahasiswa..."
                            class="w-full bg-slate-950 border border-slate-800 rounded-xl p-2.5 text-xs text-white focus:outline-none focus:border-purple-500">{{ old('reason') }}</textarea>
                    </div>

                    <button type="submit"
                        class="w-full py-2.5 px-4 rounded-xl bg-purple-600 hover:bg-purple-500 text-white font-bold text-xs transition shadow-lg shadow-purple-600/20 cursor-pointer flex items-center justify-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                        <span>Verifikasi & Teruskan ke Wadek 1</span>
                    </button>
                </form>
            </div>

            <!-- 2. Form Tolak Pengajuan -->
            <div class="bg-gradient-to-b from-slate-900/90 to-slate-950 border border-rose-500/30 rounded-2xl p-5 shadow-xl">
                <h3 class="text-sm font-bold text-rose-400 uppercase tracking-wider mb-2 flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                    <span>Tolak Pengajuan Magang</span>
                </h3>
                <p class="text-xs text-slate-400 mb-4">
                    Tolak pengajuan jika rencana magang tidak relevan dengan kompetensi program studi. Wajib sertakan alasan.
                </p>

                <form method="POST" action="{{ route('kaprodi.internships.review', $internship) }}" class="space-y-3">
                    @csrf
                    <input type="hidden" name="decision" value="REJECT">

                    <div>
                        <label class="block text-xs font-semibold text-rose-300 mb-1">
                            Alasan Penolakan Kaprodi <span class="text-rose-400">*</span>
                        </label>
                        <textarea name="reason" rows="3" required
                            placeholder="Jelaskan alasan penolakan secara jelas..."
                            class="w-full bg-slate-950 border border-rose-500/30 rounded-xl p-2.5 text-xs text-white focus:outline-none focus:border-rose-500">{{ old('reason') }}</textarea>
                    </div>

                    <button type="submit"
                        onclick="return confirm('Apakah Anda yakin ingin menolak pendaftaran magang ini?');"
                        class="w-full py-2.5 px-4 rounded-xl bg-rose-600 hover:bg-rose-500 text-white font-bold text-xs transition shadow-lg shadow-rose-600/20 cursor-pointer flex items-center justify-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                        <span>Tolak Pendaftaran Magang</span>
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
