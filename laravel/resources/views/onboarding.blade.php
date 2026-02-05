<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=0">
    <meta name="theme-color" content="#ffffff">
    <title>Welcome - {{ config('app.name') }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        /* Smooth transitions */
        .fade-enter { opacity: 0; transform: translateY(10px); }
        .fade-enter-active { opacity: 1; transform: translateY(0); transition: opacity 0.4s ease-out, transform 0.4s ease-out; }
    </style>
</head>
<body class="bg-white text-gray-900 min-h-screen flex flex-col justify-between font-sans selection:bg-emerald-100">

    <!-- Content Area -->
    <div class="flex-grow flex flex-col items-center justify-center px-8 pt-12 pb-6 text-center max-w-md mx-auto fade-enter-active">
        
        <!-- Illustration Placeholder -->
        <div class="w-64 h-64 bg-gray-50 rounded-full flex items-center justify-center mb-10 shadow-inner">
            @if($step == 1)
                <span class="text-6xl">👋</span>
            @elseif($step == 2)
                <span class="text-6xl">📊</span>
            @else
                <span class="text-6xl">🚀</span>
            @endif
        </div>

        <!-- Text Content -->
        <h1 class="text-3xl font-bold tracking-tight mb-4 text-gray-900 leading-tight">
            @if($step == 1)
                Welcome to<br><span class="text-emerald-600">Gym Companion</span>
            @elseif($step == 2)
                Track Progress<br><span class="text-emerald-600">Effortlessly</span>
            @else
                Ready to Crush<br><span class="text-emerald-600">Your Goals?</span>
            @endif
        </h1>

        <p class="text-gray-500 text-lg leading-relaxed">
            @if($step == 1)
                The ultimate tool for serious lifters. Track metrics, manage routines, and visualize your strength gains.
            @elseif($step == 2)
                Log sets in seconds, get smart weight suggestions, and never plateau again with advanced analytics.
            @else
                Join thousands of athletes pushing their limits. Start your journey today.
            @endif
        </p>

    </div>

    <!-- Bottom Action Area -->
    <div class="px-8 pb-12 w-full max-w-md mx-auto">
        <!-- Progress Dots -->
        <div class="flex justify-center gap-2 mb-8">
            <div class="h-2 w-2 rounded-full {{ $step == 1 ? 'bg-emerald-600 w-6' : 'bg-gray-200' }} transition-all duration-300"></div>
            <div class="h-2 w-2 rounded-full {{ $step == 2 ? 'bg-emerald-600 w-6' : 'bg-gray-200' }} transition-all duration-300"></div>
            <div class="h-2 w-2 rounded-full {{ $step == 3 ? 'bg-emerald-600 w-6' : 'bg-gray-200' }} transition-all duration-300"></div>
        </div>

        <!-- Buttons -->
        @if($step < 3)
            <a href="/onboarding/{{ $step + 1 }}" class="block w-full bg-emerald-600 text-white font-bold text-center py-4 rounded-2xl shadow-lg shadow-emerald-200 hover:bg-emerald-700 active:scale-95 transition-all text-lg">
                Next
            </a>
            <div class="mt-4 text-center">
                 <a href="/onboarding/3" class="text-sm font-semibold text-gray-400 hover:text-gray-600">Skip</a>
            </div>
        @else
            <div class="space-y-3">
                <a href="{{ route('login') }}" onclick="finishOnboarding()" class="block w-full bg-emerald-600 text-white font-bold text-center py-4 rounded-2xl shadow-lg shadow-emerald-200 hover:bg-emerald-700 active:scale-95 transition-all text-lg">
                    Log In
                </a>
                <a href="{{ route('register') }}" onclick="finishOnboarding()" class="block w-full bg-emerald-50 text-emerald-700 font-bold text-center py-4 rounded-2xl hover:bg-emerald-100 active:scale-95 transition-all text-lg border border-emerald-100">
                    Create Account
                </a>
            </div>
        @endif
    </div>

    <script>
        function finishOnboarding() {
            localStorage.setItem('onboarding_seen', 'true');
        }
    </script>
</body>
</html>
