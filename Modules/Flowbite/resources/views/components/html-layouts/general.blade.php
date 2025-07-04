@props(['title' => 'No title'])

<x-core::default-html-layout :title="$title">
    <x-flowbite::body-layouts.default>
        {{ $slot }}
    </x-flowbite::body-layouts.default>
</x-core::default-html-layout>
