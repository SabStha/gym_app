@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gray-50 dark:bg-gray-900 pb-32">
    
    <!-- Hero Header -->
    <div class="relative w-full h-[45vh] bg-gray-900 border-b border-gray-100/10 overflow-hidden">
        <!-- Background Image/Gradient -->
        <div class="absolute inset-0 bg-gradient-to-br from-gray-900 via-gray-800 to-black z-0"></div>
        <div class="absolute inset-0 bg-[url('https://images.unsplash.com/photo-1534438327276-14e5300c3a48?q=80&w=1470&auto=format&fit=crop')] bg-cover bg-center opacity-30 mix-blend-overlay z-0"></div>
        <div class="absolute inset-0 bg-gradient-to-t from-gray-900 via-transparent to-transparent z-10"></div>

        <!-- Content -->
        <div class="relative z-20 h-full flex flex-col justify-end px-6 pb-8">
            
            <!-- Navbar Actions -->
            <div class="absolute top-0 left-0 right-0 p-4 pt-safe-top flex justify-between items-center z-30">
                 <a href="{{ route('routines.index') }}" class="w-10 h-10 rounded-full bg-white/10 backdrop-blur-md flex items-center justify-center text-white hover:bg-white/20 transition-colors">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
                 </a>
                 
                 <!-- Kebab Menu -->
                 <div x-data="{ open: false }" class="relative">
                    <button @click="open = !open" @click.away="open = false" class="w-10 h-10 rounded-full bg-white/10 backdrop-blur-md flex items-center justify-center text-white hover:bg-white/20 transition-colors">
                        <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24"><path d="M12 8c1.1 0 2-.9 2-2s-.9-2-2-2-2 .9-2 2 .9 2 2 2zm0 2c-1.1 0-2 .9-2 2s.9 2 2 2 2-.9 2-2-.9-2-2-2zm0 6c-1.1 0-2 .9-2 2s.9 2 2 2 2-.9 2-2-.9-2-2-2z"/></svg>
                    </button>
                    <!-- Dropdown -->
                    <div x-show="open" 
                         class="absolute right-0 mt-2 w-48 bg-white dark:bg-gray-800 rounded-xl shadow-xl py-2 z-50 origin-top-right transform transition-all"
                         x-transition:enter="transition ease-out duration-100"
                         x-transition:enter-start="transform opacity-0 scale-95"
                         x-transition:enter-end="transform opacity-100 scale-100"
                         style="display: none;">
                        <a href="{{ route('routines.edit', $routine) }}" class="block px-4 py-3 text-sm font-bold text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700">Edit Split</a>
                        <form action="{{ route('routines.activate', $routine) }}" method="POST">
                            @csrf
                            <button type="submit" class="w-full text-left px-4 py-3 text-sm font-bold text-emerald-600 hover:bg-emerald-50 dark:hover:bg-emerald-900/20">Set Active</button>
                        </form>
                        <div class="h-px bg-gray-100 dark:bg-gray-700 my-1"></div>
                        <form action="{{ route('routines.destroy', $routine) }}" method="POST" onsubmit="return confirm('Delete split?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="w-full text-left px-4 py-3 text-sm font-bold text-red-500 hover:bg-red-50 dark:hover:bg-red-900/20">Delete</button>
                        </form>
                    </div>
                 </div>
            </div>

            <!-- Title & Badges -->
            <div class="space-y-4 mb-2">
                <div class="flex flex-wrap gap-2">
                    <span class="bg-emerald-500 text-white text-[10px] font-black tracking-wider px-2 py-1 rounded uppercase shadow-lg shadow-emerald-500/20">
                        {{ $routine->routineDays->count() }} Workouts
                    </span>
                    @if($routine->is_active)
                        <span class="bg-white/10 backdrop-blur-md text-white border border-white/20 text-[10px] font-bold tracking-wider px-2 py-1 rounded uppercase">
                            Active Split
                        </span>
                    @endif
                </div>
                
                <h1 class="text-4xl md:text-5xl font-black text-white leading-none tracking-tight drop-shadow-lg">
                    {{ $routine->title }}
                </h1>
                
                @if($routine->note)
                    <p class="text-gray-300 text-sm font-medium line-clamp-2 max-w-md">{{ $routine->note }}</p>
                @endif
            </div>

            <!-- Primary CTA -->
            <div class="pt-4">
                @if($routine->routineDays->count() > 0)
                    <a href="{{ route('routine-days.show', [$routine, $routine->routineDays->first()]) }}" class="inline-flex items-center gap-2 bg-white text-gray-900 px-6 py-3 rounded-full font-black text-sm shadow-xl hover:scale-105 active:scale-95 transition-transform">
                        <svg class="w-5 h-5 text-emerald-600" fill="currentColor" viewBox="0 0 24 24"><path d="M8 5v14l11-7z"/></svg>
                        Start First Workout
                    </a>
                @else
                    <button onclick="document.getElementById('add-day-modal').classList.remove('hidden')" class="inline-flex items-center gap-2 bg-emerald-500 text-white px-6 py-3 rounded-full font-black text-sm shadow-xl hover:scale-105 active:scale-95 transition-transform">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                        Add First Workout
                    </button>
                @endif
            </div>
        </div>
    </div>

    <!-- Workouts Carousel Section -->
    <div class="px-6 py-8">
        <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
            Weekly Schedule
            <span class="text-gray-400 text-xs font-medium bg-gray-100 dark:bg-gray-800 px-2 py-1 rounded-full">{{ $routine->routineDays->count() }} items</span>
        </h2>
        
        <!-- Carousel Container -->
        <div class="-mx-6">
            <div class="flex overflow-x-auto snap-x snap-mandatory gap-4 px-6 pb-8 no-scrollbar" style="-webkit-overflow-scrolling: touch;">
                
                @foreach($routine->routineDays as $index => $day)
                    @php
                        $displayName = $day->day_name;
                        // Naming logic
                        if (empty($displayName) || $displayName === 'Day 1') {
                             $letters = range('A', 'Z');
                             $idx = $index % 26;
                             $displayName = 'Workout ' . $letters[$idx];
                        }
                    @endphp
                    
                    <a href="{{ route('routine-days.show', [$routine, $day]) }}" 
                       class="snap-center shrink-0 w-[85vw] md:w-[340px] h-64 bg-white dark:bg-gray-800 rounded-[2rem] shadow-[0_10px_40px_-5px_rgba(0,0,0,0.08)] border border-gray-100 dark:border-gray-700 overflow-hidden relative group hover:scale-[1.02] transition-transform">
                        
                        <!-- Card Header -->
                        <div class="h-1/2 bg-gray-50 dark:bg-gray-700/50 p-6 flex flex-col justify-between relative">
                            <span class="absolute top-0 right-0 p-4 opacity-5 text-[6rem] font-black leading-none -mt-4 -mr-4 text-gray-900 pointer-events-none">
                                {{ $index + 1 }}
                            </span>
                            <div class="inline-flex">
                                <span class="bg-white dark:bg-gray-600 text-[10px] font-bold uppercase tracking-wider px-2 py-1 rounded text-gray-500 dark:text-gray-300 shadow-sm">
                                    {{ $day->dayExercises->count() }} Exercises
                                </span>
                            </div>
                            <h3 class="text-2xl font-black text-gray-900 dark:text-white relative z-10">{{ $displayName }}</h3>
                        </div>
                        
                        <!-- Card Body -->
                        <div class="p-6 h-1/2 flex flex-col justify-between bg-white dark:bg-gray-800">
                             <!-- Preview exercises (first 2) -->
                             <div class="space-y-1">
                                 @foreach($day->dayExercises->take(2) as $ex)
                                     <div class="flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400">
                                         <div class="w-1.5 h-1.5 rounded-full bg-emerald-500"></div>
                                         <span class="truncate">{{ $ex->exercise->name }}</span>
                                     </div>
                                 @endforeach
                                 @if($day->dayExercises->count() > 2)
                                     <div class="text-xs text-gray-400 pl-3.5">+ {{ $day->dayExercises->count() - 2 }} more</div>
                                 @endif
                             </div>

                             <div class="text-emerald-600 dark:text-emerald-400 font-bold text-sm flex items-center gap-1 group-hover:gap-2 transition-all">
                                 Open Workout <span class="text-lg">&rarr;</span>
                             </div>
                        </div>
                    </a>
                @endforeach

                <!-- Add Workout Card -->
                <button onclick="document.getElementById('add-day-modal').classList.remove('hidden')" 
                        class="snap-center shrink-0 w-[85vw] md:w-[340px] h-64 rounded-[2rem] border-3 border-dashed border-gray-200 dark:border-gray-700 flex flex-col items-center justify-center gap-4 text-gray-400 hover:text-emerald-600 hover:bg-emerald-50/30 hover:border-emerald-200 transition-all group">
                    <div class="w-16 h-16 rounded-full bg-gray-100 dark:bg-gray-800 flex items-center justify-center group-hover:scale-110 transition-transform text-inherit">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                    </div>
                    <span class="font-bold">Add Another Workout</span>
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Add Workout Modal (Simple Form) -->
<div id="add-day-modal" class="fixed inset-0 bg-black/80 z-[60] hidden flex items-end md:items-center justify-center backdrop-blur-sm transition-all"
     onclick="if(event.target === this) this.classList.add('hidden')">
    <div class="bg-white dark:bg-gray-900 w-full md:max-w-md md:rounded-3xl rounded-t-3xl p-6 shadow-2xl transform transition-transform">
        <div class="w-12 h-1.5 bg-gray-200 rounded-full mx-auto mb-6"></div>
        <h3 class="text-2xl font-black text-gray-900 dark:text-white mb-6">New Workout</h3>
        <form action="{{ route('routine-days.store', $routine) }}" method="POST">
            @csrf
            
            <div class="space-y-4 mb-6">
                <div>
                    <label class="block text-sm font-bold text-gray-500 mb-1">Workout Name</label>
                    <input type="text" name="day_name" placeholder="e.g. Legs & Shoulders" class="w-full h-14 bg-gray-50 dark:bg-gray-800 border-transparent rounded-xl px-4 font-bold text-gray-900 dark:text-white focus:ring-2 focus:ring-emerald-500 focus:bg-white transition-colors" required>
                </div>
                <input type="hidden" name="order_index" value="{{ $routine->routineDays->count() }}">
            </div>

            <button type="submit" class="w-full h-14 bg-emerald-600 text-white font-bold rounded-xl shadow-lg shadow-emerald-200 hover:bg-emerald-700 active:scale-[0.98] transition-all">
                Create Workout
            </button>
        </form>
    </div>
</div>

<!-- AlpineJS -->
<script src="//unpkg.com/alpinejs" defer></script>
<style>
    .pt-safe-top { padding-top: max(20px, env(safe-area-inset-top)); }
</style>
@endsection
