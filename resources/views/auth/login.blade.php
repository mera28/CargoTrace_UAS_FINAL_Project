<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-slate-900">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Sign In - CargoTrace Maritime Intelligence</title>

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
</head>
<body class="h-full flex items-center justify-center p-4 bg-slate-950 bg-[radial-gradient(ellipse_80%_80%_at_50%_-20%,rgba(2,132,199,0.25),rgba(255,255,255,0))]">

    <div class="w-full max-w-md">
        <!-- Logo and Brand Title -->
        <div class="text-center mb-8">
            <div class="inline-flex w-14 h-14 rounded-2xl bg-gradient-to-tr from-ocean-600 to-sky-400 items-center justify-center text-white shadow-xl shadow-ocean-600/30 mb-3">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1M5 17a2 2 0 104 0m-4 0a2 2 0 114 0m6 0a2 2 0 104 0m-4 0a2 2 0 114 0" />
                </svg>
            </div>
            <h2 class="text-2xl font-extrabold text-white tracking-tight">Cargo<span class="text-ocean-500">Trace</span></h2>
            <p class="text-xs text-slate-400 font-medium mt-1">Maritime Logistics Control & Risk Intelligence</p>
        </div>

        <!-- Main Card -->
        <div class="bg-slate-900 border border-slate-800 rounded-2xl shadow-2xl p-7 text-slate-200">
            <h3 class="text-lg font-bold text-white mb-1">Sign in to your account</h3>
            <p class="text-xs text-slate-400 mb-6">Enter your credentials to access the logistics control center</p>

            @if(session('info'))
                <div class="mb-4 p-3 bg-sky-950/80 border border-sky-800 rounded-lg text-sky-300 text-xs flex items-center space-x-2">
                    <svg class="w-4 h-4 text-sky-400 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                    </svg>
                    <span>{{ session('info') }}</span>
                </div>
            @endif

            @if(isset($errors) && $errors->any())
                <div class="mb-4 p-3 bg-rose-950/70 border border-rose-800 rounded-lg text-rose-300 text-xs">
                    <ul class="list-disc list-inside space-y-1">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('login') }}" class="space-y-4">
                @csrf

                <!-- Email Input -->
                <div>
                    <label for="email" class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-1.5">Email Address</label>
                    <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus
                        class="w-full px-3.5 py-2.5 bg-slate-950 border border-slate-700 rounded-lg text-sm text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-ocean-500 focus:border-transparent transition-all"
                        placeholder="operator@cargotrace.test">
                </div>

                <!-- Password Input -->
                <div>
                    <div class="flex items-center justify-between mb-1.5">
                        <label for="password" class="block text-xs font-semibold text-slate-300 uppercase tracking-wider">Password</label>
                    </div>
                    <input id="password" type="password" name="password" required
                        class="w-full px-3.5 py-2.5 bg-slate-950 border border-slate-700 rounded-lg text-sm text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-ocean-500 focus:border-transparent transition-all"
                        placeholder="••••••••">
                </div>

                <!-- Remember Me Checkbox -->
                <div class="flex items-center justify-between">
                    <label class="flex items-center space-x-2 text-xs text-slate-400 cursor-pointer">
                        <input type="checkbox" name="remember" class="w-4 h-4 rounded bg-slate-950 border-slate-700 text-ocean-600 focus:ring-ocean-500 focus:ring-offset-slate-900">
                        <span>Remember this device</span>
                    </label>
                </div>

                <!-- Submit Button -->
                <button type="submit"
                    class="w-full py-2.5 px-4 bg-gradient-to-r from-ocean-600 to-sky-600 hover:from-ocean-500 hover:to-sky-500 text-white font-semibold rounded-lg text-sm shadow-lg shadow-ocean-600/30 transition-all flex items-center justify-center space-x-2">
                    <span>Sign In to CargoTrace</span>
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                    </svg>
                </button>
            </form>

            <!-- Register Link -->
            <div class="mt-6 pt-5 border-t border-slate-800 text-center text-xs text-slate-400">
                Don't have an account yet?
                <a href="{{ route('register') }}" class="font-semibold text-ocean-400 hover:text-ocean-300 ml-1 underline decoration-ocean-500/40">Register here</a>
            </div>
        </div>

        <!-- Demo Accounts Quick Reference Card -->
        <div class="mt-5 bg-slate-900/70 border border-slate-800/80 rounded-xl p-4 text-xs">
            <div class="flex items-center justify-between mb-2">
                <span class="font-semibold text-slate-300 flex items-center space-x-1.5">
                    <svg class="w-4 h-4 text-amber-400" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                    </svg>
                    <span>Pre-configured Demo Accounts</span>
                </span>
                <span class="text-[10px] text-slate-400 font-mono">pwd: password123</span>
            </div>
            <div class="grid grid-cols-3 gap-2 pt-1 text-[11px]">
                <button type="button" onclick="fillCreds('admin@cargotrace.test')" class="p-2 bg-slate-950/80 hover:bg-slate-800 border border-slate-800 hover:border-purple-500/50 rounded-lg text-left transition-colors">
                    <div class="font-bold text-purple-400">Admin</div>
                    <div class="text-[10px] text-slate-400 truncate">admin@cargotrace.test</div>
                </button>
                <button type="button" onclick="fillCreds('operator@cargotrace.test')" class="p-2 bg-slate-950/80 hover:bg-slate-800 border border-slate-800 hover:border-sky-500/50 rounded-lg text-left transition-colors">
                    <div class="font-bold text-sky-400">Operator</div>
                    <div class="text-[10px] text-slate-400 truncate">operator@cargotrace.test</div>
                </button>
                <button type="button" onclick="fillCreds('customer1@cargotrace.test')" class="p-2 bg-slate-950/80 hover:bg-slate-800 border border-slate-800 hover:border-emerald-500/50 rounded-lg text-left transition-colors">
                    <div class="font-bold text-emerald-400">Customer 1</div>
                    <div class="text-[10px] text-slate-400 truncate">customer1@cargotrace.test</div>
                </button>
            </div>
        </div>
    </div>

    <script>
        function fillCreds(email) {
            document.getElementById('email').value = email;
            document.getElementById('password').value = 'password123';
        }
    </script>
</body>
</html>
