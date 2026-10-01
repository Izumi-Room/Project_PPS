@props(['variant' => 'default', 'title' => ''])

@php
    $variants = [
        'default' => 'bg-slate-900/70 border-slate-800 text-slate-100',
        'info' => 'bg-blue-500/10 border-blue-500/30 text-blue-300',
        'success' => 'bg-emerald-500/10 border-emerald-500/30 text-emerald-300',
        'warning' => 'bg-amber-500/10 border-amber-500/30 text-amber-300',
        'danger' => 'bg-rose-500/10 border-rose-500/30 text-rose-300'
    ];
@endphp

<div {{ $attributes->merge(['class' => "p-6 rounded-3xl border shadow-xl backdrop-blur-md {$variants[$variant]}"]) }}>
    @if($title)
        <h3 class="text-base font-bold mb-3 tracking-tight">{{ $title }}</h3>
    @endif
    {{ $slot }}
</div>