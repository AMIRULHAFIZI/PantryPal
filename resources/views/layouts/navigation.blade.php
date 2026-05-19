<nav x-data="{ open: false }" class="bg-white border-b border-gray-100 relative z-[9999] w-full">
    <!-- Primary Navigation Menu -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex">
                <!-- Logo -->
                <div class="shrink-0 flex items-center">
                    <a href="{{ Auth::user()->isAdmin() ? route('admin.dashboard') : route('dashboard') }}">
                        <x-application-logo class="block h-9 w-auto fill-current text-gray-800" />
                    </a>
                </div>

                <!-- Navigation Links -->
                <div class="hidden space-x-8 sm:-my-px sm:ms-10 sm:flex">
                    @if(!Auth::user()->isAdmin())
                        <x-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                            {{ __('Dashboard') }}
                        </x-nav-link>
                        <x-nav-link :href="route('smart-scan.index')" :active="request()->routeIs('smart-scan.*')">
                            {{ __('Smart Scan') }}
                        </x-nav-link>
                    @endif
                    @if(Auth::user()->isAdmin())
                        <x-nav-link :href="route('admin.dashboard')" :active="request()->routeIs('admin.*')">
                            🛡️ {{ __('Admin Panel') }}
                        </x-nav-link>
                    @endif
                </div>
            </div>

            <!-- Settings Dropdown -->
            <div class="hidden sm:flex sm:items-center sm:ms-6">
                <x-dropdown align="right" width="48">
                    <x-slot name="trigger">
                        <button class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 bg-white hover:text-gray-700 focus:outline-none transition ease-in-out duration-150">
                            <div>{{ Auth::user()->name }}</div>

                            <div class="ms-1">
                                <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                </svg>
                            </div>
                        </button>
                    </x-slot>

                    <x-slot name="content">
                        <x-dropdown-link :href="route('profile.edit')">
                            {{ __('Profile') }}
                        </x-dropdown-link>

                        <!-- Authentication -->
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf

                            <x-dropdown-link :href="route('logout')"
                                    onclick="event.preventDefault();
                                                this.closest('form').submit();">
                                {{ __('Log Out') }}
                            </x-dropdown-link>
                        </form>
                    </x-slot>
                </x-dropdown>
            </div>

            <!-- Hamburger (Removed for Mobile UI enhancement) -->
        </div>
    </div>

</nav>

<!-- Mobile Bottom Navigation Bar -->
<div class="fixed bottom-0 left-0 w-full bg-slate-900 border-t border-slate-800 sm:hidden z-[9999] px-6 py-3 flex justify-between items-center shadow-[0_-4px_20px_rgba(0,0,0,0.5)]">
    @if(!Auth::user()->isAdmin())
    <!-- Dashboard -->
    <a href="{{ route('dashboard') }}" class="flex flex-col items-center justify-center w-16 transition-colors duration-200 {{ request()->routeIs('dashboard') ? 'text-emerald-400' : 'text-slate-500 hover:text-slate-300' }}">
        <svg class="h-6 w-6 mb-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
        </svg>
        <span class="text-[10px] font-semibold tracking-wide">Dashboard</span>
    </a>

    <!-- Smart Scan (Center floating button style) -->
    <a href="{{ route('smart-scan.index') }}" class="relative -top-5 flex flex-col items-center justify-center">
        <div class="bg-emerald-500 p-4 rounded-full shadow-lg shadow-emerald-500/30 text-white transform transition-transform active:scale-95 border-4 border-slate-900">
            <svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" />
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z" />
            </svg>
        </div>
        <span class="text-[10px] font-semibold tracking-wide mt-1 {{ request()->routeIs('smart-scan.*') ? 'text-emerald-400' : 'text-slate-500' }}">Scan</span>
    </a>
    @endif

    <!-- Profile -->
    <a href="{{ route('profile.edit') }}" class="flex flex-col items-center justify-center w-16 transition-colors duration-200 {{ request()->routeIs('profile.*') ? 'text-emerald-400' : 'text-slate-500 hover:text-slate-300' }}">
        <svg class="h-6 w-6 mb-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
        </svg>
        <span class="text-[10px] font-semibold tracking-wide">Profile</span>
    </a>

    @if(Auth::user()->isAdmin())
    <!-- Admin Panel (mobile) -->
    <a href="{{ route('admin.dashboard') }}" class="flex flex-col items-center justify-center w-16 transition-colors duration-200 {{ request()->routeIs('admin.*') ? 'text-purple-400' : 'text-slate-500 hover:text-slate-300' }}">
        <svg class="h-6 w-6 mb-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
        </svg>
        <span class="text-[10px] font-semibold tracking-wide">Admin</span>
    </a>
    @endif
</div>
