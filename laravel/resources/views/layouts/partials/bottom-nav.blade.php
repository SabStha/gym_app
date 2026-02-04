@php
    // Check for active workout to determing target of Workout Tab
    $activeWorkoutId = null;
    if(auth()->check()) {
        $activeWorkout = auth()->user()->workouts()
            ->where('status', 'in_progress')
            ->select('id')
            ->latest('started_at')
            ->first();
        $activeWorkoutId = $activeWorkout ? $activeWorkout->id : null;
    }
@endphp

<nav class="fixed bottom-0 left-0 w-full bg-white border-t border-gray-200 lg:hidden z-50 pb-[env(safe-area-inset-bottom)]">
    <div class="flex justify-around items-center h-16">
        
        <!-- Dashboard -->
        <a href="{{ route('dashboard') }}" class="flex flex-col items-center justify-center w-full h-full space-y-1 {{ request()->routeIs('dashboard') ? 'text-emerald-600' : 'text-gray-500 hover:text-gray-700' }}">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 {{ request()->routeIs('dashboard') ? 'stroke-2' : '' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
            </svg>
            <span class="text-[10px] font-medium {{ request()->routeIs('dashboard') ? 'font-bold' : '' }}">Home</span>
        </a>

        <!-- Workout (Smart Link) -->
        <a href="{{ $activeWorkoutId ? route('workouts.show', $activeWorkoutId) : route('workouts.create') }}" class="flex flex-col items-center justify-center w-full h-full space-y-1 {{ (request()->routeIs('workouts.create') || request()->routeIs('workouts.show')) ? 'text-emerald-600' : 'text-gray-500 hover:text-gray-700' }}">
            <div class="{{ (request()->routeIs('workouts.create') || request()->routeIs('workouts.show')) ? 'bg-emerald-600 text-white border-gray-100' : 'bg-gray-100 text-gray-500' }} rounded-full p-2 -mt-6 shadow-md border-4 border-white transition-colors">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
            </div>
            <span class="text-[10px] font-medium {{ (request()->routeIs('workouts.create') || request()->routeIs('workouts.show')) ? 'font-bold' : '' }}">Workout</span>
        </a>

        <!-- Progress -->
        <a href="{{ route('progress.index') }}" class="flex flex-col items-center justify-center w-full h-full space-y-1 {{ request()->routeIs('progress.*') ? 'text-emerald-600' : 'text-gray-500 hover:text-gray-700' }}">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 {{ request()->routeIs('progress.*') ? 'stroke-2' : '' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 002 2h2a2 2 0 002-2z" />
            </svg>
            <span class="text-[10px] font-medium {{ request()->routeIs('progress.*') ? 'font-bold' : '' }}">Progress</span>
        </a>

        <!-- Profile -->
        <a href="{{ route('profile.edit') }}" class="flex flex-col items-center justify-center w-full h-full space-y-1 {{ request()->routeIs('profile.*') ? 'text-emerald-600' : 'text-gray-500 hover:text-gray-700' }}">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 {{ request()->routeIs('profile.*') ? 'stroke-2' : '' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
            </svg>
            <span class="text-[10px] font-medium {{ request()->routeIs('profile.*') ? 'font-bold' : '' }}">Profile</span>
        </a>
    </div>
</nav>
