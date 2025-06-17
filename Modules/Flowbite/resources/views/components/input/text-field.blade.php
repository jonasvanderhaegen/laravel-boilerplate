@props([
    'field' => 'form.name',
    'type' => 'text',
    'divclass' => '',
    'label' => '',
    'helper' => false,
    'required' => false,
    'disabled' => false,
    'showPassword' => false,
])

@php
    $error = $errors->has($field);
@endphp

<div class="{{ $divclass }} relative z-0">
    @if ($type === 'password')
        <x-flowbite::input.password
            :label="$label"
            :error="$error"
            :show-password="$showPassword"
            :field="$field"
            :required="$required"
            :disabled="$disabled"
        />
    @else
        <x-flowbite::input.text
            {{ $attributes }}
            :error="$error"
            :field="$field"
            :type="$type"
            :required="$required"
            :disabled="$disabled"
        />
        <x-flowbite::input.label
            :error="$error"
            :field="$field"
            :label="$label"
            :required="$required"
            :disabled="$disabled"
        />
    @endif

    <x-flowbite::input.helper-error
        :helper="$helper"
        :error="$field"
        :disabled="$disabled"
    />
</div>
