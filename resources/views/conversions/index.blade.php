@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h2 class="text-2xl font-extrabold text-white tracking-tight flex items-center gap-3">
                <span class="w-10 h-10 rounded-xl bg-gradient-to-tr from-cyan-600 to-blue-600 flex items-center justify-center text-white shadow-lg shadow-cyan-500/20">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
                    </svg>
                </span>
                Konversi Mata Kuliah
            </h2>
            <p class="text-sm text-slate-400 mt-1">
                Ekuivalensi dan rekognisi kegiatan magang ke dalam mata kuliah kurikulum.
            </p>
        </div>

        @if ($approvedInternships->isNotEmpty())
            <a href="{{ route('conversions.create') }}"
                class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-500 text-white font-semibold text-xs transition shadow-lg shadow-blue-600/30">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                <span>Ajukan Konversi MK</span>
            </a>
        @endif
    </div>

    <!-- Filter & Search -->
    <div class="p-4 rounded-2xl bg-slate-900 border border-slate-800 flex flex-col md:flex-row items-center justify-between gap-4">
        <form method="GET" action="{{ route('conversions.index') }}" class="w-full flex flex-col sm:flex-row items-center gap-3">
            <div class="relative flex-1 w-full">
                <input type="text" name="search" value="{{ request('search') }}"
                    placeholder="Cari nama atau kode mata kuliah..."
                    class="w-full pl-10 pr-4 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white text-xs focus:ring-2 focus:ring-blue-500 focus:outline-none">
                <svg class="w-4 h-4 text-slate-500 absolute left-3 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
            </div>

            <select name="status" class="w-full sm:w-auto px-3 py-2 rounded-xl bg-slate-950 border border-slate-800 text-slate-300 text-xs focus:ring-2 focus:ring-blue-500 focus:outline-none">
                <option value="">Semua Status</option>
                <option value="DIAJUKAN" {{ request('status') === 'DIAJUKAN' ? 'selected' : '' }}>Diajukan</option>
                <option value="DISETUJUI_DOSEN_MK" {{ request('status') === 'DISETUJUI_DOSEN_MK' ? 'selected' : '' }}>Disetujui Dosen MK</option>
                <option value="DIVERIFIKASI_DOSBING" {{ request('status') === 'DIVERIFIKASI_DOSBING' ? 'selected' : '' }}>Diverifikasi Dosbing</option>
                <option value="DISETUJUI_WADEK1" {{ request('status') === 'DISETUJUI_WADEK1' ? 'selected' : '' }}>Disetujui Wadek 1</option>
                <option value="DIKETAHUI_KAPRODI" {{ request('status') === 'DIKETAHUI_KAPRODI' ? 'selected' : '' }}>Diketahui Kaprodi (Selesai)</option>
                <option value="DITOLAK" {{ request('status') === 'DITOLAK' ? 'selected' : '' }}>Ditolak</option>
            </select>

            <button type="submit" class="w-full sm:w-auto px-4 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-white font-medium text-xs transition">
                Filter
            </button>
            @if(request()->hasAny(['search', 'status']))
                <a href="{{ route('conversions.index') }}" class="text-xs text-slate-400 hover:text-white underline">Reset</a>
            @endif
        </form>
    </div>

    <!-- Conversions Table -->
    <div class="rounded-2xl bg-slate-900 border border-slate-800 overflow-hidden shadow-xl">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-300">
                <thead class="bg-slate-950/70 text-slate-400 uppercase font-mono text-[10px] tracking-wider border-b border-slate-800">
                    <tr>
                        <th class="py-3 px-4">Mata Kuliah</th>
                        <th class="py-3 px-4">Instansi & Pembimbing</th>
                        <th class="py-3 px-4">Status Alur</th>
                        <th class="py-3 px-4">Tanggal Pengajuan</th>
                        <th class="py-3 px-4 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/60 font-medium">
                    @forelse ($conversions as $conv)
                        <tr class="hover:bg-slate-800/30 transition">
                            <td class="py-3.5 px-4">
                                <div class="font-bold text-white text-sm">{{ $conv->course->name }}</div>
                                <div class="text-[11px] text-slate-400 font-mono mt-0.5">
                                    {{ $conv->course->code }} • {{ $conv->course->credits }} SKS • Semester {{ $conv->course->semester }}
                                </div>
                            </td>
                            <td class="py-3.5 px-4">
                                <div class="text-slate-200">{{ $conv->internshipApplication->partnerInstitution->name }}</div>
                                <div class="text-[11px] text-emerald-400 mt-0.5">
                                    Dosbing: {{ $conv->internshipApplication->advisor->name ?? 'Belum Ditentukan' }}
                                </div>
                            </td>
                            <td class="py-3.5 px-4">
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[10px] font-bold border {{ $conv->status_badge_classes }}">
                                    {{ $conv->status_label }}
                                </span>
                                @if($conv->isRejected())
                                    <p class="text-[10px] text-rose-400/90 mt-1 max-w-xs truncate" title="{{ $conv->rejection_reason }}">
                                        Alasan: {{ $conv->rejection_reason }}
                                    </p>
                                @endif
                            </td>
                            <td class="py-3.5 px-4 text-slate-400 font-mono text-[11px]">
                                {{ $conv->created_at->format('d M Y, H:i') }}
                            </td>
                            <td class="py-3.5 px-4 text-right space-x-2">
                                <a href="{{ route('conversions.show', $conv->id) }}"
                                    class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg bg-blue-600/20 hover:bg-blue-600/30 text-blue-300 border border-blue-500/30 text-xs font-semibold transition">
                                    Detail
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="py-12 text-center text-slate-500">
                                <svg class="w-12 h-12 mx-auto text-slate-600 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
                                </svg>
                                <p class="text-sm font-semibold text-slate-400">Belum ada pengajuan konversi mata kuliah</p>
                                <p class="text-xs text-slate-500 mt-1">Pastikan pendaftaran magang Anda sudah disetujui resmi dan memiliki Dosen Pembimbing aktif.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($conversions->hasPages())
            <div class="p-4 border-t border-slate-800">
                {{ $conversions->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
