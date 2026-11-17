<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-slate-900">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Register - CargoTrace Maritime Intelligence</title>

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

    <div class="w-full max-w-lg my-8">
        <!-- Logo and Brand Title -->
        <div class="text-center mb-6">
            <div class="inline-flex w-12 h-12 rounded-2xl bg-gradient-to-tr from-ocean-600 to-sky-400 items-center justify-center text-white shadow-xl shadow-ocean-600/30 mb-2">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1M5 17a2 2 0 104 0m-4 0a2 2 0 114 0m6 0a2 2 0 104 0m-4 0a2 2 0 114 0" />
                </svg>
            </div>
            <h2 class="text-2xl font-extrabold text-white tracking-tight">Cargo<span class="text-ocean-500">Trace</span></h2>
            <p class="text-xs text-slate-400 font-medium">Customer Portal Registration</p>
        </div>

        <!-- Registration Form Card -->
        <div class="bg-slate-900 border border-slate-800 rounded-2xl shadow-2xl p-7 text-slate-200">
            <h3 class="text-lg font-bold text-white mb-1">Create Customer Account</h3>
            <p class="text-xs text-slate-400 mb-5">Register to track and manage shipments across Southeast Asia & Europe</p>

            @if(isset($errors) && $errors->any())
                <div class="mb-4 p-3 bg-rose-950/70 border border-rose-800 rounded-lg text-rose-300 text-xs">
                    <ul class="list-disc list-inside space-y-1">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('register') }}" class="space-y-4">
                @csrf

                <!-- Name -->
                <div>
                    <label for="name" class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-1.5">Full Name *</label>
                    <input id="name" type="text" name="name" value="{{ old('name') }}" required autofocus
                        class="w-full px-3.5 py-2 bg-slate-950 border border-slate-700 rounded-lg text-sm text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-ocean-500 focus:border-transparent transition-all"
                        placeholder="John Doe">
                </div>

                <!-- Email -->
                <div>
                    <label for="email" class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-1.5">Email Address *</label>
                    <input id="email" type="email" name="email" value="{{ old('email') }}" required
                        class="w-full px-3.5 py-2 bg-slate-950 border border-slate-700 rounded-lg text-sm text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-ocean-500 focus:border-transparent transition-all"
                        placeholder="john@example.com">
                </div>

                <!-- Company and Phone (2 columns) -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="company_name" class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-1.5">Company Name</label>
                        <input id="company_name" type="text" name="company_name" value="{{ old('company_name') }}"
                            class="w-full px-3.5 py-2 bg-slate-950 border border-slate-700 rounded-lg text-sm text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-ocean-500 focus:border-transparent transition-all"
                            placeholder="PT Global Logistik">
                    </div>

                    <div>
                        <label for="phone" class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-1.5">Phone Number</label>
                        <input id="phone" type="text" name="phone" value="{{ old('phone') }}"
                            class="w-full px-3.5 py-2 bg-slate-950 border border-slate-700 rounded-lg text-sm text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-ocean-500 focus:border-transparent transition-all"
                            placeholder="+62 812 3456 7890">
                    </div>
                </div>

                <!-- Password and Confirmation (2 columns) -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="password" class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-1.5">Password *</label>
                        <input id="password" type="password" name="password" required
                            class="w-full px-3.5 py-2 bg-slate-950 border border-slate-700 rounded-lg text-sm text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-ocean-500 focus:border-transparent transition-all"
                            placeholder="Min. 8 characters">
                    </div>

                    <div>
                        <label for="password_confirmation" class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-1.5">Confirm Password *</label>
                        <input id="password_confirmation" type="password" name="password_confirmation" required
                            class="w-full px-3.5 py-2 bg-slate-950 border border-slate-700 rounded-lg text-sm text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-ocean-500 focus:border-transparent transition-all"
                            placeholder="Repeat password">
                    </div>
                </div>

                <!-- Notice -->
                <p class="text-[11px] text-slate-400">
                    By registering, your account will have <span class="text-ocean-400 font-semibold">Customer</span> role privileges to track and create shipments.
                </p>

                <!-- Submit Button -->
                <button type="submit"
                    class="w-full py-2.5 px-4 bg-gradient-to-r from-ocean-600 to-sky-600 hover:from-ocean-500 hover:to-sky-500 text-white font-semibold rounded-lg text-sm shadow-lg shadow-ocean-600/30 transition-all flex items-center justify-center space-x-2">
                    <span>Create Customer Account</span>
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                    </svg>
                </button>
            </form>

            <!-- Login Link -->
            <div class="mt-6 pt-5 border-t border-slate-800 text-center text-xs text-slate-400">
                Already have an account?
                <a href="{{ route('login') }}" class="font-semibold text-ocean-400 hover:text-ocean-300 ml-1 underline decoration-ocean-500/40">Sign in here</a>
            </div>
        </div>
    </div>

</body>
</html>
