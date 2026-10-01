@extends('layouts.app', ['title' => 'Edit Instansi - ' . $partnerInstitution->name])

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
        <span>/</span>
        <span class="text-white font-medium">Edit: {{ $partnerInstitution->name }}</span>
    </div>

    <!-- Form Card -->
    <div class="rounded-2xl bg-slate-900/80 border border-slate-800 p-6 sm:p-8 shadow-xl">
        <div class="border-b border-slate-800 pb-5 mb-6 flex items-center justify-between">
            <div>
                <h2 class="text-lg font-bold text-white tracking-tight">Edit Data Instansi Mitra</h2>
                <p class="text-xs text-slate-400 mt-1">Perbarui profil kontak dan informasi kemitraan instansi.</p>
            </div>
            <span class="px-2.5 py-1 rounded-full text-xs font-mono font-semibold {{ $partnerInstitution->is_active ? 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/20' : 'bg-rose-500/10 text-rose-400 border border-rose-500/20' }}">
                {{ $partnerInstitution->is_active ? 'Aktif' : 'Non-Aktif' }}
            </span>
        </div>

        <form action="{{ route('master.partner-institutions.update', $partnerInstitution) }}" method="POST" class="space-y-5" onsubmit="this.querySelector('button[type=submit]').disabled=true; this.querySelector('button[type=submit]').innerText='Menyimpan Perubahan...';">
            @csrf
            @method('PUT')

            <!-- Nama Instansi -->
            <div>
                <label for="name" class="block text-xs font-semibold text-slate-300 mb-1.5">
                    Nama Instansi / Perusahaan <span class="text-rose-400">*</span>
                </label>
                <input type="text" id="name" name="name" value="{{ old('name', $partnerInstitution->name) }}" required
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
                        <option value="Swasta" {{ old('sector', $partnerInstitution->sector) === 'Swasta' ? 'selected' : '' }}>Swasta (Nasional / Multinasional)</option>
                        <option value="BUMN" {{ old('sector', $partnerInstitution->sector) === 'BUMN' ? 'selected' : '' }}>BUMN / BUMD</option>
                        <option value="Pemerintah" {{ old('sector', $partnerInstitution->sector) === 'Pemerintah' ? 'selected' : '' }}>Instansi Pemerintah / Kementerian / Dinas</option>
                        <option value="Startup" {{ old('sector', $partnerInstitution->sector) === 'Startup' ? 'selected' : '' }}>Startup / Perusahaan Teknologi</option>
                        <option value="LSM" {{ old('sector', $partnerInstitution->sector) === 'LSM' ? 'selected' : '' }}>LSM / Organisasi Nirlaba</option>
                    </select>
                    @error('sector')
                        <p class="text-[11px] text-rose-400 mt-1 font-medium">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="contact_person" class="block text-xs font-semibold text-slate-300 mb-1.5">
                        Kontak Person (PIC) <span class="text-rose-400">*</span>
                    </label>
                    <input type="text" id="contact_person" name="contact_person" value="{{ old('contact_person', $partnerInstitution->contact_person) }}" required
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
                    <input type="email" id="email" name="email" value="{{ old('email', $partnerInstitution->email) }}" required
                        class="w-full px-3.5 py-2.5 bg-slate-950 border @error('email') border-rose-500 @else border-slate-800 @enderror rounded-xl text-xs text-white font-mono focus:outline-none focus:ring-2 focus:ring-blue-500 transition">
                    @error('email')
                        <p class="text-[11px] text-rose-400 mt-1 font-medium">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="phone" class="block text-xs font-semibold text-slate-300 mb-1.5">
                        Nomor Telepon <span class="text-rose-400">*</span>
                    </label>
                    <input type="text" id="phone" name="phone" value="{{ old('phone', $partnerInstitution->phone) }}" required
                        class="w-full px-3.5 py-2.5 bg-slate-950 border @error('phone') border-rose-500 @else border-slate-800 @enderror rounded-xl text-xs text-white font-mono focus:outline-none focus:ring-2 focus:ring-blue-500 transition">
                    @error('phone')
                        <p class="text-[11px] text-rose-400 mt-1 font-medium">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Website -->
            <div>
                <label for="website" class="block text-xs font-semibold text-slate-300 mb-1.5">
                    Website Resmi
                </label>
                <input type="url" id="website" name="website" value="{{ old('website', $partnerInstitution->website) }}" placeholder="https://..."
                    class="w-full px-3.5 py-2.5 bg-slate-950 border @error('website') border-rose-500 @else border-slate-800 @enderror rounded-xl text-xs text-white font-mono focus:outline-none focus:ring-2 focus:ring-blue-500 transition">
                @error('website')
                    <p class="text-[11px] text-rose-400 mt-1 font-medium">{{ $message }}</p>
                @enderror
            </div>

            <!-- Alamat Lengkap -->
            <div>
                <label for="address" class="block text-xs font-semibold text-slate-300 mb-1.5">
                    Alamat Lengkap Instansi <span class="text-rose-400">*</span>
                </label>
                <textarea id="address" name="address" rows="3" required
                    class="w-full px-3.5 py-2.5 bg-slate-950 border @error('address') border-rose-500 @else border-slate-800 @enderror rounded-xl text-xs text-white focus:outline-none focus:ring-2 focus:ring-blue-500 transition">{{ old('address', $partnerInstitution->address) }}</textarea>
                @error('address')
                    <p class="text-[11px] text-rose-400 mt-1 font-medium">{{ $message }}</p>
                @enderror
            </div>

            <!-- Deskripsi -->
            <div>
                <label for="description" class="block text-xs font-semibold text-slate-300 mb-1.5">
                    Keterangan Tambahan / Profil Singkat
                </label>
                <textarea id="description" name="description" rows="2"
                    class="w-full px-3.5 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-xs text-white focus:outline-none focus:ring-2 focus:ring-blue-500 transition">{{ old('description', $partnerInstitution->description) }}</textarea>
            </div>

            <!-- Status Aktif Checkbox -->
            <div class="flex items-center gap-3 p-3.5 rounded-xl bg-slate-950/60 border border-slate-800">
                <input type="checkbox" id="is_active" name="is_active" value="1" {{ old('is_active', $partnerInstitution->is_active) ? 'checked' : '' }}
                    class="w-4 h-4 rounded text-blue-600 bg-slate-900 border-slate-700 focus:ring-blue-500">
                <div>
                    <label for="is_active" class="text-xs font-semibold text-white block cursor-pointer">
                        Status Kemitraan Aktif
                    </label>
                    <p class="text-[11px] text-slate-400">Instansi aktif dapat dipilih oleh mahasiswa saat mengisi pendaftaran magang.</p>
                </div>
            </div>

            <!-- Submit Button Bar -->
            <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-800">
                <a href="{{ route('master.partner-institutions.index') }}"
                    class="px-4 py-2.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 text-xs font-semibold transition">
                    Batal
                </a>
                <button type="submit"
                    class="px-5 py-2.5 rounded-xl bg-gradient-to-r from-amber-600 to-orange-600 hover:from-amber-500 hover:to-orange-500 text-white text-xs font-semibold shadow-lg shadow-orange-500/20 transition cursor-pointer">
                    Perbarui Instansi
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
