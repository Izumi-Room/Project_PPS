@extends('layouts.app', ['title' => 'Edit Pengguna - ' . $user->name])

@section('content')
<div class="max-w-2xl mx-auto space-y-8 animate-fade-in" style="animation-delay: 100ms;">

    <!-- Header -->
    <header class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-semibold text-white">Edit Pengguna</h1>
            <p class="text-sm text-slate-400 mt-1">Perbarui profil dan peran akses {{ $user->name }}.</p>
        </div>
        <a href="{{ route('master.users.index') }}" class="text-sm text-slate-400 hover:text-white transition">
            Batal
        </a>
    </header>

    <!-- Form -->
    <form action="{{ route('master.users.update', $user) }}" method="POST" class="space-y-6">
        @csrf
        @method('PUT')

        <div class="bg-slate-900/40 border border-white/5 rounded-2xl p-6 space-y-6">
            <h3 class="text-sm font-medium text-white">Informasi Dasar</h3>

            <div class="space-y-4">
                <div>
                    <label for="name" class="block text-[11px] font-medium text-slate-400 uppercase tracking-widest mb-2">Nama Lengkap</label>
                    <input type="text" id="name" name="name" value="{{ old('name', $user->name) }}" required
                        class="w-full px-4 py-2.5 bg-slate-950 border border-white/5 rounded-xl text-sm text-white focus:outline-none focus:border-indigo-500/50 transition">
                    @error('name') <p class="text-xs text-rose-400 mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="email" class="block text-[11px] font-medium text-slate-400 uppercase tracking-widest mb-2">Alamat Email</label>
                    <input type="email" id="email" name="email" value="{{ old('email', $user->email) }}" required
                        class="w-full px-4 py-2.5 bg-slate-950 border border-white/5 rounded-xl text-sm text-white focus:outline-none focus:border-indigo-500/50 transition">
                    @error('email') <p class="text-xs text-rose-400 mt-1">{{ $message }}</p> @enderror
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="password" class="block text-[11px] font-medium text-slate-400 uppercase tracking-widest mb-2">Password Baru (Opsional)</label>
                        <input type="password" id="password" name="password" minlength="8" placeholder="Kosongkan jika tidak ubah"
                            class="w-full px-4 py-2.5 bg-slate-950 border border-white/5 rounded-xl text-sm text-white placeholder-slate-600 focus:outline-none focus:border-indigo-500/50 transition">
                        @error('password') <p class="text-xs text-rose-400 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="password_confirmation" class="block text-[11px] font-medium text-slate-400 uppercase tracking-widest mb-2">Konfirmasi Password</label>
                        <input type="password" id="password_confirmation" name="password_confirmation" minlength="8" placeholder="Ulangi password baru"
                            class="w-full px-4 py-2.5 bg-slate-950 border border-white/5 rounded-xl text-sm text-white placeholder-slate-600 focus:outline-none focus:border-indigo-500/50 transition">
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-slate-900/40 border border-white/5 rounded-2xl p-6 space-y-6">
            <h3 class="text-sm font-medium text-white">Akademik & Peran</h3>

            <div class="space-y-4">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="study_program_id" class="block text-[11px] font-medium text-slate-400 uppercase tracking-widest mb-2">Program Studi</label>
                        <select id="study_program_id" name="study_program_id"
                            class="w-full px-4 py-2.5 bg-slate-950 border border-white/5 rounded-xl text-sm text-slate-300 focus:outline-none focus:border-indigo-500/50 transition">
                            <option value="">Umum / Non-Prodi</option>
                            @foreach($studyPrograms as $prodi)
                                <option value="{{ $prodi->id }}" {{ old('study_program_id', $user->study_program_id) == $prodi->id ? 'selected' : '' }}>
                                    {{ $prodi->code }} - {{ $prodi->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label for="identifier_number" class="block text-[11px] font-medium text-slate-400 uppercase tracking-widest mb-2">NIM / NIP</label>
                        <input type="text" id="identifier_number" name="identifier_number" value="{{ old('identifier_number', $user->identifier_number) }}"
                            class="w-full px-4 py-2.5 bg-slate-950 border border-white/5 rounded-xl text-sm text-white focus:outline-none focus:border-indigo-500/50 transition">
                    </div>
                </div>

                @php
                    $assignedRoleIds = $user->roles->pluck('id')->toArray();
                @endphp
                <div>
                    <label class="block text-[11px] font-medium text-slate-400 uppercase tracking-widest mb-3">Pilih Peran (Role)</label>
                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                        @foreach($roles as $role)
                            <label class="flex items-center gap-2 p-3 rounded-xl bg-slate-950 border border-white/5 cursor-pointer hover:bg-white/5 transition">
                                <input type="checkbox" name="roles[]" value="{{ $role->id }}"
                                    {{ is_array(old('roles')) ? (in_array($role->id, old('roles')) ? 'checked' : '') : (in_array($role->id, $assignedRoleIds) ? 'checked' : '') }}
                                    class="w-4 h-4 rounded text-indigo-600 bg-slate-900 border-white/10">
                                <span class="text-xs text-white">{{ $role->name }}</span>
                            </label>
                        @endforeach
                    </div>
                    @error('roles') <p class="text-xs text-rose-400 mt-1">{{ $message }}</p> @enderror
                </div>
            </div>
        </div>

        <div class="flex items-center justify-end gap-3 pt-4">
            <button type="submit" class="px-6 py-2.5 rounded-xl bg-white text-slate-950 text-sm font-medium hover:bg-slate-200 transition active:scale-[0.98]">
                Simpan Perubahan
            </button>
        </div>
    </form>
</div>
@endsection
