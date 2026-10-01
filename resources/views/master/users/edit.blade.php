@extends('layouts.app', ['title' => 'Edit Pengguna - ' . $user->name])

@section('content')
<div class="max-w-3xl mx-auto space-y-6">

    <!-- Breadcrumb & Back -->
    <div class="flex items-center gap-2 text-xs text-slate-400">
        <a href="{{ route('master.users.index') }}" class="hover:text-white transition flex items-center gap-1">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            <span>Kembali ke Daftar Pengguna</span>
        </a>
        <span>/</span>
        <span class="text-white font-medium">Edit: {{ $user->name }}</span>
    </div>

    <!-- Form Card -->
    <div class="rounded-2xl bg-slate-900/80 border border-slate-800 p-6 sm:p-8 shadow-xl">
        <div class="border-b border-slate-800 pb-5 mb-6 flex items-center justify-between">
            <div>
                <h2 class="text-lg font-bold text-white tracking-tight">Edit Data Pengguna</h2>
                <p class="text-xs text-slate-400 mt-1">Perbarui profil, password, prodi, dan penugasan peran akses pengguna.</p>
            </div>
            <span class="px-2.5 py-1 rounded-full text-xs font-mono font-semibold {{ $user->is_active ? 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/20' : 'bg-rose-500/10 text-rose-400 border border-rose-500/20' }}">
                {{ $user->is_active ? 'Akun Aktif' : 'Non-Aktif' }}
            </span>
        </div>

        <form action="{{ route('master.users.update', $user) }}" method="POST" class="space-y-5" onsubmit="this.querySelector('button[type=submit]').disabled=true; this.querySelector('button[type=submit]').innerText='Menyimpan Perubahan...';">
            @csrf
            @method('PUT')

            <!-- Nama & Email -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="name" class="block text-xs font-semibold text-slate-300 mb-1.5">
                        Nama Lengkap <span class="text-rose-400">*</span>
                    </label>
                    <input type="text" id="name" name="name" value="{{ old('name', $user->name) }}" required
                        class="w-full px-3.5 py-2.5 bg-slate-950 border @error('name') border-rose-500 @else border-slate-800 @enderror rounded-xl text-xs text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-blue-500 transition">
                    @error('name')
                        <p class="text-[11px] text-rose-400 mt-1 font-medium">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="email" class="block text-xs font-semibold text-slate-300 mb-1.5">
                        Alamat Email <span class="text-rose-400">*</span>
                    </label>
                    <input type="email" id="email" name="email" value="{{ old('email', $user->email) }}" required
                        class="w-full px-3.5 py-2.5 bg-slate-950 border @error('email') border-rose-500 @else border-slate-800 @enderror rounded-xl text-xs text-white font-mono focus:outline-none focus:ring-2 focus:ring-blue-500 transition">
                    @error('email')
                        <p class="text-[11px] text-rose-400 mt-1 font-medium">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Ubah Password (Opsional) -->
            <div class="p-4 rounded-xl bg-slate-950/40 border border-slate-800 space-y-3">
                <span class="block text-xs font-bold text-slate-300">Ubah Password (Kosongkan jika tidak ingin mengubah)</span>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="password" class="block text-[11px] text-slate-400 mb-1">Password Baru</label>
                        <input type="password" id="password" name="password" minlength="8" placeholder="Password baru..."
                            class="w-full px-3.5 py-2 bg-slate-950 border @error('password') border-rose-500 @else border-slate-800 @enderror rounded-xl text-xs text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-blue-500 transition">
                        @error('password')
                            <p class="text-[11px] text-rose-400 mt-1 font-medium">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="password_confirmation" class="block text-[11px] text-slate-400 mb-1">Konfirmasi Password Baru</label>
                        <input type="password" id="password_confirmation" name="password_confirmation" minlength="8" placeholder="Ulangi password baru..."
                            class="w-full px-3.5 py-2 bg-slate-950 border border-slate-800 rounded-xl text-xs text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-blue-500 transition">
                    </div>
                </div>
            </div>

            <!-- Program Studi & Identitas (NIM/NIP) -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div>
                    <label for="study_program_id" class="block text-xs font-semibold text-slate-300 mb-1.5">
                        Program Studi (Jika Ada)
                    </label>
                    <select id="study_program_id" name="study_program_id"
                        class="w-full px-3.5 py-2.5 bg-slate-950 border @error('study_program_id') border-rose-500 @else border-slate-800 @enderror rounded-xl text-xs text-white focus:outline-none focus:ring-2 focus:ring-blue-500 transition">
                        <option value="">-- Non-Prodi / Umum --</option>
                        @foreach($studyPrograms as $prodi)
                            <option value="{{ $prodi->id }}" {{ old('study_program_id', $user->study_program_id) == $prodi->id ? 'selected' : '' }}>
                                {{ $prodi->code }} - {{ $prodi->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('study_program_id')
                        <p class="text-[11px] text-rose-400 mt-1 font-medium">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="identifier_number" class="block text-xs font-semibold text-slate-300 mb-1.5">
                        Nomor Identitas (NIM / NIP)
                    </label>
                    <input type="text" id="identifier_number" name="identifier_number" value="{{ old('identifier_number', $user->identifier_number) }}"
                        class="w-full px-3.5 py-2.5 bg-slate-950 border @error('identifier_number') border-rose-500 @else border-slate-800 @enderror rounded-xl text-xs text-white font-mono focus:outline-none focus:ring-2 focus:ring-blue-500 transition">
                    @error('identifier_number')
                        <p class="text-[11px] text-rose-400 mt-1 font-medium">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="phone" class="block text-xs font-semibold text-slate-300 mb-1.5">
                        Nomor WhatsApp / HP
                    </label>
                    <input type="text" id="phone" name="phone" value="{{ old('phone', $user->phone) }}"
                        class="w-full px-3.5 py-2.5 bg-slate-950 border @error('phone') border-rose-500 @else border-slate-800 @enderror rounded-xl text-xs text-white font-mono focus:outline-none focus:ring-2 focus:ring-blue-500 transition">
                    @error('phone')
                        <p class="text-[11px] text-rose-400 mt-1 font-medium">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Role Assignment Checkboxes -->
            @php
                $assignedRoleIds = $user->roles->pluck('id')->toArray();
            @endphp
            <div class="p-4 rounded-xl bg-slate-950/60 border border-slate-800 space-y-3">
                <label class="block text-xs font-bold text-white uppercase tracking-wider font-mono">
                    Penugasan Peran Sistem (Roles)
                </label>
                <p class="text-[11px] text-slate-400">Pilih satu atau lebih role yang dimiliki pengguna:</p>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-2.5 pt-1">
                    @foreach($roles as $role)
                        <label class="flex items-start gap-2.5 p-2.5 rounded-xl bg-slate-900 border border-slate-800/80 hover:border-slate-700 cursor-pointer transition">
                            <input type="checkbox" name="roles[]" value="{{ $role->id }}"
                                {{ is_array(old('roles')) ? (in_array($role->id, old('roles')) ? 'checked' : '') : (in_array($role->id, $assignedRoleIds) ? 'checked' : '') }}
                                class="mt-0.5 w-4 h-4 rounded text-blue-600 bg-slate-950 border-slate-700 focus:ring-blue-500">
                            <div>
                                <span class="block text-xs font-bold text-white font-mono">{{ $role->name }}</span>
                                <span class="block text-[10px] text-slate-400">{{ $role->label }}</span>
                            </div>
                        </label>
                    @endforeach
                </div>
                @error('roles')
                    <p class="text-[11px] text-rose-400 mt-1 font-medium">{{ $message }}</p>
                @enderror
            </div>

            <!-- Status Aktif Checkbox -->
            <div class="flex items-center gap-3 p-3.5 rounded-xl bg-slate-950/60 border border-slate-800">
                <input type="checkbox" id="is_active" name="is_active" value="1" {{ old('is_active', $user->is_active) ? 'checked' : '' }}
                    class="w-4 h-4 rounded text-blue-600 bg-slate-900 border-slate-700 focus:ring-blue-500">
                <div>
                    <label for="is_active" class="text-xs font-semibold text-white block cursor-pointer">
                        Akun Aktif
                    </label>
                    <p class="text-[11px] text-slate-400">Jika dinonaktifkan, akun ini tidak dapat digunakan untuk login.</p>
                </div>
            </div>

            <!-- Buttons -->
            <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-800">
                <a href="{{ route('master.users.index') }}"
                    class="px-4 py-2.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 text-xs font-semibold transition">
                    Batal
                </a>
                <button type="submit"
                    class="px-5 py-2.5 rounded-xl bg-gradient-to-r from-amber-600 to-orange-600 hover:from-amber-500 hover:to-orange-500 text-white text-xs font-semibold shadow-lg shadow-orange-500/20 transition cursor-pointer">
                    Perbarui Pengguna
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
