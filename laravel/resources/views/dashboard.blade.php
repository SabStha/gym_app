<x-app-layout>
    <div class="pb-40 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 space-y-8">
        
        <!-- 1. Context Header -->
        <header>
            <p class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-1">{{ now()->format('l, M j') }}</p>
            <h1 class="text-3xl font-black text-gray-900 leading-tight">
                @if($currentWorkout)
                    Resuming Session...
                @elseif($nextSession)
                    Time to {{ \Illuminate\Support\Str::title($nextSession->day_name) }}
                @else
                    Let's Train
                @endif
            </h1>
        </header>

        <!-- 2. Launchpad (Primary Action) -->
        <section>
            @if($currentWorkout)
                <!-- Resume Card -->
                <div class="relative overflow-hidden bg-gray-900 rounded-3xl p-6 text-white shadow-2xl shadow-emerald-500/20">
                    <div class="relative z-10">
                        <div class="flex items-center gap-2 mb-4">
                            <span class="relative flex h-3 w-3">
                              <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                              <span class="relative inline-flex rounded-full h-3 w-3 bg-emerald-500"></span>
                            </span>
                            <span class="text-sm font-bold text-emerald-400 uppercase tracking-wider">In Progress</span>
                        </div>
                        <h2 class="text-2xl font-black mb-1 capitalize">{{ $currentWorkout->routineDay->day_name ?? 'Free Workout' }}</h2>
                        <p class="text-gray-400 text-sm mb-8">Started {{ $currentWorkout->started_at->diffForHumans() }}</p>
                        
                        <a href="{{ route('workouts.show', $currentWorkout) }}" class="block w-full py-4 bg-emerald-500 hover:bg-emerald-400 text-white font-black text-center rounded-xl uppercase tracking-widest transition-all active:scale-[0.98]">
                            Resume Workout
                        </a>
                    </div>
                    <!-- Decorative BG -->
                    <div class="absolute top-0 right-0 -mt-10 -mr-10 w-64 h-64 bg-emerald-500/10 rounded-full blur-3xl"></div>
                </div>

            @elseif($nextSession)
                <!-- Next Session Card -->
                <div class="relative overflow-hidden bg-gradient-to-br from-gray-900 to-gray-800 rounded-3xl p-6 text-white shadow-xl">
                    <div class="relative z-10">
                        <p class="text-xs font-bold text-gray-500 uppercase tracking-widest mb-2">Next Up</p>
                        <h2 class="text-3xl font-black mb-2 capitalize">{{ $nextSession->day_name }}</h2>
                        <div class="flex items-center gap-3 text-sm text-gray-400 mb-8">
                            <span>{{ $nextSession->dayExercises->count() }} Exercises</span>
                            <span>&bull;</span>
                            <span>Est. 45-60 min</span>
                        </div>
                        
                        <form action="{{ route('workouts.store') }}" method="POST">
                            @csrf
                            <input type="hidden" name="routine_day_id" value="{{ $nextSession->id }}">
                            <button type="submit" class="w-full py-4 bg-white text-gray-900 font-black text-center rounded-xl uppercase tracking-widest hover:bg-gray-100 transition-all active:scale-[0.98] flex items-center justify-center gap-2">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                Start Workout
                            </button>
                        </form>
                    </div>
                     <!-- Decorative Elements -->
                    <div class="absolute top-0 right-0 w-32 h-32 bg-white/5 rounded-full blur-2xl -mr-10 -mt-10"></div>
                </div>

            @else
                <!-- Empty State / Free Workout -->
                 <div class="bg-gradient-to-br from-gray-900 to-gray-800 rounded-3xl p-8 text-white text-center shadow-lg">
                    <h2 class="text-2xl font-black mb-2">No Routine Active</h2>
                    <p class="text-gray-400 mb-6">Create a routine or start a freestyle session.</p>
                    <a href="{{ route('workouts.create') }}" class="inline-block px-8 py-3 bg-white text-gray-900 font-bold rounded-xl uppercase tracking-wide hover:bg-gray-100 transition-colors">
                        Free Workout
                    </a>
                </div>
            @endif
        </section>

        <!-- 3. Stats Strip (Compact) -->
        <section class="grid grid-cols-3 gap-2">
            <div class="bg-white p-3 rounded-xl border border-gray-100 text-center shadow-sm">
                <span class="block text-xl font-black text-gray-900">{{ $workoutsThisWeek }}</span>
                <span class="text-[10px] uppercase font-bold text-gray-400">Workouts</span>
            </div>
            <div class="bg-white p-3 rounded-xl border border-gray-100 text-center shadow-sm">
                <span class="block text-xl font-black text-gray-900">{{ number_format($totalVolume / 1000, 1) }}k</span>
                <span class="text-[10px] uppercase font-bold text-gray-400">Kg Vol</span>
            </div>
            <div class="bg-white p-3 rounded-xl border border-gray-100 text-center shadow-sm">
                <span class="block text-xl font-black text-gray-900 truncate px-1">{{ \Illuminate\Support\Str::limit($topExercise, 6) }}</span>
                <span class="text-[10px] uppercase font-bold text-gray-400">Best Lift</span>
            </div>
        </section>

        <!-- 4. Upcoming & Recent (Grid) -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
            
            <!-- Upcoming Schedule -->
            <section>
                <div class="flex justify-between items-end mb-4 px-1">
                    <h3 class="font-bold text-gray-900">Coming Up</h3>
                    <a href="{{ route('routines.index') }}" class="text-xs font-bold text-emerald-600">View Routine</a>
                </div>
                
                @if($upcomingSessions->isNotEmpty())
                    <div class="relative space-y-0 pl-4 border-l-2 border-gray-100 ml-2">
                        @foreach($upcomingSessions as $day)
                            <div class="relative pl-6 py-3 hover:bg-gray-50/50 rounded-r-xl transition-colors">
                                <!-- Timeline Dot -->
                                <div class="absolute -left-[9px] top-1/2 -translate-y-1/2 w-4 h-4 rounded-full border-2 border-white bg-gray-200"></div>
                                
                                <div>
                                    <h4 class="font-bold text-gray-900 capitalize">{{ $day->day_name }}</h4>
                                    <p class="text-xs text-gray-500">{{ $day->dayExercises->count() }} Exercises</p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center p-6 bg-gray-50 rounded-2xl border border-gray-100 border-dashed">
                        <p class="text-sm text-gray-400">No upcoming sessions scheduled.</p>
                    </div>
                @endif
            </section>

            <!-- Recent Activity (Collapsed) -->
            <section>
                 <div class="flex justify-between items-end mb-4 px-1">
                    <h3 class="font-bold text-gray-900">Last Session</h3>
                    <a href="{{ route('history.index') }}" class="text-xs font-bold text-emerald-600">History</a>
                </div>

                @if($lastWorkout)
                    <a href="{{ route('history.show', $lastWorkout) }}" class="block bg-white p-5 rounded-2xl border border-gray-100 shadow-sm hover:border-emerald-500/30 hover:shadow-emerald-500/5 transition-all group">
                         <div class="flex justify-between items-start">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-full bg-emerald-100 text-emerald-600 flex items-center justify-center">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                </div>
                                <div>
                                    <span class="block text-[10px] font-bold text-gray-400 uppercase tracking-wider">{{ $lastWorkout->workout_date->format('M j') }}</span>
                                    <h4 class="font-bold text-gray-900 text-lg group-hover:text-emerald-600 transition-colors capitalize">{{ $lastWorkout->routineDay->day_name ?? 'Freestyle' }}</h4>
                                </div>
                            </div>
                            <div class="text-right">
                                <span class="block font-black text-gray-900">{{ $lastWorkout->workoutExercises->count() }}</span>
                                <span class="text-[10px] text-gray-400 uppercase font-bold">Exercises</span>
                            </div>
                        </div>
                    </a>
                @else
                    <div class="text-center p-6 bg-gray-50 rounded-2xl border border-gray-100 border-dashed">
                        <p class="text-sm text-gray-400">No recent activity.</p>
                    </div>
                @endif
            </section>
        </div>
    </div>

    <!-- Sticky Footer CTA (Mobile Only - if not resuming) -->
    @if(!$currentWorkout && $nextSession)
        <div class="fixed left-0 right-0 p-4 bg-gradient-to-t from-white via-white/95 to-transparent md:hidden z-30 pointer-events-none flex justify-center pb-6" style="bottom: calc(4rem + env(safe-area-inset-bottom));">
            <form action="{{ route('workouts.store') }}" method="POST" class="w-full max-w-sm pointer-events-auto">
                @csrf
                <input type="hidden" name="routine_day_id" value="{{ $nextSession->id }}">
                <button type="submit" class="w-full py-4 bg-emerald-500 hover:bg-emerald-600 text-white font-black rounded-2xl uppercase tracking-widest shadow-xl shadow-emerald-500/30 flex items-center justify-center gap-2 transition-all active:scale-[0.98]">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    Start {{ $nextSession->day_name }}
                </button>
            </form>
        </div>
    @endif
</x-app-layout>


