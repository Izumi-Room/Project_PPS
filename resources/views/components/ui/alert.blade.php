@props(['variant' => 'info', 'dismissible' => false])

@php
    $variants = [
        'info' => 'bg-blue-500/10 border-blue-500/20 text-blue-300',
        'success' => 'bg-emerald-500/10 border-emerald-500/20 text-emerald-300',
        'warning' => 'bg-amber-500/10 border-amber-500/20 text-amber-300',
        'danger' => 'bg-rose-500/10 border-rose-500/20 text-rose-300'
    ];
@endphp

<div role="alert" {{ $attributes->merge(['class' => "p-4 rounded-2xl border flex items-center justify-between shadow-lg {$variants[$variant]}"]) }}>
    <div class="flex items-center gap-3">
        {{ $slot }}
    </div>
    @if($dismissible)
        <button type="button" aria-label="Dismiss alert" onclick="this.parentElement.remove()" class="opacity-60 hover:opacity-100 p-1">
            &times;
        </button>
    @endif
</div>