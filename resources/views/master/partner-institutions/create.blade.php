@extends('layouts.app', ['title' => 'Tambah Instansi Mitra'])

@section('content')
<div class="max-w-3xl mx-auto space-y-6">

    <!-- Breadcrumb & Back -->
    <div class="flex items-center gap-2 text-xs text-slate-400">
        <a href="{{ route('master.partner-institutions.index') }}" class="hover:text-white transition flex items-center gap-1">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            <span>Kembali ke Daftar Instansi</span>
        </a>
    </div>

    <!-- Form Card -->
    <div class="rounded-2xl bg-slate-900/80 border border-slate-800 p-6 sm:p-8 shadow-xl">
        <div class="border-b border-slate-800 pb-5 mb-6">
            <h2 class="text-lg font-bold text-white tracking-tight">Tambah Instansi Mitra Baru</h2>
            <p class="text-xs text-slate-400 mt-1">Daftarkan perusahaan atau instansi tempat pelaksanaan program magang.</p>
        </div>

        <form action="{{ route('master.partner-institutions.store') }}" method="POST" class="space-y-5" onsubmit="this.querySelector('button[type=submit]').disabled=true; this.querySelector('button[type=submit]').innerText='Menyimpan...';">
            @csrf

            <!-- Nama Instansi -->
            <div>
                <label for="name" class="block text-xs font-semibold text-slate-300 mb-1.5">
                    Nama Instansi / Perusahaan <span class="text-rose-400">*</span>
                </label>
                <input type="text" id="name" name="name" value="{{ old('name') }}" required placeholder="Contoh: PT Telkom Indonesia (Persero) Tbk"
                    class="w-full px-3.5 py-2.5 bg-slate-950 border @error('name') border-rose-500 @else border-slate-800 @enderror rounded-xl text-xs text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-blue-500 transition">
                @error('name')
                    <p class="text-[11px] text-rose-400 mt-1 font-medium">{{ $message }}</p>
                @enderror
            </div>

            <!-- Sektor & PIC -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="sector" class="block text-xs font-semibold text-slate-300 mb-1.5">
                        Kategori Sektor <span class="text-rose-400">*</span>
                    </label>
                    <select id="sector" name="sector" required
                        class="w-full px-3.5 py-2.5 bg-slate-950 border @error('sector') border-rose-500 @else border-slate-800 @enderror rounded-xl text-xs text-white focus:outline-none focus:ring-2 focus:ring-blue-500 transition">
                        <option value="Swasta" {{ old('sector') === 'Swasta' ? 'selected' : '' }}>Swasta (Nasional / Multinasional)</option>
                        <option value="BUMN" {{ old('sector') === 'BUMN' ? 'selected' : '' }}>BUMN / BUMD</option>
                        <option value="Pemerintah" {{ old('sector') === 'Pemerintah' ? 'selected' : '' }}>Instansi Pemerintah / Kementerian / Dinas</option>
                        <option value="Startup" {{ old('sector') === 'Startup' ? 'selected' : '' }}>Startup / Perusahaan Teknologi</option>
                        <option value="LSM" {{ old('sector') === 'LSM' ? 'selected' : '' }}>LSM / Organisasi Nirlaba</option>
                    </select>
                    @error('sector')
                        <p class="text-[11px] text-rose-400 mt-1 font-medium">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="contact_person" class="block text-xs font-semibold text-slate-300 mb-1.5">
                        Kontak Person (PIC) <span class="text-rose-400">*</span>
                    </label>
                    <input type="text" id="contact_person" name="contact_person" value="{{ old('contact_person') }}" required placeholder="Contoh: Bpk. Bambang Supriyadi (HC)"
                        class="w-full px-3.5 py-2.5 bg-slate-950 border @error('contact_person') border-rose-500 @else border-slate-800 @enderror rounded-xl text-xs text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-blue-500 transition">
                    @error('contact_person')
                        <p class="text-[11px] text-rose-400 mt-1 font-medium">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Email & Telepon -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="email" class="block text-xs font-semibold text-slate-300 mb-1.5">
                        Email Resmi Instansi <span class="text-rose-400">*</span>
                    </label>
                    <input type="email" id="email" name="email" value="{{ old('email') }}" required placeholder="internship@perusahaan.co.id"
                        class="w-full px-3.5 py-2.5 bg-slate-950 border @error('email') border-rose-500 @else border-slate-800 @enderror rounded-xl text-xs text-white placeholder-slate-500 font-mono focus:outline-none focus:ring-2 focus:ring-blue-500 transition">
                    @error('email')
                        <p class="text-[11px] text-rose-400 mt-1 font-medium">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="phone" class="block text-xs font-semibold text-slate-300 mb-1.5">
                        Nomor Telepon <span class="text-rose-400">*</span>
                    </label>
                    <input type="text" id="phone" name="phone" value="{{ old('phone') }}" required placeholder="Contoh: 021-23588000"
                        class="w-full px-3.5 py-2.5 bg-slate-950 border @error('phone') border-rose-500 @else border-slate-800 @enderror rounded-xl text-xs text-white placeholder-slate-500 font-mono focus:outline-none focus:ring-2 focus:ring-blue-500 transition">
                    @error('phone')
                        <p class="text-[11px] text-rose-400 mt-1 font-medium">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Website -->
            <div>
                <label for="website" class="block text-xs font-semibold text-slate-300 mb-1.5">
                    Website Resmi (Opsional)
                </label>
                <input type="url" id="website" name="website" value="{{ old('website') }}" placeholder="https://www.perusahaan.co.id"
                    class="w-full px-3.5 py-2.5 bg-slate-950 border @error('website') border-rose-500 @else border-slate-800 @enderror rounded-xl text-xs text-white placeholder-slate-500 font-mono focus:outline-none focus:ring-2 focus:ring-blue-500 transition">
                @error('website')
                    <p class="text-[11px] text-rose-400 mt-1 font-medium">{{ $message }}</p>
                @enderror
            </div>

            <!-- Alamat Lengkap -->
            <div>
                <label for="address" class="block text-xs font-semibold text-slate-300 mb-1.5">
                    Alamat Lengkap Instansi <span class="text-rose-400">*</span>
                </label>
                <textarea id="address" name="address" rows="3" required placeholder="Jalan, nomor gedung, kelurahan, kota, provinsi, kode pos..."
                    class="w-full px-3.5 py-2.5 bg-slate-950 border @error('address') border-rose-500 @else border-slate-800 @enderror rounded-xl text-xs text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-blue-500 transition">{{ old('address') }}</textarea>
                @error('address')
                    <p class="text-[11px] text-rose-400 mt-1 font-medium">{{ $message }}</p>
                @enderror
            </div>

            <!-- Deskripsi -->
            <div>
                <label for="description" class="block text-xs font-semibold text-slate-300 mb-1.5">
                    Keterangan Tambahan / Profil Singkat
                </label>
                <textarea id="description" name="description" rows="2" placeholder="Bidang usaha utama, divisi yang terbuka untuk magang..."
                    class="w-full px-3.5 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-xs text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-blue-500 transition">{{ old('description') }}</textarea>
            </div>

            <!-- Status Aktif Checkbox -->
            <div class="flex items-center gap-3 p-3.5 rounded-xl bg-slate-950/60 border border-slate-800">
                <input type="checkbox" id="is_active" name="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }}
                    class="w-4 h-4 rounded text-blue-600 bg-slate-900 border-slate-700 focus:ring-blue-500">
                <div>
                    <label for="is_active" class="text-xs font-semibold text-white block cursor-pointer">
                        Status Kemitraan Aktif
                    </label>
                    <p class="text-[11px] text-slate-400">Instansi aktif dapat dipilih oleh mahasiswa saat mengisi pengajuan tempat magang.</p>
                </div>
            </div>

            <!-- Buttons -->
            <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-800">
                <a href="{{ route('master.partner-institutions.index') }}"
                    class="px-4 py-2.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 text-xs font-semibold transition">
                    Batal
                </a>
                <button type="submit"
                    class="px-5 py-2.5 rounded-xl bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-500 hover:to-indigo-500 text-white text-xs font-semibold shadow-lg shadow-blue-500/20 transition cursor-pointer">
                    Simpan Instansi
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
