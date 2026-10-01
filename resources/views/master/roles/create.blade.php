@extends('layouts.app', ['title' => 'Tambah Role Baru'])

@section('content')
<div class="max-w-4xl mx-auto space-y-6">

    <!-- Breadcrumb & Back -->
    <div class="flex items-center gap-2 text-xs text-slate-400">
        <a href="{{ route('master.roles.index') }}" class="hover:text-white transition flex items-center gap-1">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            <span>Kembali ke Daftar Role</span>
        </a>
    </div>

    <!-- Form Card -->
    <div class="rounded-2xl bg-slate-900/80 border border-slate-800 p-6 sm:p-8 shadow-xl">
        <div class="border-b border-slate-800 pb-5 mb-6">
            <h2 class="text-lg font-bold text-white tracking-tight">Tambah Role Baru</h2>
            <p class="text-xs text-slate-400 mt-1">Definisikan peran baru dan pilih hak akses (permissions) yang dimiliki peran tersebut.</p>
        </div>

        <form action="{{ route('master.roles.store') }}" method="POST" class="space-y-6" onsubmit="this.querySelector('button[type=submit]').disabled=true; this.querySelector('button[type=submit]').innerText='Menyimpan...';">
            @csrf

            <!-- Kode Role & Label -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="name" class="block text-xs font-semibold text-slate-300 mb-1.5">
                        Kode Role (Kapital/Alpha-dash) <span class="text-rose-400">*</span>
                    </label>
                    <input type="text" id="name" name="name" value="{{ old('name') }}" required maxlength="50" placeholder="Contoh: STAFF_AKADEMIK"
                        class="w-full px-3.5 py-2.5 bg-slate-950 border @error('name') border-rose-500 @else border-slate-800 @enderror rounded-xl text-xs text-white font-mono placeholder-slate-500 uppercase focus:outline-none focus:ring-2 focus:ring-blue-500 transition">
                    @error('name')
                        <p class="text-[11px] text-rose-400 mt-1 font-medium">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="label" class="block text-xs font-semibold text-slate-300 mb-1.5">
                        Label / Nama Peran <span class="text-rose-400">*</span>
                    </label>
                    <input type="text" id="label" name="label" value="{{ old('label') }}" required placeholder="Contoh: Staf Akademik Fakultas"
                        class="w-full px-3.5 py-2.5 bg-slate-950 border @error('label') border-rose-500 @else border-slate-800 @enderror rounded-xl text-xs text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-blue-500 transition">
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
                <textarea id="description" name="description" rows="2" placeholder="Jelaskan ruang lingkup wewenang role ini..."
                    class="w-full px-3.5 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-xs text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-blue-500 transition">{{ old('description') }}</textarea>
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
                        <p class="text-[11px] text-slate-400">Centang hak akses yang diizinkan untuk peran ini:</p>
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
                                {{ is_array(old('permissions')) && in_array($perm->id, old('permissions')) ? 'checked' : '' }}
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
                    class="px-5 py-2.5 rounded-xl bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-500 hover:to-indigo-500 text-white text-xs font-semibold shadow-lg shadow-blue-500/20 transition cursor-pointer">
                    Simpan Role
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
