@extends('layouts.app')

@section('content')
<div class="h-[calc(100vh-theme(spacing.20))] flex flex-col p-6 gap-6 relative overflow-hidden">
    <!-- Background Gradient Ambience -->
    <div class="absolute top-0 left-0 w-full h-full bg-gradient-to-br from-gray-50 to-gray-100 dark:from-gray-900 dark:to-gray-800 -z-20"></div>
    <div class="absolute top-[-20%] right-[-20%] w-[80vw] h-[80vw] bg-emerald-400/20 rounded-full blur-[100px] -z-10"></div>
    <div class="absolute bottom-[-10%] left-[-10%] w-[60vw] h-[60vw] bg-blue-400/10 rounded-full blur-[80px] -z-10"></div>

    <!-- Header -->
    <div class="text-center py-4">
        <h1 class="text-3xl font-black text-gray-900 dark:text-white tracking-tight">Focus</h1>
        <p class="text-base text-gray-500 font-medium">What are we tracking today?</p>
    </div>

    <!-- Action Buttons Container -->
    <div class="flex-1 flex flex-col gap-6 justify-center pb-12">
        
        <!-- 1. Workout Button -->
        <a href="{{ route('routines.index') }}" 
           class="group relative flex-1 flex flex-col items-center justify-center bg-white dark:bg-gray-800/80 backdrop-blur-xl rounded-[2.5rem] shadow-2xl border border-white/20 dark:border-gray-700 hover:scale-[1.02] active:scale-95 transition-all duration-300 overflow-hidden">
            
            <!-- Icon Background Glo -->
            <div class="absolute inset-0 bg-gradient-to-tr from-emerald-500/10 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-500"></div>
            
            <div class="relative z-10 bg-emerald-100 dark:bg-emerald-900/30 p-6 rounded-full mb-4 group-hover:bg-emerald-500 group-hover:text-white transition-colors duration-300 text-emerald-600 dark:text-emerald-400">
                <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg> 
                <!-- Changed icon to 'Globe/Activity' style, maybe Dumbbell: M4 8V6a6 6 0 1112 0v2h2.009... let's use a simpler distinct one -->
                <div class="absolute inset-0 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity">
                     <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
                </div>
            </div>
            
            <h2 class="relative z-10 text-2xl font-black text-gray-900 dark:text-white">Workout Log</h2>
            <p class="relative z-10 text-sm text-gray-500 font-medium mt-1">
                {{ $activeWorkoutId ? 'Continue Session' : 'Start Training' }}
            </p>
        </a>

        <!-- 2. Food Button -->
        <a href="{{ route('nutrition.index') }}" 
           class="group relative flex-1 flex flex-col items-center justify-center bg-white dark:bg-gray-800/80 backdrop-blur-xl rounded-[2.5rem] shadow-2xl border border-white/20 dark:border-gray-700 hover:scale-[1.02] active:scale-95 transition-all duration-300 overflow-hidden">
            
            <!-- Icon Background Glo -->
            <div class="absolute inset-0 bg-gradient-to-tr from-orange-500/10 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-500"></div>
            
            <div class="relative z-10 bg-orange-100 dark:bg-orange-900/30 p-6 rounded-full mb-4 group-hover:bg-orange-500 group-hover:text-white transition-colors duration-300 text-orange-600 dark:text-orange-400">
                <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path></svg>
                <!-- Maybe Apple icon? -->
                <div class="absolute inset-0 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity">
                    <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                </div>
            </div>
            
            <h2 class="relative z-10 text-2xl font-black text-gray-900 dark:text-white">Food Log</h2>
            <p class="relative z-10 text-sm text-gray-500 font-medium mt-1">Track Calories & Macros</p>
        </a>
        
    </div>
</div>
@endsection
