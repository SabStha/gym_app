@extends('layouts.app')

@section('content')
@php
    // Calculate Stats on the fly
    $volume = 0;
    $totalSets = 0;
    $totalRecords = 0; // Placeholder for future logic
    
    foreach($workout->workoutExercises as $we) {
        foreach($we->workoutSets as $set) {
            $volume += ($set->weight_kg * $set->reps);
            $totalSets++;
        }
    }
@endphp

<div class="min-h-screen bg-gray-900 pb-32">
    <!-- Hero Section -->
    <div class="pt-8 pb-8 px-5 bg-gradient-to-b from-emerald-900/30 via-gray-900/50 to-gray-900 border-b border-white/5">
        <div class="flex flex-col items-center text-center">
            <!-- Icon/Trophy -->
            <div class="w-16 h-16 bg-gradient-to-br from-emerald-400 to-emerald-600 rounded-2xl flex items-center justify-center shadow-lg shadow-emerald-500/20 mb-4 transform rotate-3">
                <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"></path></svg>
            </div>
            
            <h1 class="text-3xl font-black text-white italic tracking-tighter uppercase mb-1">
                Workout Complete!
            </h1>
            <p class="text-gray-400 font-medium text-sm">
                {{ $workout->routineDay->day_name ?? 'Custom Session' }} &bull; {{ $workout->finished_at ? $workout->finished_at->format('M d, H:i') : now()->format('M d, H:i') }}
            </p>
        </div>
    </div>

    <!-- Stats Grid -->
    <div class="px-5 -mt-4 mb-8">
        <div class="grid grid-cols-2 gap-3">
            <!-- Duration -->
            <div class="bg-gray-800/80 backdrop-blur-sm p-4 rounded-2xl border border-white/10 shadow-lg relative overflow-hidden group">
                <div class="absolute inset-0 bg-gradient-to-br from-white/5 to-transparent opacity-0 group-hover:opacity-100 transition-opacity"></div>
                <p class="text-[10px] text-gray-400 uppercase font-bold tracking-widest mb-1">Duration</p>
                <div class="flex items-baseline gap-1">
                    <p class="text-2xl font-black text-white">{{ $workout->duration_minutes ?? 0 }}</p>
                    <span class="text-xs font-bold text-gray-500">min</span>
                </div>
            </div>

            <!-- Volume -->
            <div class="bg-gray-800/80 backdrop-blur-sm p-4 rounded-2xl border border-white/10 shadow-lg relative overflow-hidden group">
                <div class="absolute inset-0 bg-gradient-to-br from-white/5 to-transparent opacity-0 group-hover:opacity-100 transition-opacity"></div>
                <p class="text-[10px] text-gray-400 uppercase font-bold tracking-widest mb-1">Volume</p>
                <div class="flex items-baseline gap-1">
                    <p class="text-2xl font-black text-white">{{ number_format($volume) }}</p>
                    <span class="text-xs font-bold text-gray-500">kg</span>
                </div>
            </div>

            <!-- Sets & Exercises Row -->
            <div class="col-span-2 bg-gray-800/80 backdrop-blur-sm p-4 rounded-2xl border border-white/10 shadow-lg flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-full bg-gray-700 flex items-center justify-center text-gray-400">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
                    </div>
                    <div>
                        <p class="text-lg font-black text-white leading-none">{{ $workout->workoutExercises->count() }}</p>
                        <p class="text-[10px] text-gray-500 uppercase font-bold tracking-wider">Exercises</p>
                    </div>
                </div>
                <div class="h-8 w-px bg-white/10"></div>
                <div class="flex items-center gap-3 text-right">
                    <div>
                        <p class="text-lg font-black text-white leading-none">{{ $totalSets }}</p>
                        <p class="text-[10px] text-gray-500 uppercase font-bold tracking-wider">Total Sets</p>
                    </div>
                    <div class="w-10 h-10 rounded-full bg-gray-700 flex items-center justify-center text-gray-400">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"></path></svg>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Session Log -->
    <div class="px-5 space-y-4">
        <div class="flex items-center justify-between px-1">
            <h3 class="text-xs font-bold text-gray-500 uppercase tracking-widest">Session Log</h3>
            <span class="text-[10px] font-bold text-emerald-500 bg-emerald-500/10 px-2 py-1 rounded-full border border-emerald-500/20">Saved in History</span>
        </div>

        @foreach($workout->workoutExercises as $we)
        <div class="bg-gray-800 rounded-2xl overflow-hidden border border-white/5 shadow-md">
            <!-- Card Header -->
            <div class="px-4 py-3 bg-white/5 border-b border-white/5 flex items-center gap-3">
                <div class="w-10 h-10 bg-gray-700 rounded-lg flex items-center justify-center text-gray-300 font-black text-sm ring-1 ring-white/10 shadow-inner">
                    {{ substr($we->exercise->name, 0, 1) }}
                </div>
                <div class="flex-1 min-w-0">
                    <h4 class="font-bold text-white text-sm truncate">{{ $we->exercise->name }}</h4>
                    <p class="text-[10px] text-gray-400 uppercase tracking-wide font-bold">
                        {{ $we->workoutSets->count() }} Sets @if($we->status == 'skipped') &bull; <span class="text-amber-500">Skipped</span> @endif
                    </p>
                </div>
            </div>
            
            <!-- Sets List -->
            @if($we->workoutSets->count() > 0)
            <div class="p-2 space-y-0.5">
                @foreach($we->workoutSets as $set)
                <div class="flex items-center justify-between px-3 py-2 rounded-lg {{ $loop->even ? 'bg-white/5' : '' }}">
                    <div class="flex items-center gap-3">
                        <span class="text-[10px] font-bold text-gray-500 w-6">SET {{ $set->set_number }}</span>
                        <div class="flex items-baseline gap-0.5">
                            <span class="text-sm font-bold text-white">{{ $set->weight_kg }}</span>
                            <span class="text-[10px] font-bold text-gray-500">kg</span>
                        </div>
                    </div>
                    <div class="flex items-baseline gap-0.5">
                        <span class="text-sm font-bold text-white">{{ $set->reps }}</span>
                        <span class="text-[10px] font-bold text-gray-500">reps</span>
                    </div>
                </div>
                @endforeach
            </div>
            @elseif($we->status == 'skipped')
                <div class="p-4 text-center">
                    <p class="text-xs text-amber-500 font-bold uppercase tracking-wider">Exercise Skipped</p>
                </div>
            @else
                <div class="p-4 text-center">
                     <p class="text-xs text-gray-500 italic">No sets recorded</p>
                </div>
            @endif
        </div>
        @endforeach
    </div>

    <!-- Bottom Actions -->
    <div class="fixed bottom-0 left-0 right-0 p-6 bg-gradient-to-t from-gray-900 via-gray-900 to-transparent z-50 flex flex-col gap-3 pb-8">
        <a href="{{ route('dashboard') }}" 
           class="w-full py-4 bg-emerald-500 hover:bg-emerald-400 text-white font-black uppercase tracking-widest text-center rounded-xl shadow-lg shadow-emerald-500/20 active:scale-[0.98] transition-all flex items-center justify-center gap-2">
            Back to Dashboard
        </a>
        
        <div class="grid grid-cols-2 gap-3">
            <a href="{{ route('workouts.create') }}" class="py-3.5 bg-gray-800 text-gray-300 font-bold uppercase tracking-widest text-center rounded-xl border border-white/5 active:scale-[0.98] transition-all flex items-center justify-center gap-2 hover:bg-gray-700 text-xs">
                Start Another
            </a>
            <button onclick="alert('Sharing coming soon!')" class="py-3.5 bg-gray-800 text-gray-300 font-bold uppercase tracking-widest text-center rounded-xl border border-white/5 active:scale-[0.98] transition-all flex items-center justify-center gap-2 hover:bg-gray-700 text-xs">
                Share Stats
            </button>
        </div>
    </div>
</div>
@endsection
