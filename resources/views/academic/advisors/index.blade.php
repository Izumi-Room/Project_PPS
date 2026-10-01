@extends('layouts.app', ['title' => 'Permintaan Penugasan Bimbingan Magang'])

@section('content')
<div class="space-y-6 max-w-7xl mx-auto">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-xl sm:text-2xl font-black text-white tracking-tight flex items-center gap-3">
                <span class="p-2 rounded-xl bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                    </svg>
                </span>
                <span>Permintaan Penugasan Bimbingan Magang (DOSBING)</span>
            </h1>
            <p class="text-xs sm:text-sm text-slate-400 mt-1">
                Tinjau penugasan bimbingan dari Ketua Program Studi. Anda dapat menerima atau menolak penugasan disertai alasan resmi.
            </p>
        </div>

        <div class="flex items-center gap-2">
            <span class="px-3.5 py-1.5 rounded-xl bg-emerald-500/10 text-emerald-300 border border-emerald-500/20 text-xs font-bold font-mono">
                {{ $pendingCount }} Permintaan Menunggu Konfirmasi
            </span>
        </div>
    </div>

    @if ($errors->any())
        <div class="p-4 rounded-2xl bg-rose-500/10 border border-rose-500/20 text-rose-300 text-xs shadow-lg space-y-1">
            <div class="font-bold flex items-center gap-2 text-sm text-rose-400">
                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                </svg>
                <span>Terdapat kesalahan respon:</span>
            </div>
            <ul class="list-disc list-inside space-y-0.5 pl-2">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- Tabs Navigation -->
    <div class="flex items-center gap-2 border-b border-slate-800 pb-2">
        <a href="{{ route('academic.advisor-assignments.index', ['tab' => 'pending']) }}"
            class="px-4 py-2 rounded-xl text-xs font-bold transition flex items-center gap-2 {{ $activeTab === 'pending' ? 'bg-amber-600 text-white shadow-lg shadow-amber-600/20' : 'text-slate-400 hover:text-white hover:bg-slate-800/60' }}">
            <span>Permintaan Menunggu Konfirmasi</span>
            <span class="px-1.5 py-0.5 rounded-full text-[10px] {{ $activeTab === 'pending' ? 'bg-black/30 text-white' : 'bg-amber-500/20 text-amber-300' }}">
                {{ $pendingCount }}
            </span>
        </a>
        <a href="{{ route('academic.advisor-assignments.index', ['tab' => 'accepted']) }}"
            class="px-4 py-2 rounded-xl text-xs font-bold transition flex items-center gap-2 {{ $activeTab === 'accepted' ? 'bg-emerald-600 text-white shadow-lg shadow-emerald-600/20' : 'text-slate-400 hover:text-white hover:bg-slate-800/60' }}">
            <span>Bimbingan Aktif Saya</span>
            <span class="px-1.5 py-0.5 rounded-full text-[10px] {{ $activeTab === 'accepted' ? 'bg-black/30 text-white' : 'bg-emerald-500/20 text-emerald-300' }}">
                {{ $acceptedCount }}
            </span>
        </a>
        <a href="{{ route('academic.advisor-assignments.index', ['tab' => 'rejected']) }}"
            class="px-4 py-2 rounded-xl text-xs font-bold transition {{ $activeTab === 'rejected' ? 'bg-slate-800 text-white' : 'text-slate-400 hover:text-white hover:bg-slate-800/60' }}">
            <span>Riwayat Ditolak</span>
        </a>
    </div>

    <!-- Assignments List -->
    <div class="space-y-4">
        @forelse ($assignments as $assignment)
            @php
                $app = $assignment->application;
            @endphp
            <div class="bg-slate-900/70 border border-slate-800 rounded-2xl p-5 sm:p-6 shadow-xl space-y-4">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 pb-3 border-b border-slate-800">
                    <div>
                        <div class="flex items-center gap-3">
                            <h2 class="text-base font-bold text-white">{{ $app->student_name }}</h2>
                            <span class="text-xs font-mono text-slate-400">NIM: {{ $app->student_nim }}</span>
                            <span class="px-2 py-0.5 rounded-lg bg-blue-500/10 text-blue-400 border border-blue-500/20 text-[10px] font-mono">
                                {{ $app->studyProgram->name ?? '-' }}
                            </span>
                        </div>
                        <p class="text-xs text-slate-400 mt-0.5">
                            Ditugaskan oleh: <strong>{{ $assignment->assigner->name ?? 'Kaprodi' }}</strong> pada {{ $assignment->assigned_at->format('d M Y, H:i') }}
                        </p>
                    </div>

                    <div>
                        <span class="px-3 py-1 rounded-full text-xs font-bold border {{ $assignment->status_badge_classes }}">
                            {{ $assignment->status_label }}
                        </span>
                    </div>
                </div>

                <!-- Detail Grid -->
                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4 text-xs">
                    <div>
                        <span class="text-slate-400 block font-medium">Instansi Mitra:</span>
                        <p class="font-bold text-white mt-0.5">{{ $app->partnerInstitution->name }}</p>
                        <p class="text-slate-400 text-[11px] truncate">{{ $app->partnerInstitution->address }}</p>
                    </div>

                    <div>
                        <span class="text-slate-400 block font-medium">Periode & Jadwal:</span>
                        <p class="font-semibold text-amber-400 mt-0.5">{{ $app->internshipPeriod->name }}</p>
                        <p class="text-slate-300 font-mono text-[11px]">{{ $app->start_date->format('d M Y') }} s/d {{ $app->end_date->format('d M Y') }}</p>
                    </div>

                    <div>
                        <span class="text-slate-400 block font-medium">Kontak Mahasiswa:</span>
                        <p class="font-mono text-slate-200 mt-0.5">{{ $app->student_phone }}</p>
                        <p class="font-mono text-slate-400 text-[11px]">{{ $app->student->email }}</p>
                    </div>
                </div>

                @if ($app->proposal_title)
                    <div class="text-xs">
                        <span class="text-slate-400 font-medium">Rencana Topik Magang:</span>
                        <p class="font-bold text-white mt-0.5">"{{ $app->proposal_title }}"</p>
                    </div>
                @endif

                <div class="text-xs">
                    <span class="text-slate-400 font-medium">Uraian Rencana Penugasan:</span>
                    <div class="mt-1 p-3 rounded-xl bg-slate-950/60 border border-slate-800 text-slate-300 text-xs leading-relaxed max-h-28 overflow-y-auto custom-scrollbar whitespace-pre-line">
                        {{ $app->internship_plan }}
                    </div>
                </div>

                <!-- Dokumen Pendukung -->
                <div class="flex flex-wrap items-center gap-2 pt-2 border-t border-slate-800 text-xs">
                    <span class="text-slate-400 mr-2 font-medium">Dokumen Mahasiswa:</span>
                    @foreach ($app->documents as $doc)
                        <a href="{{ route('internships.documents.download', [$app, $doc]) }}" target="_blank"
                            class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-slate-800 hover:bg-slate-700 text-slate-300 hover:text-white transition text-[11px]">
                            <svg class="w-3.5 h-3.5 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                            <span>{{ $doc->document_name }}</span>
                        </a>
                    @endforeach
                </div>

                @if ($assignment->isRejected() && $assignment->rejection_reason)
                    <div class="p-3 rounded-xl bg-rose-500/10 border border-rose-500/20 text-rose-300 text-xs">
                        <strong>Alasan Penolakan Anda:</strong> {{ $assignment->rejection_reason }}
                    </div>
                @endif

                <!-- ACTIONS: ACCEPT OR REJECT (ONLY FOR PENDING) -->
                @if ($assignment->isPending())
                    <div class="pt-4 border-t border-slate-800 flex flex-col sm:flex-row items-center justify-end gap-3">
                        <!-- Form Terima -->
                        <form method="POST" action="{{ route('academic.advisor-assignments.respond', $assignment) }}">
                            @csrf
                            <input type="hidden" name="decision" value="ACCEPT">
                            <button type="submit"
                                onclick="return confirm('Apakah Anda bersedia menerima penugasan bimbingan magang untuk mahasiswa ini?');"
                                class="w-full sm:w-auto px-5 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-xs transition shadow-lg shadow-emerald-600/20 cursor-pointer flex items-center justify-center gap-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                </svg>
                                <span>Terima Bimbingan Magang</span>
                            </button>
                        </form>

                        <!-- Button Trigger Tolak Modal -->
                        <button type="button" onclick="document.getElementById('reject-modal-{{ $assignment->id }}').classList.remove('hidden')"
                            class="w-full sm:w-auto px-4 py-2.5 rounded-xl bg-rose-600/20 hover:bg-rose-600/30 text-rose-300 border border-rose-500/30 font-semibold text-xs transition cursor-pointer flex items-center justify-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                            <span>Tolak Penugasan</span>
                        </button>
                    </div>

                    <!-- MODAL TOLAK DENGAN ALASAN WAJIB -->
                    <div id="reject-modal-{{ $assignment->id }}" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-slate-950/80 backdrop-blur-sm p-4">
                        <div class="bg-slate-900 border border-rose-500/30 rounded-2xl max-w-lg w-full p-6 shadow-2xl space-y-4">
                            <div class="flex items-center justify-between pb-3 border-b border-slate-800">
                                <h3 class="text-sm font-bold text-rose-400 flex items-center gap-2">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                    </svg>
                                    <span>Tolak Penugasan Bimbingan Magang</span>
                                </h3>
                                <button type="button" onclick="document.getElementById('reject-modal-{{ $assignment->id }}').classList.add('hidden')"
                                    class="text-slate-400 hover:text-white">&times;</button>
                            </div>

                            <p class="text-xs text-slate-300">
                                Mahasiswa <strong>{{ $app->student_name }}</strong> akan dikembalikan ke antrean Kaprodi untuk ditentukan dosen pembimbing pengganti. <strong>Alasan penolakan wajib disertakan.</strong>
                            </p>

                            <form method="POST" action="{{ route('academic.advisor-assignments.respond', $assignment) }}" class="space-y-4">
                                @csrf
                                <input type="hidden" name="decision" value="REJECT">

                                <div>
                                    <label class="block text-xs font-semibold text-rose-300 mb-1">
                                        Alasan Penolakan <span class="text-rose-400">*</span>
                                    </label>
                                    <textarea name="reason" rows="4" required
                                        placeholder="Contoh: Kuota bimbingan mahasiswa semester ini sudah penuh, atau topik magang tidak sesuai bidang keahlian..."
                                        class="w-full bg-slate-950 border border-rose-500/30 rounded-xl p-3 text-xs text-white focus:outline-none focus:border-rose-500"></textarea>
                                </div>

                                <div class="flex items-center justify-end gap-2 pt-2">
                                    <button type="button" onclick="document.getElementById('reject-modal-{{ $assignment->id }}').classList.add('hidden')"
                                        class="px-4 py-2 bg-slate-800 hover:bg-slate-700 text-slate-300 rounded-xl text-xs font-semibold">
                                        Batal
                                    </button>
                                    <button type="submit"
                                        class="px-5 py-2 bg-rose-600 hover:bg-rose-500 text-white rounded-xl text-xs font-bold transition">
                                        Konfirmasi Tolak Penugasan
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                @endif

                @if ($assignment->isAccepted())
                    <div class="pt-4 border-t border-slate-800 flex items-center justify-end gap-3">
                        <a href="{{ route('academic.logbooks.student', $app->id) }}"
                            class="px-4 py-2 rounded-xl bg-amber-600 hover:bg-amber-500 text-white font-semibold text-xs transition flex items-center gap-1.5 shadow-md shadow-amber-600/20">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                            </svg>
                            <span>Buka & Review Logbook Mahasiswa</span>
                        </a>
                    </div>
                @endif
            </div>
        @empty
            <div class="bg-slate-900/60 border border-slate-800 rounded-2xl p-12 text-center text-slate-500">
                <svg class="w-12 h-12 mx-auto text-slate-600 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                </svg>
                <p class="text-sm font-semibold text-slate-400">
                    {{ $activeTab === 'pending' ? 'Tidak ada permintaan penugasan bimbingan yang menunggu respon Anda.' : 'Belum ada data bimbingan pada tab ini.' }}
                </p>
                <p class="text-xs text-slate-500 mt-1">
                    Kaprodi akan mengirimkan pemberitahuan ketika Anda ditunjuk sebagai pembimbing mahasiswa magang.
                </p>
            </div>
        @endforelse
    </div>

    @if ($assignments->hasPages())
        <div class="p-4 bg-slate-900/60 border border-slate-800 rounded-2xl">
            {{ $assignments->links() }}
        </div>
    @endif
</div>
@endsection
