@props(['nombre'])

<img src="{{ asset('iconos/'.$nombre.'-claro.svg') }}" alt="" {{ $attributes->merge(['class' => 'icono-claro']) }}>
<img src="{{ asset('iconos/'.$nombre.'-oscuro.svg') }}" alt="" {{ $attributes->merge(['class' => 'icono-oscuro']) }}>
