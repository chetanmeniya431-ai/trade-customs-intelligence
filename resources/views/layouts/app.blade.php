<!DOCTYPE html>
<html lang="en" class="h-full bg-gray-50">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? 'Dashboard' }} — {{ config('app.name') }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4"></script>
    @livewireStyles
    <style>body { font-family: 'Inter', ui-sans-serif, system-ui, sans-serif; }</style>
</head>
<body class="h-full">
<div x-data="{ sidebarOpen: false }" class="min-h-full lg:flex">

    <div x-show="sidebarOpen" x-cloak class="fixed inset-0 z-40 bg-gray-900/60 lg:hidden" @click="sidebarOpen = false"></div>

    <div :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'"
         class="fixed inset-y-0 left-0 z-50 w-64 transform bg-gray-900 transition-transform duration-200 ease-in-out lg:static lg:translate-x-0 lg:flex lg:flex-col lg:shrink-0">
        <div class="flex h-16 items-center gap-2 px-5">
            <div class="flex h-8 w-8 items-center justify-center rounded-md bg-teal-600 text-white font-bold">TC</div>
            <span class="text-white font-semibold text-sm leading-tight">Trade Customs<br>Intelligence</span>
        </div>
        <nav class="flex-1 space-y-1 px-3 py-4">
            @php
                $navItems = [
                    ['label' => 'Dashboard', 'route' => 'dashboard', 'icon' => 'home'],
                    ['label' => 'Shipments', 'route' => 'shipments.index', 'icon' => 'cube'],
                    ['label' => 'HS Code Finder', 'route' => 'hs-code-finder', 'icon' => 'magnifying-glass'],
                    ['label' => 'Tariff Documents', 'route' => 'tariff-documents.index', 'icon' => 'document-text'],
                    ['label' => 'Signals', 'route' => 'signals.index', 'icon' => 'bell-alert'],
                    ['label' => 'Compliance Insights', 'route' => 'insights.index', 'icon' => 'light-bulb'],
                    ['label' => 'Reports', 'route' => 'reports.index', 'icon' => 'chart-bar'],
                ];
            @endphp
            @foreach ($navItems as $item)
                <a href="{{ route($item['route']) }}"
                   class="group flex items-center gap-x-3 rounded-md px-3 py-2 text-sm font-medium {{ request()->routeIs($item['route']) ? 'bg-teal-600 text-white' : 'text-gray-300 hover:bg-gray-800 hover:text-white' }}">
                    <x-icon :name="$item['icon']" class="h-5 w-5 shrink-0" />
                    {{ $item['label'] }}
                </a>
            @endforeach

            @role('Customs Broker|Import/Export Coordinator')
                <div class="pt-4 mt-4 border-t border-gray-800 space-y-1">
                    <a href="{{ route('clients.index') }}"
                       class="group flex items-center gap-x-3 rounded-md px-3 py-2 text-sm font-medium {{ request()->routeIs('clients.*') ? 'bg-teal-600 text-white' : 'text-gray-300 hover:bg-gray-800 hover:text-white' }}">
                        <x-icon name="building-office" class="h-5 w-5 shrink-0" />
                        Clients
                    </a>
                    @role('Customs Broker')
                    <a href="{{ route('settings.users') }}"
                       class="group flex items-center gap-x-3 rounded-md px-3 py-2 text-sm font-medium {{ request()->routeIs('settings.*') ? 'bg-teal-600 text-white' : 'text-gray-300 hover:bg-gray-800 hover:text-white' }}">
                        <x-icon name="cog-6-tooth" class="h-5 w-5 shrink-0" />
                        Settings
                    </a>
                    @endrole
                </div>
            @endrole
        </nav>
        <div class="border-t border-gray-800 p-4">
            <div class="flex items-center gap-3">
                <div class="flex h-9 w-9 items-center justify-center rounded-full bg-teal-700 text-white text-sm font-semibold">
                    {{ strtoupper(substr(auth()->user()->name ?? '?', 0, 1)) }}
                </div>
                <div class="min-w-0 flex-1">
                    <p class="truncate text-sm font-medium text-white">{{ auth()->user()->name }}</p>
                    <p class="truncate text-xs text-gray-400">{{ auth()->user()->getRoleNames()->first() }}</p>
                </div>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="text-gray-400 hover:text-white" title="Log out">
                        <x-icon name="arrow-right-on-rectangle" class="h-5 w-5" />
                    </button>
                </form>
            </div>
        </div>
    </div>

    <div class="lg:pl-0 flex flex-1 flex-col min-h-screen">
        <div class="sticky top-0 z-30 flex h-16 items-center gap-x-4 border-b border-gray-200 bg-white px-4 shadow-sm lg:hidden">
            <button @click="sidebarOpen = true" class="text-gray-700">
                <x-icon name="bars-3" class="h-6 w-6" />
            </button>
            <span class="font-semibold text-gray-900">{{ $title ?? 'Dashboard' }}</span>
        </div>

        <main class="flex-1 px-4 py-6 sm:px-6 lg:px-8">
            <div class="mx-auto max-w-7xl">
                {{ $slot }}
            </div>
        </main>
    </div>
</div>
@livewireScripts
</body>
</html>
