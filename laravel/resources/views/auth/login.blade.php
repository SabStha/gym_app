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
                <button type="submit" class="w-full flex justify-center items-center h-14 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-2xl shadow-lg shadow-emerald-200 transition-all transform active:scale-[0.98] text-lg">
                    Sign In
                </button>
                
                <div class="text-center">
                    <span class="text-gray-500 text-sm">Don't have an account?</span>
                    <a href="{{ route('register') }}" class="text-emerald-600 font-bold ml-1 hover:underline">Sign Up</a>
                </div>
            </div>
        </form>
    </div>
</x-guest-layout>
