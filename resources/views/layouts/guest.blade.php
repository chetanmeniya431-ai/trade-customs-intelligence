<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? 'Login' }} — {{ config('app.name') }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
    <style>body { font-family: 'Inter', ui-sans-serif, system-ui, sans-serif; }</style>
</head>
<body class="h-full bg-gray-50">
    <div class="flex min-h-full flex-col justify-center px-6 py-12 lg:px-8">
        <div class="sm:mx-auto sm:w-full sm:max-w-sm">
            <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-lg bg-teal-600 text-white font-bold text-lg">TC</div>
            <h2 class="mt-6 text-center text-2xl font-bold tracking-tight text-gray-900">{{ config('app.name') }}</h2>
            <p class="mt-1 text-center text-sm text-gray-500">Customs document intelligence</p>
        </div>
        <div class="mt-8 sm:mx-auto sm:w-full sm:max-w-sm">
            {{ $slot }}
        </div>
    </div>
    @livewireScripts
</body>
</html>
