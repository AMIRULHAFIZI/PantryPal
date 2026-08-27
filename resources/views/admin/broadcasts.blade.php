<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Broadcast Messages') }}
        </h2>
    </x-slot>

    <div class="py-12 bg-slate-900 min-h-screen text-white">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8">

            {{-- Page Header --}}
            <div class="mb-8 flex items-center justify-between">
                <div>
                    <div class="flex items-center gap-3 mb-1">
                        <span class="text-2xl">📢</span>
                        <h1 class="text-3xl font-bold text-white">Broadcast Messages</h1>
                    </div>
                    <p class="text-slate-400 ml-10">Send announcements, ads, or food campaigns to all users</p>
                </div>
                <a href="{{ route('admin.dashboard') }}"
                   class="flex items-center gap-2 text-slate-400 hover:text-white transition text-sm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                    </svg>
                    Back to Dashboard
                </a>
            </div>

            {{-- Flash Messages --}}
            @if(session('success'))
                <div class="mb-6 bg-emerald-500/20 border border-emerald-500/50 text-emerald-400 px-4 py-3 rounded-xl flex items-center gap-2">
                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    {{ session('success') }}
                </div>
            @endif
            @if(session('error'))
                <div class="mb-6 bg-red-500/20 border border-red-500/50 text-red-400 px-4 py-3 rounded-xl">
                    {{ session('error') }}
                </div>
            @endif

            {{-- Create Broadcast Form --}}
            <div class="bg-slate-800 border border-slate-700 rounded-2xl p-6 mb-8">
                <h2 class="text-lg font-bold text-white mb-5 flex items-center gap-2">
                    <span class="h-7 w-7 bg-orange-500/20 rounded-lg flex items-center justify-center text-orange-400 text-sm">✉️</span>
                    Create New Broadcast
                </h2>

                <form action="{{ route('admin.broadcasts.store') }}" method="POST" enctype="multipart/form-data" class="space-y-5">
                    @csrf

                    {{-- Title --}}
                    <div>
                        <label for="title" class="block text-sm font-medium text-slate-300 mb-1.5">
                            Broadcast Title <span class="text-red-400">*</span>
                        </label>
                        <input type="text" id="title" name="title" value="{{ old('title') }}"
                               placeholder="e.g. Ramadan Food Campaign 🌙"
                               class="w-full bg-slate-700 border border-slate-600 text-white placeholder-slate-500
                                      rounded-xl px-4 py-3 focus:outline-none focus:ring-2 focus:ring-orange-500/50
                                      focus:border-orange-500 transition @error('title') border-red-500 @enderror">
                        @error('title')
                            <p class="text-red-400 text-xs mt-1.5">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Message --}}
                    <div>
                        <label for="message" class="block text-sm font-medium text-slate-300 mb-1.5">
                            Message <span class="text-red-400">*</span>
                        </label>
                        <textarea id="message" name="message" rows="4"
                                  placeholder="Write your announcement or campaign details here..."
                                  class="w-full bg-slate-700 border border-slate-600 text-white placeholder-slate-500
                                         rounded-xl px-4 py-3 focus:outline-none focus:ring-2 focus:ring-orange-500/50
                                         focus:border-orange-500 transition resize-none @error('message') border-red-500 @enderror">{{ old('message') }}</textarea>
                        @error('message')
                            <p class="text-red-400 text-xs mt-1.5">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Image Upload --}}
                    <div>
                        <label class="block text-sm font-medium text-slate-300 mb-1.5">
                            Image <span class="text-slate-500 font-normal">(optional — max 4MB)</span>
                        </label>
                        <div class="relative">
                            <input type="file" id="image" name="image" accept="image/*"
                                   class="hidden" onchange="previewImage(event)">
                            <label for="image"
                                   class="flex items-center gap-3 cursor-pointer bg-slate-700 border-2 border-dashed
                                          border-slate-600 hover:border-orange-500/60 rounded-xl px-4 py-4 transition group">
                                <div class="h-10 w-10 bg-slate-600 group-hover:bg-orange-500/20 rounded-lg flex items-center
                                            justify-center transition shrink-0">
                                    <svg class="w-5 h-5 text-slate-400 group-hover:text-orange-400 transition" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                              d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                    </svg>
                                </div>
                                <div>
                                    <p class="text-slate-300 text-sm font-medium group-hover:text-white transition" id="upload-label">
                                        Click to upload an image
                                    </p>
                                    <p class="text-slate-500 text-xs">PNG, JPG, GIF, WEBP up to 4MB</p>
                                </div>
                            </label>
                        </div>
                        {{-- Preview --}}
                        <div id="image-preview-wrap" class="mt-3 hidden">
                            <img id="image-preview" src="" alt="Preview"
                                 class="h-36 w-auto rounded-xl border border-slate-600 object-cover">
                            <button type="button" onclick="clearImage()"
                                    class="mt-2 text-xs text-red-400 hover:text-red-300 transition">
                                ✕ Remove image
                            </button>
                        </div>
                        @error('image')
                            <p class="text-red-400 text-xs mt-1.5">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Expiry Date --}}
                    <div>
                        <label for="expires_at" class="block text-sm font-medium text-slate-300 mb-1.5">
                            Expiry Date
                            <span class="text-slate-500 font-normal">(optional — leave blank for no expiry)</span>
                        </label>
                        <input type="datetime-local" id="expires_at" name="expires_at"
                               value="{{ old('expires_at') }}"
                               min="{{ now()->format('Y-m-d\TH:i') }}"
                               class="w-full bg-slate-700 border border-slate-600 text-white placeholder-slate-500
                                      rounded-xl px-4 py-3 focus:outline-none focus:ring-2 focus:ring-orange-500/50
                                      focus:border-orange-500 transition @error('expires_at') border-red-500 @enderror
                                      [color-scheme:dark]">
                        <p class="text-slate-500 text-xs mt-1.5">⏰ After this date & time, the message will automatically stop appearing to users.</p>
                        @error('expires_at')
                            <p class="text-red-400 text-xs mt-1.5">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Active Toggle + Submit --}}
                    <div class="flex items-center justify-between pt-2">
                        <label class="flex items-center gap-3 cursor-pointer">
                            <div class="relative">
                                <input type="checkbox" name="is_active" id="is_active" value="1"
                                       class="sr-only peer" checked>
                                <div class="w-11 h-6 bg-slate-600 peer-checked:bg-orange-500 rounded-full transition-colors"></div>
                                <div class="absolute top-0.5 left-0.5 w-5 h-5 bg-white rounded-full shadow
                                            peer-checked:translate-x-5 transition-transform"></div>
                            </div>
                            <span class="text-sm text-slate-300">Send as <strong class="text-white">Active</strong> (visible to users immediately)</span>
                        </label>

                        <button type="submit"
                                class="flex items-center gap-2 bg-orange-500 hover:bg-orange-400 text-white font-semibold
                                       px-6 py-3 rounded-xl transition active:scale-95 shadow-lg shadow-orange-500/20">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                            </svg>
                            Send Broadcast
                        </button>
                    </div>
                </form>
            </div>

            {{-- Broadcasts List --}}
            <div class="bg-slate-800 border border-slate-700 rounded-2xl overflow-hidden">
                <div class="p-5 border-b border-slate-700 flex items-center justify-between">
                    <h2 class="text-lg font-bold text-white">All Broadcasts</h2>
                    <span class="text-xs text-slate-500 bg-slate-700 px-2.5 py-1 rounded-full">
                        {{ $broadcasts->count() }} total
                    </span>
                </div>

                @forelse($broadcasts as $broadcast)
                    <div class="border-b border-slate-700 last:border-0 p-5">
                        <div class="flex gap-4">
                            {{-- Image Thumbnail --}}
                            @if($broadcast->image_path)
                                <img src="{{ Storage::url($broadcast->image_path) }}"
                                     alt="{{ $broadcast->title }}"
                                     class="h-20 w-28 object-cover rounded-xl border border-slate-600 shrink-0">
                            @else
                                <div class="h-20 w-28 bg-slate-700 rounded-xl flex items-center justify-center shrink-0 border border-slate-600">
                                    <span class="text-2xl">📢</span>
                                </div>
                            @endif

                            {{-- Content --}}
                            <div class="flex-1 min-w-0">
                                <div class="flex items-start justify-between gap-3 mb-1">
                                    <h3 class="text-white font-semibold truncate">{{ $broadcast->title }}</h3>
                                    <div class="flex items-center gap-2 shrink-0">
                                        @if($broadcast->isExpired())
                                            <span class="text-xs font-bold px-2.5 py-1 rounded-full bg-red-500/20 text-red-400 border border-red-500/40">
                                                ⏰ Ended
                                            </span>
                                        @endif
                                        <span class="text-xs font-bold px-2.5 py-1 rounded-full
                                            {{ $broadcast->is_active
                                                ? 'bg-emerald-500/20 text-emerald-400 border border-emerald-500/40'
                                                : 'bg-slate-700 text-slate-400 border border-slate-600' }}">
                                            {{ $broadcast->is_active ? '● Active' : '○ Inactive' }}
                                        </span>
                                    </div>
                                </div>
                                <p class="text-slate-400 text-sm line-clamp-2 mb-2">{{ $broadcast->message }}</p>
                                <div class="flex items-center gap-4">
                                    <p class="text-slate-600 text-xs">📅 Created: {{ $broadcast->created_at->format('d M Y, g:i A') }}</p>
                                    @if($broadcast->expires_at)
                                        <p class="text-xs {{ $broadcast->isExpired() ? 'text-red-400' : 'text-amber-500' }}">
                                            ⏰ Deadline: {{ $broadcast->expires_at->format('d M Y, g:i A') }}
                                        </p>
                                    @else
                                        <p class="text-slate-600 text-xs">⏰ No expiry</p>
                                    @endif
                                </div>
                            </div>
                        </div>

                        {{-- Actions --}}
                        <div class="flex items-center gap-3 mt-4 ml-32">
                            {{-- Toggle Active --}}
                            <form action="{{ route('admin.broadcasts.toggle', $broadcast) }}" method="POST">
                                @csrf
                                <button type="submit"
                                        class="text-xs font-medium px-3 py-1.5 rounded-lg border transition
                                               {{ $broadcast->is_active
                                                   ? 'border-yellow-500/40 text-yellow-400 hover:bg-yellow-500/10'
                                                   : 'border-emerald-500/40 text-emerald-400 hover:bg-emerald-500/10' }}">
                                    {{ $broadcast->is_active ? '⏸ Deactivate' : '▶ Activate' }}
                                </button>
                            </form>

                            {{-- Delete --}}
                            <form action="{{ route('admin.broadcasts.destroy', $broadcast) }}" method="POST"
                                  onsubmit="return confirm('Delete this broadcast? This cannot be undone.')">
                                @csrf
                                @method('DELETE')
                                <button type="submit"
                                        class="text-xs font-medium px-3 py-1.5 rounded-lg border border-red-500/40
                                               text-red-400 hover:bg-red-500/10 transition">
                                    🗑 Delete
                                </button>
                            </form>
                        </div>
                    </div>
                @empty
                    <div class="p-10 text-center">
                        <div class="text-4xl mb-3">📭</div>
                        <p class="text-slate-500 text-sm">No broadcasts yet. Create your first one above!</p>
                    </div>
                @endforelse
            </div>

        </div>
    </div>

    {{-- Image Preview Script --}}
    <script>
        function previewImage(event) {
            const file = event.target.files[0];
            if (!file) return;

            const reader = new FileReader();
            reader.onload = function(e) {
                document.getElementById('image-preview').src = e.target.result;
                document.getElementById('image-preview-wrap').classList.remove('hidden');
                document.getElementById('upload-label').textContent = file.name;
            };
            reader.readAsDataURL(file);
        }

        function clearImage() {
            document.getElementById('image').value = '';
            document.getElementById('image-preview-wrap').classList.add('hidden');
            document.getElementById('upload-label').textContent = 'Click to upload an image';
        }
    </script>
</x-app-layout>
