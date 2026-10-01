@extends('layouts.app', ['title' => 'Kelola Role - ' . $user->name])

@section('content')
<div class="max-w-4xl mx-auto space-y-6">

    <!-- Breadcrumb & Back -->
    <div class="flex items-center gap-2 text-xs text-slate-400">
        <a href="{{ route('master.user-roles.index') }}" class="hover:text-white transition flex items-center gap-1">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            <span>Kembali ke Penugasan User Roles</span>
        </a>
        <span>/</span>
        <span class="text-white font-medium">{{ $user->name }}</span>
    </div>

    <!-- Form Card -->
    <div class="rounded-2xl bg-slate-900/80 border border-slate-800 p-6 sm:p-8 shadow-xl space-y-6">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-slate-800 pb-5">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 rounded-2xl bg-gradient-to-tr from-purple-600 to-indigo-600 flex items-center justify-center font-bold text-white text-base">
                    {{ strtoupper(substr($user->name, 0, 2)) }}
                </div>
                <div>
                    <h2 class="text-lg font-bold text-white tracking-tight">Atur Role Pengguna</h2>
                    <p class="text-xs text-slate-400 mt-0.5">{{ $user->name }} • <span class="font-mono">{{ $user->email }}</span></p>
                </div>
            </div>

            <div class="text-right">
                <span class="block text-[10px] text-slate-400 uppercase font-mono tracking-wider">Peran Efektif</span>
                <div class="flex flex-wrap gap-1 justify-end mt-1">
                    @foreach($effectiveRoles as $er)
                        <span class="px-2 py-0.5 rounded text-[10px] font-mono font-semibold bg-purple-500/20 text-purple-300 border border-purple-500/30">
                            {{ $er }}
                        </span>
                    @endforeach
                </div>
            </div>
        </div>

        <form action="{{ route('master.user-roles.update', $user) }}" method="POST" class="space-y-6" onsubmit="this.querySelector('button[type=submit]').disabled=true; this.querySelector('button[type=submit]').innerText='Menyimpan...';">
            @csrf
            @method('PUT')

            <div class="space-y-3">
                <div>
                    <label class="block text-xs font-bold text-white uppercase tracking-wider font-mono">
                        Pilih Peran Sistem (Many-to-Many Roles) <span class="text-rose-400">*</span>
                    </label>
                    <p class="text-[11px] text-slate-400 mt-0.5">
                        Centang satu atau beberapa peran. Catatan: Pengguna dengan peran <strong class="text-purple-300">KAPRODI</strong> otomatis mewarisi akses <strong class="text-rose-300">SUPERADMIN</strong> pada seluruh backend gate & controller.
                    </p>
                </div>

                @php
                    $currentRoleIds = $user->roles->pluck('id')->toArray();
                @endphp

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 pt-2">
                    @foreach($allRoles as $role)
                        @php
                            $isChecked = is_array(old('roles')) ? in_array($role->id, old('roles')) : in_array($role->id, $currentRoleIds);
                        @endphp
                        <label class="p-3.5 rounded-2xl bg-slate-950/60 border {{ $isChecked ? 'border-purple-500/50 bg-purple-950/10' : 'border-slate-800' }} hover:border-slate-700 cursor-pointer transition flex items-start gap-3">
                            <input type="checkbox" name="roles[]" value="{{ $role->id }}" {{ $isChecked ? 'checked' : '' }}
                                class="mt-1 w-4 h-4 rounded text-purple-600 bg-slate-900 border-slate-700 focus:ring-purple-500">
                            <div class="flex-1">
                                <div class="flex items-center justify-between">
                                    <span class="font-mono font-bold text-xs text-white">{{ $role->name }}</span>
                                    <span class="text-[10px] font-mono text-slate-400">{{ $role->permissions->count() }} permissions</span>
                                </div>
                                <span class="block text-xs font-semibold text-slate-300 mt-0.5">{{ $role->label }}</span>
                                <span class="block text-[11px] text-slate-400 mt-0.5 leading-relaxed">{{ $role->description }}</span>
                            </div>
                        </label>
                    @endforeach
                </div>
                @error('roles')
                    <p class="text-[11px] text-rose-400 mt-1 font-medium">{{ $message }}</p>
                @enderror
            </div>

            <!-- Submit Button Bar -->
            <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-800">
                <a href="{{ route('master.user-roles.index') }}"
                    class="px-4 py-2.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 text-xs font-semibold transition">
                    Batal
                </a>
                <button type="submit"
                    class="px-5 py-2.5 rounded-xl bg-gradient-to-r from-purple-600 to-indigo-600 hover:from-purple-500 hover:to-indigo-500 text-white text-xs font-semibold shadow-lg shadow-purple-500/20 transition cursor-pointer">
                    Simpan Perubahan Role
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
