@extends('layouts.app', ['title' => 'Pusat Notifikasi'])

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-xl sm:text-2xl font-black text-white tracking-tight flex items-center gap-3">
                <span class="p-2 rounded-xl bg-blue-500/10 text-blue-400 border border-blue-500/20">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                    </svg>
                </span>
                <span>Pusat Notifikasi & Informasi</span>
            </h1>
            <p class="text-xs sm:text-sm text-slate-400 mt-1">
                Pemberitahuan perubahan status pengajuan magang, verifikasi, perbaikan berkas, dan persetujuan.
            </p>
        </div>

        <form method="POST" action="{{ route('notifications.read-all') }}">
            @csrf
            <button type="submit"
                class="px-4 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 hover:text-white font-semibold text-xs transition cursor-pointer">
                Tandai Semua Sudah Dibaca
            </button>
        </form>
    </div>

    <!-- Notification List -->
    <div class="bg-slate-900/60 border border-slate-800 rounded-2xl overflow-hidden shadow-xl divide-y divide-slate-800/60">
        @forelse ($notifications as $notif)
            <div class="p-4 sm:p-5 flex items-start gap-4 transition {{ $notif->is_read ? 'bg-transparent' : 'bg-blue-950/20' }}">
                <div class="p-2.5 rounded-xl flex-shrink-0 
                    {{ $notif->type === 'APPROVAL' ? 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/20' : '' }}
                    {{ $notif->type === 'INCOMPLETE' ? 'bg-rose-500/10 text-rose-400 border border-rose-500/20' : '' }}
                    {{ $notif->type === 'REJECTION' ? 'bg-rose-700/10 text-rose-400 border border-rose-600/20' : '' }}
                    {{ in_array($notif->type, ['SUBMISSION', 'RESUBMISSION']) ? 'bg-blue-500/10 text-blue-400 border border-blue-500/20' : '' }}">
                    @if ($notif->type === 'APPROVAL')
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                    @elseif (in_array($notif->type, ['INCOMPLETE', 'REJECTION']))
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                    @else
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    @endif
                </div>

                <div class="flex-1 min-w-0">
                    <div class="flex items-center justify-between gap-2 mb-1">
                        <h3 class="text-xs sm:text-sm font-bold {{ $notif->is_read ? 'text-slate-300' : 'text-white' }}">
                            {{ $notif->title }}
                        </h3>
                        <span class="text-[10px] text-slate-500 font-mono flex-shrink-0">
                            {{ $notif->created_at->diffForHumans() }}
                        </span>
                    </div>

                    <p class="text-xs text-slate-400 leading-relaxed">
                        {{ $notif->message }}
                    </p>

                    @if ($notif->action_url)
                        <div class="mt-2.5">
                            <form method="POST" action="{{ route('notifications.read', $notif) }}" class="inline">
                                @csrf
                                <button type="submit"
                                    class="inline-flex items-center gap-1.5 text-xs text-blue-400 hover:text-blue-300 font-semibold cursor-pointer">
                                    <span>Lihat Rincian Pengajuan</span>
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                    </svg>
                                </button>
                            </form>
                        </div>
                    @endif
                </div>

                @if (! $notif->is_read)
                    <span class="w-2.5 h-2.5 rounded-full bg-blue-500 flex-shrink-0 mt-2" title="Belum dibaca"></span>
                @endif
            </div>
        @empty
            <div class="p-12 text-center text-slate-500">
                <svg class="w-12 h-12 mx-auto text-slate-600 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                </svg>
                <p class="text-sm font-semibold text-slate-400">Belum ada notifikasi baru</p>
                <p class="text-xs text-slate-500 mt-1">Anda akan menerima pemberitahuan setiap ada pembaruan status magang.</p>
            </div>
        @endforelse
    </div>

    @if ($notifications->hasPages())
        <div class="p-4 border-t border-slate-800">
            {{ $notifications->links() }}
        </div>
    @endif
</div>
@endsection
