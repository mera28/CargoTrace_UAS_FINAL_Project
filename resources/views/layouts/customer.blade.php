<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-slate-50">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'CargoTrace') - Global Shipment Tracking Portal</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">

    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['"Plus Jakarta Sans"', 'sans-serif'],
                    },
                    colors: {
                        navy: {
                            800: '#1E293B',
                            900: '#0F172A',
                            950: '#020617',
                        },
                        ocean: {
                            400: '#38BDF8',
                            500: '#0EA5E9',
                            600: '#0284C7',
                            700: '#0369A1',
                        }
                    }
                }
            }
        }
    </script>
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
    </style>
    @stack('styles')
</head>
<body class="min-h-full flex flex-col bg-slate-50 text-slate-800 antialiased selection:bg-ocean-500 selection:text-white">

    <!-- Top Navigation Bar (Customer Portal) -->
    <header class="sticky top-0 z-50 bg-navy-950/90 backdrop-blur-md border-b border-slate-800/80 text-white shadow-lg transition-all">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-18 py-2.5">

                <!-- Left: Brand Logo & Portal Tag -->
                <div class="flex items-center space-x-6">
                    <a href="{{ route('dashboard') }}" class="flex items-center space-x-3 group">
                        <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-ocean-600 via-sky-500 to-ocean-400 flex items-center justify-center text-white shadow-lg shadow-ocean-600/30 group-hover:scale-105 transition-transform">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1M5 17a2 2 0 104 0m-4 0a2 2 0 114 0m6 0a2 2 0 104 0m-4 0a2 2 0 114 0" />
                            </svg>
                        </div>
                        <div>
                            <span class="text-xl font-black tracking-tight text-white flex items-center">
                                Cargo<span class="text-ocean-400">Trace</span>
                            </span>
                            <p class="text-[10px] tracking-wider uppercase text-slate-400 font-bold">Client Tracking Portal</p>
                        </div>
                    </a>

                    <!-- Navigation Links -->
                    <nav class="hidden md:flex items-center space-x-1 pl-4 border-l border-slate-800">
                        <a href="{{ route('dashboard') }}"
                           class="px-3.5 py-2 rounded-xl text-xs font-bold transition-all {{ request()->routeIs('dashboard') ? 'bg-ocean-600 text-white shadow-sm' : 'text-slate-300 hover:text-white hover:bg-slate-800/60' }}">
                            Home / Track
                        </a>

                        <a href="{{ route('shipments.index') }}"
                           class="px-3.5 py-2 rounded-xl text-xs font-bold transition-all {{ request()->routeIs('shipments.*') ? 'bg-ocean-600 text-white shadow-sm' : 'text-slate-300 hover:text-white hover:bg-slate-800/60' }}">
                            My Shipments
                        </a>

                        <a href="{{ route('notifications.index') }}"
                           class="relative px-3.5 py-2 rounded-xl text-xs font-bold transition-all flex items-center space-x-1.5 {{ request()->routeIs('notifications.*') ? 'bg-ocean-600 text-white shadow-sm' : 'text-slate-300 hover:text-white hover:bg-slate-800/60' }}">
                            <span>Notifications</span>
                            @php
                                $unreadCount = auth()->user()->notifications()->where('is_read', false)->count();
                            @endphp
                            @if($unreadCount > 0)
                                <span class="inline-flex items-center justify-center px-1.5 py-0.2 text-[10px] font-black rounded-full bg-rose-500 text-white animate-pulse">
                                    {{ $unreadCount > 9 ? '9+' : $unreadCount }}
                                </span>
                            @endif
                        </a>
                    </nav>
                </div>

                <!-- Right: Client Profile & Logout -->
                <div class="flex items-center space-x-4">
                    <div class="hidden sm:block text-right">
                        <div class="text-xs font-bold text-slate-100">{{ auth()->user()->name }}</div>
                        <div class="text-[11px] text-ocean-400 font-medium truncate max-w-[200px]">{{ auth()->user()->company_name ?? 'Individual Shipper' }}</div>
                    </div>

                    <span class="hidden sm:inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-extrabold bg-emerald-500/20 text-emerald-300 border border-emerald-500/30">
                        VERIFIED CLIENT
                    </span>

                    <div class="h-6 w-px bg-slate-800 hidden sm:block"></div>

                    <!-- Mobile Notifications Icon -->
                    <a href="{{ route('notifications.index') }}" class="md:hidden relative p-2 text-slate-400 hover:text-white">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                        @if(isset($unreadCount) && $unreadCount > 0)
                            <span class="absolute top-1 right-1 w-2 h-2 rounded-full bg-rose-500 ring-2 ring-navy-900"></span>
                        @endif
                    </a>

                    <!-- Logout Button -->
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="inline-flex items-center space-x-1.5 px-3 py-1.5 rounded-xl text-xs font-bold text-rose-400 hover:text-white hover:bg-rose-600/90 border border-rose-500/30 transition-all shadow-sm">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                            </svg>
                            <span class="hidden sm:inline">Logout</span>
                        </button>
                    </form>
                </div>

            </div>
        </div>
    </header>

    <!-- Flash Messages Container -->
    @if(session('success') || session('info') || session('error'))
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 w-full pt-4">
            @if(session('success'))
                <div class="mb-4 flex items-center p-4 bg-emerald-50 border border-emerald-200 rounded-2xl text-emerald-800 text-xs shadow-sm">
                    <svg class="w-5 h-5 mr-3 text-emerald-600 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                    </svg>
                    <span class="font-medium">{{ session('success') }}</span>
                </div>
            @endif

            @if(session('info'))
                <div class="mb-4 flex items-center p-4 bg-sky-50 border border-sky-200 rounded-2xl text-sky-800 text-xs shadow-sm">
                    <svg class="w-5 h-5 mr-3 text-sky-600 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                    </svg>
                    <span class="font-medium">{{ session('info') }}</span>
                </div>
            @endif

            @if(session('error'))
                <div class="mb-4 flex items-center p-4 bg-rose-50 border border-rose-200 rounded-2xl text-rose-800 text-xs shadow-sm">
                    <svg class="w-5 h-5 mr-3 text-rose-600 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                    </svg>
                    <span class="font-medium">{{ session('error') }}</span>
                </div>
            @endif
        </div>
    @endif

    <!-- Main Page Body Content -->
    <main class="flex-1 pb-16">
        @yield('content')
    </main>

    <!-- Modern Customer Portal Footer -->
    <footer class="bg-navy-950 text-slate-400 border-t border-slate-800 mt-auto">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-8 mb-8 text-xs">
                <!-- Col 1: Brand Info -->
                <div class="space-y-3 md:col-span-2">
                    <div class="flex items-center space-x-2.5">
                        <div class="w-8 h-8 rounded-lg bg-ocean-600 text-white flex items-center justify-center font-black">
                            CT
                        </div>
                        <span class="text-base font-extrabold text-white tracking-tight">CargoTrace Portal</span>
                    </div>
                    <p class="text-slate-400 text-xs leading-relaxed max-w-md">
                        Specialized Global Maritime Visibility and Consignment Intelligence for the strategic trade corridor connecting Southeast Asia and European ports.
                    </p>
                    <div class="flex items-center space-x-3 text-[11px] text-slate-400 font-mono">
                        <span class="flex items-center space-x-1.5">
                            <span class="w-2 h-2 rounded-full bg-emerald-400"></span>
                            <span>Corridor Telemetry Active</span>
                        </span>
                        <span>&bull;</span>
                        <span>Threshold: {{ config('cargotrace.delay_threshold_hours', 12) }}h</span>
                    </div>
                </div>

                <!-- Col 2: Strategic Hubs -->
                <div>
                    <h4 class="text-slate-200 font-bold uppercase tracking-wider text-[11px] mb-3">Trade Corridor Ports</h4>
                    <ul class="space-y-1.5 text-[11px] text-slate-400 font-mono">
                        <li>&bull; Tanjung Priok (IDJKT)</li>
                        <li>&bull; Port of Singapore (SGSIN)</li>
                        <li>&bull; Tanjung Pelepas (MYTPP)</li>
                        <li>&bull; Rotterdam (NLRTM)</li>
                        <li>&bull; Hamburg (DEHAM)</li>
                        <li>&bull; Antwerp (BEANR)</li>
                    </ul>
                </div>

                <!-- Col 3: Portal Links -->
                <div>
                    <h4 class="text-slate-200 font-bold uppercase tracking-wider text-[11px] mb-3">Quick Navigation</h4>
                    <ul class="space-y-2 text-xs">
                        <li>
                            <a href="{{ route('dashboard') }}" class="hover:text-white transition-colors">Track Consignments</a>
                        </li>
                        <li>
                            <a href="{{ route('shipments.index') }}" class="hover:text-white transition-colors">All My Shipments</a>
                        </li>
                        <li>
                            <a href="{{ route('notifications.index') }}" class="hover:text-white transition-colors">Alerts & Notifications</a>
                        </li>
                    </ul>
                </div>
            </div>

            <div class="pt-6 border-t border-slate-850 flex flex-col sm:flex-row sm:items-center justify-between text-[11px] text-slate-400 gap-3">
                <p>&copy; {{ date('Y') }} CargoTrace Maritime Logistics Intelligence. Final Project (UAS).</p>
                <p class="text-slate-400">Powered by Laravel 12 &bull; OpenStreetMap &bull; Open-Meteo &bull; Leaflet.js</p>
            </div>
        </div>
    </footer>

    @stack('scripts')
</body>
</html>
