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
@php $isDemo = auth()->check() && !auth()->user()->hasRole('Super Admin'); @endphp
<body class="h-full" style="{{ $isDemo ? 'padding-top: 40px' : '' }}">
<livewire:demo-contact-modal />
<div x-data="{ sidebarOpen: false }" class="min-h-full lg:flex">

    <div x-show="sidebarOpen" x-cloak class="fixed inset-0 z-40 bg-gray-900/60 lg:hidden" @click="sidebarOpen = false"></div>

    <div :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'"
         class="fixed left-0 bottom-0 z-50 w-64 transform bg-gray-900 transition-transform duration-200 ease-in-out lg:static lg:translate-x-0 lg:flex lg:flex-col lg:shrink-0"
         style="{{ $isDemo ? 'top: 40px' : 'top: 0' }}">
        <div class="flex h-16 shrink-0 items-center gap-2 px-5">
            <div class="flex h-8 w-8 items-center justify-center rounded-md bg-teal-600 text-white font-bold">TC</div>
            <span class="text-white font-semibold text-sm leading-tight">Trade Customs<br>Intelligence</span>
        </div>
        <nav class="flex-1 min-h-0 space-y-1 overflow-y-auto px-3 py-4">
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

            @role('Super Admin')
                <div class="pt-4 mt-4 border-t border-gray-800 space-y-1">
                    <a href="{{ route('super-admin.contacts') }}"
                       class="group flex items-center gap-x-3 rounded-md px-3 py-2 text-sm font-medium {{ request()->routeIs('super-admin.*') ? 'bg-teal-600 text-white' : 'text-gray-300 hover:bg-gray-800 hover:text-white' }}">
                        <x-icon name="envelope" class="h-5 w-5 shrink-0" />
                        Contact Requests
                        @php $newCount = \App\Models\ContactRequest::where('status','new')->count(); @endphp
                        @if($newCount > 0)
                            <span class="ml-auto inline-flex h-5 w-5 items-center justify-center rounded-full bg-amber-500 text-xs font-bold text-white">{{ $newCount }}</span>
                        @endif
                    </a>
                </div>
            @endrole

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
        <div class="shrink-0 border-t border-gray-800 p-4">
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
        <div class="sticky z-[45] flex h-16 items-center gap-x-4 border-b border-gray-200 bg-white px-4 shadow-sm lg:hidden"
             style="{{ $isDemo ? 'top: 40px' : 'top: 0' }}">
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

@if($isDemo)
<script nonce="{{ \Illuminate\Support\Facades\Vite::cspNonce() }}">
(function () {
    /* ================================================================
     * DEMO MODE GUARD v2 — fetch interception
     *
     * Intercepts every Livewire POST before it hits the server:
     *   - demo-contact-modal requests  → allow (contact form works)
     *   - poll ($refresh) / model-only → allow (page stays interactive)
     *   - write method calls            → show modal, return proper no-op
     *
     * The no-op echoes each component's snapshot unchanged so loading
     * indicators clear and the page stays intact.
     * ================================================================ */

    var WRITE_METHODS = [
        'create','save','delete','store','update','upload','submit',
        'add','remove','approve','reject','attach','detach','assign',
        'confirm','destroy','complete','fail','mark','process','archive',
        'activate','deactivate','reset','publish','verify','resolve',
        'retry','toggle','import','send','generate','record','log'
    ];

    function isWriteCall(name) {
        if (!name || name === '$refresh') return false;
        var lc = name.toLowerCase();
        return WRITE_METHODS.some(function (k) { return lc.indexOf(k) !== -1; });
    }

    function showDemoModal() {
        if (window.Livewire) { window.Livewire.dispatch('show-demo-modal'); }
    }

    var _fetch = window.fetch;
    window.fetch = function (url, opts) {
        var urlStr = typeof url === 'string' ? url : (url && url.href ? url.href : String(url));
        var isLwUpdate = opts && opts.method === 'POST' && urlStr.indexOf('livewire/update') !== -1;

        // Let the temp file upload through untouched — it only stores a
        // temporary file server-side (no domain write), and blocking it here
        // corrupts Livewire's own upload state so the *next* request (the
        // real save/store call) reaches the server for real instead of being
        // caught below. The actual write is always a wire:model.update call,
        // which IS caught by the isLwUpdate branch regardless of whether a
        // file was involved.
        if (!isLwUpdate) return _fetch.apply(this, arguments);

        var body = '';
        try { body = opts.body ? String(opts.body) : ''; } catch (e) {}

        // Always allow the DemoContactModal (contact form + show/close actions)
        if (body.indexOf('demo-contact-modal') !== -1) return _fetch.apply(this, arguments);

        // Allow poll-only and model-only updates (no explicit write calls)
        var hasWrite = false;
        try {
            var d = JSON.parse(body);
            if (Array.isArray(d.components)) {
                hasWrite = d.components.some(function (c) {
                    return (c.calls || []).some(function (call) { return isWriteCall(call.method); });
                });
            }
        } catch (e) {}

        if (!hasWrite) return _fetch.apply(this, arguments);

        // Write request: show modal and return a proper no-op so loading states clear
        showDemoModal();
        try {
            var req = JSON.parse(body);
            var noOp = (req.components || []).map(function (c) {
                try {
                    var snap = JSON.parse(c.snapshot);
                    return { id: snap.memo.id, snapshot: c.snapshot,
                             effects: { html: null, returns: {}, dispatches: [], xjs: [] } };
                } catch (e2) { return null; }
            }).filter(Boolean);
            return Promise.resolve(new Response(
                JSON.stringify({ components: noOp, assets: [] }),
                { status: 200, headers: { 'Content-Type': 'application/json' } }
            ));
        } catch (e) {}

        return Promise.resolve(new Response(
            JSON.stringify({ components: [], assets: [] }),
            { status: 200, headers: { 'Content-Type': 'application/json' } }
        ));
    };

    // Regular HTML form submissions (non-Livewire, non-logout, non-GET)
    document.addEventListener('submit', function (e) {
        var form = e.target;
        if (!form) return;
        if (form.method && form.method.toLowerCase() === 'get') return;
        if (form.action && form.action.indexOf('logout') !== -1) return;
        if (form.getAttribute && form.getAttribute('wire:submit')) return;
        e.preventDefault();
        e.stopImmediatePropagation();
        showDemoModal();
    }, true);
})();
</script>
@endif
</body>
</html>
