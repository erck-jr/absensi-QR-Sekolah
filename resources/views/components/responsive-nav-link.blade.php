@props(['active'])

@php
$classes = ($active ?? false)
            ? 'block w-full ps-3 pe-4 py-2 border-l-4 border-orange-500 text-start text-base font-medium text-orange-400 bg-navy-800 focus:outline-none focus:text-orange-500 focus:bg-navy-900 focus:border-orange-700 transition duration-150 ease-in-out'
            : 'block w-full ps-3 pe-4 py-2 border-l-4 border-transparent text-start text-base font-medium text-gray-300 hover:text-white hover:bg-navy-800 hover:border-gray-300 focus:outline-none focus:text-white focus:bg-navy-800 focus:border-gray-300 transition duration-150 ease-in-out';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>
