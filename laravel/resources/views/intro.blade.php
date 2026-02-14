<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=0">
    <meta name="theme-color" content="#10B981">
    
    <!-- PWA Manifest & Icons -->
    <!-- PWA Manifest & Icons -->
    <link rel="manifest" href="/manifest.webmanifest">
    <link rel="apple-touch-icon" href="/icons/icon-192.png">
    
    <!-- iOS PWA Splashes & Status Bar -->
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <link rel="apple-touch-startup-image" href="/icons/icon-512.png">

    <title>{{ config('app.name', 'Gym Companion') }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        /* Hide scrollbars for splash */
        body { overflow: hidden; }
    </style>
</head>
<body class="bg-emerald-600 flex items-center justify-center min-h-screen">
    
    <div class="text-center animate-pulse">
        <!-- Logo Icon -->
        <div class="bg-white rounded-3xl h-24 w-24 flex items-center justify-center mx-auto mb-6 shadow-xl">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-12 h-12 text-emerald-600">
                <path fill-rule="evenodd" d="M12.378 1.602a.75.75 0 01.39.63l.562 9.492 5.06-2.53a.75.75 0 011.071.848l-2.025 8.098a.75.75 0 01-1.353-.195l-.75-3.001-2.686 1.343.375 1.5a.75.75 0 01-1.455.364l-.375-1.5-2.686 1.343.75 3.002a.75.75 0 01-1.353.194L5.53 11.242a.75.75 0 011.07-.848l5.06 2.53.562-9.493a.75.75 0 01.156-.429z" clip-rule="evenodd" />
            </svg>
        </div>
        
        <!-- App Name -->
        <h1 class="text-3xl font-bold text-white tracking-tight">Gym Companion</h1>
        <p class="text-emerald-100 text-sm mt-2 font-medium">Your personal trainer</p>
    </div>

    <script>
        class SplashController {
            constructor() {
                this.timerId = null;
                // Check auth status rendered from Blade
                this.isAuthenticated = {{ auth()->check() ? 'true' : 'false' }};
                this.onboardingSeen = localStorage.getItem('onboarding_seen') === 'true';
                
                // Determine destination
                if (this.isAuthenticated) {
                    this.redirectUrl = "{{ route('hub') }}";
                } else {
                    this.redirectUrl = "/onboarding/1";
                }

                this.delay = 2500; // 2.5s to allow PWA banner to triggering
                
                this.init();
            }

            init() {
                // Listen for PWA events
                window.addEventListener('pwa-banner-shown', () => this.pause());
                window.addEventListener('pwa-banner-dismissed', () => this.resume());

                // Start initial timer
                this.start();
            }

            start() {
                this.timerId = setTimeout(() => {
                    this.navigate();
                }, this.delay);
            }

            pause() {
                if (this.timerId) {
                    clearTimeout(this.timerId);
                    this.timerId = null;
                }
            }

            resume() {
                this.timerId = setTimeout(() => {
                    this.navigate();
                }, 500); 
            }

            navigate() {
                window.location.href = this.redirectUrl;
            }
        }

        document.addEventListener('DOMContentLoaded', () => {
             new SplashController();
        });
    </script>
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
    
    <x-pwa-install-banner />
</body>
</html>
