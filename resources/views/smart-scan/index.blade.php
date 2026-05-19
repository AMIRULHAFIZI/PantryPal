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

                    <!-- Ripeness Scan Placeholder -->
                    <div class="bg-slate-800 overflow-hidden shadow-sm border border-slate-700 rounded-xl hover:shadow-md transition-shadow duration-200 relative group cursor-not-allowed">
                        <div class="absolute inset-0 bg-slate-700/30 group-hover:bg-slate-700/50 transition duration-200"></div>
                        <div class="p-6 relative z-10 flex">
                            <div class="flex-shrink-0 bg-slate-700 rounded-xl p-4 border border-slate-600">
                                <svg class="h-8 w-8 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" />
                                </svg>
                            </div>
                            <div class="ml-5">
                                <h3 class="text-lg font-bold text-white mb-1">Check Ripeness</h3>
                                <p class="text-sm text-slate-400 leading-relaxed">Upload photos of fruits and vegetables to assess their ripeness level and estimated shelf life.</p>
                                <span class="inline-block mt-3 px-2 py-1 bg-slate-700 text-slate-300 border border-slate-600 text-xs font-semibold rounded uppercase tracking-wide">Coming Soon</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <script>
        function showProcessingOverlay() {
            document.getElementById('processing-overlay').classList.remove('hidden');
        }

        document.getElementById('upload-receipt-form').addEventListener('submit', function(e) {
            // Only show overlay if a file has actually been chosen
            var fileInput = document.getElementById('dropzone-file');
            if (fileInput.files && fileInput.files.length > 0) {
                showProcessingOverlay();
                // Update button UI
                document.getElementById('upload-btn-text').textContent = 'Processing…';
                document.getElementById('upload-btn-icon').classList.add('hidden');
                document.getElementById('upload-btn-spinner').classList.remove('hidden');
                document.getElementById('upload-btn').disabled = true;
                document.getElementById('upload-btn').classList.add('opacity-75', 'cursor-not-allowed');
            }
        });
    </script>
</x-app-layout>
