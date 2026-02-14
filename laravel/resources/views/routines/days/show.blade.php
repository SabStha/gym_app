@extends('layouts.app')

@section('content')
<style>
    [x-cloak] { display: none !important; }
    .no-scrollbar::-webkit-scrollbar { display: none; }
    .no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
    
    /* Premium Glass Header */
    .glass-header {
        background: rgba(255, 255, 255, 0.85);
        backdrop-filter: blur(12px);
        -webkit-backdrop-filter: blur(12px);
        border-bottom: 1px solid rgba(0,0,0,0.05);
    }
    .dark .glass-header {
        background: rgba(17, 24, 39, 0.85);
        border-bottom: 1px solid rgba(255,255,255,0.05);
    }
</style>

<!-- Main Layout: locked to 100dvh, no window scrolling -->
<div class="fixed inset-0 z-40 bg-gray-50 dark:bg-gray-900 flex flex-col pb-[calc(9rem+env(safe-area-inset-bottom))]">
    
    <!-- 1. Header Section (Compressed) -->
    <div class="glass-header z-30 pt-safe-top shrink-0" 
         x-data="{ 
            editing: false, 
            name: '{{ $day->day_name }}',
            async saveName() {
                if (this.name === '{{ $day->day_name }}' || !this.name.trim()) {
                    this.editing = false;
                    return;
                }
                try {
                    const res = await fetch('{{ route('routine-days.update', $day) }}', {
                        method: 'PUT',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name='+'\u0022csrf-token\u0022'+']').getAttribute('content')
                        },
                        body: JSON.stringify({ day_name: this.name })
                    });
                    if (!res.ok) throw new Error();
                    this.editing = false; 
                } catch (e) {
                    this.name = '{{ $day->day_name }}';
                    this.editing = false;
                }
            }
         }">
        <!-- Top Bar: Back + Routine Title + Edit Toggle -->
        <div class="px-4 py-2 flex items-center justify-between">
            <a href="{{ route('routines.index') }}" class="flex items-center text-xs font-bold text-gray-400 hover:text-emerald-600 transition-colors py-1">
                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7"></path></svg>
                Back
            </a>
            
            <span class="text-[10px] font-bold tracking-widest uppercase text-gray-400">{{ $routine->title }}</span>

            <!-- STABLE EDIT BUTTON (Internal x-data scope) -->
            <button @click="editing = true; $nextTick(() => $refs.input.focus())" 
                    class="p-2 -mr-2 text-gray-300 hover:text-emerald-600 transition-colors"
                    aria-label="Rename Workout">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
            </button>
        </div>

        <!-- Inline Title & Chips Row -->
        <div class="px-4 pb-3 flex flex-col gap-2">
            <!-- Editable Workout Title -->
            <div class="relative h-9 flex items-center">
                
                <!-- Display Text -->
                <h1 x-show="!editing" 
                    x-text="name"
                    class="text-2xl font-black text-gray-900 dark:text-white truncate">
                </h1>

                <!-- Edit Input & Save Button -->
                <div x-show="editing" class="flex items-center w-full gap-2" x-cloak>
                    <input x-ref="input" 
                           x-model="name" 
                           @keydown.enter="$refs.input.blur()"
                           class="text-2xl font-black text-gray-900 dark:text-white bg-transparent border-0 border-b-2 border-emerald-500 focus:ring-0 p-0 w-full rounded-none leading-none placeholder-gray-400"
                           placeholder="Workout Name">
                    
                    <button @click="saveName()" 
                            class="p-2 bg-emerald-500 hover:bg-emerald-600 text-white rounded-lg shadow-md transition-all shrink-0 flex items-center justify-center">
                        <span class="text-xs font-bold mr-1">SAVE</span>
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                    </button>
                </div>
            </div>

            <!-- Chips Row -->
            <div class="flex overflow-x-auto no-scrollbar gap-2 -mx-4 px-4">
                @foreach($routine->routineDays as $d)
                    <a href="{{ route('routine-days.show', [$routine, $d]) }}" 
                       class="flex-shrink-0 px-3 py-1.5 rounded-full text-[10px] font-bold border transition-all
                              {{ $d->id === $day->id 
                                 ? 'bg-gray-900 text-white border-gray-900 dark:bg-white dark:text-gray-900 dark:border-white shadow-md' 
                                 : 'bg-white text-gray-500 border-gray-200 dark:bg-gray-800 dark:text-gray-400 dark:border-gray-700' }}">
                        {{ $d->day_name }}
                    </a>
                @endforeach
                <button onclick="document.getElementById('add-day-modal').classList.remove('hidden')" class="flex-shrink-0 w-7 h-7 rounded-full bg-emerald-100 text-emerald-600 flex items-center justify-center">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                </button>
            </div>
        </div>
    </div>

    <!-- 2. Hero Carousel -->
    <div class="flex-1 w-full relative overflow-hidden flex flex-col justify-center">
        @if($day->dayExercises->count() > 0)
            <div class="flex items-center overflow-x-auto snap-x snap-mandatory px-6 gap-4 no-scrollbar h-full py-4"
                 style="-webkit-overflow-scrolling: touch;">
                
                @foreach($day->dayExercises->sortBy('order_index') as $exercise)
                    <!-- Hero Card -->
                    <div class="snap-center flex-shrink-0 w-[80vw] max-w-sm h-full max-h-[75vh] bg-white dark:bg-gray-800 rounded-[2.5rem] shadow-2xl relative flex flex-col overflow-hidden border border-gray-100 dark:border-gray-700 group">
                        
                        <!-- Card Header -->
                        <div class="p-6 pb-2 shrink-0 flex justify-between items-start z-10">
                            <div>
                                <span class="bg-emerald-100 dark:bg-emerald-500/20 text-emerald-700 dark:text-emerald-300 text-[10px] font-black px-2 py-1 rounded-lg uppercase tracking-wider">
                                    {{ $exercise->exercise->muscle_group }}
                                </span>
                                <h3 class="text-2xl font-black text-gray-900 dark:text-white mt-2 leading-tight">
                                    {{ $exercise->exercise->name }}
                                </h3>
                            </div>
                            
                            <!-- Kebab Menu (Custom Dropdown) -->
                            <div x-data="{ open: false }" class="relative">
                                <button @click="open = !open" 
                                        @click.away="open = false" 
                                        class="p-2 -mr-2 text-gray-400 hover:text-gray-900 dark:hover:text-white transition-colors">
                                    <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24"><path d="M12 8c1.1 0 2-.9 2-2s-.9-2-2-2-2 .9-2 2 .9 2 2 2zm0 2c-1.1 0-2 .9-2 2s.9 2 2 2 2-.9 2-2-.9-2-2-2zm0 6c-1.1 0-2 .9-2 2s.9 2 2 2 2-.9 2-2-.9-2-2-2z"/></svg>
                                </button>
                                
                                <!-- Dropdown Menu -->
                                <div x-show="open" 
                                     x-cloak
                                     class="absolute right-0 mt-1 w-32 bg-white dark:bg-gray-700 rounded-xl shadow-xl border border-gray-100 dark:border-gray-600 py-1 z-50 text-left">
                                    <form action="{{ route('day-exercises.destroy', $exercise) }}" method="POST" onsubmit="return confirm('Remove exercise?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="w-full text-left px-4 py-3 text-xs text-red-500 font-bold hover:bg-gray-50 dark:hover:bg-gray-600">
                                            Remove Exercise
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>

                        <!-- Hero Icon/Illustration (Centered) -->
                        <div class="flex-1 flex items-center justify-center p-6 relative overflow-hidden">
                            @if($exercise->exercise->image_url)
                                <img src="{{ $exercise->exercise->image_url }}" 
                                     class="absolute inset-0 w-full h-full object-cover opacity-90"
                                     alt="{{ $exercise->exercise->name }}"
                                     onerror="this.style.display='none'">
                                <div class="absolute inset-0 bg-gradient-to-t from-gray-50 via-transparent to-transparent dark:from-gray-800 dark:to-transparent"></div>
                            @else
                                <!-- Background Pattern/Blob -->
                                <div class="absolute inset-0 flex items-center justify-center opacity-10 dark:opacity-5 pointer-events-none">
                                    <div class="w-48 h-48 bg-emerald-500 rounded-full blur-3xl"></div>
                                </div>
                                
                                <!-- Icon -->
                                <div class="w-32 h-32 rounded-full bg-gradient-to-br from-gray-50 to-gray-100 dark:from-gray-700 dark:to-gray-600 flex items-center justify-center shadow-inner relative z-10">
                                    <svg class="w-16 h-16 text-gray-300 dark:text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                                </div>
                            @endif
                        </div>

                        <!-- Bottom Stats Tiles -->
                        <div class="p-4 bg-gray-50 dark:bg-gray-900/50 mt-auto">
                            <div class="grid grid-cols-2 gap-3">
                                <div class="bg-white dark:bg-gray-800 p-4 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 flex flex-col items-center justify-center">
                                    <span class="text-[10px] font-bold text-gray-400 uppercase tracking-wider">Sets</span>
                                    <span class="text-3xl font-black text-gray-900 dark:text-white">{{ $exercise->target_sets }}</span>
                                </div>
                                <div class="bg-white dark:bg-gray-800 p-4 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 flex flex-col items-center justify-center">
                                    <span class="text-[10px] font-bold text-gray-400 uppercase tracking-wider">Reps</span>
                                    <span class="text-3xl font-black text-gray-900 dark:text-white">{{ $exercise->rep_min }}-{{ $exercise->rep_max }}</span>
                                </div>
                            </div>
                        </div>

                    </div>
                @endforeach

                <!-- Add Card -->
                <button onclick="openAddExercise()" 
                        class="snap-center flex-shrink-0 w-[80vw] max-w-sm h-full max-h-[75vh] bg-white/50 dark:bg-gray-800/30 rounded-[2.5rem] border-2 border-dashed border-gray-300 dark:border-gray-700 flex flex-col items-center justify-center gap-4 text-gray-400 hover:text-emerald-500 hover:border-emerald-400 hover:bg-emerald-50/30 transition-all">
                    <div class="w-16 h-16 rounded-full bg-white dark:bg-gray-800 flex items-center justify-center shadow-sm">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"></path></svg>
                    </div>
                    <span class="font-bold text-sm">Add Next Exercise</span>
                </button>
            </div>
        @else
            <!-- Empty State -->
            <div class="flex flex-col items-center justify-center text-center px-8 z-10">
                <div class="w-24 h-24 bg-gray-100 dark:bg-gray-800 rounded-full flex items-center justify-center mb-6 animate-pulse">
                    <svg class="w-10 h-10 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
                </div>
                <h3 class="text-xl font-black text-gray-900 dark:text-white mb-2">Ready to Build?</h3>
                <p class="text-sm text-gray-500 max-w-xs mb-8">This workout is empty. Tap below to start adding exercises.</p>
                <button onclick="openAddExercise()" class="bg-emerald-600 text-white px-8 py-4 rounded-2xl font-bold shadow-xl shadow-emerald-500/20 hover:scale-105 transition-transform">
                    Start Adding Exercises
                </button>
            </div>
        @endif
        
    <!-- Bottom Actions: Start Button ONLY -->
    <div class="fixed bottom-[calc(6rem+env(safe-area-inset-bottom))] inset-x-0 px-6 z-40 flex items-center justify-center pointer-events-none">
        
        <!-- Start Workout Button (Full Width Hero) -->
        <form action="{{ route('workouts.start') }}" method="POST" class="w-full max-w-sm pointer-events-auto">
            @csrf
            <input type="hidden" name="routine_day_id" value="{{ $day->id }}">
            <button type="submit" class="group relative w-full h-16 bg-gray-900 dark:bg-white text-white dark:text-gray-900 rounded-3xl shadow-2xl shadow-gray-900/20 flex items-center justify-between px-2 overflow-hidden transition-all active:scale-95 hover:shadow-3xl">
                <!-- Button Glow -->
                <div class="absolute inset-0 bg-gradient-to-r from-emerald-500/0 via-white/10 to-emerald-500/0 translate-x-[-100%] group-hover:translate-x-[100%] transition-transform duration-1000"></div>
                
                <!-- Icon Circle -->
                <div class="w-12 h-12 bg-white/20 dark:bg-black/10 rounded-full flex items-center justify-center ml-1">
                    <svg class="w-6 h-6 ml-1" fill="currentColor" viewBox="0 0 24 24"><path d="M8 5v14l11-7z"/></svg>
                </div>
                
                <!-- Text -->
                <span class="text-lg font-black tracking-widest uppercase mr-auto ml-4">Start Workout</span>
                
                <!-- Chevron -->
                <div class="mr-4 opacity-50 group-hover:translate-x-1 transition-transform">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"></path></svg>
                </div>
            </button>
        </form>
    </div>
</div>

<!-- Backdrop -->
<div id="sheet-backdrop" class="fixed inset-0 bg-black/60 z-50 hidden transition-opacity opacity-0 backdrop-blur-sm" onclick="closeAddExercise()"></div>

<!-- Bottom Sheet -->
<div id="bottom-sheet" class="fixed inset-x-0 bottom-0 bg-white dark:bg-gray-900 rounded-t-[2rem] shadow-[0_-10px_40px_rgba(0,0,0,0.2)] z-[60] transition-transform duration-300 transform translate-y-full flex flex-col max-h-[85vh]">
    <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-800 flex items-center justify-between sticky top-0 bg-white dark:bg-gray-900 rounded-t-[2rem] z-10">
        <div class="w-8"></div>
        <div class="w-12 h-1 bg-gray-200 dark:bg-gray-700 rounded-full"></div>
        <button onclick="closeAddExercise()" class="w-8 h-8 flex items-center justify-center text-gray-400 bg-gray-50 dark:bg-gray-800 rounded-full hover:bg-gray-100">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
        </button>
    </div>
    <div class="flex-1 overflow-y-auto pb-safe">
         @include('partials.exercise-picker', ['day' => $day])
    </div>
</div>

<!-- Add Day Modal -->
<div id="add-day-modal" class="fixed inset-0 bg-black/60 z-[70] hidden flex items-center justify-center px-4 backdrop-blur-sm">
    <div class="bg-white dark:bg-gray-900 w-full max-w-xs rounded-[2rem] p-6 shadow-2xl">
        <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-4">New Workout Day</h3>
        <form action="{{ route('routine-days.store', $routine) }}" method="POST">
            @csrf
            <div class="space-y-3 mb-6">
                <div>
                    <label class="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">Workout Name</label>
                    <input type="text" name="day_name" placeholder="e.g. Shoulders & Arms" class="w-full bg-gray-50 dark:bg-gray-800 border-transparent focus:bg-white dark:focus:bg-gray-700 focus:ring-2 focus:ring-emerald-500 rounded-xl h-12 px-4 font-bold text-base dark:text-white transition-all" required autofocus>
                </div>
                <input type="hidden" name="order_index" value="{{ $routine->routineDays->count() }}">
            </div>
            <div class="flex gap-2">
                <button type="button" onclick="document.getElementById('add-day-modal').classList.add('hidden')" class="flex-1 py-3 text-gray-500 font-bold hover:bg-gray-50 dark:hover:bg-gray-800 rounded-xl transition-colors text-sm">Cancel</button>
                <button type="submit" class="flex-1 py-3 bg-emerald-500 text-white font-bold rounded-xl shadow-lg shadow-emerald-500/30 transition-all text-sm">Create</button>
            </div>
        </form>
    </div>
</div>

<script src="//unpkg.com/alpinejs" defer></script>
<script>
    const sheet = document.getElementById('bottom-sheet');
    const backdrop = document.getElementById('sheet-backdrop');
    
    function openAddExercise() {
        backdrop.classList.remove('hidden');
        requestAnimationFrame(() => backdrop.classList.remove('opacity-0'));
        sheet.classList.remove('translate-y-full');
    }

    function closeAddExercise() {
        sheet.classList.add('translate-y-full');
        backdrop.classList.add('opacity-0');
        setTimeout(() => backdrop.classList.add('hidden'), 300);
    }
</script>
<style>
    .pt-safe-top { padding-top: max(20px, env(safe-area-inset-top)); }
</style>
@endsection
