@extends('layouts.app', ['title' => 'Dashboard Utama & Status RBAC'])

@section('content')
<div class="space-y-8 max-w-7xl mx-auto">
    <!-- Welcome Banner with Active Role Status -->
    <div class="relative overflow-hidden rounded-3xl bg-gradient-to-r from-blue-900/60 via-indigo-900/50 to-slate-900 border border-slate-800 p-6 sm:p-8 shadow-xl">
        <div class="relative z-10">
            <div class="flex flex-wrap items-center justify-between gap-4">
                <div>
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-blue-500/20 text-blue-300 border border-blue-500/30 mb-3">
                        <span class="w-2 h-2 rounded-full bg-emerald-400"></span>
                        Sesi Aktif Terautentikasi
                    </span>
                    <h2 class="text-2xl sm:text-3xl font-extrabold text-white tracking-tight">
                        Selamat Datang, {{ $user->name }}
                    </h2>
                    <p class="mt-1 text-sm text-slate-300">
                        Sistem Pendaftaran & Pengelolaan Magang Mahasiswa — Tahap 1B (Authentication & RBAC)
                    </p>
                </div>

                <!-- Roles Badges Display -->
                <div class="flex flex-col items-end">
                    <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2">Role Anda:</span>
                    <div class="flex flex-wrap gap-2 justify-end">
                        @forelse ($user->roles as $role)
                            <span class="px-3 py-1 rounded-xl text-xs font-mono font-bold tracking-wide shadow-sm
                                {{ $role->name === 'SUPERADMIN' ? 'bg-rose-500/20 text-rose-300 border border-rose-500/30' : '' }}
                                {{ $role->name === 'KAPRODI' ? 'bg-purple-500/20 text-purple-300 border border-purple-500/30' : '' }}
                                {{ $role->name === 'MHS' ? 'bg-blue-500/20 text-blue-300 border border-blue-500/30' : '' }}
                                {{ $role->name === 'TU' ? 'bg-amber-500/20 text-amber-300 border border-amber-500/30' : '' }}
                                {{ $role->name === 'DOSBING' ? 'bg-emerald-500/20 text-emerald-300 border border-emerald-500/30' : '' }}
                                {{ $role->name === 'DOSEN_MK' ? 'bg-teal-500/20 text-teal-300 border border-teal-500/30' : '' }}
                                {{ $role->name === 'WADEK1' ? 'bg-indigo-500/20 text-indigo-300 border border-indigo-500/30' : '' }}">
                                {{ $role->name }} ({{ $role->label }})
                            </span>
                        @empty
                            <span class="px-3 py-1 rounded-xl text-xs font-semibold bg-rose-500/20 text-rose-300 border border-rose-500/30">
                                Tanpa Role
                            </span>
                        @endforelse

                        @if ($user->hasRole('KAPRODI') && !$user->roles->contains('name', 'SUPERADMIN'))
                            <span class="px-3 py-1 rounded-xl text-xs font-mono font-bold tracking-wide bg-gradient-to-r from-rose-500/20 to-purple-500/20 text-rose-300 border border-rose-500/30" title="Aturan Khusus: Kaprodi otomatis mendapatkan hak Superadmin">
                                ⭐ KAPRODI &rarr; SUPERADMIN (Inherited)
                            </span>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Interactive RBAC Verification & Test Deck -->
    <div>
        <div class="flex items-center justify-between mb-4">
            <div>
                <h3 class="text-lg font-bold text-white tracking-tight">Pengujian Otorisasi & Pertahanan Backend</h3>
                <p class="text-xs text-slate-400">Verifikasi bahwa otorisasi diterapkan di Backend, Route, Service, dan API.</p>
            </div>
            <span class="text-xs font-mono text-emerald-400 bg-emerald-500/10 border border-emerald-500/20 px-2.5 py-1 rounded-lg">
                Backend Enforced
            </span>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
            <!-- Test 1: Superadmin Panel Access -->
            <div class="bg-slate-900/70 border border-slate-800 rounded-2xl p-5 flex flex-col justify-between hover:border-slate-700 transition">
                <div>
                    <div class="flex items-center justify-between mb-3">
                        <span class="w-8 h-8 rounded-lg bg-rose-500/10 text-rose-400 flex items-center justify-center font-bold text-xs">
                            01
                        </span>
                        <span class="text-[10px] font-mono px-2 py-0.5 rounded bg-slate-800 text-slate-300">
                            Route: role:SUPERADMIN
                        </span>
                    </div>
                    <h4 class="font-bold text-white text-sm">Akses Panel Superadmin</h4>
                    <p class="text-xs text-slate-400 mt-1 mb-4 leading-relaxed">
                        Hanya <strong class="text-rose-400">SUPERADMIN</strong> dan <strong class="text-purple-400">KAPRODI</strong> yang boleh masuk. Role lain (MHS, TU, Dosbing, dll.) akan ditolak dengan <strong class="text-rose-400">HTTP 403 Forbidden</strong>.
                    </p>
                </div>
                <a href="{{ route('admin.superadmin') }}"
                    class="w-full text-center py-2.5 px-4 rounded-xl text-xs font-semibold bg-rose-600 hover:bg-rose-500 text-white shadow-md shadow-rose-600/20 transition">
                    Uji Akses Superadmin &rarr;
                </a>
            </div>

            <!-- Test 2: Academic Portal Access -->
            <div class="bg-slate-900/70 border border-slate-800 rounded-2xl p-5 flex flex-col justify-between hover:border-slate-700 transition">
                <div>
                    <div class="flex items-center justify-between mb-3">
                        <span class="w-8 h-8 rounded-lg bg-emerald-500/10 text-emerald-400 flex items-center justify-center font-bold text-xs">
                            02
                        </span>
                        <span class="text-[10px] font-mono px-2 py-0.5 rounded bg-slate-800 text-slate-300">
                            Route: role:DOSBING,DOSEN_MK
                        </span>
                    </div>
                    <h4 class="font-bold text-white text-sm">Portal Akademik Dosen</h4>
                    <p class="text-xs text-slate-400 mt-1 mb-4 leading-relaxed">
                        Dapat diakses oleh <strong class="text-emerald-400">DOSBING</strong>, <strong class="text-teal-400">DOSEN_MK</strong>, atau user dengan <strong>kedua role tersebut</strong>. Mahasiswa atau TU akan ditolak.
                    </p>
                </div>
                <a href="{{ route('academic.portal') }}"
                    class="w-full text-center py-2.5 px-4 rounded-xl text-xs font-semibold bg-emerald-600 hover:bg-emerald-500 text-white shadow-md shadow-emerald-600/20 transition">
                    Uji Akses Portal Dosen &rarr;
                </a>
            </div>

            <!-- Test 3: Backend Service/Action Assertion -->
            <div class="bg-slate-900/70 border border-slate-800 rounded-2xl p-5 flex flex-col justify-between hover:border-slate-700 transition">
                <div>
                    <div class="flex items-center justify-between mb-3">
                        <span class="w-8 h-8 rounded-lg bg-indigo-500/10 text-indigo-400 flex items-center justify-center font-bold text-xs">
                            03
                        </span>
                        <span class="text-[10px] font-mono px-2 py-0.5 rounded bg-slate-800 text-slate-300">
                            Service: AuthorizationService
                        </span>
                    </div>
                    <h4 class="font-bold text-white text-sm">Uji Service Layer Backend</h4>
                    <p class="text-xs text-slate-400 mt-1 mb-4 leading-relaxed">
                        Memvalidasi eksekusi aksi di tingkat service class menggunakan <code class="text-blue-300">assertRole('SUPERADMIN')</code> untuk mencegah bypass URL.
                    </p>
                </div>
                <button type="button" onclick="testServiceAssertion()"
                    class="w-full text-center py-2.5 px-4 rounded-xl text-xs font-semibold bg-indigo-600 hover:bg-indigo-500 text-white shadow-md shadow-indigo-600/20 transition cursor-pointer">
                    Eksekusi Uji Service Backend
                </button>
            </div>
        </div>

        <!-- Service Test Result Output Box -->
        <div id="serviceTestBox" class="mt-4 hidden p-4 rounded-2xl border text-xs">
            <div class="flex items-center justify-between mb-2">
                <span class="font-bold" id="serviceTestTitle">Hasil Uji Service:</span>
                <span id="serviceTestStatus" class="font-mono px-2 py-0.5 rounded text-[10px]"></span>
            </div>
            <pre id="serviceTestContent" class="overflow-x-auto p-3 bg-slate-950/80 rounded-xl font-mono text-[11px]"></pre>
        </div>
    </div>

    <!-- Active User Permissions Pills -->
    <div class="bg-slate-900/70 border border-slate-800 rounded-3xl p-6 sm:p-8">
        <h3 class="text-base font-bold text-white mb-1">Daftar Hak Akses (Permissions) Efektif Anda</h3>
        <p class="text-xs text-slate-400 mb-4">
            Permission yang diperoleh dari akumulasi role aktif (atau seluruh izin jika berstatus Superadmin / Kaprodi):
        </p>

        <div class="flex flex-wrap gap-2">
            @php
                $userPermissions = [];
                if ($user->hasRole('SUPERADMIN')) {
                    $userPermissions = \App\Models\Permission::pluck('name', 'label')->toArray();
                } else {
                    foreach ($user->roles as $role) {
                        foreach ($role->permissions as $perm) {
                            $userPermissions[$perm->label] = $perm->name;
                        }
                    }
                }
            @endphp

            @forelse ($userPermissions as $label => $code)
                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-slate-800/90 border border-slate-700 text-xs text-slate-200">
                    <svg class="w-3.5 h-3.5 text-emerald-400" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                    </svg>
                    <span>{{ $label }}</span>
                    <code class="text-[10px] text-slate-400 font-mono">({{ $code }})</code>
                </span>
            @empty
                <div class="p-4 rounded-xl bg-slate-950/60 border border-slate-800 text-slate-400 text-xs w-full text-center">
                    Tidak ada hak akses aktif yang terdeteksi untuk akun ini.
                </div>
            @endforelse
        </div>
    </div>

    <!-- Complete Role & Permission Matrix Table -->
    <div class="bg-slate-900/70 border border-slate-800 rounded-3xl p-6 sm:p-8">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-6">
            <div>
                <h3 class="text-base font-bold text-white">Matriks Role & Hak Akses Sistem (RBAC Matrix)</h3>
                <p class="text-xs text-slate-400 mt-0.5">Satu user dapat memiliki lebih dari satu role (Many-to-Many). Kaprodi mewarisi hak Superadmin.</p>
            </div>
            <span class="text-xs font-mono text-slate-400 bg-slate-800 px-3 py-1 rounded-xl self-start sm:self-auto">
                7 Role Terdaftar
            </span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="border-b border-slate-800 text-slate-400 font-semibold uppercase tracking-wider text-[11px]">
                        <th class="py-3 px-4">Role Code</th>
                        <th class="py-3 px-4">Nama Role</th>
                        <th class="py-3 px-4">Deskripsi Peran</th>
                        <th class="py-3 px-4">Hak Akses Utama</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/60 text-slate-300">
                    @foreach ($allRoles as $roleItem)
                        <tr class="hover:bg-slate-800/30 transition {{ $user->hasRole($roleItem->name) ? 'bg-blue-500/5' : '' }}">
                            <td class="py-3.5 px-4 font-mono font-bold text-white">
                                <span class="px-2 py-1 rounded bg-slate-800 border border-slate-700">
                                    {{ $roleItem->name }}
                                </span>
                            </td>
                            <td class="py-3.5 px-4 font-semibold text-white">
                                {{ $roleItem->label }}
                                @if ($roleItem->name === 'KAPRODI')
                                    <span class="ml-1 text-[10px] text-amber-400 bg-amber-500/10 px-1.5 py-0.5 rounded font-mono">
                                        +SUPERADMIN
                                    </span>
                                @endif
                            </td>
                            <td class="py-3.5 px-4 text-slate-400 max-w-xs">
                                {{ $roleItem->description }}
                            </td>
                            <td class="py-3.5 px-4">
                                <div class="flex flex-wrap gap-1 max-w-md">
                                    @foreach ($roleItem->permissions as $p)
                                        <span class="text-[10px] font-mono px-1.5 py-0.5 rounded bg-slate-800/80 text-slate-300">
                                            {{ $p->name }}
                                        </span>
                                    @endforeach
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
    async function testServiceAssertion() {
        const box = document.getElementById('serviceTestBox');
        const title = document.getElementById('serviceTestTitle');
        const status = document.getElementById('serviceTestStatus');
        const content = document.getElementById('serviceTestContent');

        box.classList.remove('hidden');
        box.className = "mt-4 p-4 rounded-2xl border bg-slate-900 border-slate-800 text-xs";
        title.innerText = "Mengirim permintaan validasi service backend...";
        content.innerText = "Memproses...";

        try {
            const res = await fetch("{{ route('test.service.action') }}?role=SUPERADMIN", {
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            const data = await res.json();

            if (res.ok) {
                box.className = "mt-4 p-4 rounded-2xl border bg-emerald-500/10 border-emerald-500/20 text-emerald-300 text-xs";
                title.innerText = "✅ Sukses: Otorisasi Backend Service Berhasil!";
                status.innerText = "HTTP " + res.status + " OK";
                status.className = "font-mono px-2 py-0.5 rounded text-[10px] bg-emerald-500/20 text-emerald-300";
            } else {
                box.className = "mt-4 p-4 rounded-2xl border bg-rose-500/10 border-rose-500/20 text-rose-300 text-xs";
                title.innerText = "⛔ Ditolak: Otorisasi Backend Service Menolak Permintaan (Tervalidasi)";
                status.innerText = "HTTP " + res.status + " " + res.statusText;
                status.className = "font-mono px-2 py-0.5 rounded text-[10px] bg-rose-500/20 text-rose-300";
            }
            content.innerText = JSON.stringify(data, null, 2);
        } catch (err) {
            box.className = "mt-4 p-4 rounded-2xl border bg-rose-500/10 border-rose-500/20 text-rose-300 text-xs";
            title.innerText = "Error Permintaan:";
            content.innerText = err.message;
        }
    }
</script>
@endsection
