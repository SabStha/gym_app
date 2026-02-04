<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Gym Tracker - Your Personal Training Companion</title>
    <!-- PWA Manifest & Icons -->
    <link rel="manifest" href="/manifest.webmanifest">
    <link rel="apple-touch-icon" href="/icons/icon-192.png">
    <meta name="theme-color" content="#10B981">
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,600&display=swap" rel="stylesheet" />
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="antialiased bg-gray-50 text-gray-800">
    <div class="relative min-h-screen flex flex-col items-center justify-center selection:bg-red-500 selection:text-white">
        <div class="w-full max-w-7xl mx-auto px-6 lg:px-8">
            <div class="text-center">
                <h1 class="text-4xl font-bold tracking-tight text-gray-900 sm:text-6xl mb-6">
                    Gym Tracker
                </h1>
                <p class="mt-4 text-xl text-gray-600 max-w-2xl mx-auto">
                    The ultimate companion for serious lifters. Track your workouts, analyze your progress, and break through plateaus with data-driven insights. No more paper logs, just gains.
                </p>
                
                <div class="mt-10 grid grid-cols-1 gap-8 sm:grid-cols-3 max-w-4xl mx-auto text-left">
                    <div class="p-6 bg-white rounded-lg shadow border border-gray-100">
                        <h3 class="font-bold text-lg mb-2">📋 Customizable Routines</h3>
                        <p class="text-gray-600">Build your perfect split. Push/Pull/Legs, Upper/Lower, or full body. We handle the schedule.</p>
                    </div>
                    <div class="p-6 bg-white rounded-lg shadow border border-gray-100">
                        <h3 class="font-bold text-lg mb-2">⚡ Live Tracking</h3>
                        <p class="text-gray-600">Log every set in real-time. Get smart suggestions for weight and reps based on your last session.</p>
                    </div>
                    <div class="p-6 bg-white rounded-lg shadow border border-gray-100">
                        <h3 class="font-bold text-lg mb-2">📈 Visual Progress</h3>
                        <p class="text-gray-600">See your strength go up over time with detailed charts and 1RM estimations.</p>
                    </div>
                </div>

                <div class="mt-12 flex items-center justify-center gap-x-6">
                    @auth
                        <a href="{{ url('/dashboard') }}" class="rounded-md bg-indigo-600 px-6 py-3 text-lg font-semibold text-white shadow-sm hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">
                            Go to Dashboard
                        </a>
                    @else
                        <a href="{{ route('login') }}" class="rounded-md bg-indigo-600 px-6 py-3 text-lg font-semibold text-white shadow-sm hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">
                            Log in
                        </a>
                        <a href="{{ route('register') }}" class="rounded-md bg-white px-6 py-3 text-lg font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50">
                            Register
                        </a>
                    @endauth
                </div>
            </div>
            
            <div class="mt-16 text-center text-sm text-gray-400">
                Graduation Project &copy; {{ date('Y') }}
            </div>
        </div>
    </div>
    </div>
    <!-- Service Worker Registration -->
    <script>
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', () => {
                navigator.serviceWorker.register('/sw.js')
                    .then(reg => console.log('SW registered!', reg.scope))
                    .catch(err => console.log('SW failed: ', err));
            });
        }
    </script>
</body>
</html>
