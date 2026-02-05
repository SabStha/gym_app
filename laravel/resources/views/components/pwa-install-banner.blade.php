<div id="pwa-install-banner" class="fixed inset-0 z-[100] flex items-center justify-center bg-black/60 backdrop-blur-sm hidden text-left">
    <div class="bg-white dark:bg-gray-800 rounded-3xl p-6 shadow-2xl max-w-sm w-full mx-4 transform transition-all scale-100 opacity-100">
        <div class="text-center mb-6">
            <div class="bg-emerald-100 w-20 h-20 rounded-2xl flex items-center justify-center mx-auto mb-4 shadow-inner">
                <img src="/icons/icon-192.png" alt="App Icon" class="w-16 h-16 rounded-xl shadow-sm">
            </div>
            <h3 class="text-2xl font-bold text-gray-900 dark:text-white mb-2">Install App</h3>
            <p class="text-gray-500 dark:text-gray-400 leading-relaxed" id="pwa-banner-text">
                Install <strong>{{ config('app.name') }}</strong> for the best experience. Works offline and looks great!
            </p>
        </div>

        <div class="flex flex-col gap-3">
            <button id="pwa-install-btn" class="w-full py-3.5 bg-emerald-600 text-white font-bold rounded-xl shadow-lg shadow-emerald-200 hover:bg-emerald-700 active:scale-[0.98] transition-all">
                Install Now
            </button>
            <button id="pwa-dismiss-btn" class="w-full py-3.5 text-gray-500 font-semibold hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 transition-colors">
                Not now
            </button>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const banner = document.getElementById('pwa-install-banner');
        const installBtn = document.getElementById('pwa-install-btn');
        const dismissBtn = document.getElementById('pwa-dismiss-btn');
        const bannerText = document.getElementById('pwa-banner-text');
        
        let deferredPrompt;
        const isIOS = /iPad|iPhone|iPod/.test(navigator.userAgent) && !window.MSStream;
        const isStandalone = window.matchMedia('(display-mode: standalone)').matches || window.navigator.standalone;

        // Check if already dismissed
        const dismissedAt = localStorage.getItem('pwa_install_dismissed');
        const now = Date.now();
        const COOLDOWN_DAYS = 7;
        const COOLDOWN_MS = COOLDOWN_DAYS * 24 * 60 * 60 * 1000;

        if (isStandalone) {
            return; // Already installed
        }

        if (dismissedAt && (now - parseInt(dismissedAt)) < COOLDOWN_MS) {
            return; // Within cooldown period
        }

        // Android / Desktop (Chrome)
        window.addEventListener('beforeinstallprompt', (e) => {
            e.preventDefault();
            deferredPrompt = e;
            showBanner();
        });

        // iOS Logic
        if (isIOS && !isStandalone) {
            bannerText.innerHTML = "Tap <span class='font-bold'>Share</span> <svg class='w-5 h-5 inline-block -mt-1' fill='none' stroke='currentColor' viewBox='0 0 24 24'><path stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z'></path></svg> then <span class='font-bold'>Add to Home Screen</span> <svg class='w-5 h-5 inline-block -mt-1' fill='none' stroke='currentColor' viewBox='0 0 24 24'><path stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M12 9v3m0 0v3m0-3h3m-3 0H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z'></path></svg>";
            installBtn.style.display = 'none'; // iOS manual install only
            showBanner();
        }

        function showBanner() {
            banner.classList.remove('hidden');
            window.dispatchEvent(new CustomEvent('pwa-banner-shown'));
        }

        function hideBanner() {
            banner.classList.add('hidden');
            // Set cooldown
            localStorage.setItem('pwa_install_dismissed', Date.now().toString());
            window.dispatchEvent(new CustomEvent('pwa-banner-dismissed'));
        }

        installBtn.addEventListener('click', async () => {
            if (deferredPrompt) {
                deferredPrompt.prompt();
                const { outcome } = await deferredPrompt.userChoice;
                console.log(`User response to the install prompt: ${outcome}`);
                deferredPrompt = null;
                hideBanner();
            }
        });

        dismissBtn.addEventListener('click', () => {
            hideBanner();
        });
    });
</script>
