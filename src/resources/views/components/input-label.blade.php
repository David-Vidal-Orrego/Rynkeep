@props(['value'])

<label {{ $attributes->merge(['class' => 'block font-medium text-sm text-texto']) }}>
    {{ $value ?? $slot }}
</label>
