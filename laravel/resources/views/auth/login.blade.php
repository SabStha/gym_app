<x-guest-layout>
    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <div class="px-2 py-6">
        <div class="text-center mb-10">
            <h1 class="text-3xl font-bold text-gray-900">Welcome Back</h1>
            <p class="text-gray-500 mt-2">Enter your details to sign in</p>
        </div>

        <form method="POST" action="{{ route('login') }}">
            @csrf

            <!-- Email Address -->
            <div class="mb-6">
                <label for="email" class="block text-sm font-bold text-gray-700 mb-2 ml-1">Email</label>
                <input id="email" class="block w-full h-14 rounded-2xl border-gray-200 bg-gray-50 focus:bg-white focus:border-emerald-500 focus:ring-emerald-500 text-lg px-4 shadow-sm transition-all" 
                       type="email" name="email" :value="old('email')" required autofocus autocomplete="username" placeholder="you@example.com" />
                <x-input-error :messages="$errors->get('email')" class="mt-2 text-sm text-red-500 ml-1" />
            </div>

            <!-- Password -->
            <div class="mb-6">
                <div class="flex justify-between items-center mb-2 ml-1">
                    <label for="password" class="block text-sm font-bold text-gray-700">Password</label>
                </div>
                
                <input id="password" class="block w-full h-14 rounded-2xl border-gray-200 bg-gray-50 focus:bg-white focus:border-emerald-500 focus:ring-emerald-500 text-lg px-4 shadow-sm transition-all"
                       type="password" name="password" required autocomplete="current-password" placeholder="••••••••" />
                <x-input-error :messages="$errors->get('password')" class="mt-2 text-sm text-red-500 ml-1" />
            </div>

            <!-- Remember Me & Forgot Password -->
            <div class="flex items-center justify-between mb-8 px-1">
                <label for="remember_me" class="inline-flex items-center cursor-pointer">
                    <input id="remember_me" type="checkbox" class="rounded border-gray-300 text-emerald-600 shadow-sm focus:ring-emerald-500 w-5 h-5" name="remember">
                    <span class="ml-2 text-sm text-gray-600">Remember me</span>
                </label>
                
                @if (Route::has('password.request'))
                    <a class="text-sm font-medium text-emerald-600 hover:text-emerald-700" href="{{ route('password.request') }}">
                        Forgot Password?
                    </a>
                @endif
            </div>

            <div class="space-y-4">
                <button type="submit" class="w-full h-14 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-2xl shadow-lg shadow-emerald-200 transition-all transform active:scale-[0.98] text-lg">
                    Sign In
                </button>

                <!-- WebAuthn Login -->
                <button type="button" @click="webauthnLogin" class="w-full h-14 bg-white border-2 border-emerald-100 text-emerald-700 font-bold rounded-2xl hover:bg-emerald-50 transition-all flex items-center justify-center gap-2 active:scale-[0.98]">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 11c0 3.517-1.009 6.799-2.753 9.571m-3.44-2.04l.054-.09A13.916 13.916 0 008 11a4 4 0 118 0c0 1.017-.07 2.019-.203 3m-2.118 6.844A21.88 21.88 0 0015.171 17m3.839 1.132c.645-2.266.99-4.659.99-7.132A8 8 0 008 4.07M3 15.364c.64-1.319 1-2.8 1-4.364 0-1.457.2-2.858.59-4.18M5.55 17.791a4.002 4.002 0 010-6m0 0a4.002 4.002 0 01-3.765 2.247m15.462 5.068a4.002 4.002 0 01-3.83-5.263m3.83 5.263l-4.13-1.65" />
                    </svg>
                    Sign in with Face ID
                </button>
                
                <p x-show="status" x-text="status" class="text-center text-sm text-emerald-600 font-medium"></p>
                <p x-show="error" x-text="error" class="text-center text-sm text-red-500 font-medium"></p>
                
                <div class="text-center pt-2">
                    <span class="text-gray-500 text-sm">Don't have an account?</span>
                    <a href="{{ route('register') }}" class="text-emerald-600 font-bold ml-1 hover:underline">Sign Up</a>
                </div>
            </div>
        </form>
    </div>

    @push('scripts')
    <script>
        document.addEventListener('alpine:init', () => {
             // Bind to the form scope if possible, or global
             // Note: x-data on the form or body would be better. For now, we assume x-data="loginForm()" on body or inline.
             // But existing code doesn't have x-data on body.
             // We can mix it in or just use document selectors if Alpine isn't already managing the root.
             // Better: add x-data to the form tag.
        });
        
        function loginWithWebAuthn() {
             // ... logic ...
        }
    </script>
    @endpush

    <!-- Script Injection for WebAuthn -->
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const form = document.querySelector('form');
            if(form) {
                form.setAttribute('x-data', 'webauthnLogin()');
            }
        });

        document.addEventListener('alpine:init', () => {
            Alpine.data('webauthnLogin', () => ({
                status: '',
                error: '',
                async webauthnLogin() {
                    const email = document.getElementById('email').value;
                    if (!email) {
                        this.error = 'Please enter your email first.';
                        return;
                    }
                    
                    this.status = 'Looking for credentials...';
                    this.error = '';

                    try {
                        const res = await axios.post('/webauthn/login/options', { email });
                        const options = res.data;

                        const decode = (str) => Uint8Array.from(atob(str.replace(/-/g, '+').replace(/_/g, '/')), c => c.charCodeAt(0));
                        
                        options.challenge = decode(options.challenge);
                        if(options.allowCredentials) {
                             options.allowCredentials.forEach(c => c.id = decode(c.id));
                        }

                        const credential = await navigator.credentials.get({ publicKey: options });

                        const encode = (buf) => btoa(String.fromCharCode(...new Uint8Array(buf))).replace(/\+/g, '-').replace(/\//g, '_').replace(/=+$/, '');

                        const response = {
                            id: credential.id,
                            type: credential.type,
                            rawId: encode(credential.rawId),
                            response: {
                                clientDataJSON: encode(credential.response.clientDataJSON),
                                authenticatorData: encode(credential.response.authenticatorData),
                                signature: encode(credential.response.signature),
                                userHandle: credential.response.userHandle ? encode(credential.response.userHandle) : null
                            }
                        };

                        await axios.post('/webauthn/login', response);
                        window.location.href = "{{ route('dashboard') }}";

                    } catch (e) {
                        console.error(e);
                        this.error = 'Login failed. ' + (e.response?.data?.message || e.message);
                        this.status = '';
                    }
                }
            }));
        });
    </script>
</x-guest-layout>
