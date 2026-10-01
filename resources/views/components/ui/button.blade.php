@props(['variant' => 'primary', 'size' => 'md', 'type' => 'button', 'loading' => false])

@php
    $base = "inline-flex items-center justify-center rounded-xl font-semibold transition-all focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-offset-slate-950";
    
    $variants = [
        'primary' => 'bg-blue-600 hover:bg-blue-500 text-white focus:ring-blue-500 shadow-lg shadow-blue-600/20',
        'secondary' => 'bg-slate-800 hover:bg-slate-700 text-white focus:ring-slate-500',
        'danger' => 'bg-rose-600 hover:bg-rose-500 text-white focus:ring-rose-500 shadow-lg shadow-rose-600/20',
        'ghost' => 'text-slate-400 hover:text-white hover:bg-slate-800 focus:ring-slate-500'
    ];
    
    $sizes = [
        'sm' => 'px-3 py-1.5 text-xs',
        'md' => 'px-5 py-2.5 text-sm',
        'lg' => 'px-6 py-3 text-base'
    ];
@endphp

<button type="{{ $type }}" {{ $attributes->merge(['class' => "$base {$variants[$variant]} {$sizes[$size]} disabled:opacity-50 disabled:cursor-not-allowed"]) }} @if($loading) disabled @endif>
    @if($loading)
        <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
        </svg>
    @endif
    {{ $slot }}
</button>