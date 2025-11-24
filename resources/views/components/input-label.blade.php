@props(['value'])

<label {{ $attributes->merge(['class' => 'block font-medium text-sm text-blue-950']) }}>
    {{ $value ?? $slot }}
</label>
