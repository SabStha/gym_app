@inject('suggestionService', 'App\Services\ProgressionSuggestionService')
<x-app-layout>
    {{-- Prepare Data for Alpine --}}
    @php
        $workoutData = $workout->workoutExercises->values()->map(function($we) use ($suggestionService, $workout) {
            $existingSets = $we->workoutSets;
            
            // Determine target sets (default 3)
            $targetSets = 3;
            if($workout->routineDay) {
                $original = $workout->routineDay->dayExercises->firstWhere('exercise_id', $we->exercise_id);
                if($original) $targetSets = $original->target_sets;
            }
            $rowCount = max($targetSets, count($existingSets), 3);

            $setsData = [];
            for($i = 0; $i < $rowCount; $i++) {
                $setRecord = $existingSets[$i] ?? null;
                $setNum = $i + 1;
                
                // Suggestion
                $sugg = null;
                if(!$setRecord) {
                   $suggRaw = $suggestionService->getSuggestion($we, $setNum);
                   if($suggRaw && $suggRaw['weight']) $sugg = $suggRaw;
                }

                $setsData[] = [
                    'set_num' => $setNum,
                    'is_saved' => !is_null($setRecord),
                    'weight' => $setRecord ? $setRecord->weight_kg : 0,
                    'reps' => $setRecord ? $setRecord->reps : 0,
                    'suggestion' => $sugg,
                    'id' => $we->id . '-' . $setNum // unique key
                ];
            }

            return [
                'id' => $we->id,
                'name' => $we->exercise->name,
                'image_url' => $we->exercise->image_url,
                'status' => $we->status,
                'sets' => $setsData,
            ];
        });
    @endphp

    <div x-data="workoutSession(@js($workoutData), @js($history))" 
         class="fixed inset-0 bg-gray-900 text-white z-50 flex flex-col overflow-hidden touch-manipulation select-none">
        
        <!-- Transition Overlay -->
        <div x-show="showTransition"
             style="display: none;"
             x-transition:enter="transition ease-out duration-500"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100"
             x-transition:leave="transition ease-in duration-300"
             x-transition:leave-start="opacity-100 scale-100"
             x-transition:leave-end="opacity-0 scale-95"
             class="fixed inset-0 z-[60] bg-gray-900 flex flex-col items-center justify-center text-center p-6">
            
            <div class="mb-8 relative">
                <div class="absolute inset-0 bg-emerald-500/20 blur-xl rounded-full animate-pulse"></div>
                <h3 class="relative text-emerald-400 font-bold tracking-widest uppercase text-sm mb-2">Up Next</h3>
                <h1 class="relative text-4xl md:text-5xl font-black text-white leading-tight" x-text="nextExerciseName"></h1>
            </div>
            
            <div class="flex items-center gap-3 opacity-60">
                <span class="text-sm font-bold tracking-widest uppercase">Exercise</span>
                <span class="text-2xl font-black" x-text="(currentExerciseIndex + 2) + '/' + exercises.length"></span>
            </div>
        </div>

        <!-- Background Image Layer -->
        <div class="absolute inset-0 z-0 bg-black">
            <template x-for="(ex, index) in exercises" :key="ex.id">
                <div x-show="currentExerciseIndex === index"
                     x-transition:enter="transition ease-out duration-700"
                     x-transition:enter-start="opacity-0"
                     x-transition:enter-end="opacity-100"
                     x-transition:leave="transition ease-in duration-300"
                     x-transition:leave-start="opacity-100"
                     x-transition:leave-end="opacity-0"
                     class="absolute inset-0">
                    <img :src="ex.image_url" class="w-full h-full object-cover opacity-25 blur-sm">
                    <!-- Standard Fade -->
                    <div class="absolute inset-0 bg-gradient-to-t from-gray-900 via-gray-900/50 to-transparent"></div>
                    <!-- Vignette -->
                    <div class="absolute inset-0 bg-[radial-gradient(circle_at_center,_var(--tw-gradient-stops))] from-transparent via-gray-900/20 to-gray-900/80"></div>
                </div>
            </template>
        </div>

        <!-- Header -->
        <header class="relative z-40 px-6 pt-safe-top pb-2 flex justify-between items-end min-h-[80px]">
            <div class="flex-1 min-w-0 mr-4">
                <div class="flex items-center gap-2 mb-1">
                    <p class="text-xs font-bold text-gray-400 uppercase tracking-widest">
                        Exercise <span x-text="currentExerciseIndex + 1"></span>/<span x-text="exercises.length"></span>
                    </p>
                    <button @click="showQueue = true" class="px-3 py-1 bg-emerald-500/10 rounded text-[10px] font-bold text-emerald-500 uppercase tracking-wider border border-emerald-500/20 hover:bg-emerald-500/20 transition-colors">
                        Queue
                    </button>
                    <div x-show="currentExercise.status === 'completed'" class="px-2 py-0.5 bg-emerald-500 rounded text-[10px] font-bold text-white uppercase tracking-wider shadow-[0_0_10px_rgba(16,185,129,0.4)] animate-pulse">
                        Completed
                    </div>
                </div>
                <h2 class="text-2xl font-black leading-none truncate w-full text-white drop-shadow-md" x-text="currentExercise.name"></h2>
            </div>
            
            <!-- Header Menu -->
            <div class="relative" x-data="{ showMenu: false }">
                <button @click="showMenu = !showMenu" class="p-2 text-gray-400 hover:text-white transition-colors">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h.01M12 12h.01M19 12h.01M6 12a1 1 0 11-2 0 1 1 0 012 0zm7 0a1 1 0 11-2 0 1 1 0 012 0zm7 0a1 1 0 11-2 0 1 1 0 012 0z"></path></svg>
                </button>
                
                <!-- Dropdown -->
                <div x-show="showMenu" 
                     @click.outside="showMenu = false"
                     x-transition:enter="transition ease-out duration-100"
                     x-transition:enter-start="opacity-0 scale-95"
                     x-transition:enter-end="opacity-100 scale-100"
                     x-transition:leave="transition ease-in duration-75"
                     x-transition:leave-start="opacity-100 scale-100"
                     x-transition:leave-end="opacity-0 scale-95"
                     class="absolute right-0 mt-2 w-48 bg-gray-800 rounded-xl shadow-xl border border-white/10 overflow-hidden z-50"
                     style="display: none;">
                    
                    <button @click="showMenu = false; showQueue = true" class="w-full text-left px-4 py-3 text-sm font-bold text-white hover:bg-white/5 flex items-center gap-2">
                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"></path></svg>
                        Show Queue
                    </button>
                    
                    <button @click="showMenu = false; skipCurrentExercise()" class="w-full text-left px-4 py-3 text-sm font-bold text-amber-500 hover:bg-amber-500/10 flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 5l7 7-7 7M5 5l7 7-7 7"></path></svg>
                        Skip Exercise
                    </button>
                    
                    <div class="h-px bg-white/10 my-0"></div>
                    
                    <form action="{{ route('workouts.finish', $workout) }}" method="POST" class="block">
                        @csrf
                        <button type="submit" class="w-full text-left px-4 py-3 text-sm font-bold text-rose-500 hover:bg-rose-500/10 flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                            Finish Workout
                        </button>
                    </form>
                </div>
            </div>
        </header>

        <!-- Main Card Area (Swipeable) -->
        <main class="flex-1 relative z-10 flex flex-col justify-center items-center w-full px-4 md:px-0"
              @touchstart="handleTouchStart"
              @touchmove="handleTouchMove"
              @touchend="handleTouchEnd">
            
            <!-- Cards Stack -->
            <div class="relative w-full max-w-md aspect-[3/4] md:aspect-auto md:h-[600px]">
                
                <template x-for="(set, idx) in currentExercise.sets" :key="set.id">
                    <div x-show="!isWorkoutComplete && currentSetIndex === idx"
                         x-transition:enter="transition ease-out duration-300 transform"
                         x-transition:enter-start="opacity-0 translate-x-12 scale-95"
                         x-transition:enter-end="opacity-100 translate-x-0 scale-100"
                         x-transition:leave="transition ease-in duration-200 transform"
                         x-transition:leave-start="opacity-100 translate-x-0 scale-100"
                         x-transition:leave-end="opacity-0 -translate-x-12 scale-95"
                         class="absolute inset-0 bg-gray-900/40 backdrop-blur-xl rounded-[2rem] border border-white/10 shadow-2xl shadow-black/50 flex flex-col p-6">
                        
                        <!-- Card Header: Set Info -->
                        <div class="flex justify-between items-center mb-6">
                             <div class="flex items-center gap-3">
                                 <div class="flex items-center justify-center w-10 h-10 rounded-full bg-emerald-500/20 text-emerald-300 border border-emerald-500/30 font-black text-sm shadow-[0_0_15px_rgba(16,185,129,0.2)]">
                                    <span x-text="set.set_num"></span>
                                 </div>
                                 <span class="text-xs text-gray-400 font-bold uppercase tracking-widest">Set</span>
                             </div>
                             
                             <!-- Saved Badge -->
                             <div x-show="set.is_saved" x-transition class="flex items-center gap-1 text-emerald-400 text-xs font-bold uppercase drop-shadow">
                                 <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path></svg>
                                 Saved
                             </div>
                        </div>

                        <!-- Card Body: Wheel Pickers -->
                        <div class="flex-1 flex flex-col justify-center gap-6">
                            
                            <!-- Suggestion Block -->
                            <div class="min-h-[60px] flex items-center justify-center">
                                <template x-if="set.suggestion">
                                    <div class="flex items-center gap-4 bg-white/5 border border-white/10 rounded-xl px-4 py-2 w-full max-w-[85%] shadow-sm backdrop-blur-sm">
                                        <div class="flex-1">
                                            <p class="text-[10px] text-gray-400 uppercase tracking-widest font-bold mb-0.5">Suggested</p>
                                            <div class="flex items-baseline gap-1 text-white">
                                                <span class="text-xl font-black" x-text="set.suggestion.weight"></span>
                                                <span class="text-xs font-bold text-gray-400">kg</span>
                                                <span class="text-gray-500 mx-1">&times;</span>
                                                
                                                <!-- Reps Range Display -->
                                                <template x-if="set.suggestion.suggest_min">
                                                    <span class="text-xl font-black">
                                                        <span x-text="set.suggestion.suggest_min"></span>
                                                        <span x-show="set.suggestion.suggest_max && set.suggestion.suggest_max != set.suggestion.suggest_min" class="text-lg text-gray-400 font-bold">-<span x-text="set.suggestion.suggest_max" class="text-white"></span></span>
                                                    </span>
                                                </template>
                                                <!-- Fallback for legacy format -->
                                                <template x-if="!set.suggestion.suggest_min">
                                                    <span class="text-xl font-black" x-text="set.suggestion.reps"></span>
                                                </template>
                                                
                                                <span class="text-xs font-bold text-gray-400">reps</span>
                                            </div>
                                        </div>
                                        <button @click="applySuggestion(set.suggestion, idx)" 
                                                class="px-5 py-2.5 bg-emerald-500 hover:bg-emerald-400 text-white text-xs font-black uppercase tracking-widest rounded-lg shadow-lg shadow-emerald-500/20 active:scale-95 transition-all">
                                            Apply
                                        </button>
                                    </div>
                                </template>
                                <!-- Fallback Hint if no suggestion -->
                                <template x-if="!set.suggestion">
                                    <div class="text-center opacity-40">
                                        <p class="text-xs font-bold text-white uppercase tracking-wider">Log your best effort</p>
                                    </div>
                                </template>
                            </div>
                            
                            <!-- iOS Wheel Picker Container -->
                            <div class="grid grid-cols-2 h-56 relative">
                                
                                <!-- Center Highlight (Shared) -->
                                <div class="absolute top-1/2 left-4 right-4 -translate-y-1/2 h-12 bg-white/5 border-y border-white/10 rounded-lg pointer-events-none z-0"></div>

                                <!-- Weight Wheel -->
                                <div class="relative h-full overflow-hidden mask-linear-y">
                                    <div class="absolute top-0 w-full text-center z-10 py-2 bg-gradient-to-b from-gray-900/90 to-transparent pointer-events-none">
                                        <span class="text-[10px] uppercase text-gray-500 font-bold tracking-widest">Kg</span>
                                    </div>
                                    
                                    <div :id="'weight-wheel-' + set.id"
                                         class="h-full overflow-y-auto no-scrollbar snap-y snap-mandatory py-[5.5rem]"
                                         x-init="initWheel($el, idx, 'weight', 0.5)"
                                         @scroll.debounce.5ms="onWheelScroll($el, idx, 'weight', 0.5)">
                                        <template x-for="val in weightOptions" :key="val">
                                            <div class="h-12 flex items-center justify-center snap-center font-bold text-xl transition-all duration-150"
                                                 :class="set.weight == val ? 'text-white scale-110' : 'text-gray-500/40 scale-90'">
                                                <span x-text="val"></span>
                                            </div>
                                        </template>
                                    </div>
                                    <div class="absolute bottom-0 w-full h-8 bg-gradient-to-t from-gray-900/90 to-transparent pointer-events-none sticky-bottom-fade"></div>
                                </div>

                                <!-- Reps Wheel -->
                                <div class="relative h-full overflow-hidden mask-linear-y">
                                    <div class="absolute top-0 w-full text-center z-10 py-2 bg-gradient-to-b from-gray-900/90 to-transparent pointer-events-none">
                                        <span class="text-[10px] uppercase text-gray-500 font-bold tracking-widest">Reps</span>
                                    </div>

                                    <div :id="'reps-wheel-' + set.id"
                                         class="h-full overflow-y-auto no-scrollbar snap-y snap-mandatory py-[5.5rem]"
                                         x-init="initWheel($el, idx, 'reps', 1)"
                                         @scroll.debounce.5ms="onWheelScroll($el, idx, 'reps', 1)">
                                        <template x-for="val in repsOptions" :key="val">
                                            <div class="h-12 flex items-center justify-center snap-center font-bold text-xl transition-all duration-150"
                                                 :class="set.reps == val ? 'text-white scale-110' : 'text-gray-500/40 scale-90'">
                                                <span x-text="val"></span>
                                            </div>
                                        </template>
                                    </div>
                                    <div class="absolute bottom-0 w-full h-8 bg-gradient-to-t from-gray-900/90 to-transparent pointer-events-none sticky-bottom-fade"></div>
                                </div>

                            </div>
                        </div>

                        <!-- Error Message -->
                        <div x-show="errorMessage" x-text="errorMessage" class="text-rose-400 text-xs font-bold text-center mt-4 animate-pulse"></div>

                        <!-- Card Footer: Save Button -->
                        <div class="mt-4">
                            <button @click="saveSet(idx)"
                                    :disabled="isSaving || set.weight <= 0 || set.reps <= 0"
                                    class="w-full py-4 rounded-2xl font-black text-base uppercase tracking-widest transition-all flex items-center justify-center gap-2"
                                    :class="set.is_saved ? 'bg-emerald-500/20 text-emerald-400 border border-emerald-500/50' : 'bg-emerald-500 text-white shadow-xl shadow-emerald-500/30 active:scale-[0.98] hover:shadow-emerald-500/50'">
                                <span x-text="isSaving ? 'Saving...' : (set.is_saved ? 'Update & Next' : 'Save & Next')"></span>
                                <svg x-show="!isSaving && !set.is_saved" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
                            </button>
                        </div>
                    </div>
                </template>

                 <!-- Workout Complete Card -->
                <div x-show="isWorkoutComplete"
                     x-transition:enter="transition ease-out duration-500 transform"
                     x-transition:enter-start="opacity-0 scale-95 translate-y-4"
                     x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                     class="absolute inset-0 bg-emerald-900/60 backdrop-blur-xl rounded-[2rem] border border-emerald-500/30 shadow-2xl flex flex-col items-center justify-center p-8 text-center z-20">
                    
                    <div class="w-24 h-24 bg-emerald-500 rounded-full flex items-center justify-center mb-6 shadow-[0_0_40px_rgba(16,185,129,0.4)] animate-bounce">
                        <svg class="w-12 h-12 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg>
                    </div>
                    
                    <h2 class="text-3xl font-black text-white mb-2 drop-shadow-lg">Workout Complete!</h2>
                    <p class="text-emerald-200 text-lg mb-8 font-medium">Great job crushing it properly.</p>
                    
                    <button @click="document.getElementById('workout-form').submit()" 
                            class="w-full py-4 bg-white text-emerald-900 font-black rounded-2xl shadow-xl hover:scale-105 transition-transform uppercase tracking-widest text-lg">
                        Finish & Save
                    </button>
                    
                    <button @click="prevSet()" class="mt-6 text-sm text-emerald-400 font-bold hover:text-white transition-colors">
                        Go Back
                    </button>
                </div>
            </div>

            <!-- Set Indicators -->
            <div class="mt-6 flex gap-2">
                 <template x-for="(set, idx) in currentExercise.sets" :key="set.id">
                     <div class="w-1.5 h-1.5 rounded-full transition-all"
                          :class="idx === currentSetIndex ? 'bg-white w-4' : (set.is_saved ? 'bg-emerald-500' : 'bg-gray-600')"></div>
                 </template>
            </div>
        </main>

        <!-- Sticky Footer: Finish -->
        <div class="relative z-20 px-6 pb-safe-bottom pt-4 bg-gray-900/80 backdrop-blur-md border-t border-white/5">
                <form action="{{ route('workouts.finish', $workout) }}" method="POST" id="workout-form" @submit.prevent="confirmFinish($event)">
                @csrf
                <!-- Hidden Inputs for Syncing State -->
                <template x-for="ex in exercises" :key="ex.id">
                    <div>
                        <input type="hidden" :name="'exercises[' + ex.id + '][difficulty]'" value="ok"> <!-- Default difficulty, can be enhanced later -->
                        <template x-for="(s, sIdx) in ex.sets" :key="s.id">
                            <div>
                                <input type="hidden" :name="'sets[' + ex.id + '][' + sIdx + '][weight]'" :value="s.weight">
                                <input type="hidden" :name="'sets[' + ex.id + '][' + sIdx + '][reps]'" :value="s.reps">
                            </div>
                        </template>
                    </div>
                </template>

                <button type="submit" class="w-full py-4 rounded-2xl bg-gray-800 hover:bg-gray-700 text-gray-200 font-bold border border-white/5 transition-colors flex items-center justify-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                    Finish Workout
                </button>
            </form>
        </div>



    <script>
        function workoutSession(data, historyData) {
            return {
                exercises: data,
                history: historyData || {},
                currentExerciseIndex: 0,
                currentSetIndex: 0,
                isSaving: false,
                isWorkoutComplete: false,
                showTransition: false,
                errorMessage: '',
                touchStartX: 0,
                
                // Wheel Options
                weightOptions: Array.from({length: 401}, (_, i) => i * 0.5), // 0 to 200
                repsOptions: Array.from({length: 51}, (_, i) => i), // 0 to 50

                get currentExercise() {
                    return this.exercises[this.currentExerciseIndex];
                },

                get nextExerciseName() {
                     if (this.currentExerciseIndex < this.exercises.length - 1) {
                        return this.exercises[this.currentExerciseIndex + 1].name;
                     }
                     return '';
                },

                get activeSet() {
                    return this.currentExercise.sets[this.currentSetIndex];
                },
                
                getHistory(exId, setNum) {
                    if (this.history[exId] && this.history[exId][setNum]) {
                        return this.history[exId][setNum];
                    }
                    return null;
                },

                // Helper to get active set
                get activeSet() {
                    return this.currentExercise.sets[this.currentSetIndex];
                },

                // NEW: strict prefill logic
                prefillCurrentSet() {
                    this.$nextTick(() => {
                        const set = this.activeSet;
                        if (!set) return;

                        // Priority determination
                        const determineValue = (field, fallback) => {
                            let curr = parseFloat(set[field]);
                            if (curr > 0) return curr; // 1. Saved/Model

                            // 2. History
                            const hist = this.getHistory(this.currentExercise.exercise_id, set.set_num);
                            if (hist && hist[field]) return parseFloat(hist[field]);

                            // 3. Default
                            return fallback;
                        };

                        set.weight = determineValue('weight', 10);
                        set.reps = determineValue('reps', 8);

                        // Snap Wheels
                        const wWheel = document.getElementById('weight-wheel-' + set.id);
                        const rWheel = document.getElementById('reps-wheel-' + set.id);
                        
                        // Just scroll to the value we just set
                        if (wWheel) this.scrollToValue(wWheel, set.weight, 0.5);
                        if (rWheel) this.scrollToValue(rWheel, set.reps, 1);
                    });
                },

                // Simplified Scroll Helper
                scrollToValue(el, val, step) {
                     const index = Math.round(val / step);
                     el.scrollTop = index * 48;
                },

                // Keep initWheel as generic initializer for x-init, but use prefill logic?
                // Actually x-init runs ONCE. We want prefillCurrentSet to run then too.
                // But x-init is per wheel.
                // Let's make x-init just register the scroll listener essentially, and maybe try to scroll to current model value.
                initWheel(el, setIdx, field, step) {
                     const set = this.currentExercise.sets[setIdx];
                     // Just scroll to whatever is in the model. 
                     // The model should be populated by prefillCurrentSet() called on page load/nav.
                     this.scrollToValue(el, set[field], step);
                },

                onWheelScroll(el, setIdx, field, step) {
                    const itemHeight = 48; // h-12
                    const scrollTop = el.scrollTop;
                    const index = Math.round(scrollTop / itemHeight);
                    const value = index * step;
                    
                    const set = this.currentExercise.sets[setIdx];
                    
                    if (set[field] !== value) {
                        set[field] = value;
                        // Haptic
                        if (navigator.vibrate) navigator.vibrate(5);
                    }
                },

                applySuggestion(sugg, setIdx) {
                    if(!sugg) return;
                    const set = this.currentExercise.sets[setIdx];
                    set.weight = parseFloat(sugg.weight);
                    
                    // Use apply_reps if available (new structure), else fallback to old parsing or just min
                    if (sugg.apply_reps) {
                        set.reps = parseInt(sugg.apply_reps);
                    } else if (typeof sugg.reps === 'string' && sugg.reps.includes('-')) {
                        // Fallback purely for safety if backend doesn't align perfectly yet
                        set.reps = parseInt(sugg.reps.split('-')[0]);
                    } else {
                        set.reps = parseInt(sugg.reps);
                    }
                    
                    // Manually scroll wheels
                    this.$nextTick(() => {
                        const wWheel = document.getElementById('weight-wheel-' + set.id);
                        const rWheel = document.getElementById('reps-wheel-' + set.id);
                        // Use scrollToValue
                        if(wWheel) this.scrollToValue(wWheel, set.weight, 0.5);
                        if(rWheel) this.scrollToValue(rWheel, set.reps, 1);
                    });
                },

                // Navigation Updates
                nextSet() {
                    if (this.currentSetIndex < this.currentExercise.sets.length - 1) {
                        this.currentSetIndex++;
                        this.prefillCurrentSet();
                    } 
                    else if (this.currentExerciseIndex < this.exercises.length - 1) {
                         this.showTransition = true;
                         setTimeout(() => {
                             this.currentExerciseIndex++;
                             this.currentSetIndex = 0;
                             this.prefillCurrentSet();
                             
                             setTimeout(() => {
                                 this.showTransition = false;
                             }, 700);
                         }, 500);
                    } 
                    else {
                        this.isWorkoutComplete = true;
                    }
                },

                prevSet() {
                    if (this.isWorkoutComplete) {
                        this.isWorkoutComplete = false;
                        return;
                    }

                    if (this.currentSetIndex > 0) {
                        this.currentSetIndex--;
                        this.prefillCurrentSet();
                    } else {
                        if (this.currentExerciseIndex > 0) {
                            this.currentExerciseIndex--;
                            this.currentSetIndex = this.currentExercise.sets.length - 1;
                            this.prefillCurrentSet();
                        }
                    }
                },
                
                // Jump/Skip Updates in next block...
                // (I will update jump/skip in a separate edit or finding them)
                
                // Init
                init() {
                    // Start
                    this.prefillCurrentSet();
                    
                    // ... existing init code for queue ...
                    const stored = localStorage.getItem('gym_queue_' + this.workoutId);
                    // ...
                },

                // Swipe Logic
                handleTouchStart(e) {
                    this.touchStartX = e.changedTouches[0].screenX;
                },
                handleTouchMove(e) {},
                handleTouchEnd(e) {
                    const diff = e.changedTouches[0].screenX - this.touchStartX;
                    if (Math.abs(diff) > 50) { // Threshold
                        if (diff > 0) this.prevSet(); // Right Swipe -> Back
                        else this.nextSet(); // Left Swipe -> Next
                    }
                },

                // Actions
                jumpToExercise(idx) {
                    this.currentExerciseIndex = idx;
                    this.currentSetIndex = 0;
                    this.showQueue = false;
                    this.prefillCurrentSet();
                },

                async saveSet(idx) {
                    const set = this.currentExercise.sets[idx];
                    this.isSaving = true;
                    this.errorMessage = ''; 

                    try {
                        const payload = {
                            workout_exercise_id: this.currentExercise.id,
                            set_number: set.set_num,
                            weight_kg: set.weight,
                            reps: set.reps
                        };

                        const res = await fetch(`{{ url('/workouts/' . $workout->id . '/sets') }}`, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            },
                            body: JSON.stringify(payload)
                        });
                        
                        const json = await res.json();
                        
                        if (res.ok && json.status === 'success') {
                            set.is_saved = true;
                            
                            // Auto-Complete Update
                            if (json.exercise_completed) {
                                this.currentExercise.status = 'completed';
                            }
                            
                            // Handle Suggestions for NEXT set
                            if (json.suggestion) {
                                let nextSetIdx = idx + 1;
                                let targetSet = null;

                                if (nextSetIdx < this.currentExercise.sets.length) {
                                    // Next set in current exercise
                                    targetSet = this.currentExercise.sets[nextSetIdx];
                                } else {
                                    // Optimization: Pre-fill Set 1 of next exercise?
                                    // Backend currently returns suggestion for "nextSetNumber" of THIS exercise.
                                    // So if I save Set 3/3, backend suggests Set 4.
                                    // But we only have 3 sets. 
                                    // So we only update if there is a next set card.
                                }
                                
                                if (targetSet) {
                                    targetSet.suggestion = json.suggestion;
                                }
                            }

                            // Small delay then advanced
                            setTimeout(() => {
                                this.nextSet();
                                this.isSaving = false;
                            }, 300);
                        } else {
                            this.errorMessage = json.message || 'Validation failed. Check inputs.';
                            this.isSaving = false;
                        }
                    } catch (e) {
                        console.error(e);
                        this.errorMessage = 'Network error. Please try again.';
                        this.isSaving = false;
                    }
                },

                confirmFinish(e) {
                    // Check logic...
                    let total = 0; 
                    let saved = 0;
                    this.exercises.forEach(ex => {
                        ex.sets.forEach(s => {
                            total++;
                            if(s.is_saved) saved++;
                        });
                    });

                    if (saved < total && saved > 0) {
                        // Optional: warn if partial. But let's just submit.
                    }
                    e.target.submit();
                },

                // Queue & Reorder Logic
                workoutId: @js($workout->id),
                showQueue: false,

                init() {
                    // Start Force Prefill
                    this.prefillCurrentSet();

                    // Initial Sort based on order_index from DB
                    this.exercises.sort((a, b) => a.order_index - b.order_index);
                    
                    // Set Current Index from DB Persistence
                    const currentId = @js($workout->current_workout_exercise_id);
                    if (currentId) {
                        const foundIdx = this.exercises.findIndex(e => e.id === currentId);
                        if (foundIdx !== -1) {
                            this.currentExerciseIndex = foundIdx;
                        }
                    }
                },

                async jumpToExercise(idx) {
                    if (idx < 0 || idx >= this.exercises.length) return;
                    
                    this.currentExerciseIndex = idx;
                    this.showQueue = false;
                    this.prefillCurrentSet();
                    
                    // Persist "Go To"
                    try {
                        await fetch(`{{ url('/workouts/' . $workout->id . '/go-to') }}`, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            },
                            body: JSON.stringify({ workout_exercise_id: this.exercises[idx].id })
                        });
                    } catch (e) { console.error('Go-To failed', e); }
                },

                async saveQueue() {
                    const ids = this.exercises.map(e => e.id);
                    // Optimistic UI update already happened in moveExercise
                    
                    try {
                        await fetch(`{{ url('/workouts/' . $workout->id . '/reorder') }}`, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            },
                            body: JSON.stringify({ order: ids })
                        });
                    } catch (e) {
                         console.error('Queue save failed', e);
                    }
                },

                moveExercise(fromIdx, toIdx) {
                    if (toIdx < 0 || toIdx >= this.exercises.length) return;
                    
                    // Move
                    const item = this.exercises.splice(fromIdx, 1)[0];
                    this.exercises.splice(toIdx, 0, item);
                    
                    if (fromIdx === this.currentExerciseIndex) {
                        this.currentExerciseIndex = toIdx;
                    } else if (fromIdx < this.currentExerciseIndex && toIdx >= this.currentExerciseIndex) {
                        this.currentExerciseIndex--;
                    } else if (fromIdx > this.currentExerciseIndex && toIdx <= this.currentExerciseIndex) {
                        this.currentExerciseIndex++;
                    }

                    this.saveQueue();
                },

                async skipCurrentExercise() {
                    const item = this.exercises[this.currentExerciseIndex];
                    if (!item) return;

                    // Optimistic: Mark as skipped in frontend
                    item.status = 'skipped';
                    
                    // Move to end in frontend (optimistic)
                    // Note: If we want strict sync, we could reload, but optimistic is better UX
                    const idx = this.currentExerciseIndex;
                    if (idx < this.exercises.length - 1) {
                         // Only move if not already last
                         this.exercises.splice(idx, 1);
                         this.exercises.push(item);
                         // Since we removed current, the index now points to the "next" visual item
                         // So we don't necessarily need to change currentExerciseIndex if we want to show the next one
                         // BUT, we should probably reset to 0 or 0 flow
                    }

                    try {
                        const response = await fetch(`{{ url('/workouts/' . $workout->id . '/skip') }}`, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            },
                            body: JSON.stringify({ workout_exercise_id: item.id })
                        });
                        
                        const data = await response.json();
                        if (data.status === 'success' && data.next_id) {
                            // Sync current index if backend dictates a specific next exercise
                            const nextIdx = this.exercises.findIndex(e => e.id === data.next_id);
                            if (nextIdx !== -1) {
                                this.currentExerciseIndex = nextIdx;
                            }
                        }
                    } catch(e) { console.error(e); }

                    // Reset View
                    this.currentSetIndex = 0;
                    this.showQueue = false;
                    this.showTransition = true;
                    setTimeout(() => {
                         this.showTransition = false;
                         this.prefillCurrentSet();
                    }, 500);
                }
            }
        }
    </script>
    
    <!-- Queue Modal (Portal/Fixed) -->
    <div x-show="showQueue" 
         style="display: none;"
         class="fixed inset-0 z-[100] flex items-end sm:items-center justify-center pointer-events-none"
         x-cloak>
        
        <!-- Backdrop -->
        <div x-show="showQueue" 
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             @click="showQueue = false"
             class="absolute inset-0 bg-black/60 backdrop-blur-sm pointer-events-auto"></div>

        <!-- Sheet -->
        <div x-show="showQueue"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="translate-y-full opacity-50"
             x-transition:enter-end="translate-y-0 opacity-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="translate-y-0 opacity-100"
             x-transition:leave-end="translate-y-full opacity-50"
             class="relative w-full max-w-md bg-gray-900 border-t border-white/10 rounded-t-3xl shadow-2xl p-6 pointer-events-auto max-h-[80vh] flex flex-col">
            
            <div class="w-12 h-1.5 bg-gray-700 rounded-full mx-auto mb-6"></div>
            
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-xl font-bold text-white">Exercise Queue</h2>
                <button @click="showQueue = false" class="text-gray-400 hover:text-white p-2">Close</button>
            </div>

            <div class="flex-1 overflow-y-auto space-y-3">
                <template x-for="(ex, idx) in exercises" :key="ex.id">
                    <div @click="jumpToExercise(idx)"
                         class="flex items-center gap-3 p-3 rounded-xl border transition-colors cursor-pointer active:scale-[0.98]"
                         :class="[
                            idx === currentExerciseIndex ? 'bg-emerald-500/10 border-emerald-500/50' : '',
                            idx < currentExerciseIndex ? 'bg-gray-800/50 border-transparent opacity-50' : '',
                            idx > currentExerciseIndex && ex.status !== 'skipped' ? 'bg-gray-800 border-white/5 hover:bg-gray-700' : '',
                            ex.status === 'skipped' ? 'bg-amber-500/10 border-amber-500/50' : ''
                         ]">
                        
                        <!-- Status Icon -->
                        <div class="w-8 h-8 rounded-full flex items-center justify-center font-bold text-xs"
                             :class="[
                                idx === currentExerciseIndex ? 'bg-emerald-500 text-white' : '',
                                idx < currentExerciseIndex ? 'bg-gray-700 text-gray-400' : '',
                                idx > currentExerciseIndex && ex.status !== 'skipped' ? 'bg-gray-700 text-white' : '',
                                ex.status === 'skipped' ? 'bg-amber-500 text-white' : ''
                             ]">
                            <span x-show="idx >= currentExerciseIndex && ex.status !== 'skipped'" x-text="idx + 1"></span>
                            <svg x-show="idx < currentExerciseIndex" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                            <svg x-show="ex.status === 'skipped'" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 5l7 7-7 7M5 5l7 7-7 7"></path></svg>
                        </div>
                        
                        <div class="flex-1 min-w-0">
                            <h4 class="font-bold text-sm truncate text-white" x-text="ex.name"></h4>
                            <div class="flex items-center gap-2">
                                <p class="text-[10px] text-gray-400 uppercase tracking-wider" x-text="idx === currentExerciseIndex ? 'Current' : (idx < currentExerciseIndex ? 'Completed' : 'Upcoming')"></p>
                                <span x-show="ex.status === 'skipped'" class="px-1.5 py-0.5 rounded text-[8px] font-bold bg-amber-500/20 text-amber-500 uppercase tracking-wider">Skipped</span>
                            </div>
                        </div>

                        <!-- Actions (Only for current/upcoming) -->
                        <div class="flex items-center gap-1" x-show="idx >= currentExerciseIndex" @click.stop>
                            <!-- Move Up -->
                            <button @click="moveExercise(idx, idx-1)" x-show="idx > currentExerciseIndex" class="p-2 text-gray-400 hover:text-white bg-gray-700/50 rounded-lg">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"></path></svg>
                            </button>
                            <!-- Move Down -->
                            <button @click="moveExercise(idx, idx+1)" x-show="idx < exercises.length - 1" class="p-2 text-gray-400 hover:text-white bg-gray-700/50 rounded-lg">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7 7"></path></svg>
                            </button>
                        </div>
                    </div>
                </template>
            </div>
            
            <!-- Quick Action for Current -->
            <div class="mt-6 pt-4 border-t border-white/10">
                <button @click="skipCurrentExercise()" class="w-full py-3 bg-gray-800 hover:bg-gray-700 text-gray-300 font-bold rounded-xl border border-white/10 flex items-center justify-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 5l7 7-7 7M5 5l7 7-7 7"></path></svg>
                    Skip Current Exercise (Move to End)
                </button>
            </div>
        </div>
    </div>
    </div>
</x-app-layout>
