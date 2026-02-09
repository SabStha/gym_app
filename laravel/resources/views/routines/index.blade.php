@extends('layouts.app')

@section('content')
<style>
    .no-scrollbar::-webkit-scrollbar { display: none; }
    .no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
    .glass-nav {
        background: rgba(255, 255, 255, 0.8);
        backdrop-filter: blur(12px);
        -webkit-backdrop-filter: blur(12px);
        border-bottom: 1px solid rgba(255, 255, 255, 0.3);
    }
    .dark .glass-nav {
        background: rgba(17, 24, 39, 0.8);
        border-bottom: 1px solid rgba(255, 255, 255, 0.05);
    }
</style>

<div class="min-h-screen bg-gray-50 dark:bg-gray-900 pb-32">
    
    <!-- Hero Header -->
    <div class="sticky top-0 z-30 glass-nav pt-safe-top">
        <div class="px-6 py-4 flex justify-between items-end">
            <div>
                <p class="text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-1">
                    {{ now()->format('l, M d') }}
                </p>
                <h1 class="text-3xl font-black text-gray-900 dark:text-white tracking-tight leading-none">
                    My Splits
                </h1>
            </div>
            
            <a href="{{ route('routines.create') }}" 
               class="bg-gray-900 dark:bg-white text-white dark:text-gray-900 w-10 h-10 rounded-full flex items-center justify-center shadow-lg hover:scale-105 active:scale-95 transition-all">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
            </a>
        </div>
    </div>

    <div class="px-4 py-6 space-y-8">
        @if(session('success'))
            <div class="bg-emerald-500/10 border border-emerald-500/20 text-emerald-600 dark:text-emerald-400 px-4 py-3 rounded-2xl text-sm font-bold shadow-sm flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                {{ session('success') }}
            </div>
        @endif

        @forelse($routines as $routine)
            <div class="space-y-4">
                <!-- Split Header -->
                <div class="px-2 flex justify-between items-end relative">
                    <div>
                        <div class="flex items-center gap-2 mb-1">
                            @if($routine->is_active)
                                <span class="bg-emerald-500 text-white text-[10px] font-bold px-1.5 py-0.5 rounded uppercase shadow-sm animate-pulse">Active</span>
                            @endif
                            <h2 class="text-2xl font-black text-gray-900 dark:text-white leading-none">{{ $routine->title }}</h2>
                        </div>
                        <p class="text-sm font-medium text-gray-400 dark:text-gray-500 line-clamp-1 max-w-[70vw]">
                            {{ $routine->note ?? $routine->routineDays->count() . ' Workouts' }}
                        </p>
                    </div>

                    <!-- Kebab Menu (Absolute to avoid layout shift) -->
                    <div x-data="{ open: false }" class="relative">
                        <button @click="open = !open" @click.away="open = false" class="text-gray-300 hover:text-gray-600 dark:text-gray-600 dark:hover:text-gray-300 transition-colors p-2 -mr-2">
                            <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24"><path d="M12 8c1.1 0 2-.9 2-2s-.9-2-2-2-2 .9-2 2 .9 2 2 2zm0 2c-1.1 0-2 .9-2 2s.9 2 2 2 2-.9 2-2-.9-2-2-2zm0 6c-1.1 0-2 .9-2 2s.9 2 2 2 2-.9 2-2-.9-2-2-2z"/></svg>
                        </button>
                        <div x-show="open" 
                             class="absolute right-0 mt-1 w-48 bg-white dark:bg-gray-800 rounded-xl shadow-xl border border-gray-100 dark:border-gray-700 py-1 z-20 origin-top-right backdrop-blur-sm"
                             style="display: none;"
                             x-transition.opacity.duration.200ms>
                            <a href="{{ route('routines.edit', $routine) }}" class="block px-4 py-2.5 text-sm font-bold text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700">Edit Details</a>
                            @if(!$routine->is_active)
                                <form action="{{ route('routines.activate', $routine) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="w-full text-left px-4 py-2.5 text-sm font-bold text-emerald-600 hover:bg-emerald-50 dark:hover:bg-emerald-900/20">Set as Active</button>
                                </form>
                            @endif
                            <div class="my-1 border-t border-gray-100 dark:border-gray-700"></div>
                            <form action="{{ route('routines.destroy', $routine) }}" method="POST" onsubmit="return confirm('Delete this split?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="w-full text-left px-4 py-2.5 text-sm font-bold text-red-500 hover:bg-red-50 dark:hover:bg-red-900/20">Delete</button>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Workouts Carousel (Full Bleed) -->
                <div class="-mx-4">
                    <div class="flex overflow-x-auto snap-x snap-mandatory gap-4 px-6 pb-4 no-scrollbar touch-pan-x" style="-webkit-overflow-scrolling: touch;">
                        
                        @foreach($routine->routineDays as $rDay)
                            @php
                                $displayName = $rDay->day_name;
                                if (empty($displayName) || $displayName === 'Day 1') {
                                     $letters = range('A', 'Z');
                                     $index = $loop->index % 26;
                                     $displayName = 'Workout ' . $letters[$index];
                                }
                                $exCount = $rDay->dayExercises->count();
                                $muscles = $rDay->dayExercises->pluck('exercise.muscle_group')->filter()->unique()->take(2)->implode(' · ');
                                if(empty($muscles)) $muscles = 'No exercises yet';
                            @endphp
                            
                            <a href="{{ route('routine-days.show', [$routine, $rDay]) }}" 
                               class="snap-center shrink-0 w-[80vw] md:w-[320px] h-48 bg-white dark:bg-gray-800 rounded-[2rem] p-6 text-left relative overflow-hidden group shadow-[0_8px_30px_rgb(0,0,0,0.04)] hover:shadow-[0_8px_30px_rgb(0,0,0,0.12)] border border-gray-100 dark:border-gray-700 transition-all active:scale-[0.98]">
                                
                                <!-- Hover Gradient -->
                                <div class="absolute inset-0 bg-gradient-to-br from-emerald-500/5 to-transparent opacity-0 group-hover:opacity-100 transition-opacity"></div>
                                
                                <div class="relative z-10 flex flex-col h-full justify-between">
                                    <div>
                                        <div class="flex justify-between items-start mb-2">
                                            <h3 class="text-2xl font-black text-gray-900 dark:text-white leading-none tracking-tight">{{ $displayName }}</h3>
                                            <!-- Arrow Icon -->
                                            <div class="w-8 h-8 rounded-full bg-gray-50 dark:bg-gray-700 flex items-center justify-center text-gray-400 group-hover:bg-emerald-500 group-hover:text-white transition-all">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 7l5 5m0 0l-5 5m5-5H6"></path></svg>
                                            </div>
                                        </div>
                                        <p class="text-sm font-bold text-gray-400 dark:text-gray-500 uppercase tracking-wide">{{ $muscles }}</p>
                                    </div>

                                    <div class="flex items-center gap-2">
                                        <span class="inline-flex items-center justify-center bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 text-xs font-bold px-2.5 py-1 rounded-lg">
                                            {{ $exCount }} Exercises
                                        </span>
                                    </div>
                                </div>
                            </a>
                        @endforeach

                        <!-- Add Workout Card -->
                        <button onclick="openDayPicker('{{ $routine->id }}')" 
                                class="snap-center shrink-0 w-[80vw] md:w-[320px] h-48 rounded-[2rem] border-3 border-dashed border-gray-200 dark:border-gray-700 flex flex-col items-center justify-center gap-3 text-gray-400 hover:text-emerald-500 hover:border-emerald-200 hover:bg-emerald-50/20 active:scale-[0.98] transition-all">
                            <div class="w-12 h-12 rounded-full bg-gray-100 dark:bg-gray-800 flex items-center justify-center text-current shadow-sm group-hover:scale-110 transition-transform">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"></path></svg>
                            </div>
                            <span class="font-bold text-sm">Add Workout</span>
                        </button>
                    </div>
                </div>
            </div>
            
            @if(!$loop->last)
                <div class="h-px bg-gray-100 dark:bg-gray-800 mx-4"></div>
            @endif

        @empty
            <div class="flex flex-col items-center justify-center py-20 text-center px-6">
                <div class="w-24 h-24 bg-gradient-to-br from-emerald-100 to-teal-50 dark:from-emerald-900/20 dark:to-teal-900/20 rounded-[2rem] flex items-center justify-center mb-6 shadow-lg shadow-emerald-500/10 rotate-3">
                    <svg class="w-10 h-10 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
                </div>
                <h3 class="text-2xl font-black text-gray-900 dark:text-white mb-2">No Splits Yet</h3>
                <p class="text-gray-500 dark:text-gray-400 mb-8 max-w-xs mx-auto leading-relaxed">Create your first training split to start tracking your progress.</p>
                <a href="{{ route('routines.create') }}" class="w-full max-w-sm bg-gray-900 dark:bg-white text-white dark:text-gray-900 py-4 rounded-2xl font-bold shadow-xl hover:shadow-2xl hover:scale-[1.02] active:scale-[0.98] transition-all flex items-center justify-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                    Create Split
                </a>
            </div>
        @endforelse
    </div>
</div>

<!-- Day Picker Modal -->
<div id="day-picker-modal" class="fixed inset-0 z-50 hidden flex items-center justify-center px-4 bg-black/60 backdrop-blur-md transition-all duration-300 opacity-0 pointer-events-none">
    <div class="bg-white dark:bg-gray-900 w-full max-w-sm rounded-[2.5rem] shadow-2xl p-6 transform scale-95 transition-all duration-300" id="day-picker-content">
        <h3 class="text-2xl font-black text-gray-900 dark:text-white mb-6 text-center tracking-tight">Add Workout</h3>
        
        <input type="hidden" id="picker-routine-id">
        
        <div class="grid grid-cols-2 gap-3 mb-4">
            @foreach(['Push Day', 'Pull Day', 'Leg Day', 'Upper Body', 'Lower Body', 'Full Body'] as $type)
                <button onclick="pickDay('{{ $type }}')" 
                        class="p-4 rounded-2xl bg-gray-50 dark:bg-gray-800 text-gray-700 dark:text-gray-300 font-bold text-sm hover:bg-emerald-500 hover:text-white dark:hover:bg-emerald-600 transition-all text-center group">
                    {{ $type }}
                </button>
            @endforeach
        </div>
        
        <button onclick="toggleCustomDay()" id="custom-day-btn" class="w-full p-4 rounded-2xl border-2 border-dashed border-gray-200 dark:border-gray-700 text-gray-400 font-bold text-sm hover:border-emerald-500 hover:text-emerald-500 transition-all mb-2">
            Custom name...
        </button>
        
        <div id="custom-input-container" class="hidden">
            <div class="flex gap-2">
                <input type="text" id="custom-day-name" placeholder="e.g. Abs & Cardio" class="flex-1 bg-gray-50 dark:bg-gray-800 border-transparent rounded-2xl px-5 h-14 font-bold text-gray-900 dark:text-white focus:ring-2 focus:ring-emerald-500">
                <button onclick="pickDay(document.getElementById('custom-day-name').value)" class="bg-emerald-500 text-white w-14 h-14 rounded-2xl flex items-center justify-center font-bold shadow-lg hover:scale-105 active:scale-95 transition-all">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 12l5 5L19 7"></path></svg>
                </button>
            </div>
        </div>

        <button onclick="closeDayPicker()" class="mt-6 w-full py-4 text-gray-400 font-bold hover:text-gray-900 dark:hover:text-white transition-colors">Cancel</button>
    </div>
</div>

<script src="//unpkg.com/alpinejs" defer></script>
<script>
    const modal = document.getElementById('day-picker-modal');
    const content = document.getElementById('day-picker-content');
    const customContainer = document.getElementById('custom-input-container');
    const customBtn = document.getElementById('custom-day-btn');
    const routineIdInput = document.getElementById('picker-routine-id');
    const customInput = document.getElementById('custom-day-name');

    function openDayPicker(routineId) {
        routineIdInput.value = routineId;
        modal.classList.remove('hidden', 'pointer-events-none', 'opacity-0');
        modal.classList.add('opacity-100');
        content.classList.remove('scale-95');
        content.classList.add('scale-100');
        
        customContainer.classList.add('hidden');
        customBtn.classList.remove('hidden');
        customInput.value = '';
    }

    function closeDayPicker() {
        modal.classList.remove('opacity-100');
        modal.classList.add('opacity-0', 'pointer-events-none');
        content.classList.remove('scale-100');
        content.classList.add('scale-95');
        setTimeout(() => modal.classList.add('hidden'), 300);
    }

    function toggleCustomDay() {
        customBtn.classList.add('hidden');
        customContainer.classList.remove('hidden');
        customInput.focus();
    }

    async function pickDay(name) {
        if (!name) return;
        const routineId = routineIdInput.value;
        const button = event.currentTarget;
        
        try {
            // Optimistic UI feedback could be added here
            button.classList.add('opacity-50', 'pointer-events-none');
            
            const res = await fetch(`/routines/${routineId}/days`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({
                    day_name: name,
                    order_index: 999 
                })
            });

            const data = await res.json();
            
            if (data.success) {
                window.location.href = data.redirect_url;
            } else {
                alert('Error creating workout');
                button.classList.remove('opacity-50', 'pointer-events-none');
            }
        } catch (e) {
            console.error(e);
            alert('Failed to connect');
            button.classList.remove('opacity-50', 'pointer-events-none');
        }
    }
    
    // Auto-focus logic for custom input
    customInput.addEventListener('keydown', function(e) {
        if (e.key === 'Enter') {
            pickDay(this.value);
        }
    });
</script>
<style>
    .pt-safe-top { padding-top: max(20px, env(safe-area-inset-top)); }
</style>
@endsection
