@extends('layouts.app', ['title' => 'Detail Instansi - ' . $partnerInstitution->name])

@section('content')
<div class="max-w-4xl mx-auto space-y-6">

    <!-- Breadcrumb & Back -->
    <div class="flex items-center justify-between text-xs text-slate-400">
        <div class="flex items-center gap-2">
            <a href="{{ route('master.partner-institutions.index') }}" class="hover:text-white transition flex items-center gap-1">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                <span>Daftar Instansi</span>
            </a>
            <span>/</span>
            <span class="text-white font-medium">{{ $partnerInstitution->name }}</span>
        </div>

        <a href="{{ route('master.partner-institutions.edit', $partnerInstitution) }}"
            class="px-3.5 py-1.5 rounded-xl bg-amber-500/10 hover:bg-amber-500/20 border border-amber-500/20 text-amber-300 font-semibold text-xs transition flex items-center gap-1.5">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
            </svg>
            <span>Edit Data</span>
        </a>
    </div>

    <!-- Main Detail Card -->
    <div class="rounded-2xl bg-slate-900/80 border border-slate-800 p-6 sm:p-8 shadow-xl space-y-6">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-slate-800 pb-6">
            <div>
                <div class="flex items-center gap-2">
                    <span class="px-2.5 py-0.5 rounded text-xs font-mono font-semibold 
                        {{ $partnerInstitution->sector === 'BUMN' ? 'bg-amber-500/10 text-amber-300 border border-amber-500/20' : '' }}
                        {{ $partnerInstitution->sector === 'Pemerintah' ? 'bg-purple-500/10 text-purple-300 border border-purple-500/20' : '' }}
                        {{ $partnerInstitution->sector === 'Startup' ? 'bg-emerald-500/10 text-emerald-300 border border-emerald-500/20' : '' }}
                        {{ $partnerInstitution->sector === 'Swasta' ? 'bg-blue-500/10 text-blue-300 border border-blue-500/20' : '' }}">
                        {{ $partnerInstitution->sector }}
                    </span>
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold {{ $partnerInstitution->is_active ? 'bg-emerald-500/10 text-emerald-300 border border-emerald-500/20' : 'bg-rose-500/10 text-rose-300 border border-rose-500/20' }}">
                        {{ $partnerInstitution->is_active ? '● Aktif' : '○ Non-Aktif' }}
                    </span>
                </div>
                <h2 class="text-xl sm:text-2xl font-extrabold text-white mt-2">{{ $partnerInstitution->name }}</h2>
                @if($partnerInstitution->website)
                    <a href="{{ $partnerInstitution->website }}" target="_blank" rel="noopener" class="text-xs text-blue-400 hover:underline inline-flex items-center gap-1 mt-1">
                        <span>{{ $partnerInstitution->website }}</span>
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                        </svg>
                    </a>
                @endif
            </div>
        </div>

        <!-- Contact Info Grid -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div class="p-4 rounded-xl bg-slate-950/70 border border-slate-800">
                <span class="block text-[10px] text-slate-400 uppercase font-mono tracking-wider">Kontak Person (PIC)</span>
                <span class="block text-sm font-bold text-white mt-1">{{ $partnerInstitution->contact_person ?? '-' }}</span>
            </div>
            <div class="p-4 rounded-xl bg-slate-950/70 border border-slate-800">
                <span class="block text-[10px] text-slate-400 uppercase font-mono tracking-wider">Email Resmi</span>
                <a href="mailto:{{ $partnerInstitution->email }}" class="block text-xs font-mono text-blue-400 hover:underline mt-1 truncate">
                    {{ $partnerInstitution->email }}
                </a>
            </div>
            <div class="p-4 rounded-xl bg-slate-950/70 border border-slate-800">
                <span class="block text-[10px] text-slate-400 uppercase font-mono tracking-wider">Nomor Telepon</span>
                <span class="block text-xs font-mono text-slate-200 mt-1">{{ $partnerInstitution->phone ?? '-' }}</span>
            </div>
        </div>

        <!-- Address & Description -->
        <div class="space-y-4">
            <div class="p-4 rounded-xl bg-slate-950/40 border border-slate-800">
                <h4 class="text-xs font-bold text-slate-300 uppercase tracking-wider font-mono mb-1.5">Alamat Lengkap</h4>
                <p class="text-xs text-slate-200 leading-relaxed">{{ $partnerInstitution->address }}</p>
            </div>

            @if($partnerInstitution->description)
                <div class="p-4 rounded-xl bg-slate-950/40 border border-slate-800">
                    <h4 class="text-xs font-bold text-slate-300 uppercase tracking-wider font-mono mb-1.5">Keterangan / Profil</h4>
                    <p class="text-xs text-slate-300 leading-relaxed">{{ $partnerInstitution->description }}</p>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
