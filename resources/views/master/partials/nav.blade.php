<div class="mb-6 flex flex-wrap items-center justify-between gap-4 border-b border-slate-800 pb-4">
    <!-- Master Data Navigation Tabs -->
    <div class="flex flex-wrap items-center gap-2">
        <a href="{{ route('master.study-programs.index') }}"
            class="px-3.5 py-2 rounded-xl text-xs font-semibold transition flex items-center gap-2 {{ request()->routeIs('master.study-programs.*') ? 'bg-blue-600 text-white shadow-md shadow-blue-600/20' : 'bg-slate-900 text-slate-400 hover:text-white hover:bg-slate-800 border border-slate-800' }}">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
            </svg>
            <span>Prodi</span>
        </a>

        <a href="{{ route('master.partner-institutions.index') }}"
            class="px-3.5 py-2 rounded-xl text-xs font-semibold transition flex items-center gap-2 {{ request()->routeIs('master.partner-institutions.*') ? 'bg-blue-600 text-white shadow-md shadow-blue-600/20' : 'bg-slate-900 text-slate-400 hover:text-white hover:bg-slate-800 border border-slate-800' }}">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
            </svg>
            <span>Instansi</span>
        </a>

        <a href="{{ route('master.courses.index') }}"
            class="px-3.5 py-2 rounded-xl text-xs font-semibold transition flex items-center gap-2 {{ request()->routeIs('master.courses.*') ? 'bg-blue-600 text-white shadow-md shadow-blue-600/20' : 'bg-slate-900 text-slate-400 hover:text-white hover:bg-slate-800 border border-slate-800' }}">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
            </svg>
            <span>Mata Kuliah</span>
        </a>

        <a href="{{ route('master.internship-periods.index') }}"
            class="px-3.5 py-2 rounded-xl text-xs font-semibold transition flex items-center gap-2 {{ request()->routeIs('master.internship-periods.*') ? 'bg-blue-600 text-white shadow-md shadow-blue-600/20' : 'bg-slate-900 text-slate-400 hover:text-white hover:bg-slate-800 border border-slate-800' }}">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
            </svg>
            <span>Periode Magang</span>
        </a>

        <a href="{{ route('master.users.index') }}"
            class="px-3.5 py-2 rounded-xl text-xs font-semibold transition flex items-center gap-2 {{ request()->routeIs('master.users.*') ? 'bg-blue-600 text-white shadow-md shadow-blue-600/20' : 'bg-slate-900 text-slate-400 hover:text-white hover:bg-slate-800 border border-slate-800' }}">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
            </svg>
            <span>Pengguna</span>
        </a>

        <a href="{{ route('master.roles.index') }}"
            class="px-3.5 py-2 rounded-xl text-xs font-semibold transition flex items-center gap-2 {{ request()->routeIs('master.roles.*') ? 'bg-blue-600 text-white shadow-md shadow-blue-600/20' : 'bg-slate-900 text-slate-400 hover:text-white hover:bg-slate-800 border border-slate-800' }}">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
            </svg>
            <span>Role</span>
        </a>

        <a href="{{ route('master.user-roles.index') }}"
            class="px-3.5 py-2 rounded-xl text-xs font-semibold transition flex items-center gap-2 {{ request()->routeIs('master.user-roles.*') ? 'bg-blue-600 text-white shadow-md shadow-blue-600/20' : 'bg-slate-900 text-slate-400 hover:text-white hover:bg-slate-800 border border-slate-800' }}">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
            </svg>
            <span>User Roles</span>
        </a>

        <a href="{{ route('master.audit-logs.index') }}"
            class="px-3.5 py-2 rounded-xl text-xs font-semibold transition flex items-center gap-2 {{ request()->routeIs('master.audit-logs.*') ? 'bg-purple-600 text-white shadow-md shadow-purple-600/20' : 'bg-slate-900 text-slate-400 hover:text-white hover:bg-slate-800 border border-slate-800' }}">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
            </svg>
            <span>Audit Log</span>
        </a>
    </div>

    <!-- Quick Indicator / Breadcrumb -->
    <div class="flex items-center gap-2 text-xs text-slate-400">
        <span class="px-2.5 py-1 rounded-md bg-purple-500/10 border border-purple-500/20 text-purple-300 font-mono text-[11px]">
            Phase 1C • Master Data
        </span>
    </div>
</div>
