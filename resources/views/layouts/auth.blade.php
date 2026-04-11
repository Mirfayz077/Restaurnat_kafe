<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Restaurant POS') }}</title>

    @vite(['resources/scss/app.scss', 'resources/js/app.js'])
</head>
<body class="app-shell min-h-screen overflow-y-auto text-slate-100 antialiased">
    <main class="mx-auto flex min-h-screen w-full max-w-7xl flex-col px-4 py-8 sm:px-6 lg:px-8">
        @yield('content')
    </main>
</body>
</html>
