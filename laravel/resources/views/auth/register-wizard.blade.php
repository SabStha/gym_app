<x-guest-layout>
    <div class="px-2 py-4 max-w-lg mx-auto">
        <!-- Progress Steps -->
        <div class="mb-8">
            <div class="flex items-center justify-between mb-2">
                <span class="text-xs font-bold text-emerald-600 uppercase tracking-wider">Step {{ $step }} of 4</span>
                <span class="text-xs font-medium text-gray-400">
                    @if($step==1) Basics @elseif($step==2) Body @elseif($step==3) Goals @else Security @endif
                </span>
            </div>
            <div class="h-1.5 w-full bg-gray-100 rounded-full overflow-hidden">
                <div class="h-full bg-emerald-500 transition-all duration-300 ease-out" style="width: {{ ($step / 4) * 100 }}%"></div>
            </div>
        </div>

        <form method="POST" action="{{ route('register.wizard.post', ['step' => $step]) }}">
            @csrf

            <!-- STEP 1: BASICS -->
            @if($step == 1)
                <div class="space-y-6 slide-in-right">
                    <div class="text-center mb-6">
                        <h1 class="text-2xl font-bold text-gray-900">Let's get started</h1>
                        <p class="text-gray-500">First, what should we call you?</p>
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-2 ml-1">Name</label>
                        <input type="text" name="name" value="{{ old('name', $data['name'] ?? '') }}" 
                               class="block w-full h-14 rounded-2xl border-gray-200 bg-gray-50 focus:bg-white focus:border-emerald-500 focus:ring-emerald-500 text-lg px-4 shadow-sm transition-all"
                               placeholder="Your Name" required autofocus>
                        <x-input-error :messages="$errors->get('name')" class="mt-2 text-sm text-red-500 ml-1" />
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-2 ml-1">Email</label>
                        <input type="email" name="email" value="{{ old('email', $data['email'] ?? '') }}" 
                               class="block w-full h-14 rounded-2xl border-gray-200 bg-gray-50 focus:bg-white focus:border-emerald-500 focus:ring-emerald-500 text-lg px-4 shadow-sm transition-all"
                               placeholder="you@example.com" required>
                        <x-input-error :messages="$errors->get('email')" class="mt-2 text-sm text-red-500 ml-1" />
                    </div>
                </div>
            @endif

            <!-- STEP 2: BODY -->
            @if($step == 2)
                <div class="space-y-6 slide-in-right">
                    <div class="text-center mb-6">
                        <h1 class="text-2xl font-bold text-gray-900">About You</h1>
                        <p class="text-gray-500">To calculate your metrics</p>
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-2 ml-1">Sex</label>
                        <div class="grid grid-cols-3 gap-3">
                            @foreach(['male', 'female', 'other'] as $s)
                                <label class="cursor-pointer">
                                    <input type="radio" name="sex" value="{{ $s }}" class="peer sr-only" {{ old('sex', $data['sex'] ?? '') == $s ? 'checked' : '' }}>
                                    <div class="h-14 flex items-center justify-center rounded-2xl border-2 border-gray-100 bg-white text-gray-500 font-bold capitalize transition-all peer-checked:border-emerald-500 peer-checked:bg-emerald-50 peer-checked:text-emerald-700 hover:border-emerald-200">
                                        {{ $s }}
                                    </div>
                                </label>
                            @endforeach
                        </div>
                        <x-input-error :messages="$errors->get('sex')" class="mt-2 text-sm text-red-500 ml-1" />
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-2 ml-1">Height (cm)</label>
                            <input type="number" name="height_cm" value="{{ old('height_cm', $data['height_cm'] ?? '') }}" 
                                   class="block w-full h-14 rounded-2xl border-gray-200 bg-gray-50 focus:bg-white focus:border-emerald-500 focus:ring-emerald-500 text-lg px-4 text-center font-bold shadow-sm"
                                   placeholder="175" required min="100" max="250">
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-2 ml-1">Weight (kg)</label>
                            <input type="number" step="0.1" name="current_weight_kg" value="{{ old('current_weight_kg', $data['current_weight_kg'] ?? '') }}" 
                                   class="block w-full h-14 rounded-2xl border-gray-200 bg-gray-50 focus:bg-white focus:border-emerald-500 focus:ring-emerald-500 text-lg px-4 text-center font-bold shadow-sm"
                                   placeholder="70.5" required min="20" max="300">
                        </div>
                    </div>
                </div>
            @endif

            <!-- STEP 3: GOALS -->
            @if($step == 3)
                <div class="space-y-6 slide-in-right">
                    <div class="text-center mb-6">
                        <h1 class="text-2xl font-bold text-gray-900">Your Goal</h1>
                        <p class="text-gray-500">What are we aiming for?</p>
                    </div>

                    <div>
                        <div class="grid grid-cols-1 gap-3">
                            @php $goal = old('goal_type', $data['goal_type'] ?? ''); @endphp
                            
                            <label class="cursor-pointer group">
                                <input type="radio" name="goal_type" value="lose" class="peer sr-only" {{ $goal == 'lose' ? 'checked' : '' }} onchange="toggleTargetWeight(this.value)">
                                <div class="p-4 rounded-2xl border-2 border-gray-100 bg-white transition-all peer-checked:border-emerald-500 peer-checked:bg-emerald-50 group-hover:border-emerald-200 flex items-center gap-4">
                                    <div class="h-10 w-10 rounded-full bg-emerald-100 text-emerald-600 flex items-center justify-center text-xl">📉</div>
                                    <div class="text-left">
                                        <div class="font-bold text-gray-900 peer-checked:text-emerald-800">Lose Weight</div>
                                        <div class="text-xs text-gray-500">Get lean and shredded</div>
                                    </div>
                                </div>
                            </label>

                            <label class="cursor-pointer group">
                                <input type="radio" name="goal_type" value="gain" class="peer sr-only" {{ $goal == 'gain' ? 'checked' : '' }} onchange="toggleTargetWeight(this.value)">
                                <div class="p-4 rounded-2xl border-2 border-gray-100 bg-white transition-all peer-checked:border-emerald-500 peer-checked:bg-emerald-50 group-hover:border-emerald-200 flex items-center gap-4">
                                    <div class="h-10 w-10 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center text-xl">💪</div>
                                    <div class="text-left">
                                        <div class="font-bold text-gray-900 peer-checked:text-emerald-800">Build Muscle</div>
                                        <div class="text-xs text-gray-500">Gain mass and strength</div>
                                    </div>
                                </div>
                            </label>

                            <label class="cursor-pointer group">
                                <input type="radio" name="goal_type" value="maintain" class="peer sr-only" {{ $goal == 'maintain' ? 'checked' : '' }} onchange="toggleTargetWeight(this.value)">
                                <div class="p-4 rounded-2xl border-2 border-gray-100 bg-white transition-all peer-checked:border-emerald-500 peer-checked:bg-emerald-50 group-hover:border-emerald-200 flex items-center gap-4">
                                    <div class="h-10 w-10 rounded-full bg-gray-100 text-gray-600 flex items-center justify-center text-xl">⚓</div>
                                    <div class="text-left">
                                        <div class="font-bold text-gray-900 peer-checked:text-emerald-800">Maintain</div>
                                        <div class="text-xs text-gray-500">Keep current physique</div>
                                    </div>
                                </div>
                            </label>
                        </div>
                        <x-input-error :messages="$errors->get('goal_type')" class="mt-2 text-sm text-red-500 ml-1" />
                    </div>

                    <div id="target-weight-container" class="{{ $goal == 'maintain' ? 'hidden' : '' }}">
                        <label class="block text-sm font-bold text-gray-700 mb-2 ml-1">Target Weight (kg)</label>
                        <input type="number" step="0.1" name="target_weight_kg" value="{{ old('target_weight_kg', $data['target_weight_kg'] ?? '') }}" 
                               class="block w-full h-14 rounded-2xl border-gray-200 bg-gray-50 focus:bg-white focus:border-emerald-500 focus:ring-emerald-500 text-lg px-4 text-center font-bold shadow-sm"
                               placeholder="Target">
                    </div>
                </div>

                <script>
                    function toggleTargetWeight(val) {
                        const container = document.getElementById('target-weight-container');
                        if (val === 'maintain') {
                            container.classList.add('hidden');
                        } else {
                            container.classList.remove('hidden');
                        }
                    }
                </script>
            @endif

            <!-- STEP 4: SECURITY -->
            @if($step == 4)
                <div class="space-y-6 slide-in-right">
                    <div class="text-center mb-6">
                        <h1 class="text-2xl font-bold text-gray-900">Secure Account</h1>
                        <p class="text-gray-500">Last step! Create your login.</p>
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-2 ml-1">Password</label>
                        <input type="password" name="password" 
                               class="block w-full h-14 rounded-2xl border-gray-200 bg-gray-50 focus:bg-white focus:border-emerald-500 focus:ring-emerald-500 text-lg px-4 shadow-sm transition-all"
                               required autocomplete="new-password" placeholder="••••••••">
                        <x-input-error :messages="$errors->get('password')" class="mt-2 text-sm text-red-500 ml-1" />
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-2 ml-1">Confirm Password</label>
                        <input type="password" name="password_confirmation" 
                               class="block w-full h-14 rounded-2xl border-gray-200 bg-gray-50 focus:bg-white focus:border-emerald-500 focus:ring-emerald-500 text-lg px-4 shadow-sm transition-all"
                               required autocomplete="new-password" placeholder="••••••••">
                    </div>
                </div>
            @endif

            <!-- Navigation Buttons -->
            <div class="mt-10 flex gap-4">
                @if($step > 1)
                    <a href="{{ route('register.wizard', ['step' => $step - 1]) }}" 
                       class="flex-1 flex items-center justify-center h-14 rounded-2xl bg-gray-100 text-gray-900 font-bold hover:bg-gray-200 transition-colors">
                        Back
                    </a>
                @endif
                
                <button type="submit" 
                        class="flex-1 flex items-center justify-center h-14 rounded-2xl bg-emerald-600 text-white font-bold shadow-lg shadow-emerald-200 hover:bg-emerald-700 active:scale-[0.98] transition-all">
                    {{ $step == 4 ? 'Create Account' : 'Next' }}
                </button>
            </div>
            
            @if($step == 1)
                <div class="mt-6 text-center">
                    <span class="text-gray-500 text-sm">Already set?</span>
                    <a href="{{ route('login') }}" class="text-emerald-600 font-bold ml-1 hover:underline">Log In</a>
                </div>
            @endif
        </form>
    </div>

    <style>
        .slide-in-right {
            animation: slideIn 0.3s ease-out forwards;
        }
        @keyframes slideIn {
            from { opacity: 0; transform: translateX(20px); }
            to { opacity: 1; transform: translateX(0); }
        }
    </style>
</x-guest-layout>
