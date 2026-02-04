@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-6 max-w-2xl">
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="p-6 bg-green-600 text-white">
            <h1 class="text-3xl font-bold mb-1">Workout Complete!</h1>
            <p class="opacity-90">{{ $workout->routineDay->day_name ?? 'Free Workout' }} • {{ $workout->workout_date->format('M d, Y') }}</p>
        </div>
        
        <div class="p-6 border-b border-gray-200">
            <div class="grid grid-cols-2 gap-4 text-center">
                <div>
                    <p class="text-gray-500 text-sm uppercase tracking-wide">Duration</p>
                    <p class="text-2xl font-bold text-gray-800">{{ $workout->duration_minutes }} min</p>
                </div>
                <div>
                    <p class="text-gray-500 text-sm uppercase tracking-wide">Exercises</p>
                    <p class="text-2xl font-bold text-gray-800">{{ $workout->workoutExercises->count() }}</p>
                </div>
            </div>
            @if($workout->note)
                <div class="mt-4 p-4 bg-gray-50 rounded text-gray-600 italic">
                    "{{ $workout->note }}"
                </div>
            @endif
        </div>

        <div class="p-6">
            <h2 class="text-xl font-bold text-gray-800 mb-4">Session Log</h2>
            <div class="space-y-6">
                @foreach($workout->workoutExercises as $we)
                    <div class="border-l-4 border-gray-300 pl-4 py-1">
                        <div class="flex justify-between items-center mb-2">
                            <h3 class="font-bold text-gray-900">{{ $we->exercise->name }}</h3>
                            @if($we->difficulty == 'hard')
                                <span class="text-xs bg-red-100 text-red-800 px-2 py-0.5 rounded">Hard</span>
                            @endif
                        </div>
                        <div class="space-y-1">
                            @if($we->workoutSets->count() > 0)
                                @foreach($we->workoutSets as $set)
                                    <div class="text-sm text-gray-600 flex justify-between max-w-xs">
                                        <span>Set {{ $set->set_number }}:</span>
                                        <span class="font-mono font-medium">{{ $set->weight_kg }}kg x {{ $set->reps }}</span>
                                    </div>
                                @endforeach
                            @else
                                <span class="text-xs text-gray-400">No sets logged</span>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
        
        <div class="p-6 bg-gray-50 border-t border-gray-200 flex justify-between">
            <a href="{{ route('routines.index') }}" class="text-gray-600 hover:text-gray-900 font-medium">Back to Routines</a>
            <a href="{{ route('dashboard') }}" class="text-blue-600 hover:text-blue-800 font-medium">Go to Dashboard</a>
        </div>
    </div>
</div>
@endsection
