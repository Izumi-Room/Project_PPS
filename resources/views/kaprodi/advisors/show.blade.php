@extends('layouts.app', ['title' => 'Tugaskan Dosen Pembimbing - ' . $internship->student_name])

@section('content')
<div class="max-w-5xl mx-auto space-y-6">
    <!-- Top Nav -->
    <div class="flex items-center justify-between pb-2 border-b border-slate-800">
        <div>
            <a href="{{ route('kaprodi.advisors.index') }}" class="inline-flex items-center gap-1.5 text-xs text-slate-400 hover:text-white transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                <span>Kembali ke Antrean Penentuan Dospem</span>
            </a>
            <h1 class="text-xl sm:text-2xl font-black text-white tracking-tight mt-1">
                Penugasan Dosen Pembimbing Magang
            </h1>
        </div>

        <div>
            <span class="px-3.5 py-1.5 rounded-full text-xs font-bold border {{ $internship->advisor_status_badge_classes }}">
                {{ $internship->advisor_status_label }}
            </span>
        </div>
    </div>

    @if ($errors->any())
        <div class="p-4 rounded-2xl bg-rose-500/10 border border-rose-500/20 text-rose-300 text-xs shadow-lg space-y-1">
            <div class="font-bold flex items-center gap-2 text-sm text-rose-400">
                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                </svg>
                <span>Terdapat kesalahan validasi penugasan:</span>
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
                    <dd class="text-purple-400 font-medium text-right">{{ $internship->studyProgram->name ?? '-' }}</dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-slate-400">Kontak</dt>
                    <dd class="font-mono text-slate-200 text-right">{{ $internship->student_phone }}</dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-slate-400">Status Magang</dt>
                    <dd class="text-emerald-400 font-semibold text-right">{{ $internship->status_label }}</dd>
                </div>
            </dl>
        </div>

        <div class="bg-slate-900/60 border border-slate-800 rounded-2xl p-5 shadow-xl">
            <h2 class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-3 pb-2 border-b border-slate-800">
                Instansi Tujuan & Waktu Magang
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

    <!-- ACTION: FORM PENUGASAN DOSEN PEMBIMBING -->
    @if ($internship->isReadyForAdvisorAssignment())
        <div class="bg-gradient-to-b from-slate-900/90 to-slate-950 border border-purple-500/30 rounded-2xl p-6 shadow-xl">
            <h3 class="text-sm font-bold text-purple-400 uppercase tracking-wider mb-2 flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
                </svg>
                <span>Formulir Penentuan Dosen Pembimbing (DOSBING)</span>
            </h3>
            <p class="text-xs text-slate-400 mb-5">
                Pilih dosen pembimbing yang memiliki peran aktif <strong>DOSBING</strong>. Dosen yang ditugaskan akan menerima notifikasi dan diminta konfirmasi penerimaan bimbingan.
            </p>

            <form method="POST" action="{{ route('kaprodi.advisors.assign', $internship) }}" class="space-y-4">
                @csrf

                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1">
                        Pilih Dosen Pembimbing <span class="text-rose-400">*</span>
                    </label>
                    <select name="advisor_id" required
                        class="w-full bg-slate-950 border border-slate-800 rounded-xl px-3.5 py-2.5 text-xs text-white focus:outline-none focus:border-purple-500">
                        <option value="">-- Pilih Dosen dari Daftar DOSBING Aktif --</option>
                        @foreach ($availableAdvisors as $advisor)
                            <option value="{{ $advisor->id }}" {{ old('advisor_id') == $advisor->id ? 'selected' : '' }}>
                                {{ $advisor->name }} {{ $advisor->identifier_number ? '(NIDN: ' . $advisor->identifier_number . ')' : '' }} &bull; {{ $advisor->email }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1">Catatan Arahan Penugasan untuk Dosen (Opsional)</label>
                    <textarea name="notes" rows="3" placeholder="Pesan atau arahan khusus terkait topik magang mahasiswa ini..."
                        class="w-full bg-slate-950 border border-slate-800 rounded-xl p-3 text-xs text-white focus:outline-none focus:border-purple-500">{{ old('notes') }}</textarea>
                </div>

                <button type="submit"
                    class="w-full py-3 px-4 rounded-xl bg-purple-600 hover:bg-purple-500 text-white font-bold text-xs transition shadow-lg shadow-purple-600/30 cursor-pointer flex items-center justify-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                    <span>Kirim Penugasan ke Dosen Pembimbing</span>
                </button>
            </form>
        </div>
    @else
        <!-- Display Current Assigned Advisor Info -->
        <div class="p-5 rounded-2xl bg-slate-900 border border-slate-800 text-xs text-slate-300 shadow-xl">
            <h3 class="text-sm font-bold text-white mb-2 flex items-center gap-2">
                <svg class="w-4 h-4 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                </svg>
                <span>Dosen Pembimbing yang Sedang Ditugaskan</span>
            </h3>
            @if ($internship->advisor)
                <p class="font-bold text-white text-base mt-1">{{ $internship->advisor->name }}</p>
                <p class="text-slate-400 font-mono text-[11px]">NIDN: {{ $internship->advisor->identifier_number ?? '-' }} &bull; Email: {{ $internship->advisor->email }}</p>
                <div class="mt-3 flex items-center gap-2">
                    <span class="px-3 py-1 rounded-full text-xs font-semibold border {{ $internship->advisor_status_badge_classes }}">
                        {{ $internship->advisor_status_label }}
                    </span>
                </div>
            @endif
        </div>
    @endif

    <!-- RIWAYAT PENUGASAN DOSEN PEMBIMBING -->
    <div class="bg-slate-900/60 border border-slate-800 rounded-2xl p-5 sm:p-6 shadow-xl">
        <h2 class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-4 pb-2 border-b border-slate-800 flex items-center gap-2">
            <svg class="w-4 h-4 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <span>Riwayat Penugasan Dosen Pembimbing (History)</span>
        </h2>

        <div class="space-y-3">
            @forelse ($internship->advisorAssignments as $assign)
                <div class="p-4 rounded-xl bg-slate-950 border border-slate-800/80 text-xs">
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-1 mb-2">
                        <div>
                            <span class="font-bold text-white text-sm">{{ $assign->advisor->name }}</span>
                            <span class="text-slate-400 font-mono text-[11px] ml-2">NIDN: {{ $assign->advisor->identifier_number ?? '-' }}</span>
                        </div>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-semibold border {{ $assign->status_badge_classes }}">
                            {{ $assign->status_label }}
                        </span>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 text-[11px] text-slate-400">
                        <div>Ditugaskan pada: <span class="font-mono text-slate-300">{{ $assign->assigned_at->format('d M Y, H:i') }}</span> oleh {{ $assign->assigner->name ?? 'Kaprodi' }}</div>
                        <div>Direspon pada: <span class="font-mono text-slate-300">{{ $assign->responded_at?->format('d M Y, H:i') ?? 'Menunggu respon' }}</span></div>
                    </div>

                    @if ($assign->rejection_reason)
                        <div class="mt-2.5 p-3 rounded-lg bg-rose-500/10 border border-rose-500/20 text-rose-300 text-xs">
                            <strong>Alasan Penolakan oleh Dosen:</strong> "{{ $assign->rejection_reason }}"
                        </div>
                    @endif

                    @if ($assign->notes)
                        <div class="mt-2 p-2 rounded-lg bg-slate-900 border border-slate-800 text-slate-400 text-[11px]">
                            Catatan Kaprodi: {{ $assign->notes }}
                        </div>
                    @endif
                </div>
            @empty
                <p class="text-slate-500 text-xs italic">Belum ada penugasan dosen pembimbing sebelumnya.</p>
            @endforelse
        </div>
    </div>
</div>
@endsection
