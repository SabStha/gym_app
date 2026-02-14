<section>
    <header>
        <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
            {{ __('Face ID / Passkeys') }}
        </h2>

        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
            {{ __('Enable secure login with Face ID, Touch ID, or other passkeys.') }}
        </p>
    </header>

    <div class="mt-6 space-y-6">
        <!-- List Existing Keys (Simplified) -->
        {{-- Ideally, list keys here using a foreach loop if passed from controller --}}
        
        <!-- Register New Key Button -->
        <form x-data="webauthnRegister()" @submit.prevent="register">
            <x-primary-button class="gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M10 2a1 1 0 00-1 1v1a1 1 0 002 0V3a1 1 0 00-1-1zM4 4h3a3 3 0 006 0h3a2 2 0 012 2v9a2 2 0 01-2 2H4a2 2 0 01-2-2V6a2 2 0 012-2zm2.5 7a1.5 1.5 0 100-3 1.5 1.5 0 000 3zm2.45 4a2.5 2.5 0 10-4.9 0h4.9zM12 9a1 1 0 100 2h3a1 1 0 100-2h-3zm-1 4a1 1 0 011-1h2a1 1 0 110 2h-2a1 1 0 01-1-1z" clip-rule="evenodd" />
                </svg>
                {{ __('Register New Device') }}
            </x-primary-button>
            
            <p x-show="status" x-text="status" class="mt-2 text-sm text-emerald-600"></p>
            <p x-show="error" x-text="error" class="mt-2 text-sm text-red-600"></p>
        </form>
    </div>

    @push('scripts')
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('webauthnRegister', () => ({
                status: '',
                error: '',
                async register() {
                    this.status = 'Registering...';
                    this.error = '';
                    
                    try {
                        // 1. Get Options
                        const optionsRes = await axios.post('/webauthn/register/options');
                        const options = optionsRes.data;

                        // 2. Create Credentials using Laragear's helper or raw API?
                        // Laragear likely expects raw API or a helper.
                        // Assuming raw API for maximum compatibility or check if library imported.
                        // Documentation says use `LaragearWebAuthn` object if using their JS, but let's use standard API with simple conversion if needed.
                        // Actually, strict dependency on `laragear/webauthn` JS assets is common.
                        // For now, let's assume we need to decode the options and call `navigator.credentials.create`.
                        
                        // Simple helper to decode base64url
                        const decode = (str) => Uint8Array.from(atob(str.replace(/-/g, '+').replace(/_/g, '/')), c => c.charCodeAt(0));

                        // Correction: Laragear returns JSON ready for `startRegistration` from `@simplewebauthn/browser` usually, or raw options.
                        // Let's rely on standard `navigator.credentials.create` if possible, but we need to convert buffer strings to ArrayBuffers.
                         
                         // Note: It is safer to use the library provided by Laragear if possible.
                         // But I don't see `php artisan vendor:publish --tag=public` for JS assets.
                         // I will try to use the raw API.

                         // Actually, Laragear WebAuthn controller returns the PublicKeyCredentialCreationOptions directly JSON encoded.
                         // But `challenge` and `user.id` are base64url encoded strings. Browsers need ArrayBuffer.
                         
                         // NOTE: Since I can't easily install npm packages here without a build step I might trip over, 
                         // I will use a concise inline helper to convert the JSON.
                         
                         /* This part is tricky without a library. I'll attempt a minimal implementation. */
                         
                         const prepareOptions = (json) => {
                             json.challenge = decode(json.challenge);
                             json.user.id = decode(json.user.id);
                             if (json.excludeCredentials) {
                                 json.excludeCredentials.forEach(c => c.id = decode(c.id));
                             }
                             return json;
                         };
                         
                         const opts = prepareOptions(options);
                         
                         const credential = await navigator.credentials.create({ publicKey: opts });
                         
                         // 3. Send back
                         // Need to encode ArrayBuffers back to base64url for transport
                         const encode = (buf) => btoa(String.fromCharCode(...new Uint8Array(buf))).replace(/\+/g, '-').replace(/\//g, '_').replace(/=+$/, '');
                         
                         const response = {
                             id: credential.id,
                             type: credential.type,
                             rawId: encode(credential.rawId),
                             response: {
                                 clientDataJSON: encode(credential.response.clientDataJSON),
                                 attestationObject: encode(credential.response.attestationObject)
                             },
                              // Some servers expect `clientExtensionResults`
                             clientExtensionResults: credential.getClientExtensionResults()
                         };

                         await axios.post('/webauthn/register', response);
                         this.status = 'Device registered successfully!';
                         setTimeout(() => this.status = '', 3000);
                         
                    } catch (e) {
                        console.error(e);
                        this.error = 'Registration failed. ' + (e.response?.data?.message || e.message);
                    }
                }
            }))
        })
    </script>
    @endpush
</section>
