<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Restaurant POS') }}</title>

    @livewireStyles
    @vite(['resources/scss/app.scss', 'resources/js/app.js'])
</head>
<body class="app-shell text-slate-100 antialiased" data-theme="night">
    <main class="mx-auto flex min-h-screen w-full max-w-7xl items-stretch px-4 py-6 sm:px-6 lg:px-8">
        <div class="w-full">
            <livewire:pos-dashboard />
        </div>
    </main>

    @livewireScripts
</body>
</html>
