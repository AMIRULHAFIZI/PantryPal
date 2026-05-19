<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('PantryPal AI Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12 bg-slate-900 min-h-screen text-white">
        <div class="max-w-6xl mx-auto sm:px-6 lg:px-8">
            <div x-data="{ show: false }"
                 x-init="setTimeout(() => show = true, 150)"
                 x-show="show"
                 x-transition:enter="transition ease-out duration-1000"
                 x-transition:enter-start="opacity-0 translate-y-4"
                 x-transition:enter-end="opacity-100 translate-y-0"
                 style="display: none;"
                 class="mb-8">
                <h1 class="text-3xl font-bold text-emerald-400">
                    Welcome to PantryPal, {{ Auth::user()->name }}! ✨
                </h1>
                <p class="text-slate-400 mt-2">Here is your current inventory summary.</p>
            </div>

            <!-- AI Recipe Suggestion Banner -->
            <div x-data="{ 
                loading: false, 
                hasIdea: false, 
                noItems: false,
                recipe: null,
                showPrompt: true,
                errorMessage: '',
                fetchIdea() {
                    this.showPrompt = false;
                    this.loading = true;
                    this.errorMessage = '';
                    fetch('/recipe-suggestion?t=' + Date.now(), {
                        headers: { 
                            'X-Requested-With': 'XMLHttpRequest', 
                            'Accept': 'application/json',
                            'ngrok-skip-browser-warning': 'true'
                        }
                    })
                        .then(res => {
                            if (!res.ok) {
                                throw new Error('HTTP status ' + res.status);
                            }
                            return res.json();
                        })
                        .then(data => {
                            this.loading = false;
                            if(data.no_expiring_items) {
                                this.noItems = true;
                            } else if(data.error) {
                                this.errorMessage = 'AI Error: ' + data.error;
                            } else {
                                this.hasIdea = true;
                                this.recipe = data;
                            }
                        })
                        .catch((err) => { 
                            this.loading = false; 
                            this.errorMessage = 'Fetch failed: ' + err.message;
                        });
                }
            }" class="mb-8" x-show="!noItems" x-cloak>

                <!-- Initial Prompt State -->
                <div x-show="showPrompt" class="bg-indigo-900/40 border border-indigo-500/50 p-6 rounded-xl shadow-sm flex flex-col sm:flex-row items-center justify-between gap-4 cursor-pointer hover:bg-indigo-900/60 transition" @click="fetchIdea()">
                    <div class="flex items-center gap-4 w-full">
                        <div class="h-10 w-10 bg-indigo-500/30 rounded-full flex items-center justify-center shrink-0">
                            <span class="text-xl">💡</span>
                        </div>
                        <div>
                            <h3 class="text-indigo-300 font-semibold text-lg">Ask Chef AI</h3>
                            <p class="text-indigo-400/90 text-sm">Tap here to see if AI can generate a recipe from your expiring food!</p>
                        </div>
                    </div>
                    <button type="button" class="bg-indigo-500 hover:bg-indigo-600 active:bg-indigo-700 text-white px-5 py-2 rounded-lg font-bold text-sm transition shrink-0 whitespace-nowrap mt-3 sm:mt-0 w-full sm:w-auto text-center cursor-pointer pointer-events-auto" @click.stop="fetchIdea()">Generate Recipe</button>
                </div>
                
                <!-- Loading State -->
                <div x-show="loading" class="bg-indigo-900/40 border border-indigo-500/50 p-6 rounded-xl shadow-sm flex items-center gap-4 animate-pulse" style="display: none;">
                    <div class="h-10 w-10 bg-indigo-500/30 rounded-full flex items-center justify-center shrink-0">
                        <svg class="w-6 h-6 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 002-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
                    </div>
                    <div>
                        <h3 class="text-indigo-300 font-semibold text-lg">Chef AI is thinking...</h3>
                        <p class="text-indigo-400/70 text-sm">Reviewing your soon-to-expire items for recipe ideas!</p>
                    </div>
                </div>

                <!-- Error State -->
                <div x-show="errorMessage" class="bg-red-900/40 border border-red-500/50 p-6 rounded-xl shadow-sm text-red-300" style="display: none;">
                    <div class="flex items-center gap-3 mb-2">
                        <svg class="w-6 h-6 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                        <h3 class="font-bold">Oops! Connection Issue</h3>
                    </div>
                    <p class="text-sm font-mono bg-red-950/50 p-3 rounded" x-text="errorMessage"></p>
                    <button class="mt-4 bg-red-500/20 hover:bg-red-500/40 text-red-300 border border-red-500/50 px-4 py-2 rounded-lg text-sm transition" @click="fetchIdea()">Try Again</button>
                </div>

                <!-- Result State -->
                <div x-show="!loading && hasIdea" class="bg-indigo-900/20 border border-indigo-500/50 p-6 rounded-xl shadow-sm relative overflow-hidden" style="display: none;">
                    <div class="absolute -right-10 -top-10 w-40 h-40 bg-indigo-500/20 rounded-full blur-3xl pointer-events-none"></div>
                    <div class="flex flex-col md:flex-row items-start gap-4">
                        <div class="h-12 w-12 bg-indigo-500/20 rounded-full flex items-center justify-center shrink-0 mt-1">
                            <span class="text-2xl">👨‍🍳</span>
                        </div>
                        <div class="flex-1 w-full">
                            <h3 class="text-indigo-300 font-bold text-xl mb-1 flex items-center flex-wrap gap-2">
                                <span x-text="recipe && recipe.has_recipe ? 'Idea to Save Your Food!' : 'Expiring Soon Alert!'"></span>
                                <span class="bg-indigo-500/30 text-indigo-300 text-[10px] px-2 py-0.5 rounded-full font-bold border border-indigo-500/50 uppercase tracking-widest mt-1 md:mt-0">AI Suggested</span>
                            </h3>
                            <p class="text-slate-300 mb-2 mt-2 leading-relaxed" x-text="recipe ? recipe.description : ''"></p>
                            
                            <template x-if="recipe && recipe.has_recipe">
                                <div class="bg-slate-900/60 rounded-xl p-5 mt-4 border border-slate-700/50">
                                    <h4 class="text-indigo-400 font-bold mb-4 flex items-center gap-2 text-lg"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path></svg> <span x-text="recipe.title"></span></h4>
                                    
                                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                                        <div class="col-span-1 border-b md:border-b-0 md:border-r border-slate-700/50 pb-4 md:pb-0 md:pr-4">
                                            <h5 class="text-slate-400 text-xs font-bold mb-3 uppercase tracking-widest flex items-center gap-2"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path></svg> Use These</h5>
                                            <ul class="list-disc list-outside ml-4 text-sm text-slate-300 space-y-2">
                                                <template x-for="ing in (recipe && recipe.ingredients_to_use ? recipe.ingredients_to_use : [])" :key="ing">
                                                    <li x-text="ing" class="pl-1"></li>
                                                </template>
                                            </ul>
                                        </div>
                                        <div class="col-span-1 md:col-span-2">
                                            <h5 class="text-slate-400 text-xs font-bold mb-3 uppercase tracking-widest flex items-center gap-2"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path></svg> Quick Steps</h5>
                                            <ol class="list-decimal list-outside ml-4 text-sm text-slate-300 space-y-2">
                                                <template x-for="step in (recipe && recipe.instructions ? recipe.instructions : [])" :key="step">
                                                    <li x-text="step" class="pl-1"></li>
                                                </template>
                                            </ol>
                                        </div>
                                    </div>

                                    {{-- WhatsApp Share Button --}}
                                    <div class="mt-5 flex justify-end">
                                        <button
                                            type="button"
                                            id="whatsapp-share-btn"
                                            @click="
                                                if (!recipe) return;
                                                let msg = '🍽️ *' + recipe.title + '*\n\n';
                                                msg += recipe.description + '\n\n';
                                                msg += '🛒 *Ingredients:*\n';
                                                (recipe.ingredients_to_use || []).forEach(ing => { msg += '• ' + ing + '\n'; });
                                                msg += '\n📋 *Steps:*\n';
                                                (recipe.instructions || []).forEach((step, i) => { msg += (i + 1) + '. ' + step + '\n'; });
                                                msg += '\n_(Generated by PantryPal AI 🤖)_';
                                                window.open('https://wa.me/?text=' + encodeURIComponent(msg), '_blank');
                                            "
                                            style="background:#25D366;"
                                            onmouseover="this.style.background='#1ebe5d';this.style.transform='scale(1.05)';"
                                            onmouseout="this.style.background='#25D366';this.style.transform='scale(1)';"
                                            onmousedown="this.style.background='#17a84f';"
                                            onmouseup="this.style.background='#1ebe5d';"
                                            class="inline-flex items-center gap-2 text-white font-bold px-5 py-2.5 rounded-xl text-sm transition-all duration-200 shadow-lg"
                                        >
                                            <svg class="w-5 h-5 shrink-0" viewBox="0 0 24 24" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
                                                <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413Z"/>
                                            </svg>
                                            Share via WhatsApp
                                        </button>
                                    </div>
                                </div>

                            </template>
                        </div>
                    </div>
                </div>
            </div>

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

            <!-- Overview Cards -->
            <div class="grid grid-cols-2 md:grid-cols-4 gap-6 mb-8">
                <div class="bg-slate-800 p-6 rounded-xl border border-slate-700 shadow-sm flex flex-col justify-center">
                    <h3 class="text-slate-400 font-medium mb-1">Total Items</h3>
                    <p class="text-3xl font-bold text-white">{{ $totalItems }}</p>
                </div>
                <div class="bg-slate-800 p-6 rounded-xl border border-orange-500/30 shadow-sm flex flex-col justify-center">
                    <h3 class="text-slate-400 font-medium mb-1">Expiring Soon</h3>
                    <p class="text-3xl font-bold text-orange-400">{{ $expiringSoon }}</p>
                    <p class="text-xs text-slate-500 mt-1">Within 7 days</p>
                </div>
                <div class="bg-slate-800 p-6 rounded-xl border border-red-500/30 shadow-sm flex flex-col justify-center">
                    <h3 class="text-slate-400 font-medium mb-1">Expired</h3>
                    <p class="text-3xl font-bold text-red-500">{{ $expired }}</p>
                    <p class="text-xs text-slate-500 mt-1">Past expiry date</p>
                </div>
                <div class="bg-slate-800 p-6 rounded-xl border border-slate-700 shadow-sm flex flex-col justify-center">
                    <h3 class="text-slate-400 font-medium mb-1">Out of Stock</h3>
                    <p class="text-3xl font-bold text-red-500">{{ $outOfStock }}</p>
                </div>
            </div>

            <!-- Add New Item Form -->
            <div class="bg-slate-800 p-6 rounded-xl mb-10 border border-slate-700 shadow-sm">
                <h2 class="text-xl mb-4 font-semibold">Add New Item</h2>
                <form action="{{ route('pantry.store') }}" method="POST" class="flex flex-wrap gap-4">
                    @csrf
                    <input type="text" name="item_name" placeholder="Item Name (e.g. Eggs)"
                        class="bg-slate-700 border-none text-white p-2 rounded flex-1 min-w-[200px]" required>
                    <input type="number" name="quantity" placeholder="Qty" class="bg-slate-700 border-none text-white p-2 rounded w-20" step="0.001" min="0" required>
                    <select name="unit" class="bg-slate-700 border-none text-white p-2 rounded">
                        <option value="pcs">pcs</option>
                        <option value="kg">kg</option>
                        <option value="g">g</option>
                        <option value="L">L</option>
                        <option value="ml">ml</option>
                        <option value="pack">pack</option>
                        <option value="box">box</option>
                        <option value="bottle">bottle</option>
                        <option value="can">can</option>
                        <option value="bag">bag</option>
                    </select>
                    <div class="flex flex-col gap-1">
                        <label for="expiry_date" class="text-xs text-slate-400 font-medium px-1">Expiry Date <span class="text-slate-500">(optional)</span></label>
                        <input type="date" id="expiry_date" name="expiry_date" class="bg-slate-700 border-none text-white p-2 rounded">
                    </div>
                    
                    <select name="category" class="bg-slate-700 border-none text-white p-2 rounded">
                        <option value="">No Category</option>
                        <option value="Dairy">Dairy</option>
                        <option value="Eggs">Eggs</option>
                        <option value="Meat">Meat</option>
                        <option value="Rice">Rice</option>
                        <option value="Sandwiches">Sandwiches</option>
                        <option value="Pastry & Desserts">Pastry &amp; Desserts</option>
                        <option value="Canned Goods">Canned Goods</option>
                        <option value="Snacks">Snacks</option>
                        <option value="Produce">Produce</option>
                        <option value="Dry Goods">Dry Goods</option>
                        <option value="Condiments">Condiments</option>
                        <option value="Frozen">Frozen</option>
                        <option value="Other">Other</option>
                    </select>

                    <button type="submit"
                        class="bg-emerald-500 hover:bg-emerald-600 text-white px-6 py-2 rounded font-bold transition">Add to
                        Pantry</button>
                </form>
            </div>

            <!-- Pantry Table with Search & Filter -->
            <div x-data="{ searchQuery: '', filterCategory: '' }" class="bg-slate-800 rounded-xl overflow-hidden border border-slate-700 shadow-sm">
                <div class="p-6 border-b border-slate-700 flex flex-col sm:flex-row gap-4 items-center justify-between">
                    <h2 class="text-xl font-semibold">Your Pantry</h2>
                    <div class="flex flex-col sm:flex-row gap-4 w-full sm:w-auto">
                        <input type="text" x-model="searchQuery" placeholder="Search items..."
                            class="bg-slate-700 border-none text-white p-2 rounded w-full sm:w-64">
                        <select x-model="filterCategory" class="bg-slate-700 border-none text-white p-2 rounded w-full sm:w-auto">
                            <option value="">All Categories</option>
                            <option value="Dairy">Dairy</option>
                            <option value="Eggs">Eggs</option>
                            <option value="Meat">Meat</option>
                            <option value="Rice">Rice</option>
                            <option value="Sandwiches">Sandwiches</option>
                            <option value="Pastry & Desserts">Pastry &amp; Desserts</option>
                            <option value="Canned Goods">Canned Goods</option>
                            <option value="Snacks">Snacks</option>
                            <option value="Produce">Produce</option>
                            <option value="Dry Goods">Dry Goods</option>
                            <option value="Condiments">Condiments</option>
                            <option value="Frozen">Frozen</option>
                            <option value="Other">Other</option>
                        </select>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left">
                        <thead class="bg-slate-700 text-emerald-400">
                            <tr>
                                <th class="p-4">Food Item</th>
                                <th class="p-4">Category</th>
                                <th class="p-4">Quantity</th>
                                <th class="p-4">Expiry Date</th>
                                <th class="p-4">Ripeness</th>
                                <th class="p-4">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($items as $item)
                                @php
                                    $isExpired = false;
                                    $isExpiringSoon = false;
                                    if ($item->expiry_date) {
                                        try {
                                            $expiry = \Carbon\Carbon::parse($item->expiry_date)->startOfDay();
                                            $today = now()->startOfDay();
                                            if ($expiry->isBefore($today)) {
                                                $isExpired = true;
                                            } elseif ($today->diffInDays($expiry) <= 7) {
                                                $isExpiringSoon = true;
                                            }
                                        } catch (\Exception $e) {}
                                    }
                                @endphp
                                <tr class="border-t border-slate-700 hover:bg-slate-700/50" 
                                    x-show="(!searchQuery || '{{ strtolower(addslashes($item->item_name)) }}'.includes(searchQuery.toLowerCase())) && (!filterCategory || '{{ addslashes($item->category ?? '') }}' === filterCategory)">
                                    <td class="p-4 font-medium">{{ $item->item_name }}</td>
                                    <td class="p-4 text-slate-400">{{ $item->category ?: '-' }}</td>
                                    <td class="p-4">
                                        @php
                                            $qty = $item->quantity;
                                            // Trim trailing zeros: 1.000 → "1", 0.848 → "0.848", 1.500 → "1.5"
                                            $qtyFormatted = rtrim(rtrim(number_format((float)$qty, 3), '0'), '.');
                                        @endphp
                                        <span class="font-medium">{{ $qtyFormatted }}</span>
                                        <span class="text-slate-400 text-sm ml-1">{{ $item->unit ?? 'pcs' }}</span>
                                    </td>
                                    <td class="p-4 border-t border-slate-700">
                                        <div class="flex items-center justify-between">
                                            <div class="flex flex-col gap-1">
                                                <span class="{{ $isExpired ? 'text-red-500 font-bold' : ($isExpiringSoon ? 'text-orange-400 font-bold' : ($item->expiry_date ? 'text-slate-300' : 'text-slate-500 italic')) }}">{{ $item->expiry_date ?? '-' }}</span>
                                                @if($isExpired)
                                                    <span class="inline-flex items-center gap-1 text-xs font-bold text-red-400 bg-red-500/20 border border-red-500/40 px-2 py-0.5 rounded-full w-fit">
                                                        <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/></svg>
                                                        Expired
                                                    </span>
                                                @elseif($isExpiringSoon)
                                                    <span class="inline-flex items-center gap-1 text-xs font-bold text-orange-400 bg-orange-500/20 border border-orange-500/40 px-2 py-0.5 rounded-full w-fit">
                                                        <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
                                                        Expiring Soon
                                                    </span>
                                                @endif
                                            </div>
                                            <form action="{{ route('pantry.scan-expiry', $item->id) }}" method="POST" enctype="multipart/form-data" class="inline" id="camera-form-{{ $item->id }}">
                                                @csrf
                                                <label class="cursor-pointer text-emerald-400 hover:text-emerald-300 ml-3 p-2 bg-slate-700 rounded-full inline-block transition-transform hover:scale-110" title="Snap Expiry Date">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                                                    <input type="file" name="expiry_image" accept="image/*" capture="environment" class="hidden" onchange="document.getElementById('camera-form-{{ $item->id }}').submit()">
                                                </label>
                                            </form>
                                        </div>
                                    </td>
                                    <td class="p-4 text-slate-400">{{ $item->ripeness_info ?: '-' }}</td>
                                    <td class="p-4">
                                        <div class="flex gap-3 items-center">
                                            <a href="{{ route('pantry.edit', $item->id) }}" class="text-blue-400 hover:text-blue-300 font-semibold text-sm transition">Edit</a>
                                            <form action="{{ route('pantry.destroy', $item->id) }}" method="POST" class="inline" onsubmit="return confirm('Are you sure you want to delete this item?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-red-400 hover:text-red-300 font-semibold text-sm transition">Delete</button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="p-10 text-center text-slate-500 italic">No items found in your database.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>