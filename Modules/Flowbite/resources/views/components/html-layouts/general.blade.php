<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

    <head>
        <script>
            (() => {

                let dark = localStorage.getItem('darkMode');
                if (dark === null) {
                    // first-time visitor: use OS preference
                    dark = window.matchMedia(
                        '(prefers-color-scheme: dark)',
                    ).matches;
                } else {
                    dark = JSON.parse(dark);
                }

                console.log(dark);

                if (dark) {
                    document.documentElement.classList.add('dark');
                }
            })();
        </script>

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

        <x-flowbite::body-layouts.default>

            {{ $slot }}

        </x-flowbite::body-layouts.default>

        @livewireScripts
        {{-- Vite JS --}}
    </body>
