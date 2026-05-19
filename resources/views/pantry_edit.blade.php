<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Edit Pantry Item') }}
        </h2>
    </x-slot>

    <div class="py-12 bg-slate-900 min-h-screen text-white flex items-center justify-center">
        <div class="max-w-xl w-full mx-auto sm:px-6 lg:px-8">
            <div class="bg-slate-800 p-8 rounded-xl border border-slate-700 shadow-xl relative overflow-hidden">
                <div class="absolute -top-10 -right-10 w-40 h-40 bg-emerald-500/10 rounded-full blur-3xl pointer-events-none"></div>

                <div class="flex items-center justify-between mb-6 relative z-10">
                    <h2 class="text-2xl font-bold text-emerald-400">Edit "{{ $pantryItem->item_name }}"</h2>
                    <a href="{{ route('dashboard') }}" class="text-slate-400 hover:text-white transition">Back</a>
                </div>

                <form action="{{ route('pantry.update', $pantryItem->id) }}" method="POST" class="flex flex-col gap-6 relative z-10">
                    @csrf
                    @method('PUT')
                    
                    <div>
                        <label class="block text-slate-300 mb-2 font-medium">Item Name</label>
                        <input type="text" name="item_name" value="{{ old('item_name', $pantryItem->item_name) }}"
                            class="bg-slate-700 border border-slate-600 focus:border-emerald-500 focus:ring-emerald-500 text-white p-3 rounded-lg w-full transition" required>
                        @error('item_name') <span class="text-red-400 text-sm mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <div class="flex gap-4">
                        <div class="w-1/4">
                            <label class="block text-slate-300 mb-2 font-medium">Quantity</label>
                            <input type="number" name="quantity" value="{{ old('quantity', $pantryItem->quantity) }}"
                                class="bg-slate-700 border border-slate-600 focus:border-emerald-500 focus:ring-emerald-500 text-white p-3 rounded-lg w-full transition" step="0.001" min="0" required>
                            @error('quantity') <span class="text-red-400 text-sm mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        <div class="w-1/4">
                            <label class="block text-slate-300 mb-2 font-medium">Unit</label>
                            <select name="unit" class="bg-slate-700 border border-slate-600 focus:border-emerald-500 focus:ring-emerald-500 text-white p-3 rounded-lg w-full transition">
                                @foreach(['pcs','kg','g','L','ml','pack','box','bottle','can','bag'] as $u)
                                    <option value="{{ $u }}" {{ old('unit', $pantryItem->unit ?? 'pcs') === $u ? 'selected' : '' }}>{{ $u }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="w-2/3">
                            <label class="block text-slate-300 mb-2 font-medium">Category</label>
                            <select name="category" class="bg-slate-700 border border-slate-600 focus:border-emerald-500 focus:ring-emerald-500 text-white p-3 rounded-lg w-full transition">
                                <option value="" {{ old('category', $pantryItem->category) == '' ? 'selected' : '' }}>No Category</option>
                                <option value="Dairy" {{ old('category', $pantryItem->category) == 'Dairy' ? 'selected' : '' }}>Dairy</option>
                                <option value="Eggs" {{ old('category', $pantryItem->category) == 'Eggs' ? 'selected' : '' }}>Eggs</option>
                                <option value="Meat" {{ old('category', $pantryItem->category) == 'Meat' ? 'selected' : '' }}>Meat</option>
                                <option value="Rice" {{ old('category', $pantryItem->category) == 'Rice' ? 'selected' : '' }}>Rice</option>
                                <option value="Sandwiches" {{ old('category', $pantryItem->category) == 'Sandwiches' ? 'selected' : '' }}>Sandwiches</option>
                                <option value="Pastry & Desserts" {{ old('category', $pantryItem->category) == 'Pastry & Desserts' ? 'selected' : '' }}>Pastry &amp; Desserts</option>
                                <option value="Canned Goods" {{ old('category', $pantryItem->category) == 'Canned Goods' ? 'selected' : '' }}>Canned Goods</option>
                                <option value="Snacks" {{ old('category', $pantryItem->category) == 'Snacks' ? 'selected' : '' }}>Snacks</option>
                                <option value="Produce" {{ old('category', $pantryItem->category) == 'Produce' ? 'selected' : '' }}>Produce</option>
                                <option value="Dry Goods" {{ old('category', $pantryItem->category) == 'Dry Goods' ? 'selected' : '' }}>Dry Goods</option>
                                <option value="Condiments" {{ old('category', $pantryItem->category) == 'Condiments' ? 'selected' : '' }}>Condiments</option>
                                <option value="Frozen" {{ old('category', $pantryItem->category) == 'Frozen' ? 'selected' : '' }}>Frozen</option>
                                <option value="Other" {{ old('category', $pantryItem->category) == 'Other' ? 'selected' : '' }}>Other</option>
                            </select>
                            @error('category') <span class="text-red-400 text-sm mt-1 block">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div>
                        <label class="block text-slate-300 mb-2 font-medium">Expiry Date</label>
                        <input type="date" name="expiry_date" value="{{ old('expiry_date', $pantryItem->expiry_date ? \Carbon\Carbon::parse($pantryItem->expiry_date)->format('Y-m-d') : '') }}"
                            class="bg-slate-700 border border-slate-600 focus:border-emerald-500 focus:ring-emerald-500 text-white p-3 rounded-lg w-full transition">
                        @if(!$pantryItem->expiry_date)
                            <p class="text-slate-400 text-xs mt-1">⚠️ No expiry date set. Enter it manually or use the camera scan on the Dashboard.</p>
                        @endif
                        @error('expiry_date') <span class="text-red-400 text-sm mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <div class="mt-4 flex justify-end gap-3">
                        <a href="{{ route('dashboard') }}" class="px-6 py-3 rounded-lg font-medium text-slate-300 bg-slate-700 hover:bg-slate-600 transition">Cancel</a>
                        <button type="submit" class="bg-emerald-500 hover:bg-emerald-600 text-white px-8 py-3 rounded-lg font-bold shadow-lg shadow-emerald-500/20 transition">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
