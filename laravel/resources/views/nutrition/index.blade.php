@extends('layouts.app')

@section('content')
<div class="pb-24" x-data="nutritionTracker()">
    <!-- 1. Header: Date & Rings -->
    <div class="bg-white dark:bg-gray-800 rounded-b-[2.5rem] shadow-xl overflow-hidden relative">
        <!-- Date Nav -->
        <div class="flex items-center justify-between px-6 py-4 relative z-10">
            <h2 class="text-2xl font-black text-gray-900 dark:text-white">Nutrition</h2>
            <div class="flex items-center gap-2 bg-gray-100 dark:bg-gray-700 rounded-full p-1">
                <button @click="changeDate(-1)" class="w-8 h-8 flex items-center justify-center rounded-full bg-white dark:bg-gray-600 shadow-sm text-gray-600 dark:text-gray-300">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
                </button>
                <span class="text-xs font-bold px-2 text-gray-700 dark:text-gray-200" x-text="formattedDate"></span>
                <button @click="changeDate(1)" class="w-8 h-8 flex items-center justify-center rounded-full bg-white dark:bg-gray-600 shadow-sm text-gray-600 dark:text-gray-300">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                </button>
            </div>
        </div>

        <!-- Rings Container -->
        <div class="px-6 pb-8 pt-2 flex flex-col items-center relative z-10">
            <!-- Calorie Main Ring -->
            <div class="relative w-48 h-48 mb-6">
                <!-- SVG Ring -->
                <svg class="w-full h-full transform -rotate-90">
                    <circle cx="96" cy="96" r="88" stroke="currentColor" stroke-width="12" fill="transparent" class="text-gray-100 dark:text-gray-700" />
                    <circle cx="96" cy="96" r="88" stroke="currentColor" stroke-width="12" fill="transparent" class="text-emerald-500 transition-all duration-1000 ease-out"
                            :stroke-dasharray="circumference"
                            :stroke-dashoffset="circumference - (pct('calories') / 100 * circumference)"
                            stroke-linecap="round" />
                </svg>
                <div class="absolute inset-0 flex flex-col items-center justify-center pt-2">
                    <span class="text-4xl font-black text-gray-900 dark:text-white" x-text="Math.round(totals.calories)"></span>
                    <span class="text-xs font-bold text-gray-400 uppercase tracking-widest">Kcal</span>
                    <span class="text-[10px] text-gray-400 mt-1">Goal: <span x-text="targets.calories"></span></span>
                </div>
            </div>

            <!-- Macro Bubbles -->
            <div class="grid grid-cols-3 gap-4 w-full max-w-sm">
                <!-- Protein -->
                <div class="bg-gray-50 dark:bg-gray-700/50 rounded-2xl p-3 flex flex-col items-center relative overflow-hidden">
                    <div class="h-1 absolute bottom-0 left-0 bg-blue-500 transition-all duration-1000" :style="`width: ${pct('protein')}%`"></div>
                    <span class="text-xs font-bold text-gray-400 mb-1">Protein</span>
                    <span class="text-xl font-black text-gray-900 dark:text-white"><span x-text="Math.round(totals.protein)"></span>g</span>
                    <span class="text-[9px] text-gray-400">/ <span x-text="targets.protein"></span>g</span>
                </div>
                <!-- Carbs -->
                <div class="bg-gray-50 dark:bg-gray-700/50 rounded-2xl p-3 flex flex-col items-center relative overflow-hidden">
                    <div class="h-1 absolute bottom-0 left-0 bg-amber-500 transition-all duration-1000" :style="`width: ${pct('carbs')}%`"></div>
                    <span class="text-xs font-bold text-gray-400 mb-1">Carbs</span>
                    <span class="text-xl font-black text-gray-900 dark:text-white"><span x-text="Math.round(totals.carbs)"></span>g</span>
                    <span class="text-[9px] text-gray-400">/ <span x-text="targets.carbs"></span>g</span>
                </div>
                <!-- Fat -->
                <div class="bg-gray-50 dark:bg-gray-700/50 rounded-2xl p-3 flex flex-col items-center relative overflow-hidden">
                    <div class="h-1 absolute bottom-0 left-0 bg-rose-500 transition-all duration-1000" :style="`width: ${pct('fat')}%`"></div>
                    <span class="text-xs font-bold text-gray-400 mb-1">Fat</span>
                    <span class="text-xl font-black text-gray-900 dark:text-white"><span x-text="Math.round(totals.fat)"></span>g</span>
                    <span class="text-[9px] text-gray-400">/ <span x-text="targets.fat"></span>g</span>
                </div>
            </div>
        </div>
    </div>

    <!-- 2. Daily Log -->
    <div class="px-4 mt-6">
        <h3 class="text-sm font-bold text-gray-400 uppercase tracking-wider mb-4 ml-2">Today's Log</h3>
        
        <div class="space-y-3">
            <template x-for="entry in entries" :key="entry.id">
                <div class="bg-white dark:bg-gray-800 rounded-2xl p-4 flex items-center justify-between shadow-sm border border-gray-100 dark:border-gray-700">
                    <div class="flex items-center gap-3">
                         <div class="w-10 h-10 rounded-xl bg-gray-100 dark:bg-gray-700 flex items-center justify-center text-xl">
                            🍎
                         </div>
                         <div>
                             <h4 class="font-bold text-gray-900 dark:text-white" x-text="entry.food.name"></h4>
                             <p class="text-xs text-gray-500"><span x-text="entry.grams"></span>g • <span x-text="entry.calories"></span> kcal</p>
                         </div>
                    </div>
                    
                    <button @click="deleteEntry(entry.id)" class="text-gray-400 hover:text-red-500 p-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                    </button>
                </div>
            </template>
            
            <div x-show="entries.length === 0" class="text-center py-12 text-gray-400">
                No food logged yet.
            </div>
        </div>
    </div>

    <!-- 3. Floating Action Button -->
    <button @click="openModal()" 
            class="fixed bottom-24 right-4 w-14 h-14 bg-emerald-500 text-white rounded-full shadow-xl shadow-emerald-500/30 flex items-center justify-center hover:scale-105 transition-transform">
        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
    </button>

    <!-- 4. Add Food Modal (Bottom Sheet style) -->
    <div x-show="modalOpen" class="fixed inset-0 z-50 flex items-end sm:items-center justify-center" x-cloak>
        <div class="absolute inset-0 bg-black/60 backdrop-blur-sm" @click="closeModal()"></div>
        
        <div class="relative w-full max-w-md bg-white dark:bg-gray-900 rounded-t-[2rem] sm:rounded-3xl shadow-2xl h-[85vh] flex flex-col"
             x-transition:enter="transition transform ease-out duration-300"
             x-transition:enter-start="translate-y-full"
             x-transition:enter-end="translate-y-0"
             x-transition:leave="transition transform ease-in duration-200"
             x-transition:leave-start="translate-y-0"
             x-transition:leave-end="translate-y-full">
             
             <!-- Modal Header -->
             <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-800 flex items-center justify-between">
                 <h3 class="text-lg font-black text-gray-900 dark:text-white">Add Food</h3>
                 <button @click="closeModal()" class="p-2 bg-gray-100 dark:bg-gray-800 rounded-full text-gray-500">
                     <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                 </button>
             </div>

             <!-- Search & Tabs -->
             <div class="px-6 py-2">
                 <input type="text" x-model="searchQuery" @input.debounce.300ms="searchFood()" placeholder="Search foods (e.g. Chicken breast)" class="w-full bg-gray-100 dark:bg-gray-800 rounded-xl border-none p-3 font-bold mb-4 focus:ring-2 focus:ring-emerald-500">
                 
                 <div class="flex gap-4 border-b border-gray-100 dark:border-gray-800 pb-2">
                     <button @click="activeTab = 'search'" :class="activeTab === 'search' ? 'text-emerald-500 border-b-2 border-emerald-500' : 'text-gray-400'" class="pb-2 font-bold text-sm transition-colors">Search</button>
                     <button @click="getRecents(); activeTab = 'recents'" :class="activeTab === 'recents' ? 'text-emerald-500 border-b-2 border-emerald-500' : 'text-gray-400'" class="pb-2 font-bold text-sm transition-colors">Recents</button>
                 </div>
             </div>

             <!-- Results List -->
             <div class="flex-1 overflow-y-auto p-6 space-y-3">
                 <template x-for="food in (activeTab === 'search' ? searchResults : recentResults)" :key="food.id">
                     <button @click="selectFood(food)" class="w-full text-left bg-gray-50 dark:bg-gray-800/50 p-4 rounded-xl flex justify-between items-center hover:bg-emerald-50 dark:hover:bg-gray-800 transition-colors">
                         <div>
                             <h4 class="font-bold text-gray-900 dark:text-white" x-text="food.name"></h4>
                             <p class="text-xs text-gray-500"><span x-text="food.calories"></span> kcal • <span x-text="food.protein"></span>P • <span x-text="food.serving_size"></span>g serving</p>
                         </div>
                         <div class="w-8 h-8 rounded-full bg-white dark:bg-gray-700 flex items-center justify-center shadow-sm text-emerald-500">
                             +
                         </div>
                     </button>
                 </template>
                 
                 <div x-show="activeTab === 'search' && searchResults.length === 0 && searchQuery" class="text-center py-8">
                     <p class="text-gray-400 mb-4">No foods found.</p>
                     <button @click="createMode = true" class="text-emerald-500 font-bold hover:underline">Create Custom Food</button>
                 </div>
             </div>
             
             <!-- Create Food Form (Overlay) -->
             <div x-show="createMode" class="absolute inset-0 bg-white dark:bg-gray-900 z-20 flex flex-col" x-transition>
                 <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-800 flex items-center gap-3">
                     <button @click="createMode = false" class="text-gray-400"><svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg></button>
                     <h3 class="text-lg font-black">Create Food</h3>
                 </div>
                 <div class="p-6 space-y-4 overflow-y-auto">
                     <div><label class="block text-xs font-bold text-gray-400 uppercase mb-1">Name</label><input type="text" x-model="newFood.name" class="w-full rounded-xl bg-gray-100 dark:bg-gray-800 border-none"></div>
                     <div><label class="block text-xs font-bold text-gray-400 uppercase mb-1">Brand</label><input type="text" x-model="newFood.brand" class="w-full rounded-xl bg-gray-100 dark:bg-gray-800 border-none"></div>
                     <div class="grid grid-cols-2 gap-4">
                         <div><label class="block text-xs font-bold text-gray-400 uppercase mb-1">Cal (per 100g)</label><input type="number" x-model="newFood.calories" class="w-full rounded-xl bg-gray-100 dark:bg-gray-800 border-none"></div>
                         <div><label class="block text-xs font-bold text-gray-400 uppercase mb-1">Protein</label><input type="number" step="0.1" x-model="newFood.protein" class="w-full rounded-xl bg-gray-100 dark:bg-gray-800 border-none"></div>
                         <div><label class="block text-xs font-bold text-gray-400 uppercase mb-1">Carbs</label><input type="number" step="0.1" x-model="newFood.carbs" class="w-full rounded-xl bg-gray-100 dark:bg-gray-800 border-none"></div>
                         <div><label class="block text-xs font-bold text-gray-400 uppercase mb-1">Fat</label><input type="number" step="0.1" x-model="newFood.fat" class="w-full rounded-xl bg-gray-100 dark:bg-gray-800 border-none"></div>
                     </div>
                     <button @click="submitNewFood()" class="w-full py-4 bg-emerald-500 text-white font-bold rounded-2xl shadow-lg mt-4">Save Food</button>
                 </div>
             </div>

             <!-- Grams Input (Overlay) -->
             <div x-show="selectedFood" class="absolute inset-0 bg-white dark:bg-gray-900 z-20 flex flex-col justify-between p-6" x-transition>
                  <div class="text-center mt-8">
                      <h3 class="text-2xl font-black text-gray-900 dark:text-white" x-text="selectedFood?.name"></h3>
                      <p class="text-gray-500" x-text="selectedFood?.brand"></p>
                  </div>
                  
                  <div class="flex-1 flex flex-col items-center justify-center gap-6">
                      <div class="text-center">
                          <input type="number" x-model="grams" x-ref="gramsInput" class="text-6xl font-black bg-transparent border-none text-center text-gray-900 dark:text-white w-full focus:ring-0 p-0" placeholder="0">
                          <span class="text-xl font-bold text-gray-400">grams</span>
                      </div>
                      
                      <!-- Quick Stats Preview -->
                      <div class="flex gap-6 opacity-60">
                          <div class="text-center"><span class="block font-bold text-gray-900 dark:text-white" x-text="Math.round(selectedFood?.calories * (grams/100)) || 0"></span><span class="text-xs">kcal</span></div>
                          <div class="text-center"><span class="block font-bold text-gray-900 dark:text-white" x-text="(selectedFood?.protein * (grams/100)).toFixed(1) || 0"></span><span class="text-xs">P</span></div>
                          <div class="text-center"><span class="block font-bold text-gray-900 dark:text-white" x-text="(selectedFood?.carbs * (grams/100)).toFixed(1) || 0"></span><span class="text-xs">C</span></div>
                          <div class="text-center"><span class="block font-bold text-gray-900 dark:text-white" x-text="(selectedFood?.fat * (grams/100)).toFixed(1) || 0"></span><span class="text-xs">F</span></div>
                      </div>
                  </div>
                  
                  <div class="grid grid-cols-2 gap-4">
                      <button @click="selectedFood = null" class="py-4 bg-gray-100 dark:bg-gray-800 font-bold rounded-2xl text-gray-500">Cancel</button>
                      <button @click="submitEntry()" class="py-4 bg-emerald-500 text-white font-bold rounded-2xl shadow-lg">Log Food</button>
                  </div>
             </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('nutritionTracker', () => ({
            date: '{{ $date->format('Y-m-d') }}',
            formattedDate: '{{ $date->format('M d, Y') }}',
            entries: @json($entries),
            totals: @json($totals),
            targets: @json($targets),
            circumference: 2 * Math.PI * 88,
            
            modalOpen: false,
            searchQuery: '',
            searchResults: [],
            recentResults: [],
            activeTab: 'search',
            
            selectedFood: null,
            grams: 100,
            
            createMode: false,
            newFood: { name: '', brand: '', calories: '', protein: '', carbs: '', fat: '', serving_size: 100 },
            
            pct(macro) {
                const max = this.targets[macro] || 1;
                const val = this.totals[macro] || 0;
                return Math.min((val / max) * 100, 100);
            },
            
            changeDate(offset) {
                const d = new Date(this.date);
                d.setDate(d.getDate() + offset);
                window.location.href = '?date=' + d.toISOString().split('T')[0];
            },
            
            deleteEntry(id) {
                if(!confirm('Delete this entry?')) return;
                fetch(`/food-entries/${id}`, {
                    method: 'DELETE',
                    headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
                }).then(() => window.location.reload());
            },
            
            openModal() {
                this.modalOpen = true;
                this.$nextTick(() => { 
                   if(this.activeTab === 'recents') this.getRecents();
                });
            },
            
            closeModal() {
                this.modalOpen = false;
                this.selectedFood = null;
                this.createMode = false;
            },
            
            async searchFood() {
                if(this.searchQuery.length < 2) return;
                const res = await fetch(`/foods/search?q=${this.searchQuery}`);
                this.searchResults = await res.json();
            },
            
            async getRecents() {
                const res = await fetch(`/foods/recents`);
                this.recentResults = await res.json();
            },
            
            selectFood(food) {
                this.selectedFood = food;
                this.grams = food.serving_size || 100;
                this.$nextTick(() => this.$refs.gramsInput.focus());
            },
            
            async submitEntry() {
                const res = await fetch('/food-entries', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({
                        food_id: this.selectedFood.id,
                        grams: this.grams,
                        date: this.date
                    })
                });
                
                if(res.ok) window.location.reload();
            },
            
            async submitNewFood() {
                const res = await fetch('/foods', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify(this.newFood)
                });
                
                if(res.ok) {
                    const food = await res.json();
                    this.createMode = false;
                    this.selectFood(food);
                }
            }
        }));
    });
</script>
@endsection
