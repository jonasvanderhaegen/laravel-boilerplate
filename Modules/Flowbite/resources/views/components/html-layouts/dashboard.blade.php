@props(['title' => __('Add title to your livewire page component')])

<x-core::default-html-layout :title="$title">
    <x-flowbite::body-layouts.dashboard>
        {{ $slot }}
    </x-flowbite::body-layouts.dashboard>
</x-core::default-html-layout>
