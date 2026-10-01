@extends('layouts.app', ['title' => 'Edit Role - ' . $role->name])

@section('content')
<div class="max-w-4xl mx-auto space-y-6">

    @php
        $isSystemRole = in_array($role->name, ['MHS', 'TU', 'KAPRODI', 'DOSBING', 'DOSEN_MK', 'WADEK1', 'SUPERADMIN']);
        $assignedPermIds = $role->permissions->pluck('id')->toArray();
    @endphp

    <!-- Breadcrumb & Back -->
    <div class="flex items-center gap-2 text-xs text-slate-400">
        <a href="{{ route('master.roles.index') }}" class="hover:text-white transition flex items-center gap-1">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            <span>Kembali ke Daftar Role</span>
        </a>
        <span>/</span>
        <span class="text-white font-medium">Edit: {{ $role->name }}</span>
    </div>

    <!-- Form Card -->
    <div class="rounded-2xl bg-slate-900/80 border border-slate-800 p-6 sm:p-8 shadow-xl">
        <div class="border-b border-slate-800 pb-5 mb-6 flex items-center justify-between">
            <div>
                <h2 class="text-lg font-bold text-white tracking-tight">Edit Role & Hak Akses</h2>
                <p class="text-xs text-slate-400 mt-1">Perbarui label tampilan, deskripsi, dan mapping permissions untuk peran ini.</p>
            </div>
            @if($isSystemRole)
                <span class="px-2.5 py-1 rounded-full text-xs font-mono font-semibold bg-rose-500/10 text-rose-300 border border-rose-500/20">
                    System Core Role
                </span>
            @endif
        </div>

        <form action="{{ route('master.roles.update', $role) }}" method="POST" class="space-y-6" onsubmit="this.querySelector('button[type=submit]').disabled=true; this.querySelector('button[type=submit]').innerText='Menyimpan Perubahan...';">
            @csrf
            @method('PUT')

            <!-- Kode Role & Label -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="name" class="block text-xs font-semibold text-slate-300 mb-1.5">
                        Kode Role
                        @if($isSystemRole)
                            <span class="text-[10px] text-amber-400 font-normal ml-1">(Terkunci untuk role sistem)</span>
                        @else
                            <span class="text-rose-400">*</span>
                        @endif
                    </label>
                    <input type="text" id="name" name="name" value="{{ old('name', $role->name) }}" required maxlength="50"
                        @if($isSystemRole) readonly class="w-full px-3.5 py-2.5 bg-slate-950/50 border border-slate-800 rounded-xl text-xs text-slate-400 font-mono cursor-not-allowed uppercase"
                        @else class="w-full px-3.5 py-2.5 bg-slate-950 border @error('name') border-rose-500 @else border-slate-800 @enderror rounded-xl text-xs text-white font-mono uppercase focus:outline-none focus:ring-2 focus:ring-blue-500 transition"
                        @endif>
                    @error('name')
                        <p class="text-[11px] text-rose-400 mt-1 font-medium">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="label" class="block text-xs font-semibold text-slate-300 mb-1.5">
                        Label / Nama Peran <span class="text-rose-400">*</span>
                    </label>
                    <input type="text" id="label" name="label" value="{{ old('label', $role->label) }}" required
                        class="w-full px-3.5 py-2.5 bg-slate-950 border @error('label') border-rose-500 @else border-slate-800 @enderror rounded-xl text-xs text-white focus:outline-none focus:ring-2 focus:ring-blue-500 transition">
                    @error('label')
                        <p class="text-[11px] text-rose-400 mt-1 font-medium">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Deskripsi -->
            <div>
                <label for="description" class="block text-xs font-semibold text-slate-300 mb-1.5">
                    Deskripsi Peran
                </label>
                <textarea id="description" name="description" rows="2"
                    class="w-full px-3.5 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-xs text-white focus:outline-none focus:ring-2 focus:ring-blue-500 transition">{{ old('description', $role->description) }}</textarea>
                @error('description')
                    <p class="text-[11px] text-rose-400 mt-1 font-medium">{{ $message }}</p>
                @enderror
            </div>

            <!-- Permissions Checklist -->
            <div class="space-y-3 pt-2">
                <div class="flex items-center justify-between">
                    <div>
                        <label class="block text-xs font-bold text-white uppercase tracking-wider font-mono">
                            Hak Akses Terkait (Permissions)
                        </label>
                        <p class="text-[11px] text-slate-400">Tentukan wewenang spesifik yang diaktifkan untuk role ini:</p>
                    </div>
                    <button type="button" onclick="document.querySelectorAll('.perm-checkbox').forEach(c => c.checked = !c.checked)"
                        class="text-xs text-blue-400 hover:underline cursor-pointer">
                        Toggle Semua
                    </button>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3 p-4 rounded-2xl bg-slate-950/60 border border-slate-800 max-h-96 overflow-y-auto custom-scrollbar">
                    @foreach($permissions as $perm)
                        <label class="flex items-start gap-2.5 p-2.5 rounded-xl bg-slate-900 border border-slate-800 hover:border-slate-700 cursor-pointer transition">
                            <input type="checkbox" name="permissions[]" value="{{ $perm->id }}"
                                {{ is_array(old('permissions')) ? (in_array($perm->id, old('permissions')) ? 'checked' : '') : (in_array($perm->id, $assignedPermIds) ? 'checked' : '') }}
                                class="perm-checkbox mt-0.5 w-4 h-4 rounded text-blue-600 bg-slate-950 border-slate-700 focus:ring-blue-500">
                            <div>
                                <span class="block text-xs font-bold text-white font-mono text-[11px]">{{ $perm->name }}</span>
                                <span class="block text-xs text-slate-300 font-medium">{{ $perm->label }}</span>
                                @if($perm->description)
                                    <span class="block text-[10px] text-slate-500 mt-0.5">{{ $perm->description }}</span>
                                @endif
                            </div>
                        </label>
                    @endforeach
                </div>
            </div>

            <!-- Buttons -->
            <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-800">
                <a href="{{ route('master.roles.index') }}"
                    class="px-4 py-2.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 text-xs font-semibold transition">
                    Batal
                </a>
                <button type="submit"
                    class="px-5 py-2.5 rounded-xl bg-gradient-to-r from-amber-600 to-orange-600 hover:from-amber-500 hover:to-orange-500 text-white text-xs font-semibold shadow-lg shadow-orange-500/20 transition cursor-pointer">
                    Perbarui Role
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
