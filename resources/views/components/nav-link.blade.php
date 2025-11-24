@props(['active'])

@php
$classes = ($active ?? false)
            ? 'inline-flex items-center px-1 pt-1 border-b-2 border-blue-700 text-sm font-semibold leading-5 text-blue-900 focus:outline-none focus:border-blue-800 transition duration-150 ease-in-out'
            : 'inline-flex items-center px-1 pt-1 border-b-2 border-transparent text-sm font-medium leading-5 text-slate-600 hover:text-blue-900 hover:border-blue-200 focus:outline-none focus:text-blue-900 focus:border-blue-200 transition duration-150 ease-in-out';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>
