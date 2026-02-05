@inject('suggestionService', 'App\Services\ProgressionSuggestionService')
<x-app-layout>
    <div class="max-w-xl mx-auto px-4 py-8 min-h-screen pb-32">
        <!-- Header -->
        <header class="mb-6 flex justify-between items-start">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 leading-tight">{{ $workout->routineDay->day_name ?? 'Free Workout' }}</h1>
                <p class="text-sm text-gray-500 mt-1">{{ now()->format('l, M j') }} • Week {{ now()->weekOfYear }}</p>
            </div>
            <div class="flex items-center gap-2">
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-emerald-100 text-emerald-800 animate-pulse">
                    Live
                </span>
            </div>
        </header>

        <!-- Main Form -->
        <form action="{{ route('workouts.finish', $workout) }}" method="POST" id="workout-form">
            @csrf
            
            <div class="space-y-8">
                @foreach($workout->workoutExercises as $we)
                    @php
                        $targetSets = 3; 
                        if($workout->routineDay) {
                             $originalDayExercise = $workout->routineDay->dayExercises->firstWhere('exercise_id', $we->exercise_id);
                             if($originalDayExercise) {
                                 $targetSets = $originalDayExercise->target_sets;
                             }
                        }
                        $existingSets = $we->workoutSets; 
                        // Show at least target sets count or existing count + 1
                        $rowCount = max($targetSets, count($existingSets), 3);
                         
                        // Determine "Next" set index (1-based)
                        $nextSetNum = $existingSets->count() + 1;
                    @endphp

                    <!-- Exercise Card -->
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden exercise-block" data-we-id="{{ $we->id }}">
                        <!-- Card Header -->
                        <div class="bg-gray-50/50 px-4 py-3 border-b border-gray-100 flex justify-between items-center">
                            <h3 class="font-bold text-lg text-gray-900">{{ $we->exercise->name }}</h3>
                            <div class="relative">
                                <select name="exercises[{{ $we->id }}][difficulty]" class="appearance-none bg-white border border-gray-200 text-gray-700 py-1 pl-3 pr-8 rounded-lg text-xs font-medium focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent cursor-pointer">
                                    <option value="ok" {{ $we->difficulty == 'ok' ? 'selected' : '' }}>Diff: OK</option>
                                    <option value="hard" {{ $we->difficulty == 'hard' ? 'selected' : '' }}>Diff: Hard</option>
                                    <option value="easy" {{ $we->difficulty == 'easy' ? 'selected' : '' }}>Diff: Easy</option>
                                </select>
                                <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-gray-500">
                                    <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
                                </div>
                            </div>
                        </div>

                        <!-- Sets List -->
                        <div class="divide-y divide-gray-100">
                            @for($i = 0; $i < $rowCount; $i++)
                                @php 
                                    $setNum = $i + 1;
                                    $setRecord = isset($existingSets[$i]) ? $existingSets[$i] : null; 
                                    $isNext = ($setNum === $nextSetNum);
                                    
                                    // Get Suggestion
                                    $suggData = null;
                                    if ($isNext) {
                                        $suggData = $suggestionService->getSuggestion($we, $setNum);
                                    }
                                    
                                    // Check if saved (has ID)
                                    $isSaved = !is_null($setRecord);
                                @endphp

                                <div class="p-4 set-row transition-colors duration-300 {{ $isSaved ? 'bg-emerald-50/60' : '' }}" 
                                     data-set-num="{{ $setNum }}" id="set-row-{{ $we->id }}-{{ $setNum }}">
                                    
                                    <!-- Top Row: Label + Suggestion Action -->
                                    <div class="flex justify-between items-center mb-3">
                                        <div class="flex items-center gap-2">
                                            <span class="flex h-6 w-6 items-center justify-center rounded-full bg-gray-100 text-xs font-bold text-gray-500 set-badge {{ $isSaved ? 'bg-emerald-200 text-emerald-800' : '' }}">
                                                {{ $setNum }}
                                            </span>
                                            
                                            <div class="text-xs text-gray-500 italic suggestion-container">
                                                @if($suggData && $suggData['weight'] !== null)
                                                    <span class="hidden sm:inline">Suggestion:</span> 
                                                    <span class="font-medium text-gray-700">{{ $suggData['weight'] }}kg × {{ $suggData['reps'] }}</span>
                                                @elseif($suggData)
                                                     <span class="text-xs">{{ $suggData['reason'] }}</span>
                                                @endif
                                            </div>
                                        </div>

                                        @if($suggData && $suggData['weight'] !== null)
                                            <!-- Apply Suggestion Button -->
                                            <button type="button" 
                                                    class="text-xs font-semibold text-blue-600 hover:text-blue-700 bg-blue-50 hover:bg-blue-100 px-2.5 py-1 rounded-md transition-colors flex items-center gap-1 touch-manipulation select-none"
                                                    onclick="applySuggestion({{ $we->id }}, {{ $setNum }}, {{ $suggData['weight'] }}, '{{ $suggData['reps'] }}')">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" /></svg>
                                                Apply
                                            </button>
                                        @endif
                                        
                                        <!-- Saved Indicator -->
                                        <div class="saved-indicator {{ $isSaved ? 'flex' : 'hidden' }} items-center text-emerald-600 text-xs font-bold uppercase tracking-wide gap-1">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                            </svg>
                                            Saved
                                        </div>
                                    </div>

                                    <!-- Controls Row -->
                                    <div class="grid grid-cols-2 gap-4">
                                        <!-- Weight Stepper -->
                                        <div>
                                            <label class="block text-[10px] uppercase text-gray-400 font-bold mb-1 text-center">Weight (kg)</label>
                                            <div class="flex items-center">
                                                <button type="button" onclick="stepValue(this, -2.5)" class="h-10 w-10 flex items-center justify-center rounded-l-xl bg-gray-100 text-gray-600 hover:bg-gray-200 active:bg-gray-300 transition-colors font-bold text-lg touch-manipulation select-none">-</button>
                                                <input type="number" step="0.5" 
                                                       name="sets[{{ $we->id }}][{{ $i }}][weight]" 
                                                       value="{{ $setRecord ? $setRecord->weight_kg : '' }}"
                                                       class="h-10 w-full border-y border-x-0 border-gray-200 text-center font-bold text-gray-900 focus:ring-0 z-10 weight-input p-0" 
                                                       placeholder="-"
                                                >
                                                <button type="button" onclick="stepValue(this, 2.5)" class="h-10 w-10 flex items-center justify-center rounded-r-xl bg-gray-100 text-gray-600 hover:bg-gray-200 active:bg-gray-300 transition-colors font-bold text-lg touch-manipulation select-none">+</button>
                                            </div>
                                        </div>

                                        <!-- Reps Stepper -->
                                        <div>
                                            <label class="block text-[10px] uppercase text-gray-400 font-bold mb-1 text-center">Reps</label>
                                            <div class="flex items-center">
                                                <button type="button" onclick="stepValue(this, -1)" class="h-10 w-10 flex items-center justify-center rounded-l-xl bg-gray-100 text-gray-600 hover:bg-gray-200 active:bg-gray-300 transition-colors font-bold text-lg touch-manipulation select-none">-</button>
                                                <input type="number" 
                                                       name="sets[{{ $we->id }}][{{ $i }}][reps]" 
                                                       value="{{ $setRecord ? $setRecord->reps : '' }}"
                                                       class="h-10 w-full border-y border-x-0 border-gray-200 text-center font-bold text-gray-900 focus:ring-0 z-10 reps-input p-0" 
                                                       placeholder="-"
                                                >
                                                <button type="button" onclick="stepValue(this, 1)" class="h-10 w-10 flex items-center justify-center rounded-r-xl bg-gray-100 text-gray-600 hover:bg-gray-200 active:bg-gray-300 transition-colors font-bold text-lg touch-manipulation select-none">+</button>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Save Action (Full Width Button) -->
                                    <div class="mt-4">
                                        <button type="button" 
                                                class="w-full flex items-center justify-center py-2.5 rounded-xl text-sm font-bold transition-all transform active:scale-[0.98] save-btn
                                                {{ $isSaved 
                                                    ? 'bg-transparent text-emerald-600 border border-emerald-200 hover:bg-emerald-50' 
                                                    : 'bg-gray-900 text-white hover:bg-black shadow-md' }}"
                                                onclick="saveSet(this, {{ $we->id }}, {{ $setNum }})">
                                            {{ $isSaved ? 'Update Set' : 'Save Set' }}
                                        </button>
                                    </div>
                                </div>
                            @endfor
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Notes & Finish -->
            <div class="mt-8 bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                <h3 class="font-bold text-gray-900 mb-4">Workout Notes</h3>
                <textarea name="note" rows="3" class="w-full border-gray-200 rounded-xl shadow-sm focus:border-emerald-500 focus:ring-emerald-500 text-sm p-4 placeholder-gray-400" placeholder="How did it feel today?"></textarea>
            </div>

            <div class="sticky bottom-24 lg:bottom-6 mt-8 z-30 px-2 lg:px-0">
                <button type="submit" class="w-full bg-emerald-600 text-white font-bold py-4 rounded-2xl hover:bg-emerald-700 shadow-xl shadow-emerald-200 transition-all transform active:scale-[0.98] text-lg flex items-center justify-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                    Finish Workout
                </button>
            </div>
        </form>
    </div>

    <script>
        // vanilla JS for lightweight interaction
        
        function stepValue(btn, step) {
             const input = btn.parentNode.querySelector('input');
             let val = parseFloat(input.value) || 0;
             
             val += step;
             
             // Constraints
             if(val < 0) val = 0;
             
             // Rounding logic for clean numbers
             if (Math.abs(step) < 1) { 
                 // Decimal steps (2.5) -> keep 1 decimal if needed, but usually .5 is fine
                 input.value = Math.round(val * 2) / 2;
             } else {
                 input.value = Math.round(val);
             }
             
             // Trigger change for any listeners
             input.dispatchEvent(new Event('change'));
        }

        function applySuggestion(weId, setNum, weight, repsRaw) {
            const row = document.getElementById(`set-row-${weId}-${setNum}`);
            if(!row) return;

            const wInput = row.querySelector('.weight-input');
            const rInput = row.querySelector('.reps-input');
            
            // Reps might be range "8-10", grab lower bound or just the string if numeric
            let reps = repsRaw;
            if(String(reps).includes('-')) {
                reps = String(reps).split('-')[0]; // simple logic: take lower bound
            }

            wInput.value = weight;
            rInput.value = reps;
            
            // Highlight effect
            row.classList.add('bg-blue-50');
            setTimeout(() => row.classList.remove('bg-blue-50'), 600);
        }

        function saveSet(btn, weId, setNum) {
            const row = document.getElementById(`set-row-${weId}-${setNum}`);
            const wInput = row.querySelector('.weight-input');
            const rInput = row.querySelector('.reps-input');
            
            const weight = wInput.value;
            const reps = rInput.value;

            if(!weight || !reps || weight <= 0 || reps <= 0) {
                // Shake validation
                row.classList.add('animate-pulse', 'bg-red-50');
                setTimeout(() => row.classList.remove('animate-pulse', 'bg-red-50'), 500);
                return;
            }

            // Loading state
            const originalText = btn.innerText;
            btn.innerText = 'Saving...';
            btn.disabled = true;
            btn.classList.add('opacity-75');

            fetch(`{{ url('/workouts/' . $workout->id . '/sets') }}`, {
                 method: 'POST',
                 headers: {
                     'Content-Type': 'application/json',
                     'X-CSRF-TOKEN': '{{ csrf_token() }}'
                 },
                 body: JSON.stringify({
                     workout_exercise_id: weId,
                     set_number: setNum,
                     weight_kg: weight,
                     reps: reps
                 })
            })
            .then(res => res.json())
            .then(data => {
                if(data.status === 'success') {
                    // Success State
                    // 1. Update Row Style
                    row.classList.add('bg-emerald-50/60');
                    
                    // 2. Show Saved Indicator
                    row.querySelector('.saved-indicator').classList.remove('hidden');
                    row.querySelector('.saved-indicator').classList.add('flex');
                    
                    // 3. Update Badge
                    const badge = row.querySelector('.set-badge');
                    badge.classList.remove('bg-gray-100', 'text-gray-500');
                    badge.classList.add('bg-emerald-200', 'text-emerald-800');
                    
                    // 4. Update Button State
                    btn.innerText = 'Update Set';
                    
                    // 5. Hide Apply button if visible (clean up)
                    const applyBtn = row.querySelector('button[onclick^="applySuggestion"]');
                    if(applyBtn) applyBtn.classList.add('hidden');

                } else {
                    alert('Failed to save set');
                }
            })
            .catch(err => {
                console.error(err);
                alert('Error connecting to server');
            })
            .finally(() => {
                btn.disabled = false;
                btn.classList.remove('opacity-75');
                if(btn.innerText === 'Saving...') btn.innerText = originalText; 
            });
        }
    </script>
</x-app-layout>
