@props(['disabled' => false])

<input @disabled($disabled) {{ $attributes->merge(['class' => 'bg-superficie text-texto border-borde placeholder:text-suave focus:border-primario focus:ring-primario rounded-md shadow-sm']) }}>
