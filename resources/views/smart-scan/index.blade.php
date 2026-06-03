<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Smart Scan') }}
        </h2>
    </x-slot>

    <div class="py-12 bg-slate-900 min-h-screen text-white">
        <div class="max-w-6xl mx-auto sm:px-6 lg:px-8">
            
            @if(session('success'))
                <div class="mb-4 bg-emerald-500/20 border border-emerald-500/50 text-emerald-400 px-4 py-3 rounded relative shadow-sm" role="alert">
                    <span class="block sm:inline">{{ session('success') }}</span>
                </div>
            @endif

            @if(session('error'))
                <div class="mb-4 bg-red-500/20 border border-red-500/50 text-red-400 px-4 py-3 rounded relative shadow-sm" role="alert">
                    <span class="block sm:inline">{{ session('error') }}</span>
                </div>
            @endif

            <!-- Scan Statistics Cards -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                <div class="bg-slate-800 p-6 rounded-xl border border-slate-700 shadow-sm flex flex-col justify-center">
                    <h3 class="text-slate-400 font-medium mb-1">Current Scan Accuracy</h3>
                    <p class="text-3xl font-bold text-emerald-400">
                        {{ session('currentPercentage') !== null ? session('currentPercentage') . '%' : 'N/A' }}
                    </p>
                </div>
                <div class="bg-slate-800 p-6 rounded-xl border border-slate-700 shadow-sm flex flex-col justify-center">
                    <h3 class="text-slate-400 font-medium mb-1">Overall Accuracy Average</h3>
                    <p class="text-3xl font-bold {{ isset($overallPercentage) && $overallPercentage > 0 ? 'text-white' : 'text-slate-500' }}">
                        {{ isset($overallPercentage) && $overallPercentage > 0 ? $overallPercentage . '%' : 'N/A' }}
                    </p>
                </div>
            </div>

            <div class="bg-slate-800 overflow-hidden shadow-sm rounded-xl border border-slate-700">
                <div class="p-8 text-white border-b border-slate-700">
                    <h3 class="text-2xl font-bold text-emerald-400 mb-2">Upload Receipt</h3>
                    <p class="text-base text-slate-400 mb-8">Upload a clear photo of your grocery receipt. Our smart AI will automatically extract the items, categorize them, and add them directly to your pantry.</p>

                    <form action="{{ route('smart-scan.upload') }}" method="POST" enctype="multipart/form-data" class="space-y-6 max-w-3xl" id="upload-receipt-form">
                        @csrf
                        
                        <div class="flex items-center justify-center w-full">
                            <label for="dropzone-file" class="flex flex-col items-center justify-center w-full h-72 border-2 border-slate-600 border-dashed rounded-xl cursor-pointer bg-slate-700 hover:bg-slate-600 transition duration-300 ease-in-out relative group">
                                <div class="flex flex-col items-center justify-center pt-5 pb-6">
                                    <div class="p-4 bg-slate-800 rounded-full border border-slate-600 shadow-sm mb-4 group-hover:scale-110 transition-transform duration-300">
                                        <svg class="w-10 h-10 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path></svg>
                                    </div>
                                    <p class="mb-2 text-lg text-emerald-400"><span class="font-bold">Click to upload</span> or drag and drop</p>
                                    <p class="text-sm text-slate-400">PNG, JPG, JPEG (MAX. 10MB)</p>
                                </div>
                                <input id="dropzone-file" type="file" name="receipt" class="opacity-0 absolute inset-0 w-full h-full cursor-pointer" accept="image/*" required onchange="document.getElementById('file-name').textContent = this.files[0].name" />
                            </label>
                        </div>
                        
                        <div class="text-center text-sm font-medium text-slate-300 bg-slate-700 py-2 rounded-md border border-slate-600" id="file-name">No file selected</div>
                        @error('receipt')
                            <p class="text-red-400 text-sm mt-2 font-medium">{{ $message }}</p>
                        @enderror

                        <div class="flex justify-end pt-4 space-x-4">
                            <label class="cursor-pointer inline-flex flex-row items-center px-6 py-3 bg-slate-700 border border-slate-600 rounded-lg font-bold text-sm text-emerald-400 hover:text-white uppercase tracking-wider hover:bg-slate-600 shadow-md hover:shadow-lg transition-all duration-200" onclick="document.getElementById('camera-receipt').click()">
                                <span>Snap Photo</span>
                                <svg class="ml-2 -mr-0.5 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" />
                                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z" />
                                </svg>
                            </label>
                            
                            <button type="submit" id="upload-btn" class="inline-flex flex-row items-center px-6 py-3 bg-emerald-500 border border-transparent rounded-lg font-bold text-sm text-white uppercase tracking-wider hover:bg-emerald-600 focus:bg-emerald-600 active:bg-emerald-700 shadow-md hover:shadow-lg focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2 transition-all duration-200">
                                <span id="upload-btn-text">Upload File</span>
                                <svg id="upload-btn-icon" class="ml-2 -mr-0.5 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
                                </svg>
                                <svg id="upload-btn-spinner" class="ml-2 -mr-0.5 h-5 w-5 animate-spin hidden" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                                </svg>
                            </button>
                        </div>
                    </form>
                    
                    <form action="{{ route('smart-scan.upload') }}" method="POST" enctype="multipart/form-data" id="camera-receipt-form" class="hidden">
                        @csrf
                        <input type="file" id="camera-receipt" name="receipt" accept="image/*" capture="environment" onchange="showProcessingOverlay(); document.getElementById('camera-receipt-form').submit();">
                    </form>

                    {{-- Processing Overlay --}}
                    <div id="processing-overlay" class="fixed inset-0 z-50 flex flex-col items-center justify-center hidden" style="background: rgba(15, 23, 42, 0.92); backdrop-filter: blur(6px);">
                        <div class="bg-slate-800 border border-slate-700 rounded-2xl p-10 flex flex-col items-center shadow-2xl max-w-sm w-full mx-4">
                            {{-- Animated ring --}}
                            <div class="relative mb-6">
                                <svg class="animate-spin h-16 w-16 text-emerald-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-20" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3"></circle>
                                    <path class="opacity-90" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                                </svg>
                                <div class="absolute inset-0 flex items-center justify-center">
                                    <svg class="h-7 w-7 text-emerald-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                                    </svg>
                                </div>
                            </div>
                            <h3 class="text-xl font-bold text-white mb-2">Analysing Receipt…</h3>
                            <p class="text-slate-400 text-sm text-center leading-relaxed">Our AI is scanning your receipt and extracting items. This may take up to 30 seconds. Please don't close this page.</p>
                            {{-- Pulsing dots --}}
                            <div class="flex space-x-2 mt-6">
                                <span class="w-2 h-2 bg-emerald-400 rounded-full animate-bounce" style="animation-delay: 0ms;"></span>
                                <span class="w-2 h-2 bg-emerald-400 rounded-full animate-bounce" style="animation-delay: 150ms;"></span>
                                <span class="w-2 h-2 bg-emerald-400 rounded-full animate-bounce" style="animation-delay: 300ms;"></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Future placeholders section -->
            <div class="mt-8">
                <h3 class="text-lg font-bold text-white mb-4 px-1">Upcoming Features</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Expired Date Scan Placeholder -->
                    <div class="bg-slate-800 overflow-hidden shadow-sm border border-slate-700 rounded-xl hover:shadow-md transition-shadow duration-200 relative group cursor-not-allowed">
                        <div class="absolute inset-0 bg-slate-700/30 group-hover:bg-slate-700/50 transition duration-200"></div>
                        <div class="p-6 relative z-10 flex">
                            <div class="flex-shrink-0 bg-slate-700 rounded-xl p-4 border border-slate-600">
                                <svg class="h-8 w-8 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                            </div>
                            <div class="ml-5">
                                <h3 class="text-lg font-bold text-white mb-1">Scan Expiry Date</h3>
                                <p class="text-sm text-slate-400 leading-relaxed">Head over to your Dashboard to scan and update specific expiry dates for your items!</p>
                                <span class="inline-block mt-3 px-2 py-1 bg-slate-700 text-slate-300 border border-slate-600 text-xs font-semibold rounded uppercase tracking-wide">Moved to Dashboard</span>
                            </div>
                        </div>
                    </div>

                    <!-- Ripeness Checker — ACTIVE -->
                    <div class="bg-slate-800 overflow-hidden shadow-sm border border-emerald-500/30 rounded-xl md:col-span-2">
                        <div class="p-6">
                            <div class="flex items-center mb-4">
                                <div class="flex-shrink-0 bg-emerald-500/10 rounded-xl p-3 border border-emerald-500/30 mr-4">
                                    <svg class="h-7 w-7 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" />
                                    </svg>
                                </div>
                                <div>
                                    <h3 class="text-lg font-bold text-white">Check Ripeness</h3>
                                    <p class="text-sm text-slate-400">Upload a photo of any fruit or vegetable to get an instant AI ripeness assessment.</p>
                                </div>
                            </div>

                            <form id="ripeness-form" enctype="multipart/form-data" class="space-y-4">
                                @csrf
                                <div class="flex flex-col sm:flex-row gap-4 items-start">
                                    <label for="ripeness-file" class="flex-1 flex flex-col items-center justify-center h-36 border-2 border-slate-600 border-dashed rounded-xl cursor-pointer bg-slate-700 hover:bg-slate-600 transition duration-200 relative group">
                                        <div class="flex flex-col items-center justify-center">
                                            <svg class="w-8 h-8 text-emerald-400 mb-2 group-hover:scale-110 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                            <p class="text-sm text-emerald-400 font-semibold">Click to upload</p>
                                            <p id="ripeness-file-name" class="text-xs text-slate-400 mt-1">PNG, JPG, JPEG (max 10MB)</p>
                                        </div>
                                        <input id="ripeness-file" type="file" name="ripeness_image" accept="image/*" class="opacity-0 absolute inset-0 w-full h-full cursor-pointer" required>
                                    </label>
                                    <div class="flex flex-col gap-2 sm:pt-2">
                                        <button type="submit" id="ripeness-btn" class="inline-flex items-center px-6 py-3 bg-emerald-500 hover:bg-emerald-600 text-white font-bold text-sm rounded-lg shadow transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-emerald-500">
                                            <svg id="ripeness-btn-icon" class="mr-2 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                                            <svg id="ripeness-btn-spinner" class="mr-2 h-4 w-4 animate-spin hidden" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
                                            <span id="ripeness-btn-text">Analyse Ripeness</span>
                                        </button>
                                        <label class="inline-flex items-center px-6 py-3 bg-slate-700 hover:bg-slate-600 text-emerald-400 font-bold text-sm rounded-lg border border-slate-600 shadow transition-all duration-200 cursor-pointer" onclick="document.getElementById('ripeness-camera').click()">
                                            <svg class="mr-2 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                            Snap Photo
                                        </label>
                                        <input type="file" id="ripeness-camera" accept="image/*" capture="environment" class="hidden">
                                    </div>
                                </div>
                            </form>

                            <!-- Ripeness Result Card (hidden until result arrives) -->
                            <div id="ripeness-result" class="hidden mt-6 bg-slate-900 border border-slate-700 rounded-xl p-5 space-y-4 animate-pulse-once">
                                <div class="flex items-center justify-between flex-wrap gap-2">
                                    <h4 id="result-name" class="text-xl font-bold text-white"></h4>
                                    <span id="result-badge" class="px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wide"></span>
                                </div>
                                <div>
                                    <div class="flex justify-between text-xs text-slate-400 mb-1">
                                        <span>Unripe</span><span>Ripe</span><span>Spoiled</span>
                                    </div>
                                    <div class="w-full bg-slate-700 rounded-full h-3">
                                        <div id="result-bar" class="h-3 rounded-full transition-all duration-700" style="width:0%"></div>
                                    </div>
                                </div>
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-sm">
                                    <div class="bg-slate-800 rounded-lg p-3 border border-slate-700">
                                        <p class="text-slate-400 text-xs font-medium uppercase tracking-wide mb-1">Appearance</p>
                                        <p id="result-color" class="text-white"></p>
                                    </div>
                                    <div class="bg-slate-800 rounded-lg p-3 border border-slate-700">
                                        <p class="text-slate-400 text-xs font-medium uppercase tracking-wide mb-1">Est. Shelf Life</p>
                                        <p id="result-shelf" class="text-white font-semibold"></p>
                                    </div>
                                    <div class="bg-slate-800 rounded-lg p-3 border border-slate-700 sm:col-span-2">
                                        <p class="text-slate-400 text-xs font-medium uppercase tracking-wide mb-1">💡 Recommendation</p>
                                        <p id="result-rec" class="text-white"></p>
                                    </div>
                                    <div class="bg-slate-800 rounded-lg p-3 border border-slate-700 sm:col-span-2">
                                        <p class="text-slate-400 text-xs font-medium uppercase tracking-wide mb-1">📦 Storage Tip</p>
                                        <p id="result-storage" class="text-white"></p>
                                    </div>
                                </div>
                            </div>

                            <!-- Error message -->
                            <div id="ripeness-error" class="hidden mt-4 bg-red-500/10 border border-red-500/40 text-red-400 px-4 py-3 rounded-lg text-sm"></div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <script>
        // ── Receipt overlay ──────────────────────────────────────
        function showProcessingOverlay() {
            document.getElementById('processing-overlay').classList.remove('hidden');
        }

        document.getElementById('upload-receipt-form').addEventListener('submit', function(e) {
            var fileInput = document.getElementById('dropzone-file');
            if (fileInput.files && fileInput.files.length > 0) {
                showProcessingOverlay();
                document.getElementById('upload-btn-text').textContent = 'Processing…';
                document.getElementById('upload-btn-icon').classList.add('hidden');
                document.getElementById('upload-btn-spinner').classList.remove('hidden');
                document.getElementById('upload-btn').disabled = true;
                document.getElementById('upload-btn').classList.add('opacity-75', 'cursor-not-allowed');
            }
        });

        // ── Ripeness checker ─────────────────────────────────────
        const ripenessFileInput = document.getElementById('ripeness-file');
        const ripenessCamera    = document.getElementById('ripeness-camera');

        ripenessFileInput.addEventListener('change', function() {
            if (this.files[0]) document.getElementById('ripeness-file-name').textContent = this.files[0].name;
        });

        // Camera snap → auto-submit
        ripenessCamera.addEventListener('change', function() {
            if (this.files[0]) {
                // Transfer file to the main input via DataTransfer
                const dt = new DataTransfer();
                dt.items.add(this.files[0]);
                ripenessFileInput.files = dt.files;
                document.getElementById('ripeness-file-name').textContent = this.files[0].name;
                document.getElementById('ripeness-form').dispatchEvent(new Event('submit', {bubbles: true, cancelable: true}));
            }
        });

        document.getElementById('ripeness-form').addEventListener('submit', async function(e) {
            e.preventDefault();

            if (!ripenessFileInput.files || ripenessFileInput.files.length === 0) {
                showRipenessError('Please select an image first.');
                return;
            }

            // Button loading state
            document.getElementById('ripeness-btn-icon').classList.add('hidden');
            document.getElementById('ripeness-btn-spinner').classList.remove('hidden');
            document.getElementById('ripeness-btn-text').textContent = 'Analysing…';
            document.getElementById('ripeness-btn').disabled = true;
            document.getElementById('ripeness-result').classList.add('hidden');
            document.getElementById('ripeness-error').classList.add('hidden');

            const formData = new FormData();
            formData.append('ripeness_image', ripenessFileInput.files[0]);
            formData.append('_token', document.querySelector('#ripeness-form [name=_token]').value);

            try {
                const res  = await fetch('{{ route("smart-scan.ripeness") }}', { method: 'POST', body: formData });
                const json = await res.json();

                if (!res.ok || json.error) {
                    showRipenessError(json.error || 'Something went wrong. Please try again.');
                    return;
                }

                renderRipenessResult(json.result);
            } catch (err) {
                showRipenessError('Network error. Please check your connection and try again.');
            } finally {
                document.getElementById('ripeness-btn-icon').classList.remove('hidden');
                document.getElementById('ripeness-btn-spinner').classList.add('hidden');
                document.getElementById('ripeness-btn-text').textContent = 'Analyse Ripeness';
                document.getElementById('ripeness-btn').disabled = false;
            }
        });

        function showRipenessError(msg) {
            const el = document.getElementById('ripeness-error');
            el.textContent = msg;
            el.classList.remove('hidden');
        }

        function renderRipenessResult(r) {
            if (!r.is_produce) {
                showRipenessError(`"${r.item_name}" doesn't appear to be a fruit or vegetable. Please upload a produce photo.`);
                return;
            }

            const badgeColors = {
                'Unripe':      'bg-blue-500/20 text-blue-300 border border-blue-500/40',
                'Nearly Ripe': 'bg-yellow-500/20 text-yellow-300 border border-yellow-500/40',
                'Ripe':        'bg-emerald-500/20 text-emerald-300 border border-emerald-500/40',
                'Overripe':    'bg-orange-500/20 text-orange-300 border border-orange-500/40',
                'Spoiled':     'bg-red-500/20 text-red-300 border border-red-500/40',
            };
            const barColors = {
                'Unripe':      '#60a5fa',
                'Nearly Ripe': '#fbbf24',
                'Ripe':        '#34d399',
                'Overripe':    '#fb923c',
                'Spoiled':     '#f87171',
            };

            const level = r.ripeness_level || 'Ripe';
            const score = Math.min(100, Math.max(0, r.ripeness_score || 50));
            const shelf = r.shelf_life_days === 0 ? 'Should not be consumed' :
                          r.shelf_life_days === 1 ? '~1 day' :
                          r.shelf_life_days  ? `~${r.shelf_life_days} days` : 'Unknown';

            document.getElementById('result-name').textContent    = r.item_name || 'Unknown';
            document.getElementById('result-color').textContent   = r.color_description || '—';
            document.getElementById('result-shelf').textContent   = shelf;
            document.getElementById('result-rec').textContent     = r.recommendation || '—';
            document.getElementById('result-storage').textContent = r.storage_tip || '—';

            const badge = document.getElementById('result-badge');
            badge.textContent  = level;
            badge.className    = 'px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wide ' + (badgeColors[level] || badgeColors['Ripe']);

            const bar = document.getElementById('result-bar');
            bar.style.background = barColors[level] || '#34d399';
            // Animate bar from 0
            setTimeout(() => { bar.style.width = score + '%'; }, 50);

            document.getElementById('ripeness-result').classList.remove('hidden');
        }
    </script>
</x-app-layout>
