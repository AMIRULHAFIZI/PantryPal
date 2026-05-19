<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Admin Panel') }}
        </h2>
    </x-slot>

    <div class="py-12 bg-slate-900 min-h-screen text-white">
        <div class="max-w-6xl mx-auto sm:px-6 lg:px-8">

            {{-- Page Header --}}
            <div class="mb-8">
                <div class="flex items-center gap-3 mb-1">
                    <span class="text-2xl">🛡️</span>
                    <h1 class="text-3xl font-bold text-white">Admin Dashboard</h1>
                </div>
                <p class="text-slate-400 ml-10">System-wide overview of PantryPal</p>
            </div>

            {{-- Flash Messages --}}
            @if(session('success'))
                <div class="mb-6 bg-emerald-500/20 border border-emerald-500/50 text-emerald-400 px-4 py-3 rounded-xl" role="alert">
                    {{ session('success') }}
                </div>
            @endif
            @if(session('error'))
                <div class="mb-6 bg-red-500/20 border border-red-500/50 text-red-400 px-4 py-3 rounded-xl" role="alert">
                    {{ session('error') }}
                </div>
            @endif

            {{-- Stat Cards --}}
            <div class="grid grid-cols-2 md:grid-cols-4 gap-5 mb-10">
                <div class="bg-slate-800 border border-slate-700 rounded-xl p-6 flex flex-col gap-1">
                    <span class="text-2xl mb-1">👥</span>
                    <p class="text-slate-400 text-sm font-medium">Total Users</p>
                    <p class="text-4xl font-bold text-white">{{ $totalUsers }}</p>
                </div>
                <div class="bg-slate-800 border border-purple-500/30 rounded-xl p-6 flex flex-col gap-1">
                    <span class="text-2xl mb-1">🛡️</span>
                    <p class="text-slate-400 text-sm font-medium">Admins</p>
                    <p class="text-4xl font-bold text-purple-400">{{ $totalAdmins }}</p>
                </div>
                <div class="bg-slate-800 border border-emerald-500/30 rounded-xl p-6 flex flex-col gap-1">
                    <span class="text-2xl mb-1">🧺</span>
                    <p class="text-slate-400 text-sm font-medium">Pantry Items</p>
                    <p class="text-4xl font-bold text-emerald-400">{{ $totalItems }}</p>
                </div>
                <div class="bg-slate-800 border border-blue-500/30 rounded-xl p-6 flex flex-col gap-1">
                    <span class="text-2xl mb-1">📄</span>
                    <p class="text-slate-400 text-sm font-medium">Total Scans</p>
                    <p class="text-4xl font-bold text-blue-400">{{ $totalScans }}</p>
                </div>
            </div>

            {{-- Quick Actions --}}
            <div class="grid grid-cols-1 md:grid-cols-3 gap-5 mb-10">
                <a href="{{ route('admin.users') }}"
                   class="bg-slate-800 border border-slate-700 hover:border-purple-500/50 rounded-xl p-6 flex items-center gap-5 transition group">
                    <div class="h-14 w-14 bg-purple-500/20 rounded-full flex items-center justify-center shrink-0 group-hover:bg-purple-500/30 transition">
                        <svg class="w-7 h-7 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-lg font-bold text-white group-hover:text-purple-300 transition">Manage Users</h3>
                        <p class="text-slate-400 text-sm mt-1">View all accounts, change roles, or remove users</p>
                    </div>
                    <svg class="w-5 h-5 text-slate-600 group-hover:text-purple-400 ml-auto transition" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                </a>

                <a href="{{ route('admin.broadcasts') }}"
                   class="bg-slate-800 border border-slate-700 hover:border-orange-500/50 rounded-xl p-6 flex items-center gap-5 transition group">
                    <div class="h-14 w-14 bg-orange-500/20 rounded-full flex items-center justify-center shrink-0 group-hover:bg-orange-500/30 transition">
                        <svg class="w-7 h-7 text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"/>
                        </svg>
                    </div>
                    <div class="flex-1">
                        <h3 class="text-lg font-bold text-white group-hover:text-orange-300 transition">Broadcast Messages</h3>
                        <p class="text-slate-400 text-sm mt-1">Send ads, campaigns & announcements to all users</p>
                    </div>
                    <div class="flex flex-col items-end gap-1 ml-auto">
                        <span class="text-xs font-bold bg-orange-500/20 text-orange-400 border border-orange-500/40 px-2 py-0.5 rounded-full">
                            {{ $totalBroadcasts }}
                        </span>
                        <svg class="w-5 h-5 text-slate-600 group-hover:text-orange-400 transition" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                    </div>
                </a>

                <a href="{{ route('dashboard') }}"
                   class="bg-slate-800 border border-slate-700 hover:border-emerald-500/50 rounded-xl p-6 flex items-center gap-5 transition group">
                    <div class="h-14 w-14 bg-emerald-500/20 rounded-full flex items-center justify-center shrink-0 group-hover:bg-emerald-500/30 transition">
                        <svg class="w-7 h-7 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-lg font-bold text-white group-hover:text-emerald-300 transition">Go to My Dashboard</h3>
                        <p class="text-slate-400 text-sm mt-1">Back to your personal pantry view</p>
                    </div>
                    <svg class="w-5 h-5 text-slate-600 group-hover:text-emerald-400 ml-auto transition" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                </a>
            </div>

            {{-- Recent Users --}}
            <div class="bg-slate-800 border border-slate-700 rounded-xl overflow-hidden">
                <div class="p-5 border-b border-slate-700 flex items-center justify-between">
                    <h2 class="text-lg font-bold text-white">Recently Joined Users</h2>
                    <a href="{{ route('admin.users') }}" class="text-sm text-purple-400 hover:text-purple-300 transition font-medium">View all →</a>
                </div>
                <div class="divide-y divide-slate-700">
                    @forelse($recentUsers as $user)
                        <div class="px-5 py-4 flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <div class="h-9 w-9 rounded-full bg-slate-700 flex items-center justify-center text-slate-300 font-bold text-sm">
                                    {{ strtoupper(substr($user->name, 0, 1)) }}
                                </div>
                                <div>
                                    <p class="text-white font-medium text-sm">{{ $user->name }}</p>
                                    <p class="text-slate-500 text-xs">{{ $user->email }}</p>
                                </div>
                            </div>
                            <span class="text-xs font-bold px-2.5 py-1 rounded-full
                                {{ $user->role === 'admin'
                                    ? 'bg-purple-500/20 text-purple-300 border border-purple-500/40'
                                    : 'bg-slate-700 text-slate-400 border border-slate-600' }}">
                                {{ ucfirst($user->role) }}
                            </span>
                        </div>
                    @empty
                        <p class="p-5 text-slate-500 italic text-sm">No users found.</p>
                    @endforelse
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
