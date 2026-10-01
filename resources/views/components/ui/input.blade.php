@props(['label' => '', 'name' => '', 'error' => null, 'type' => 'text', 'required' => false])

<div class="space-y-1.5">
    @if($label)
        <label for="{{ $name }}" class="block text-xs font-bold text-slate-300">
            {{ $label }} @if($required) <span class="text-rose-400">*</span> @endif
        </label>
    @endif
    
    <input type="{{ $type }}" name="{{ $name }}" id="{{ $name }}" {{ $attributes->merge(['class' => 'w-full px-4 py-2.5 rounded-xl bg-slate-950/80 border ' . ($error ? 'border-rose-500 focus:ring-rose-500' : 'border-slate-800 focus:border-blue-500 focus:ring-blue-500') . ' text-slate-100 text-xs sm:text-sm focus:outline-none focus:ring-2 transition']) }}>

    @if($error)
        <p class="text-xs text-rose-400 mt-1">{{ $error }}</p>
    @endif
</div>