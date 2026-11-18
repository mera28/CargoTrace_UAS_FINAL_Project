<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-slate-200">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'CargoTrace') - Maritime Logistics Intelligence</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

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
<body class="h-full flex antialiased bg-slate-200 text-slate-800">

    <!-- Left Sidebar -->
    <aside class="w-64 bg-navy-900 text-slate-300 flex-shrink-0 flex flex-col justify-between border-r border-slate-800">
        <div>
            <!-- Brand Logo -->
            <div class="h-16 flex items-center px-6 border-b border-slate-800/80 bg-navy-950/40">
                <a href="{{ route('dashboard') }}" class="flex items-center space-x-3 group">
                    <div class="w-9 h-9 rounded-lg bg-gradient-to-tr from-ocean-600 to-sky-400 flex items-center justify-center text-white shadow-md shadow-ocean-600/30">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1M5 17a2 2 0 104 0m-4 0a2 2 0 114 0m6 0a2 2 0 104 0m-4 0a2 2 0 114 0" />
                        </svg>
                    </div>
                    <div>
                        <span class="text-lg font-extrabold text-white tracking-tight flex items-center">
                            Cargo<span class="text-ocean-500">Trace</span>
                        </span>
                        <p class="text-[10px] tracking-wider uppercase text-slate-400 font-semibold">Maritime Intel</p>
                    </div>
                </a>
            </div>

            <!-- Navigation Links -->
            <nav class="p-4 space-y-1 text-sm font-medium">
                <div class="px-3 py-2 text-[11px] font-semibold text-slate-400 uppercase tracking-wider">
                    Core Platform
                </div>

                <!-- Dashboard (All Roles) -->
                <a href="{{ route('dashboard') }}" class="flex items-center space-x-3 px-3 py-2.5 rounded-lg {{ request()->routeIs('dashboard') ? 'bg-ocean-600 text-white font-semibold shadow-sm' : 'text-slate-300 hover:bg-slate-800/70 hover:text-white' }} transition-colors">
                    <svg class="w-5 h-5 text-current" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
                    </svg>
                    <span>Dashboard</span>
                </a>

                <!-- Shipments (All Roles) -->
                <a href="{{ route('shipments.index') }}" class="flex items-center space-x-3 px-3 py-2.5 rounded-lg {{ request()->routeIs('shipments.*') ? 'bg-ocean-600 text-white font-semibold shadow-sm' : 'text-slate-300 hover:bg-slate-800/70 hover:text-white' }} transition-colors">
                    <svg class="w-5 h-5 text-current" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                    </svg>
                    <span>Shipments</span>
                </a>

                @if(auth()->check() && (auth()->user()->isAdmin() || auth()->user()->isOperator()))
                    <div class="pt-4 px-3 py-2 text-[11px] font-semibold text-slate-400 uppercase tracking-wider">
                        Operations & Control
                    </div>

                    <a href="#" class="flex items-center space-x-3 px-3 py-2.5 rounded-lg text-slate-300 hover:bg-slate-800/70 hover:text-white transition-colors">
                        <svg class="w-5 h-5 text-current" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7" />
                        </svg>
                        <span>Tracking Events</span>
                    </a>

                    <a href="#" class="flex items-center space-x-3 px-3 py-2.5 rounded-lg text-slate-300 hover:bg-slate-800/70 hover:text-white transition-colors">
                        <svg class="w-5 h-5 text-current" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                        <span>Exceptions</span>
                    </a>
                @endif

                @if(auth()->check() && auth()->user()->isAdmin())
                    <div class="pt-4 px-3 py-2 text-[11px] font-semibold text-slate-400 uppercase tracking-wider">
                        Master Data & System
                    </div>

                    <!-- Carriers (Admin Only) -->
                    <a href="{{ route('carriers.index') }}" class="flex items-center space-x-3 px-3 py-2.5 rounded-lg {{ request()->routeIs('carriers.*') ? 'bg-ocean-600 text-white font-semibold shadow-sm' : 'text-slate-300 hover:bg-slate-800/70 hover:text-white' }} transition-colors">
                        <svg class="w-5 h-5 text-current" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                        </svg>
                        <span>Carriers</span>
                    </a>

                    <!-- Locations (Admin Only) -->
                    <a href="{{ route('locations.index') }}" class="flex items-center space-x-3 px-3 py-2.5 rounded-lg {{ request()->routeIs('locations.*') ? 'bg-ocean-600 text-white font-semibold shadow-sm' : 'text-slate-300 hover:bg-slate-800/70 hover:text-white' }} transition-colors">
                        <svg class="w-5 h-5 text-current" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                        <span>Locations</span>
                    </a>

                    <!-- Audit Logs (Admin Only) -->
                    <a href="{{ route('audit-logs.index') }}" class="flex items-center space-x-3 px-3 py-2.5 rounded-lg {{ request()->routeIs('audit-logs.*') ? 'bg-ocean-600 text-white font-semibold shadow-sm' : 'text-slate-300 hover:bg-slate-800/70 hover:text-white' }} transition-colors">
                        <svg class="w-5 h-5 text-current" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        <span>Audit Logs</span>
                    </a>
                @endif
            </nav>
        </div>

        <!-- Corridor Status Badge in Sidebar -->
        <div class="p-4 border-t border-slate-800 bg-navy-950/60">
            <div class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider mb-2">Active Corridor</div>
            <div class="bg-slate-800/60 rounded-lg p-2.5 border border-slate-700/60 text-xs">
                <div class="flex items-center space-x-1.5 text-ocean-400 font-semibold mb-1">
                    <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                    <span>SEA &rarr; Europe</span>
                </div>
                <div class="text-[11px] text-slate-400">Delay Threshold: <strong class="text-slate-200">12h</strong></div>
            </div>
        </div>
    </aside>

    <!-- Main Content Area -->
    <div class="flex-1 flex flex-col min-w-0 overflow-y-auto bg-slate-200">
        <!-- Top Navigation Bar -->
        <header class="h-16 bg-white border-b border-slate-300 flex items-center justify-between px-6 sticky top-0 z-10 shadow-sm">
            <div>
                <h1 class="text-xl font-bold text-slate-900 tracking-tight">
                    @yield('header-title', 'Dashboard')
                </h1>
                <p class="text-xs text-slate-500 font-medium">@yield('header-subtitle', 'Southeast Asia &rarr; Europe Corridor Control')</p>
            </div>

            <!-- Right Profile & Action Section -->
            @auth
            <div class="flex items-center space-x-4">
                <!-- Notifications Bell Icon -->
                @php
                    $unreadNotificationsCount = auth()->user()->notifications()->where('is_read', false)->count();
                @endphp
                <a href="{{ route('notifications.index') }}" class="relative p-2 rounded-xl text-slate-500 hover:text-slate-800 hover:bg-slate-100 transition-colors" title="Notifications">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                    </svg>
                    @if($unreadNotificationsCount > 0)
                        <span class="absolute top-1 right-1 flex h-4 min-w-4 px-1 items-center justify-center rounded-full bg-rose-500 text-[10px] font-bold text-white ring-2 ring-white">
                            {{ $unreadNotificationsCount > 9 ? '9+' : $unreadNotificationsCount }}
                        </span>
                    @endif
                </a>

                <!-- Divider -->
                <div class="h-6 w-px bg-slate-200"></div>

                <div class="text-right">
                    <div class="text-sm font-semibold text-slate-800">{{ auth()->user()->name }}</div>
                    <div class="text-xs text-slate-500">{{ auth()->user()->company_name ?? auth()->user()->email }}</div>
                </div>

                <!-- Role Badge -->
                <div>
                    @if(auth()->user()->isAdmin())
                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-purple-100 text-purple-800 ring-1 ring-inset ring-purple-600/20">
                            ADMIN
                        </span>
                    @elseif(auth()->user()->isOperator())
                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-sky-100 text-sky-800 ring-1 ring-inset ring-sky-600/20">
                            OPERATOR
                        </span>
                    @else
                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-800 ring-1 ring-inset ring-emerald-600/20">
                            CUSTOMER
                        </span>
                    @endif
                </div>

                <!-- Divider -->
                <div class="h-6 w-px bg-slate-200"></div>

                <!-- Logout Button -->
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="inline-flex items-center space-x-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold text-rose-600 hover:text-white hover:bg-rose-600 border border-rose-200 hover:border-transparent transition-all shadow-sm">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                        </svg>
                        <span>Logout</span>
                    </button>
                </form>
            </div>
            @endauth
        </header>

        <!-- Flash Messages -->
        <div class="px-6 pt-4">
            @if(session('success'))
                <div class="mb-4 flex items-center p-4 bg-emerald-50 border-l-4 border-emerald-500 rounded-r-lg text-emerald-800 text-sm shadow-sm">
                    <svg class="w-5 h-5 mr-3 text-emerald-500 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                    </svg>
                    <span>{{ session('success') }}</span>
                </div>
            @endif

            @if(session('info'))
                <div class="mb-4 flex items-center p-4 bg-sky-50 border-l-4 border-sky-500 rounded-r-lg text-sky-800 text-sm shadow-sm">
                    <svg class="w-5 h-5 mr-3 text-sky-500 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                    </svg>
                    <span>{{ session('info') }}</span>
                </div>
            @endif

            @if(session('error'))
                <div class="mb-4 flex items-center p-4 bg-rose-50 border-l-4 border-rose-500 rounded-r-lg text-rose-800 text-sm shadow-sm">
                    <svg class="w-5 h-5 mr-3 text-rose-500 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                    </svg>
                    <span>{{ session('error') }}</span>
                </div>
            @endif
        </div>

        <!-- Page Body Content -->
        <main class="flex-1 p-6">
            @yield('content')
        </main>
    </div>

    @stack('scripts')
</body>
</html>
