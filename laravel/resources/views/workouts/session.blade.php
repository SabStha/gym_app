@extends('layouts.app')

@inject('suggestionService', 'App\Services\ProgressionSuggestionService')

@section('content')
<div class="container mx-auto px-4 py-6 max-w-2xl">
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">{{ $workout->routineDay->day_name ?? 'Free Workout' }}</h1>
            <p class="text-sm text-gray-500">{{ now()->format('M d, Y') }}</p>
        </div>
        <div class="text-right">
            <span class="inline-block px-3 py-1 bg-blue-100 text-blue-800 text-xs font-semibold rounded-full">In Progress</span>
        </div>
    </div>

    <!-- Main Workout Form (Post to finish) -->
    <form action="{{ route('workouts.finish', $workout) }}" method="POST" id="workout-form">
        @csrf
        
        <div class="space-y-6 mb-8">
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
                    // Show at least 3 rows or existing count + 1 (to always show next step) but typically 3 is fine for UI
                    $rowCount = max($targetSets, count($existingSets), 3);
                    
                    // Determine which set is "Next"
                    $nextSetNum = $existingSets->count() + 1;
                @endphp

                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 exercise-block" data-we-id="{{ $we->id }}">
                    <div class="flex justify-between items-center mb-4 border-b pb-2">
                        <h3 class="font-bold text-lg text-gray-900">{{ $we->exercise->name }}</h3>
                        <div class="flex items-center gap-2">
                            <span class="text-xs text-gray-400">Difficulty:</span>
                            <select name="exercises[{{ $we->id }}][difficulty]" class="text-sm border-gray-300 rounded p-1">
                                <option value="ok" {{ $we->difficulty == 'ok' ? 'selected' : '' }}>OK</option>
                                <option value="hard" {{ $we->difficulty == 'hard' ? 'selected' : '' }}>Hard</option>
                            </select>
                        </div>
                    </div>

                    <div class="space-y-3">
                        <div class="grid grid-cols-12 gap-2 text-xs font-medium text-gray-500 uppercase text-center mb-1">
                            <div class="col-span-1">Set</div>
                            <div class="col-span-3">Kg</div>
                            <div class="col-span-3">Reps</div>
                            <div class="col-span-5 text-left pl-2">Suggestion</div>
                        </div>

                        @for($i = 0; $i < $rowCount; $i++)
                            @php 
                                $setNum = $i + 1;
                                $setRecord = isset($existingSets[$i]) ? $existingSets[$i] : null; 
                                $isNext = ($setNum === $nextSetNum);
                                
                                $suggData = null;
                                if ($isNext) {
                                    $suggData = $suggestionService->getSuggestion($we, $setNum);
                                }
                            @endphp
                            <div class="grid grid-cols-12 gap-2 items-center set-row" data-set-num="{{ $setNum }}">
                                <div class="col-span-1 text-center font-bold text-gray-400 set-number">{{ $setNum }}</div>
                                
                                <div class="col-span-3">
                                    <input type="number" step="0.5" 
                                           name="sets[{{ $we->id }}][{{ $i }}][weight]" 
                                           value="{{ $setRecord ? $setRecord->weight_kg : '' }}"
                                           class="w-full border-gray-300 rounded shadow-sm p-2 text-center weight-input" 
                                           placeholder="kg">
                                </div>
                                
                                <div class="col-span-3">
                                    <input type="number" 
                                           name="sets[{{ $we->id }}][{{ $i }}][reps]" 
                                           value="{{ $setRecord ? $setRecord->reps : '' }}"
                                           class="w-full border-gray-300 rounded shadow-sm p-2 text-center reps-input" 
                                           placeholder="reps">
                                </div>

                                <div class="col-span-5 pl-2 flex items-center justify-between text-xs">
                                    <span class="suggestion-text text-gray-500 italic truncate" id="sugg-text-{{ $we->id }}-{{ $setNum }}"
                                          title="{{ $suggData['reason'] ?? '' }}">
                                        @if($suggData && $suggData['weight'] !== null)
                                            {{ $suggData['weight'] }}kg x {{ $suggData['reps'] }}
                                        @elseif($suggData)
                                            {{ $suggData['reason'] }}
                                        @endif
                                    </span>

                                    <!-- Apply Button: Show only if we have data -->
                                    <button type="button" 
                                            class="apply-btn {{ ($suggData && $suggData['weight'] !== null) ? '' : 'hidden' }} text-blue-600 font-bold hover:underline ml-1"
                                            onclick="applySuggestion({{ $we->id }}, {{ $setNum }})">
                                        [Apply]
                                    </button>
                                    
                                    <!-- Save Button -->
                                     <button type="button" 
                                            class="save-set-btn text-green-600 font-bold hover:underline ml-2"
                                            onclick="saveSet({{ $we->id }}, {{ $setNum }})">
                                        ✓
                                    </button>
                                    
                                    <!-- Hidden storage for suggestion values -->
                                    <input type="hidden" id="sugg-weight-{{ $we->id }}-{{ $setNum }}" 
                                           value="{{ $suggData ? $suggData['weight'] : '' }}">
                                    <input type="hidden" id="sugg-reps-{{ $we->id }}-{{ $setNum }}"
                                            @php
                                                $repVal = '';
                                                if ($suggData && isset($suggData['reps'])) {
                                                    $repVal = $suggData['reps'];
                                                    if(is_string($repVal) && str_contains($repVal, '-')) {
                                                        $repVal = explode('-', $repVal)[1];
                                                    }
                                                }
                                            @endphp
                                           value="{{ $repVal }}">
                                </div>
                            </div>
                        @endfor
                    </div>
                </div>
            @endforeach
        </div>

        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 mb-8">
            <h3 class="font-bold text-gray-900 mb-2">Finish Workout</h3>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Note</label>
                <textarea name="note" rows="2" class="w-full border-gray-300 rounded shadow-sm focus:ring-green-500 focus:border-green-500"></textarea>
            </div>
        </div>

        <button type="submit" class="w-full bg-green-600 text-white font-bold py-4 rounded-lg hover:bg-green-700 shadow-lg text-lg sticky bottom-4">
            Finish Workout
        </button>
    </form>
</div>

<script>
    // No DOMContentLoaded fetch anymore - Server Side Rendered!

    function applySuggestion(weId, setNum) {
        const weight = document.getElementById(`sugg-weight-${weId}-${setNum}`).value;
        const reps = document.getElementById(`sugg-reps-${weId}-${setNum}`).value;
        
        const row = document.querySelector(`.exercise-block[data-we-id="${weId}"] .set-row[data-set-num="${setNum}"]`);
        if(row && weight && reps) {
            row.querySelector('.weight-input').value = weight;
            row.querySelector('.reps-input').value = reps;
        }
    }

    function saveSet(weId, setNum) {
        const row = document.querySelector(`.exercise-block[data-we-id="${weId}"] .set-row[data-set-num="${setNum}"]`);
        const weight = row.querySelector('.weight-input').value;
        const reps = row.querySelector('.reps-input').value;

        if(!weight || !reps) {
            alert('Please enter weight and reps');
            return;
        }

        fetch(`{{ route('workout-sets.store', $workout) }}`, {
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
                // Visual feedback
                const btn = row.querySelector('.save-set-btn');
                const originalText = btn.innerText;
                btn.innerText = 'Saved!';
                setTimeout(() => btn.innerText = originalText, 1000);

                // Load suggestion for NEXT set
                if(data.suggestion && data.next_set) {
                     const nextSetNum = data.next_set;
                     const nextRow = document.querySelector(`.exercise-block[data-we-id="${weId}"] .set-row[data-set-num="${nextSetNum}"]`);
                     
                     if(nextRow) {
                         const sugg = data.suggestion;
                         
                         // Store values in hidden inputs
                         let weightVal = '';
                         let repVal = '';
                         
                         if(sugg.weight !== null) {
                             weightVal = sugg.weight;
                             repVal = sugg.reps;
                             if(typeof repVal === 'string' && repVal.includes('-')) repVal = repVal.split('-')[1];
                             
                             document.getElementById(`sugg-weight-${weId}-${nextSetNum}`).value = weightVal;
                             document.getElementById(`sugg-reps-${weId}-${nextSetNum}`).value = repVal;
                         }

                         // Update visual text
                         const textSpan = document.getElementById(`sugg-text-${weId}-${nextSetNum}`);
                         if(textSpan) {
                             if(sugg.weight !== null) {
                                 textSpan.innerText = `${sugg.weight}kg x ${sugg.reps}`;
                                 textSpan.nextElementSibling.classList.remove('hidden'); // Show apply
                             } else {
                                 textSpan.innerText = sugg.reason;
                                 textSpan.nextElementSibling.classList.add('hidden');
                             }
                             textSpan.title = sugg.reason;
                         }
                     }
                }
            }
        })
        .catch(err => alert('Error saving set'));
    }
</script>
@endsection
