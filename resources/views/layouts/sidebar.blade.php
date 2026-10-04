@php
    $currentUser = Auth::user();
    $isSuperadmin = $currentUser->hasRole('SUPERADMIN');
    $hasMhs = $currentUser->hasRole('MHS');
    $hasTu = $currentUser->hasRole('TU');
    $hasKaprodi = $currentUser->hasRole('KAPRODI');
    $hasDosbing = $currentUser->hasRole('DOSBING');
    $hasDosenMk = $currentUser->hasRole('DOSEN_MK');
    $hasWadek1 = $currentUser->hasRole('WADEK1');
@endphp

<aside id="appSidebar" class="hidden lg:flex w-64 bg-slate-950 border-r border-white/5 flex-col flex-shrink-0 z-30 transition-all duration-300">
    <!-- Brand Header -->
    <div class="h-16 flex items-center px-6 border-b border-white/5 gap-3">
        <div class="w-8 h-8 rounded-xl bg-white text-slate-950 flex items-center justify-center font-bold text-xs shadow-sm">
            SI
        </div>
        <div>
            <span class="font-bold text-sm tracking-tight text-white block leading-none">SIMAGANG</span>
            <span class="text-[10px] text-slate-500 font-mono">PORTAL MAGANG</span>
        </div>
    </div>

    <!-- User Mini Profile -->
    <div class="p-4 mx-3 my-3 rounded-2xl bg-white/5 border border-white/5">
        <div class="flex items-center gap-3">
            <div class="w-9 h-9 rounded-xl bg-indigo-500/20 text-indigo-300 border border-indigo-500/30 flex items-center justify-center font-bold text-xs">
                {{ strtoupper(substr($currentUser->name, 0, 2)) }}
            </div>
            <div class="overflow-hidden">
                <p class="text-xs font-semibold text-white truncate" title="{{ $currentUser->name }}">{{ $currentUser->name }}</p>
                <div class="flex flex-wrap gap-1 mt-1">
                    @foreach ($currentUser->roles as $role)
                        <span class="px-1.5 py-0.5 rounded text-[9px] font-mono font-medium bg-white/10 text-slate-300">
                            {{ $role->name }}
                        </span>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    <!-- Navigation Menu Items -->
    <nav class="flex-1 px-3 py-2 space-y-1 overflow-y-auto custom-scrollbar text-xs">
        
        <!-- General Dashboard -->
        <a href="{{ route('dashboard') }}"
            class="flex items-center gap-3 px-3 py-2.5 rounded-xl font-medium transition-all duration-200 {{ request()->routeIs('dashboard') ? 'bg-white/10 text-white shadow-sm' : 'text-slate-400 hover:text-white hover:bg-white/5' }}">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" /></svg>
            <span>Dashboard</span>
        </a>

        <!-- ROLE: MHS MENU -->
        @if ($hasMhs || $isSuperadmin)
            <div class="pt-4 pb-1.5 px-3 text-[10px] font-semibold text-slate-500 uppercase tracking-widest">
                Mahasiswa
            </div>
            <a href="{{ route('internships.index') }}"
                class="flex items-center gap-3 px-3 py-2 rounded-xl transition-all duration-200 {{ request()->routeIs('internships.*') ? 'bg-white/10 text-white font-medium' : 'text-slate-400 hover:text-white hover:bg-white/5' }}">
                <span>Pengajuan Magang</span>
            </a>
            <a href="{{ route('conversions.index') }}"
                class="flex items-center gap-3 px-3 py-2 rounded-xl transition-all duration-200 {{ request()->routeIs('conversions.*') ? 'bg-white/10 text-white font-medium' : 'text-slate-400 hover:text-white hover:bg-white/5' }}">
                <span>Konversi MK</span>
            </a>
            <a href="{{ route('logbooks.index') }}"
                class="flex items-center gap-3 px-3 py-2 rounded-xl transition-all duration-200 {{ request()->routeIs('logbooks.*') ? 'bg-white/10 text-white font-medium' : 'text-slate-400 hover:text-white hover:bg-white/5' }}">
                <span>Logbook Mingguan</span>
            </a>
            <a href="{{ route('submissions.index') }}"
                class="flex items-center gap-3 px-3 py-2 rounded-xl transition-all duration-200 {{ request()->routeIs('submissions.*') ? 'bg-white/10 text-white font-medium' : 'text-slate-400 hover:text-white hover:bg-white/5' }}">
                <span>Tugas Mata Kuliah</span>
            </a>
            <a href="{{ route('seminars.index') }}"
                class="flex items-center gap-3 px-3 py-2 rounded-xl transition-all duration-200 {{ request()->routeIs('seminars.*') ? 'bg-white/10 text-white font-medium' : 'text-slate-400 hover:text-white hover:bg-white/5' }}">
                <span>Seminar Magang</span>
            </a>
        @endif

        <!-- ROLE: TU MENU -->
        @if ($hasTu || $isSuperadmin)
            <div class="pt-4 pb-1.5 px-3 text-[10px] font-semibold text-slate-500 uppercase tracking-widest">
                Tata Usaha
            </div>
            <a href="{{ route('tu.internships.index') }}"
                class="flex items-center gap-3 px-3 py-2 rounded-xl transition-all duration-200 {{ request()->routeIs('tu.internships.*') ? 'bg-white/10 text-white font-medium' : 'text-slate-400 hover:text-white hover:bg-white/5' }}">
                <span>Verifikasi Dokumen</span>
            </a>
        @endif

        <!-- ROLE: DOSBING & DOSEN_MK MENU -->
        @if ($hasDosbing || $hasDosenMk || $isSuperadmin)
            <div class="pt-4 pb-1.5 px-3 text-[10px] font-semibold text-slate-500 uppercase tracking-widest">
                Portal Dosen
            </div>
            <a href="{{ route('academic.portal') }}"
                class="flex items-center gap-3 px-3 py-2 rounded-xl transition-all duration-200 {{ request()->routeIs('academic.portal') ? 'bg-white/10 text-white font-medium' : 'text-slate-400 hover:text-white hover:bg-white/5' }}">
                <span>Portal Akademik</span>
            </a>
            @if ($hasDosenMk || $isSuperadmin)
                <a href="{{ route('dosen-mk.conversions.index') }}"
                    class="flex items-center gap-3 px-3 py-2 rounded-xl transition-all duration-200 {{ request()->routeIs('dosen-mk.conversions.*') ? 'bg-white/10 text-white font-medium' : 'text-slate-400 hover:text-white hover:bg-white/5' }}">
                    <span>Queue Konversi MK</span>
                </a>
                <a href="{{ route('dosen-mk.components.index') }}"
                    class="flex items-center gap-3 px-3 py-2 rounded-xl transition-all duration-200 {{ request()->routeIs('dosen-mk.components.*') ? 'bg-white/10 text-white font-medium' : 'text-slate-400 hover:text-white hover:bg-white/5' }}">
                    <span>Komponen Tugas</span>
                </a>
                <a href="{{ route('dosen-mk.submissions.index') }}"
                    class="flex items-center gap-3 px-3 py-2 rounded-xl transition-all duration-200 {{ request()->routeIs('dosen-mk.submissions.*') ? 'bg-white/10 text-white font-medium' : 'text-slate-400 hover:text-white hover:bg-white/5' }}">
                    <span>Review Tugas</span>
                </a>
                <a href="{{ route('dosen-mk.seminars.index') }}"
                    class="flex items-center gap-3 px-3 py-2 rounded-xl transition-all duration-200 {{ request()->routeIs('dosen-mk.seminars.*') ? 'bg-white/10 text-white font-medium' : 'text-slate-400 hover:text-white hover:bg-white/5' }}">
                    <span>Jadwal Seminar</span>
                </a>
            @endif
            @if ($hasDosbing || $isSuperadmin)
                <a href="{{ route('academic.advisor-assignments.index') }}"
                    class="flex items-center gap-3 px-3 py-2 rounded-xl transition-all duration-200 {{ request()->routeIs('academic.advisor-assignments.*') ? 'bg-white/10 text-white font-medium' : 'text-slate-400 hover:text-white hover:bg-white/5' }}">
                    <span>Permintaan Bimbingan</span>
                </a>
                <a href="{{ route('academic.conversions.index') }}"
                    class="flex items-center gap-3 px-3 py-2 rounded-xl transition-all duration-200 {{ request()->routeIs('academic.conversions.*') ? 'bg-white/10 text-white font-medium' : 'text-slate-400 hover:text-white hover:bg-white/5' }}">
                    <span>Verifikasi Konversi</span>
                </a>
                <a href="{{ route('academic.seminars.index') }}"
                    class="flex items-center gap-3 px-3 py-2 rounded-xl transition-all duration-200 {{ request()->routeIs('academic.seminars.*') ? 'bg-white/10 text-white font-medium' : 'text-slate-400 hover:text-white hover:bg-white/5' }}">
                    <span>Konfirmasi Seminar</span>
                </a>
            @endif
        @endif

        <!-- ROLE: KAPRODI & WADEK1 MENU -->
        @if ($hasKaprodi || $hasWadek1 || $isSuperadmin)
            <div class="pt-4 pb-1.5 px-3 text-[10px] font-semibold text-slate-500 uppercase tracking-widest">
                Pimpinan
            </div>
            @if ($hasKaprodi || $isSuperadmin)
                <a href="{{ route('kaprodi.internships.index') }}"
                    class="flex items-center gap-3 px-3 py-2 rounded-xl transition-all duration-200 {{ request()->routeIs('kaprodi.internships.*') ? 'bg-white/10 text-white font-medium' : 'text-slate-400 hover:text-white hover:bg-white/5' }}">
                    <span>Persetujuan Kaprodi</span>
                </a>
                <a href="{{ route('kaprodi.advisors.index') }}"
                    class="flex items-center gap-3 px-3 py-2 rounded-xl transition-all duration-200 {{ request()->routeIs('kaprodi.advisors.*') ? 'bg-white/10 text-white font-medium' : 'text-slate-400 hover:text-white hover:bg-white/5' }}">
                    <span>Penentuan Dospem</span>
                </a>
                <a href="{{ route('kaprodi.conversions.index') }}"
                    class="flex items-center gap-3 px-3 py-2 rounded-xl transition-all duration-200 {{ request()->routeIs('kaprodi.conversions.*') ? 'bg-white/10 text-white font-medium' : 'text-slate-400 hover:text-white hover:bg-white/5' }}">
                    <span>Pengesahan Konversi</span>
                </a>
                <a href="{{ route('kaprodi.seminars.index') }}"
                    class="flex items-center gap-3 px-3 py-2 rounded-xl transition-all duration-200 {{ request()->routeIs('kaprodi.seminars.*') ? 'bg-white/10 text-white font-medium' : 'text-slate-400 hover:text-white hover:bg-white/5' }}">
                    <span>Mengetahui Seminar</span>
                </a>
            @endif
            @if ($hasWadek1 || $isSuperadmin)
                <a href="{{ route('wadek1.internships.index') }}"
                    class="flex items-center gap-3 px-3 py-2 rounded-xl transition-all duration-200 {{ request()->routeIs('wadek1.internships.*') ? 'bg-white/10 text-white font-medium' : 'text-slate-400 hover:text-white hover:bg-white/5' }}">
                    <span>Approval Wadek 1 (Magang)</span>
                </a>
                <a href="{{ route('wadek1.conversions.index') }}"
                    class="flex items-center gap-3 px-3 py-2 rounded-xl transition-all duration-200 {{ request()->routeIs('wadek1.conversions.*') ? 'bg-white/10 text-white font-medium' : 'text-slate-400 hover:text-white hover:bg-white/5' }}">
                    <span>Approval Wadek 1 (Konversi)</span>
                </a>
                <a href="{{ route('wadek1.seminars.index') }}"
                    class="flex items-center gap-3 px-3 py-2 rounded-xl transition-all duration-200 {{ request()->routeIs('wadek1.seminars.*') ? 'bg-white/10 text-white font-medium' : 'text-slate-400 hover:text-white hover:bg-white/5' }}">
                    <span>Approval Wadek 1 (Seminar)</span>
                </a>
            @endif
        @endif

        <!-- ROLE: SUPERADMIN & KAPRODI PANEL -->
        @if ($isSuperadmin)
            <div class="pt-4 pb-1.5 px-3 text-[10px] font-semibold text-slate-500 uppercase tracking-widest">
                Administrator
            </div>
            <a href="{{ route('admin.superadmin') }}"
                class="flex items-center gap-3 px-3 py-2 rounded-xl transition-all duration-200 {{ request()->routeIs('admin.superadmin') ? 'bg-white/10 text-white font-medium' : 'text-slate-400 hover:text-white hover:bg-white/5' }}">
                <span>Panel Superadmin</span>
            </a>

            <div class="pt-3 pb-1 px-3 text-[10px] font-semibold text-slate-600 uppercase tracking-widest">
                Master Data
            </div>
            <a href="{{ route('master.study-programs.index') }}"
                class="flex items-center gap-3 px-3 py-1.5 rounded-xl transition-all duration-200 {{ request()->routeIs('master.study-programs.*') ? 'bg-white/10 text-white font-medium' : 'text-slate-400 hover:text-white hover:bg-white/5' }}">
                <span>Prodi</span>
            </a>
            <a href="{{ route('master.partner-institutions.index') }}"
                class="flex items-center gap-3 px-3 py-1.5 rounded-xl transition-all duration-200 {{ request()->routeIs('master.partner-institutions.*') ? 'bg-white/10 text-white font-medium' : 'text-slate-400 hover:text-white hover:bg-white/5' }}">
                <span>Instansi</span>
            </a>
            <a href="{{ route('master.courses.index') }}"
                class="flex items-center gap-3 px-3 py-1.5 rounded-xl transition-all duration-200 {{ request()->routeIs('master.courses.*') ? 'bg-white/10 text-white font-medium' : 'text-slate-400 hover:text-white hover:bg-white/5' }}">
                <span>Mata Kuliah</span>
            </a>
            <a href="{{ route('master.internship-periods.index') }}"
                class="flex items-center gap-3 px-3 py-1.5 rounded-xl transition-all duration-200 {{ request()->routeIs('master.internship-periods.*') ? 'bg-white/10 text-white font-medium' : 'text-slate-400 hover:text-white hover:bg-white/5' }}">
                <span>Periode Magang</span>
            </a>
            <a href="{{ route('master.users.index') }}"
                class="flex items-center gap-3 px-3 py-1.5 rounded-xl transition-all duration-200 {{ request()->routeIs('master.users.*') ? 'bg-white/10 text-white font-medium' : 'text-slate-400 hover:text-white hover:bg-white/5' }}">
                <span>Pengguna</span>
            </a>
            <a href="{{ route('master.roles.index') }}"
                class="flex items-center gap-3 px-3 py-1.5 rounded-xl transition-all duration-200 {{ request()->routeIs('master.roles.*') ? 'bg-white/10 text-white font-medium' : 'text-slate-400 hover:text-white hover:bg-white/5' }}">
                <span>Role</span>
            </a>
            <a href="{{ route('master.user-roles.index') }}"
                class="flex items-center gap-3 px-3 py-1.5 rounded-xl transition-all duration-200 {{ request()->routeIs('master.user-roles.*') ? 'bg-white/10 text-white font-medium' : 'text-slate-400 hover:text-white hover:bg-white/5' }}">
                <span>User Roles</span>
            </a>
            <a href="{{ route('master.audit-logs.index') }}"
                class="flex items-center gap-3 px-3 py-1.5 rounded-xl transition-all duration-200 {{ request()->routeIs('master.audit-logs.*') ? 'bg-white/10 text-white font-medium' : 'text-slate-400 hover:text-white hover:bg-white/5' }}">
                <span>Audit Log</span>
            </a>
        @endif

        <!-- User Settings & Profile -->
        <div class="pt-4 pb-1.5 px-3 text-[10px] font-semibold text-slate-500 uppercase tracking-widest">
            Pengaturan
        </div>
        <a href="{{ route('profile.show') }}"
            class="flex items-center gap-3 px-3 py-2 rounded-xl transition-all duration-200 {{ request()->routeIs('profile.*') ? 'bg-white/10 text-white font-medium' : 'text-slate-400 hover:text-white hover:bg-white/5' }}">
            <span>Profil & Keamanan</span>
        </a>
    </nav>

    <!-- Logout Action Button in Footer -->
    <div class="p-3 border-t border-white/5">
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit"
                class="w-full flex items-center justify-center gap-2 px-3 py-2 rounded-xl bg-white/5 hover:bg-rose-500/10 border border-white/5 hover:border-rose-500/20 text-slate-400 hover:text-rose-400 font-medium text-xs transition-all duration-200 cursor-pointer">
                <span>Keluar</span>
            </button>
        </form>
    </div>
</aside>
