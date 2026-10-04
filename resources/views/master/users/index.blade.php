@extends('layouts.app', ['title' => 'Pengguna'])

@section('content')
<div class="max-w-7xl mx-auto space-y-8 animate-fade-in" style="animation-delay: 100ms;">

    <!-- Header -->
    <header class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-semibold text-white">Pengguna</h1>
            <p class="text-sm text-slate-400 mt-1">Kelola akun pengguna, akses, dan status aplikasi.</p>
        </div>
        <a href="{{ route('master.users.create') }}" class="px-4 py-2.5 rounded-xl bg-white text-slate-950 text-sm font-medium hover:bg-slate-200 transition active:scale-[0.98]">
            Tambah Pengguna
        </a>
    </header>

    <!-- Toolbar -->
    <section class="bg-slate-900/40 border border-white/5 rounded-2xl p-4">
        <form method="GET" action="{{ route('master.users.index') }}" class="grid grid-cols-1 sm:grid-cols-4 gap-4">
            <div class="sm:col-span-1">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama atau email..."
                    class="w-full px-4 py-2.5 bg-slate-950 border border-white/5 rounded-xl text-sm text-white placeholder-slate-600 focus:outline-none focus:border-indigo-500/50 transition">
            </div>
            <div class="sm:col-span-1">
                <select name="role" class="w-full px-4 py-2.5 bg-slate-950 border border-white/5 rounded-xl text-sm text-slate-300 focus:outline-none focus:border-indigo-500/50 transition">
                    <option value="">Semua Role</option>
                    @foreach($roles as $r)
                        <option value="{{ $r->name }}" {{ request('role') === $r->name ? 'selected' : '' }}>{{ $r->label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="sm:col-span-1">
                <select name="status" class="w-full px-4 py-2.5 bg-slate-950 border border-white/5 rounded-xl text-sm text-slate-300 focus:outline-none focus:border-indigo-500/50 transition">
                    <option value="">Semua Status</option>
                    <option value="1" {{ request('status') === '1' ? 'selected' : '' }}>Aktif</option>
                    <option value="0" {{ request('status') === '0' ? 'selected' : '' }}>Non-Aktif</option>
                </select>
            </div>
            <div class="flex gap-2">
                <button type="submit" class="flex-1 px-4 py-2.5 rounded-xl bg-indigo-600/10 text-indigo-400 border border-indigo-500/20 text-sm font-medium hover:bg-indigo-600/20 transition">Filter</button>
                @if(request()->anyFilled(['search', 'role', 'status']))
                    <a href="{{ route('master.users.index') }}" class="px-4 py-2.5 rounded-xl bg-white/5 text-slate-400 hover:text-white border border-white/5 text-sm transition">Reset</a>
                @endif
            </div>
        </form>
    </section>

    <!-- Table -->
    <div class="bg-slate-900/40 border border-white/5 rounded-2xl overflow-hidden">
        <div class="overflow-x-auto custom-scrollbar">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="border-b border-white/5 text-[11px] uppercase tracking-widest text-slate-500">
                        <th class="py-4 px-6 font-medium">Pengguna</th>
                        <th class="py-4 px-6 font-medium">Email</th>
                        <th class="py-4 px-6 font-medium">Role</th>
                        <th class="py-4 px-6 font-medium text-center">Status</th>
                        <th class="py-4 px-6 font-medium text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-white/5 text-sm text-slate-300">
                    @forelse ($users as $u)
                        <tr class="hover:bg-white/5 transition">
                            <td class="py-4 px-6 font-medium text-white">{{ $u->name }}</td>
                            <td class="py-4 px-6 text-slate-400">{{ $u->email }}</td>
                            <td class="py-4 px-6">
                                <div class="flex gap-1 flex-wrap">
                                    @foreach($u->roles as $role)
                                        <span class="px-2 py-0.5 rounded-md bg-white/5 text-[10px] text-slate-300">{{ $role->name }}</span>
                                    @endforeach
                                </div>
                            </td>
                            <td class="py-4 px-6 text-center">
                                <form action="{{ route('master.users.toggle', $u) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="text-xs {{ $u->is_active ? 'text-emerald-400' : 'text-rose-400' }}">
                                        {{ $u->is_active ? 'Aktif' : 'Non-Aktif' }}
                                    </button>
                                </form>
                            </td>
                            <td class="py-4 px-6 text-right">
                                <a href="{{ route('master.users.show', $u) }}" class="text-indigo-400 hover:text-indigo-300 text-xs">Detail</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="py-12 text-center text-slate-500 text-sm">Tidak ada data ditemukan</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($users->hasPages())
            <div class="p-6 border-t border-white/5">
                {{ $users->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
