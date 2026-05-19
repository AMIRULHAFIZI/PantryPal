@props(['disabled' => false])

<input @disabled($disabled) {{ $attributes->merge(['class' => 'bg-slate-700 border-none text-white rounded focus:ring-emerald-500 focus:border-emerald-500 shadow-sm']) }}>
