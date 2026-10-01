@extends('layouts.app', ['title' => 'Pendaftaran Magang Mahasiswa'])

@section('content')
<div class="space-y-6">
    <!-- Header Page -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-xl sm:text-2xl font-black text-white tracking-tight flex items-center gap-3">
                <span class="p-2 rounded-xl bg-blue-500/10 text-blue-400 border border-blue-500/20">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                </span>
                <span>Pendaftaran Magang Mahasiswa</span>
            </h1>
            <p class="text-xs sm:text-sm text-slate-400 mt-1">
                Kelola pengajuan magang, pantau status verifikasi TU, Kaprodi, dan persetujuan Dekanat secara transparan.
            </p>
        </div>

        @if (Auth::user()->hasAnyRole(['MHS', 'SUPERADMIN']))
            <a href="{{ route('internships.create') }}"
                class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-500 text-white font-semibold text-xs transition shadow-lg shadow-blue-600/30">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                <span>Daftar Magang Baru</span>
            </a>
        @endif
    </div>

    <!-- Filter & Search Toolbar -->
    <div class="bg-slate-900/60 border border-slate-800 rounded-2xl p-4 sm:p-5">
        <form method="GET" action="{{ route('internships.index') }}" class="grid grid-cols-1 sm:grid-cols-3 gap-3">
            <div>
                <label class="block text-xs font-medium text-slate-400 mb-1">Pencarian</label>
                <input type="text" name="search" value="{{ request('search') }}"
                    placeholder="Cari instansi, judul, atau no. surat..."
                    class="w-full bg-slate-950 border border-slate-800 rounded-xl px-3 py-2 text-xs text-white focus:outline-none focus:border-blue-500">
            </div>

            <div>
                <label class="block text-xs font-medium text-slate-400 mb-1">Status Pendaftaran</label>
                <select name="status" class="w-full bg-slate-950 border border-slate-800 rounded-xl px-3 py-2 text-xs text-white focus:outline-none focus:border-blue-500">
                    <option value="">Semua Status</option>
                    <option value="DRAFT" {{ request('status') === 'DRAFT' ? 'selected' : '' }}>Draft</option>
                    <option value="DIAJUKAN" {{ request('status') === 'DIAJUKAN' ? 'selected' : '' }}>Diajukan (Validasi TU)</option>
                    <option value="TIDAK_LENGKAP" {{ request('status') === 'TIDAK_LENGKAP' ? 'selected' : '' }}>Tidak Lengkap (Perlu Revisi)</option>
                    <option value="LOLOS_TU" {{ request('status') === 'LOLOS_TU' ? 'selected' : '' }}>Lolos TU (Menunggu Kaprodi)</option>
                    <option value="VERIFIKASI_KAPRODI" {{ request('status') === 'VERIFIKASI_KAPRODI' ? 'selected' : '' }}>Verifikasi Kaprodi (Menunggu Wadek 1)</option>
                    <option value="DISETUJUI" {{ request('status') === 'DISETUJUI' ? 'selected' : '' }}>Disetujui Wadek 1</option>
                    <option value="DITOLAK" {{ request('status') === 'DITOLAK' ? 'selected' : '' }}>Ditolak</option>
                </select>
            </div>

            <div class="flex items-end gap-2">
                <button type="submit" class="flex-1 px-4 py-2 bg-slate-800 hover:bg-slate-700 text-white rounded-xl text-xs font-semibold transition">
                    Filter
                </button>
                <a href="{{ route('internships.index') }}" class="px-3 py-2 bg-slate-950 border border-slate-800 hover:bg-slate-900 text-slate-400 hover:text-white rounded-xl text-xs transition">
                    Reset
                </a>
            </div>
        </form>
    </div>

    <!-- Application List -->
    <div class="bg-slate-900/60 border border-slate-800 rounded-2xl overflow-hidden shadow-xl">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-950/70 border-b border-slate-800 text-slate-400 uppercase font-mono text-[10px] tracking-wider">
                    <tr>
                        <th class="px-6 py-4">Mahasiswa / Program Studi</th>
                        <th class="px-6 py-4">Instansi Tujuan</th>
                        <th class="px-6 py-4">Periode & Durasi</th>
                        <th class="px-6 py-4">Status & Alur</th>
                        <th class="px-6 py-4 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/60">
                    @forelse ($applications as $app)
                        <tr class="hover:bg-slate-800/30 transition">
                            <td class="px-6 py-4">
                                <div class="font-bold text-white text-sm">{{ $app->student_name }}</div>
                                <div class="text-slate-400 font-mono text-[11px]">NIM: {{ $app->student_nim }}</div>
                                <div class="text-blue-400 text-[11px] mt-0.5">{{ $app->studyProgram->name ?? '-' }}</div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="font-semibold text-white">{{ $app->partnerInstitution->name }}</div>
                                <div class="text-slate-400 text-[11px] truncate max-w-xs">{{ $app->partnerInstitution->address }}</div>
                                @if ($app->proposal_title)
                                    <div class="text-slate-300 text-[11px] italic mt-1">"{{ $app->proposal_title }}"</div>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                <div class="font-medium text-slate-300">{{ $app->internshipPeriod->name }}</div>
                                <div class="text-slate-400 text-[11px] font-mono mt-0.5">
                                    {{ $app->start_date->format('d M Y') }} s/d {{ $app->end_date->format('d M Y') }}
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[11px] font-semibold border {{ $app->status_badge_classes }}">
                                    {{ $app->status_label }}
                                </span>
                                @if ($app->status === 'TIDAK_LENGKAP' && $app->review_notes)
                                    <p class="text-[11px] text-rose-400 mt-1 max-w-xs truncate" title="{{ $app->review_notes }}">
                                        Catatan TU: {{ $app->review_notes }}
                                    </p>
                                @endif
                                @if ($app->status === 'DITOLAK' && $app->review_notes)
                                    <p class="text-[11px] text-rose-400 mt-1 max-w-xs truncate" title="{{ $app->review_notes }}">
                                        Alasan: {{ $app->review_notes }}
                                    </p>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-right">
                                <div class="inline-flex items-center gap-2">
                                    <a href="{{ route('internships.show', $app) }}"
                                        class="px-3 py-1.5 rounded-lg bg-blue-600/20 hover:bg-blue-600/30 text-blue-400 border border-blue-500/30 font-medium text-xs transition">
                                        Detail
                                    </a>

                                    @if ($app->isEditableByStudent() && (Auth::id() === $app->user_id || Auth::user()->hasRole('SUPERADMIN')))
                                        <a href="{{ route('internships.edit', $app) }}"
                                            class="px-3 py-1.5 rounded-lg bg-amber-600/20 hover:bg-amber-600/30 text-amber-300 border border-amber-500/30 font-medium text-xs transition">
                                            {{ $app->status === 'TIDAK_LENGKAP' ? 'Perbaiki & Ajukan' : 'Edit Draft' }}
                                        </a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-12 text-center text-slate-500">
                                <svg class="w-12 h-12 mx-auto text-slate-600 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                                <p class="text-sm font-semibold text-slate-400">Belum ada data pendaftaran magang</p>
                                <p class="text-xs text-slate-500 mt-1">Gunakan tombol "Daftar Magang Baru" untuk memulai pengajuan magang.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($applications->hasPages())
            <div class="p-4 border-t border-slate-800">
                {{ $applications->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
