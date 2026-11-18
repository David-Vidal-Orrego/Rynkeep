@props(['active'])

@php
$classes = ($active ?? false)
            ? 'block w-full ps-3 pe-4 py-2 border-l-4 border-primario text-start text-base font-medium text-primario bg-fondo focus:outline-none focus:text-primario focus:bg-fondo focus:border-primario transition duration-150 ease-in-out'
            : 'block w-full ps-3 pe-4 py-2 border-l-4 border-transparent text-start text-base font-medium text-suave hover:text-texto hover:bg-fondo hover:border-borde focus:outline-none focus:text-texto focus:bg-fondo focus:border-borde transition duration-150 ease-in-out';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>
