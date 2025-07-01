<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">

    <title>{{ config('app.name') }}{{ isset($title) ? ' | '.$title : '' }}</title>

    <meta name="description" content="{{ $description ?? '' }}">
    <meta name="keywords" content="{{ $keywords ?? '' }}">
    <meta name="author" content="{{ $author ?? '' }}">

    @livewireStyles

    {{-- Vite CSS --}}
    @vite(['resources/css/app.css', 'resources/ts/app.ts'])
</head>

<body>

<x-flowbite::body-layouts.dashboard>

    {{ $slot }}

</x-flowbite::body-layouts.dashboard>

@livewireScripts
{{-- Vite JS --}}
</body>
