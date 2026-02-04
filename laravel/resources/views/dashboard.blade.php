<x-app-layout>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
        
        <!-- Header -->
        <header class="mb-6 flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Dashboard</h1>
                <p class="text-gray-500 text-sm">Track your training and progress</p>
            </div>
        </header>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            
            <!-- Left Column: Stats & Actions (2 cols wide on desktop) -->
            <div class="space-y-6 lg:col-span-2">
                
                <!-- Stats Grid -->
                <section class="grid grid-cols-3 gap-3 md:gap-6">
                    <!-- Workouts -->
                    <div class="bg-white rounded-xl p-3 md:p-6 shadow-sm border border-gray-100 flex flex-col justify-center items-center md:items-start text-center md:text-left">
                        <span class="text-xs md:text-sm text-gray-500 font-medium uppercase tracking-wide">Workouts</span>
                        <span class="text-xl md:text-3xl font-bold text-gray-900 mt-1">{{ $workoutsThisWeek }}</span>
                        <span class="text-[10px] md:text-xs text-emerald-600 font-medium bg-emerald-50 px-1.5 py-0.5 rounded-full mt-1">This Week</span>
                    </div>

                    <!-- Volume -->
                    <div class="bg-white rounded-xl p-3 md:p-6 shadow-sm border border-gray-100 flex flex-col justify-center items-center md:items-start text-center md:text-left">
                        <span class="text-xs md:text-sm text-gray-500 font-medium uppercase tracking-wide">Volume</span>
                        <span class="text-xl md:text-3xl font-bold text-gray-900 mt-1">{{ \Illuminate\Support\Str::replace('k', 'K', number_format($totalVolume, 0) . 'k') }}</span>
                        <span class="text-[10px] md:text-xs text-gray-400 font-medium mt-1">kg Total</span>
                    </div>

                    <!-- Top Exercise -->
                    <div class="bg-white rounded-xl p-3 md:p-6 shadow-sm border border-gray-100 flex flex-col justify-center items-center md:items-start text-center md:text-left overflow-hidden">
                        <span class="text-xs md:text-sm text-gray-500 font-medium uppercase tracking-wide">Top Lift</span>
                        <div class="h-10 md:h-9 flex items-center justify-center md:justify-start w-full mt-1">
                            <span class="text-sm md:text-xl font-bold text-gray-900 leading-tight line-clamp-2" title="{{ $topExercise }}">
                                {{ $topExercise != 'N/A' ? $topExercise : '-' }}
                            </span>
                        </div>
                        <span class="text-[10px] md:text-xs text-gray-400 font-medium mt-1">Most Sets</span>
                    </div>
                </section>

                <!-- Primary Action -->
                <section>
                    @if(isset($currentWorkout) && $currentWorkout)
                        <div class="flex flex-col gap-3">
                            <a href="{{ route('workouts.show', $currentWorkout) }}" class="flex-grow flex items-center justify-center gap-3 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl h-16 shadow-md transition-all font-bold text-lg">
                                <span class="relative flex h-3 w-3">
                                  <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                                  <span class="relative inline-flex rounded-full h-3 w-3 bg-white"></span>
                                </span>
                                Resume Workout
                            </a>
                            <div class="text-center">
                                <a href="{{ route('workouts.create') }}" class="text-sm font-medium text-gray-500 hover:text-emerald-600">
                                    or Start New Session
                                </a>
                            </div>
                        </div>
                    @else
                        <a href="{{ route('workouts.create') }}" class="flex items-center justify-center gap-2 w-full bg-gray-900 hover:bg-black text-white rounded-xl h-14 shadow-md transition-all font-bold text-lg group">
                            <div class="bg-gray-800 p-1 rounded-full group-hover:bg-gray-700 transition-colors">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd" />
                                </svg>
                            </div>
                            Start Workout
                        </a>
                    @endif
                </section>
            </div>

            <!-- Right Column: Content (Routine + Recent) -->
            <div class="space-y-6">
                
                <!-- Active Routine -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                    <div class="px-4 py-3 border-b border-gray-100 flex justify-between items-center bg-gray-50/50">
                        <h3 class="font-semibold text-gray-900">Active Routine</h3>
                        <a href="{{ route('routines.index') }}" class="text-xs font-semibold text-emerald-600 hover:text-emerald-700">Manage</a>
                    </div>
                    
                    <div class="p-4">
                        @if($activeRoutine)
                            <div class="mb-4">
                                <h4 class="font-bold text-gray-800 text-lg">{{ $activeRoutine->title }}</h4>
                                <p class="text-xs text-gray-500">{{ $activeRoutine->routineDays->count() }} day split</p>
                            </div>
                            
                            <a href="{{ route('workouts.create') }}" class="block w-full py-2 bg-emerald-50 text-emerald-700 font-semibold rounded-lg text-center hover:bg-emerald-100 transition-colors mb-4 text-sm">
                                Start Next Session
                            </a>

                            <div class="space-y-2">
                                @foreach($activeRoutine->routineDays->take(3) as $day)
                                    <div class="flex items-center gap-3 text-sm">
                                        <div class="h-6 w-6 rounded-full bg-gray-100 flex items-center justify-center text-xs font-bold text-gray-500 text-[10px]">
                                            {{ $loop->iteration }}
                                        </div>
                                        <span class="text-gray-700 truncate">{{ $day->day_name }}</span>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="text-center py-4">
                                <p class="text-sm text-gray-400 mb-2">No routine active</p>
                                <a href="{{ route('routines.create') }}" class="text-sm font-semibold text-emerald-600">Create one &rarr;</a>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Recent Activity -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                    <div class="px-4 py-3 border-b border-gray-100 flex justify-between items-center bg-gray-50/50">
                        <h3 class="font-semibold text-gray-900">Recent Activity</h3>
                        <a href="{{ route('history.index') }}" class="text-xs font-semibold text-emerald-600 hover:text-emerald-700">History</a>
                    </div>

                    @if($lastWorkout)
                        <div class="p-4">
                            <div class="flex justify-between items-start mb-3">
                                <div>
                                    <h4 class="font-bold text-gray-800 text-sm">{{ $lastWorkout->routineDay->day_name ?? 'Free Workout' }}</h4>
                                    <p class="text-xs text-gray-500">{{ $lastWorkout->workout_date->format('M j, Y') }}</p>
                                </div>
                                <span class="bg-emerald-100 text-emerald-800 text-[10px] font-bold px-2 py-0.5 rounded-full uppercase tracking-wide">
                                    Completed
                                </span>
                            </div>

                            <div class="grid grid-cols-3 gap-2 text-center bg-gray-50 rounded-lg p-2">
                                <div>
                                    <span class="block font-bold text-gray-900 text-sm">{{ $lastWorkout->workoutExercises->count() }}</span>
                                    <span class="text-[10px] text-gray-500 uppercase">Exer</span>
                                </div>
                                <div class="border-l border-gray-200">
                                    <span class="block font-bold text-gray-900 text-sm">{{ number_format($lastWorkout->workoutExercises->sum(fn($we) => $we->workoutSets->sum(fn($ws) => $ws->weight_kg * $ws->reps)), 0) }}</span>
                                    <span class="text-[10px] text-gray-500 uppercase">Vol</span>
                                </div>
                                <div class="border-l border-gray-200">
                                    <span class="block font-bold text-gray-900 text-sm">{{ $lastWorkout->duration_minutes }}</span>
                                    <span class="text-[10px] text-gray-500 uppercase">Min</span>
                                </div>
                            </div>
                            
                            <div class="mt-3 text-right">
                                <a href="{{ route('history.show', $lastWorkout) }}" class="text-xs font-semibold text-gray-500 hover:text-emerald-600 transition-colors inline-block">
                                    View Details &rarr;
                                </a>
                            </div>
                        </div>
                    @else
                         <div class="p-6 text-center text-sm text-gray-400">
                             No workouts yet.
                         </div>
                    @endif
                </div>

            </div>

        </div>
    </div>
</x-app-layout>


