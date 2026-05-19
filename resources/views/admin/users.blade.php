<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('User Management') }}
        </h2>
    </x-slot>

    <div class="py-12 bg-slate-900 min-h-screen text-white">
        <div class="max-w-6xl mx-auto sm:px-6 lg:px-8">

            {{-- Page Header --}}
            <div class="mb-8 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                <div>
                    <div class="flex items-center gap-3 mb-1">
                        <span class="text-2xl">👥</span>
                        <h1 class="text-3xl font-bold text-white">User Management</h1>
                    </div>
                    <p class="text-slate-400 ml-10">{{ $users->count() }} total registered users</p>
                </div>
                <a href="{{ route('admin.dashboard') }}"
                   class="flex items-center gap-2 text-sm text-slate-400 hover:text-white bg-slate-800 border border-slate-700 hover:border-slate-500 px-4 py-2 rounded-lg transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                    </svg>
                    Back to Admin Dashboard
                </a>
            </div>

            {{-- Flash Messages --}}
            @if(session('success'))
                <div class="mb-6 bg-emerald-500/20 border border-emerald-500/50 text-emerald-400 px-4 py-3 rounded-xl">
                    {{ session('success') }}
                </div>
            @endif
            @if(session('error'))
                <div class="mb-6 bg-red-500/20 border border-red-500/50 text-red-400 px-4 py-3 rounded-xl">
                    {{ session('error') }}
                </div>
            @endif

            {{-- Users Table --}}
            <div class="bg-slate-800 border border-slate-700 rounded-xl overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left">
                        <thead class="bg-slate-700/70 text-slate-400 text-xs uppercase tracking-widest">
                            <tr>
                                <th class="px-6 py-4">User</th>
                                <th class="px-6 py-4">Role</th>
                                <th class="px-6 py-4">Pantry Items</th>
                                <th class="px-6 py-4">Joined</th>
                                <th class="px-6 py-4 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-700">
                            @forelse($users as $user)
                                <tr class="hover:bg-slate-700/30 transition">
                                    {{-- User info --}}
                                    <td class="px-6 py-4">
                                        <div class="flex items-center gap-3">
                                            <div class="h-10 w-10 rounded-full shrink-0 flex items-center justify-center font-bold text-sm
                                                {{ $user->role === 'admin' ? 'bg-purple-500/20 text-purple-300' : 'bg-slate-700 text-slate-300' }}">
                                                {{ strtoupper(substr($user->name, 0, 1)) }}
                                            </div>
                                            <div>
                                                <p class="font-semibold text-white flex items-center gap-2">
                                                    {{ $user->name }}
                                                    @if($user->id === auth()->id())
                                                        <span class="text-[10px] bg-emerald-500/20 text-emerald-400 border border-emerald-500/30 px-1.5 py-0.5 rounded-full font-bold">You</span>
                                                    @endif
                                                </p>
                                                <p class="text-slate-500 text-sm">{{ $user->email }}</p>
                                            </div>
                                        </div>
                                    </td>

                                    {{-- Role Badge --}}
                                    <td class="px-6 py-4">
                                        <span class="inline-flex items-center gap-1.5 text-xs font-bold px-2.5 py-1.5 rounded-full
                                            {{ $user->role === 'admin'
                                                ? 'bg-purple-500/20 text-purple-300 border border-purple-500/40'
                                                : 'bg-slate-700 text-slate-400 border border-slate-600' }}">
                                            @if($user->role === 'admin')
                                                🛡️ Admin
                                            @else
                                                👤 User
                                            @endif
                                        </span>
                                    </td>

                                    {{-- Item Count --}}
                                    <td class="px-6 py-4">
                                        <span class="text-emerald-400 font-semibold">{{ $user->pantry_items_count }}</span>
                                        <span class="text-slate-500 text-sm ml-1">items</span>
                                    </td>

                                    {{-- Join Date --}}
                                    <td class="px-6 py-4 text-slate-400 text-sm">
                                        {{ $user->created_at->format('d M Y') }}
                                    </td>

                                    {{-- Actions --}}
                                    <td class="px-6 py-4">
                                        <div class="flex items-center justify-end gap-2">
                                            @if($user->id !== auth()->id())
                                                {{-- Toggle Role --}}
                                                @php
                                                    $toggleLabel = $user->role === 'admin' ? 'User' : 'Admin';
                                                    $toggleConfirm = "Change {$user->name} to {$toggleLabel}?";
                                                @endphp
                                                <form action="{{ route('admin.users.toggle-role', $user) }}" method="POST">
                                                    @csrf
                                                    <button type="submit"
                                                        class="text-xs font-semibold px-3 py-1.5 rounded-lg border transition
                                                            {{ $user->role === 'admin'
                                                                ? 'border-orange-500/40 text-orange-400 hover:bg-orange-500/10'
                                                                : 'border-purple-500/40 text-purple-400 hover:bg-purple-500/10' }}"
                                                        onclick="return confirm('{{ $toggleConfirm }}')">
                                                        {{ $user->role === 'admin' ? '↓ Make User' : '↑ Make Admin' }}
                                                    </button>
                                                </form>

                                                {{-- Delete User --}}
                                                <form action="{{ route('admin.users.delete', $user) }}" method="POST">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit"
                                                        class="text-xs font-semibold px-3 py-1.5 rounded-lg border border-red-500/40 text-red-400 hover:bg-red-500/10 transition"
                                                        onclick="return confirm('Delete {{ $user->name }} and ALL their pantry data? This cannot be undone.')">
                                                        🗑 Delete
                                                    </button>
                                                </form>
                                            @else
                                                <span class="text-slate-600 text-xs italic">Cannot edit self</span>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-6 py-12 text-center text-slate-500 italic">No users found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
