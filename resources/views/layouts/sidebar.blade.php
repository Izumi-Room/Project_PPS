@php
    $currentUser = Auth::user();
    $isSuperadmin = $currentUser->hasRole('SUPERADMIN'); // Automatically true for KAPRODI
    $hasMhs = $currentUser->hasRole('MHS');
    $hasTu = $currentUser->hasRole('TU');
    $hasKaprodi = $currentUser->hasRole('KAPRODI');
    $hasDosbing = $currentUser->hasRole('DOSBING');
    $hasDosenMk = $currentUser->hasRole('DOSEN_MK');
    $hasWadek1 = $currentUser->hasRole('WADEK1');
@endphp

<aside id="appSidebar" class="hidden lg:flex w-64 bg-slate-900 border-r border-slate-800 flex-col flex-shrink-0 transition-all duration-300 z-30">
    <!-- Brand Header -->
    <div class="h-16 flex items-center px-6 border-b border-slate-800 gap-3">
        <div class="w-9 h-9 rounded-xl bg-gradient-to-tr from-blue-600 to-indigo-600 flex items-center justify-center text-white shadow-md shadow-blue-500/20">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
            </svg>
        </div>
        <div>
            <span class="font-extrabold text-base tracking-tight text-white">SIMAGANG</span>
            <span class="block text-[10px] text-slate-400 font-mono">PORTAL RBAC</span>
        </div>
    </div>

    <!-- User Mini Badge Profile -->
    <div class="p-4 mx-3 my-3 rounded-2xl bg-slate-950/60 border border-slate-800/80">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-full bg-gradient-to-tr from-blue-500 to-indigo-500 flex items-center justify-center font-bold text-white text-sm shadow">
                {{ strtoupper(substr($currentUser->name, 0, 2)) }}
            </div>
            <div class="overflow-hidden">
                <p class="text-xs font-bold text-white truncate" title="{{ $currentUser->name }}">{{ $currentUser->name }}</p>
                <div class="flex flex-wrap gap-1 mt-1">
                    @foreach ($currentUser->roles as $role)
                        <span class="px-1.5 py-0.5 rounded text-[9px] font-mono font-semibold 
                            {{ $role->name === 'SUPERADMIN' ? 'bg-rose-500/20 text-rose-300' : '' }}
                            {{ $role->name === 'KAPRODI' ? 'bg-purple-500/20 text-purple-300' : '' }}
                            {{ $role->name === 'MHS' ? 'bg-blue-500/20 text-blue-300' : '' }}
                            {{ $role->name === 'TU' ? 'bg-amber-500/20 text-amber-300' : '' }}
                            {{ $role->name === 'DOSBING' ? 'bg-emerald-500/20 text-emerald-300' : '' }}
                            {{ $role->name === 'DOSEN_MK' ? 'bg-teal-500/20 text-teal-300' : '' }}
                            {{ $role->name === 'WADEK1' ? 'bg-indigo-500/20 text-indigo-300' : '' }}">
                            {{ $role->name }}
                        </span>
                    @endforeach
                    @if ($hasKaprodi && !$currentUser->roles->contains('name', 'SUPERADMIN'))
                        <span class="px-1.5 py-0.5 rounded text-[9px] font-mono font-semibold bg-rose-500/20 text-rose-300" title="Inherited from KAPRODI">
                            +SUPERADMIN*
                        </span>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Navigation Menu Items -->
    <nav class="flex-1 px-3 py-2 space-y-1 overflow-y-auto custom-scrollbar text-xs">
        
        <!-- General Dashboard -->
        <a href="{{ route('dashboard') }}"
            class="flex items-center gap-3 px-3 py-2.5 rounded-xl font-medium transition {{ request()->routeIs('dashboard') ? 'bg-blue-600 text-white shadow-md shadow-blue-600/30' : 'text-slate-400 hover:text-white hover:bg-slate-800/60' }}">
            <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
            </svg>
            <span>Dashboard</span>
        </a>

        <!-- ROLE: MHS MENU -->
        @if ($hasMhs || $isSuperadmin)
            <div class="pt-4 pb-1 px-3 text-[10px] font-bold text-slate-500 uppercase tracking-wider">
                Mahasiswa (MHS)
            </div>
            <a href="{{ route('internships.index') }}"
                class="flex items-center gap-3 px-3 py-2 rounded-xl transition {{ request()->routeIs('internships.*') ? 'bg-blue-600 text-white shadow-md shadow-blue-600/30' : 'text-slate-400 hover:text-white hover:bg-slate-800/60' }}">
                <svg class="w-4 h-4 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                <span>Pengajuan Magang</span>
            </a>
            <a href="{{ route('conversions.index') }}"
                class="flex items-center gap-3 px-3 py-2 rounded-xl transition {{ request()->routeIs('conversions.*') ? 'bg-blue-600 text-white shadow-md shadow-blue-600/30' : 'text-slate-400 hover:text-white hover:bg-slate-800/60' }}">
                <svg class="w-4 h-4 text-cyan-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
                </svg>
                <span>Konversi MK</span>
            </a>
            <a href="{{ route('logbooks.index') }}"
                class="flex items-center gap-3 px-3 py-2 rounded-xl transition {{ request()->routeIs('logbooks.*') ? 'bg-blue-600 text-white shadow-md shadow-blue-600/30' : 'text-slate-400 hover:text-white hover:bg-slate-800/60' }}">
                <svg class="w-4 h-4 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                </svg>
                <span>Logbook Mingguan</span>
            </a>
            <a href="{{ route('submissions.index') }}"
                class="flex items-center gap-3 px-3 py-2 rounded-xl transition {{ request()->routeIs('submissions.*') ? 'bg-blue-600 text-white shadow-md shadow-blue-600/30' : 'text-slate-400 hover:text-white hover:bg-slate-800/60' }}">
                <svg class="w-4 h-4 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                </svg>
                <span>Pengumpulan Tugas MK</span>
            </a>
            <a href="{{ route('seminars.index') }}"
                class="flex items-center gap-3 px-3 py-2 rounded-xl transition {{ request()->routeIs('seminars.*') ? 'bg-blue-600 text-white shadow-md shadow-blue-600/30' : 'text-slate-400 hover:text-white hover:bg-slate-800/60' }}">
                <svg class="w-4 h-4 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
                <span>Seminar Magang</span>
            </a>
        @endif

        <!-- ROLE: TU MENU -->
        @if ($hasTu || $isSuperadmin)
            <div class="pt-4 pb-1 px-3 text-[10px] font-bold text-slate-500 uppercase tracking-wider">
                Tata Usaha (TU)
            </div>
            <a href="{{ route('tu.internships.index') }}"
                class="flex items-center gap-3 px-3 py-2 rounded-xl transition {{ request()->routeIs('tu.internships.*') ? 'bg-amber-600 text-white shadow-md shadow-amber-600/30' : 'text-slate-400 hover:text-white hover:bg-slate-800/60' }}">
                <svg class="w-4 h-4 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
                </svg>
                <span>Verifikasi Dokumen (TU)</span>
            </a>
        @endif

        <!-- ROLE: DOSBING & DOSEN_MK MENU -->
        @if ($hasDosbing || $hasDosenMk || $isSuperadmin)
            <div class="pt-4 pb-1 px-3 text-[10px] font-bold text-slate-500 uppercase tracking-wider">
                Portal Dosen (Dosbing / MK)
            </div>
            <a href="{{ route('academic.portal') }}"
                class="flex items-center gap-3 px-3 py-2 rounded-xl font-medium transition {{ request()->routeIs('academic.portal') ? 'bg-emerald-600/30 text-emerald-300 border border-emerald-500/30' : 'text-slate-400 hover:text-white hover:bg-slate-800/60' }}">
                <svg class="w-4 h-4 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                </svg>
                <span>Portal Akademik Dosen</span>
            </a>
            @if ($hasDosenMk || $isSuperadmin)
                <a href="{{ route('dosen-mk.conversions.index') }}"
                    class="flex items-center gap-3 px-3 py-2 rounded-xl transition {{ request()->routeIs('dosen-mk.conversions.*') ? 'bg-teal-600 text-white shadow-md shadow-teal-600/30' : 'text-slate-400 hover:text-white hover:bg-slate-800/60' }}">
                    <svg class="w-4 h-4 text-teal-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span>Queue Konversi MK</span>
                </a>
                <a href="{{ route('dosen-mk.components.index') }}"
                    class="flex items-center gap-3 px-3 py-2 rounded-xl transition {{ request()->routeIs('dosen-mk.components.*') ? 'bg-teal-600 text-white shadow-md shadow-teal-600/30' : 'text-slate-400 hover:text-white hover:bg-slate-800/60' }}">
                    <svg class="w-4 h-4 text-teal-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
                    </svg>
                    <span>Komponen Tugas MK</span>
                </a>
                <a href="{{ route('dosen-mk.submissions.index') }}"
                    class="flex items-center gap-3 px-3 py-2 rounded-xl transition {{ request()->routeIs('dosen-mk.submissions.*') ? 'bg-teal-600 text-white shadow-md shadow-teal-600/30' : 'text-slate-400 hover:text-white hover:bg-slate-800/60' }}">
                    <svg class="w-4 h-4 text-teal-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    <span>Review Tugas Mahasiswa</span>
                </a>
                <a href="{{ route('dosen-mk.seminars.index') }}"
                    class="flex items-center gap-3 px-3 py-2 rounded-xl transition {{ request()->routeIs('dosen-mk.seminars.*') ? 'bg-teal-600 text-white shadow-md shadow-teal-600/30' : 'text-slate-400 hover:text-white hover:bg-slate-800/60' }}">
                    <svg class="w-4 h-4 text-teal-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                    <span>Jadwal Seminar Magang</span>
                </a>
            @endif
            @if ($hasDosbing || $isSuperadmin)
                <a href="{{ route('academic.advisor-assignments.index') }}"
                    class="flex items-center gap-3 px-3 py-2 rounded-xl transition {{ request()->routeIs('academic.advisor-assignments.*') ? 'bg-emerald-600 text-white shadow-md shadow-emerald-600/30' : 'text-slate-400 hover:text-white hover:bg-slate-800/60' }}">
                    <svg class="w-4 h-4 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                    </svg>
                    <span>Permintaan Bimbingan (DOSBING)</span>
                </a>
                <a href="{{ route('academic.conversions.index') }}"
                    class="flex items-center gap-3 px-3 py-2 rounded-xl transition {{ request()->routeIs('academic.conversions.*') ? 'bg-emerald-600 text-white shadow-md shadow-emerald-600/30' : 'text-slate-400 hover:text-white hover:bg-slate-800/60' }}">
                    <svg class="w-4 h-4 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
                    </svg>
                    <span>Verifikasi Konversi (DOSBING)</span>
                </a>
                <a href="{{ route('academic.seminars.index') }}"
                    class="flex items-center gap-3 px-3 py-2 rounded-xl transition {{ request()->routeIs('academic.seminars.*') ? 'bg-emerald-600 text-white shadow-md shadow-emerald-600/30' : 'text-slate-400 hover:text-white hover:bg-slate-800/60' }}">
                    <svg class="w-4 h-4 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                    <span>Konfirmasi Seminar (DOSBING)</span>
                </a>
            @endif
        @endif

        <!-- ROLE: KAPRODI & WADEK1 MENU -->
        @if ($hasKaprodi || $hasWadek1 || $isSuperadmin)
            <div class="pt-4 pb-1 px-3 text-[10px] font-bold text-slate-500 uppercase tracking-wider">
                Pimpinan (Kaprodi / Dekanat)
            </div>
            @if ($hasKaprodi || $isSuperadmin)
                <a href="{{ route('kaprodi.internships.index') }}"
                    class="flex items-center gap-3 px-3 py-2 rounded-xl transition {{ request()->routeIs('kaprodi.internships.*') ? 'bg-purple-600 text-white shadow-md shadow-purple-600/30' : 'text-slate-400 hover:text-white hover:bg-slate-800/60' }}">
                    <svg class="w-4 h-4 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                    </svg>
                    <span>Persetujuan Kaprodi</span>
                </a>
                <a href="{{ route('kaprodi.advisors.index') }}"
                    class="flex items-center gap-3 px-3 py-2 rounded-xl transition {{ request()->routeIs('kaprodi.advisors.*') ? 'bg-purple-600 text-white shadow-md shadow-purple-600/30' : 'text-slate-400 hover:text-white hover:bg-slate-800/60' }}">
                    <svg class="w-4 h-4 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                    <span>Penentuan Dospem</span>
                </a>
                <a href="{{ route('kaprodi.conversions.index') }}"
                    class="flex items-center gap-3 px-3 py-2 rounded-xl transition {{ request()->routeIs('kaprodi.conversions.*') ? 'bg-purple-600 text-white shadow-md shadow-purple-600/30' : 'text-slate-400 hover:text-white hover:bg-slate-800/60' }}">
                    <svg class="w-4 h-4 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
                    </svg>
                    <span>Pengesahan Konversi MK</span>
                </a>
                <a href="{{ route('kaprodi.seminars.index') }}"
                    class="flex items-center gap-3 px-3 py-2 rounded-xl transition {{ request()->routeIs('kaprodi.seminars.*') ? 'bg-purple-600 text-white shadow-md shadow-purple-600/30' : 'text-slate-400 hover:text-white hover:bg-slate-800/60' }}">
                    <svg class="w-4 h-4 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                    <span>Mengetahui Seminar (Kaprodi)</span>
                </a>
            @endif
            @if ($hasWadek1 || $isSuperadmin)
                <a href="{{ route('wadek1.internships.index') }}"
                    class="flex items-center gap-3 px-3 py-2 rounded-xl transition {{ request()->routeIs('wadek1.internships.*') ? 'bg-indigo-600 text-white shadow-md shadow-indigo-600/30' : 'text-slate-400 hover:text-white hover:bg-slate-800/60' }}">
                    <svg class="w-4 h-4 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                    </svg>
                    <span>Approval Wadek 1 (Magang)</span>
                </a>
                <a href="{{ route('wadek1.conversions.index') }}"
                    class="flex items-center gap-3 px-3 py-2 rounded-xl transition {{ request()->routeIs('wadek1.conversions.*') ? 'bg-indigo-600 text-white shadow-md shadow-indigo-600/30' : 'text-slate-400 hover:text-white hover:bg-slate-800/60' }}">
                    <svg class="w-4 h-4 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span>Approval Wadek 1 (Konversi)</span>
                </a>
                <a href="{{ route('wadek1.seminars.index') }}"
                    class="flex items-center gap-3 px-3 py-2 rounded-xl transition {{ request()->routeIs('wadek1.seminars.*') ? 'bg-indigo-600 text-white shadow-md shadow-indigo-600/30' : 'text-slate-400 hover:text-white hover:bg-slate-800/60' }}">
                    <svg class="w-4 h-4 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span>Approval Seminar (Wadek 1)</span>
                </a>
            @endif
        @endif


        <!-- ROLE: SUPERADMIN & KAPRODI PANEL -->
        @if ($isSuperadmin)
            <div class="pt-4 pb-1 px-3 text-[10px] font-bold text-rose-400/90 uppercase tracking-wider flex items-center justify-between">
                <span>Super Administrator</span>
                <span class="text-[9px] bg-rose-500/20 text-rose-300 px-1.5 py-0.2 rounded font-mono">RBAC</span>
            </div>
            <a href="{{ route('admin.superadmin') }}"
                class="flex items-center gap-3 px-3 py-2 rounded-xl font-medium transition {{ request()->routeIs('admin.superadmin') ? 'bg-rose-600 text-white shadow-lg shadow-rose-600/30' : 'text-slate-400 hover:text-white hover:bg-slate-800/60' }}">
                <svg class="w-4 h-4 text-rose-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
                <span>Panel Superadmin</span>
            </a>

            <!-- Master Data Group in Sidebar -->
            <div class="pt-3 pb-1 px-3 text-[10px] font-bold text-blue-400 uppercase tracking-wider flex items-center justify-between">
                <span>Master Data</span>
                <span class="text-[9px] bg-blue-500/20 text-blue-300 px-1 py-0.2 rounded font-mono">1C</span>
            </div>
            <a href="{{ route('master.study-programs.index') }}"
                class="flex items-center gap-3 px-3 py-1.5 rounded-xl font-medium transition {{ request()->routeIs('master.study-programs.*') ? 'bg-blue-600/30 text-blue-300 border border-blue-500/30' : 'text-slate-400 hover:text-white hover:bg-slate-800/60' }}">
                <svg class="w-4 h-4 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                </svg>
                <span>1. Prodi</span>
            </a>
            <a href="{{ route('master.partner-institutions.index') }}"
                class="flex items-center gap-3 px-3 py-1.5 rounded-xl font-medium transition {{ request()->routeIs('master.partner-institutions.*') ? 'bg-blue-600/30 text-blue-300 border border-blue-500/30' : 'text-slate-400 hover:text-white hover:bg-slate-800/60' }}">
                <svg class="w-4 h-4 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                </svg>
                <span>2. Instansi</span>
            </a>
            <a href="{{ route('master.courses.index') }}"
                class="flex items-center gap-3 px-3 py-1.5 rounded-xl font-medium transition {{ request()->routeIs('master.courses.*') ? 'bg-blue-600/30 text-blue-300 border border-blue-500/30' : 'text-slate-400 hover:text-white hover:bg-slate-800/60' }}">
                <svg class="w-4 h-4 text-teal-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                </svg>
                <span>3. Mata Kuliah</span>
            </a>
            <a href="{{ route('master.internship-periods.index') }}"
                class="flex items-center gap-3 px-3 py-1.5 rounded-xl font-medium transition {{ request()->routeIs('master.internship-periods.*') ? 'bg-blue-600/30 text-blue-300 border border-blue-500/30' : 'text-slate-400 hover:text-white hover:bg-slate-800/60' }}">
                <svg class="w-4 h-4 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
                <span>4. Periode Magang</span>
            </a>
            <a href="{{ route('master.users.index') }}"
                class="flex items-center gap-3 px-3 py-1.5 rounded-xl font-medium transition {{ request()->routeIs('master.users.*') ? 'bg-blue-600/30 text-blue-300 border border-blue-500/30' : 'text-slate-400 hover:text-white hover:bg-slate-800/60' }}">
                <svg class="w-4 h-4 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                </svg>
                <span>5. Pengguna</span>
            </a>
            <a href="{{ route('master.roles.index') }}"
                class="flex items-center gap-3 px-3 py-1.5 rounded-xl font-medium transition {{ request()->routeIs('master.roles.*') ? 'bg-blue-600/30 text-blue-300 border border-blue-500/30' : 'text-slate-400 hover:text-white hover:bg-slate-800/60' }}">
                <svg class="w-4 h-4 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                </svg>
                <span>6. Role</span>
            </a>
            <a href="{{ route('master.user-roles.index') }}"
                class="flex items-center gap-3 px-3 py-1.5 rounded-xl font-medium transition {{ request()->routeIs('master.user-roles.*') ? 'bg-blue-600/30 text-blue-300 border border-blue-500/30' : 'text-slate-400 hover:text-white hover:bg-slate-800/60' }}">
                <svg class="w-4 h-4 text-pink-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                </svg>
                <span>7. User Roles</span>
            </a>
            <a href="{{ route('master.audit-logs.index') }}"
                class="flex items-center gap-3 px-3 py-1.5 rounded-xl font-medium transition {{ request()->routeIs('master.audit-logs.*') ? 'bg-purple-600/30 text-purple-300 border border-purple-500/30' : 'text-slate-400 hover:text-white hover:bg-slate-800/60' }}">
                <svg class="w-4 h-4 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
                </svg>
                <span>8. Audit Log</span>
            </a>
        @endif

        <!-- User Settings & Profile -->
        <div class="pt-4 pb-1 px-3 text-[10px] font-bold text-slate-500 uppercase tracking-wider">
            Pengaturan Akun
        </div>
        <a href="{{ route('profile.show') }}"
            class="flex items-center gap-3 px-3 py-2 rounded-xl font-medium transition {{ request()->routeIs('profile.*') ? 'bg-slate-800 text-white' : 'text-slate-400 hover:text-white hover:bg-slate-800/60' }}">
            <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
            </svg>
            <span>Profil & Keamanan</span>
        </a>
    </nav>

    <!-- Logout Action Button in Footer -->
    <div class="p-3 border-t border-slate-800">
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit"
                class="w-full flex items-center justify-center gap-2 px-3 py-2.5 rounded-xl bg-slate-950/80 hover:bg-rose-500/10 border border-slate-800 hover:border-rose-500/20 text-slate-400 hover:text-rose-400 font-semibold text-xs transition cursor-pointer">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                </svg>
                <span>Keluar (Logout)</span>
            </button>
        </form>
    </div>
</aside>
