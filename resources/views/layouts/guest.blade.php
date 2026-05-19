<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans text-slate-200 antialiased bg-slate-900 overflow-x-hidden">
        <div class="min-h-screen w-full flex">
            <!-- Left Side: Form -->
            <div class="w-full lg:w-1/2 flex flex-col justify-center items-center px-6 py-12 sm:px-12 bg-slate-900 border-r border-slate-800/60 z-20">
                <!-- Mobile Logo -->
                <div class="mb-8 lg:hidden">
                    <a href="/">
                        <x-application-logo class="w-[87px] h-[87px] object-contain rounded-full drop-shadow-md mx-auto" />
                    </a>
                </div>

                <div class="w-full sm:max-w-md bg-slate-800/80 backdrop-blur-xl border border-slate-700/50 shadow-2xl overflow-hidden rounded-2xl px-8 py-10 relative">
                    <!-- Subtle glow effect behind form -->
                    <div class="absolute -top-10 -right-10 w-40 h-40 bg-emerald-500/10 rounded-full blur-3xl pointer-events-none"></div>
                    <div class="absolute -bottom-10 -left-10 w-40 h-40 bg-emerald-500/10 rounded-full blur-3xl pointer-events-none"></div>
                    
                    <div class="relative z-10">
                        {{ $slot }}
                    </div>
                </div>
            </div>

            <!-- Right Side: Logo & Gradient -->
            <div class="hidden lg:flex lg:w-1/2 flex-col justify-center items-center relative overflow-hidden bg-slate-950 z-10">
                <!-- Subtle Gradient Pattern -->
                <div class="absolute inset-0 bg-gradient-to-br from-emerald-900/30 via-slate-900 to-slate-950 mix-blend-multiply"></div>
                <div class="absolute inset-0 bg-[url('data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iNjAiIGhlaWdodD0iNjAiIHZpZXdCb3g9IjAgMCA2MCA2MCIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj48ZyBmaWxsPSJub25lIiBmaWxsLXJ1bGU9ImV2ZW5vZGQiPjxnIGZpbGw9IiMzMzQxNTUiIGZpbGwtb3BhY2l0eT0iMC4xNSI+PHBhdGggZD0iTTM2IDM0djIwaDJWMzRoLTJ6bS0yMHYyMGgyVjM0SDE2em0tMTYtNHYyaDQwdi0ySDB6bTAyNHYyaDQwdi0ySDB6Ii8+PC9nPjwvZz48L3N2Zz4=')] opacity-30"></div>
                
                <!-- Glowing orb behind logo -->
                <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[500px] h-[500px] bg-emerald-600/10 rounded-full blur-[120px] pointer-events-none"></div>

                <!-- Logo Container -->
                <div class="relative z-10 transform hover:scale-105 transition-transform duration-700 ease-out flex flex-col items-center">
                    <a href="/" class="block group">
                        <x-application-logo class="w-[300px] h-[300px] object-contain rounded-full drop-shadow-[0_0_40px_rgba(16,185,129,0.2)] ring-8 ring-slate-800/30 group-hover:ring-emerald-500/30 transition-all duration-500 bg-white/5 backdrop-blur-sm" />
                    </a>
                    
                    <div class="mt-12 text-center transform translate-y-4 opacity-100">
                        <h2 class="text-4xl font-extrabold bg-clip-text text-transparent bg-gradient-to-r from-emerald-400 to-teal-200 drop-shadow-md tracking-tight">
                            PantryPal
                        </h2>
                        <p class="mt-4 text-slate-400 max-w-md mx-auto text-lg leading-relaxed font-medium">
                            Join us in our mission to reduce food waste and manage your pantry effortlessly.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </body>
</html>
