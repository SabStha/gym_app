<x-app-layout>
    <div class="max-w-md mx-auto px-4 py-8 min-h-screen flex flex-col">
        
        <!-- Header -->
        <header class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900">Start Workout</h1>
            <p class="text-gray-500 mt-2">Select a routine day to begin your session</p>
        </header>

        <!-- Active Routine Card -->
        <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100 mb-8 relative overflow-hidden">
            <div class="absolute top-0 right-0 w-24 h-24 bg-emerald-50 rounded-bl-full -mr-4 -mt-4 z-0"></div>
            
            <div class="relative z-10">
                <div class="flex justify-between items-center mb-1">
                    <span class="text-xs font-semibold text-emerald-600 uppercase tracking-wider bg-emerald-50 px-2 py-0.5 rounded-full">Active Routine</span>
                    <a href="{{ route('routines.index') }}" class="text-sm font-medium text-gray-400 hover:text-emerald-600 transition-colors">Change</a>
                </div>
                
                <h2 class="text-2xl font-bold text-gray-900 mt-2">{{ $activeRoutine->title }}</h2>
                <p class="text-gray-500 text-sm mt-1">
                    {{ $activeRoutine->routineDays->count() }}-day split
                </p>
            </div>
        </div>

        <!-- Form -->
        <form action="{{ route('workouts.start') }}" method="POST" class="flex-grow flex flex-col justify-between">
            @csrf
            
            <!-- Day Selection -->
            <div>
                <label class="block text-sm font-bold text-gray-900 mb-4 ml-1">Select Day</label>
                <div class="space-y-3">
                    @foreach($activeRoutine->routineDays as $day)
                        <div class="relative">
                            <input 
                                type="radio" 
                                name="routine_day_id" 
                                id="day_{{ $day->id }}" 
                                value="{{ $day->id }}" 
                                class="peer hidden" 
                                required
                            >
                            <label 
                                for="day_{{ $day->id }}" 
                                class="flex items-center justify-between p-4 bg-white border-2 border-transparent rounded-xl cursor-pointer shadow-sm 
                                       peer-checked:bg-emerald-600 peer-checked:text-white peer-checked:shadow-emerald-200 peer-checked:shadow-lg
                                       hover:bg-gray-50 peer-checked:hover:bg-emerald-700
                                       transition-all duration-200 ease-in-out group"
                            >
                                <div class="flex items-center gap-4">
                                    <div class="h-8 w-8 rounded-full bg-gray-100 flex items-center justify-center text-sm font-bold text-gray-500 
                                                group-hover:bg-white group-peer-checked:bg-white/20 group-peer-checked:text-white transition-colors">
                                        {{ $loop->iteration }}
                                    </div>
                                    <div>
                                        <span class="block font-bold text-lg leading-tight">{{ $day->day_name }}</span>
                                        <span class="text-xs text-gray-400 group-peer-checked:text-emerald-100">{{ $day->dayExercises->count() }} Exercises</span>
                                    </div>
                                </div>
                                <div class="text-gray-300 group-peer-checked:text-white">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                </div>
                            </label>
                            
                            <!-- Unchecked border state (simulated since peer-checked border is tricky with transparent default) -->
                            <div class="absolute inset-0 border border-gray-100 rounded-xl pointer-events-none peer-checked:hidden"></div>
                        </div>
                    @endforeach
                </div>
                @error('routine_day_id')
                    <p class="text-red-500 text-sm mt-3 ml-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Submit Button (Fixed at bottom on desktop inside form flow, or just sticky if needed, keeping simple flow for now) -->
            <div class="mt-8 mb-4">
                <button 
                    type="submit" 
                    class="w-full flex items-center justify-center h-14 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-xl shadow-lg shadow-emerald-200 transition-all transform active:scale-95 text-lg"
                >
                    Start Session
                </button>
            </div>
        </form>
    </div>
</x-app-layout>
