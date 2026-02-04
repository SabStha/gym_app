@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-6 max-w-2xl">
    <div class="mb-6">
        <a href="{{ route('history.index') }}" class="text-gray-500 hover:text-gray-700 text-sm flex items-center">
            &larr; Back to History
        </a>
    </div>

    <div class="bg-white rounded-lg shadow overflow-hidden mb-6">
        <div class="p-6 border-b border-gray-200 flex justify-between items-center bg-gray-50">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">{{ $workout->routineDay->day_name ?? 'Free Workout' }}</h1>
                <p class="text-sm text-gray-500">{{ $workout->workout_date->format('l, F j, Y') }}</p>
            </div>
            <div class="text-right">
                <span class="block text-2xl font-bold text-gray-800">{{ $workout->duration_minutes }} min</span>
                <span class="text-xs text-gray-500 uppercase tracking-wide">Duration</span>
            </div>
        </div>

        @if($workout->note)
            <div class="px-6 py-4 bg-yellow-50 border-b border-yellow-100 text-sm text-yellow-800">
                <strong>Note:</strong> {{ $workout->note }}
            </div>
        @endif

        <div class="p-6 space-y-8">
            @foreach($workout->workoutExercises as $exercise)
                <div>
                    <div class="flex justify-between items-baseline mb-3 border-b border-gray-100 pb-2">
                        <h3 class="text-lg font-bold text-gray-800">{{ $exercise->exercise->name }}</h3>
                        @if($exercise->difficulty == 'hard')
                            <span class="text-xs font-semibold bg-red-100 text-red-700 px-2 py-1 rounded">Hard</span>
                        @endif
                    </div>
                    
                    @if($exercise->workoutSets->count() > 0)
                        <table class="min-w-full text-sm">
                            <thead>
                                <tr class="text-gray-500 border-b border-gray-100">
                                    <th class="py-2 text-left w-16">Set</th>
                                    <th class="py-2 text-center">Weight (kg)</th>
                                    <th class="py-2 text-center">Reps</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-50">
                                @foreach($exercise->workoutSets as $set)
                                    <tr>
                                        <td class="py-2 text-gray-500 font-medium">{{ $set->set_number }}</td>
                                        <td class="py-2 text-center text-gray-900 font-bold">{{ $set->weight_kg }}</td>
                                        <td class="py-2 text-center text-gray-700">{{ $set->reps }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @else
                        <p class="text-sm text-gray-400 italic">No sets logged.</p>
                    @endif
                </div>
            @endforeach
        </div>
    </div>
</div>
@endsection
