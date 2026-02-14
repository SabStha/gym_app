<x-app-layout>
    <div class="py-6 px-4 pb-24 max-w-7xl mx-auto space-y-6">
        
        <!-- 1. Profile Header Card -->
        <div class="relative overflow-hidden bg-white dark:bg-gray-800 rounded-[2.5rem] shadow-xl border border-gray-100 dark:border-gray-700 p-8 text-center sm:text-left">
            <!-- Background Glow -->
            <div class="absolute top-0 right-0 -mt-16 -mr-16 w-64 h-64 bg-emerald-500/20 rounded-full blur-3xl pointer-events-none"></div>
            
            <div class="relative z-10 flex flex-col sm:flex-row items-center gap-6">
                <!-- Avatar -->
                <div class="w-24 h-24 rounded-full bg-gradient-to-br from-emerald-400 to-cyan-500 flex items-center justify-center text-white text-3xl font-black shadow-lg ring-4 ring-white dark:ring-gray-800">
                    {{ substr($user->name, 0, 1) }}
                </div>
                
                <!-- Info & Stats -->
                <div class="flex-1">
                    <h2 class="text-3xl font-black text-gray-900 dark:text-white mb-1">{{ $user->name }}</h2>
                    <p class="text-gray-500 dark:text-gray-400 font-medium mb-4">{{ $user->email }}</p>
                    
                    <!-- Stats Strip -->
                    <div class="flex flex-wrap justify-center sm:justify-start gap-4">
                        <div class="bg-gray-50 dark:bg-gray-900/50 rounded-xl px-4 py-2 border border-gray-100 dark:border-gray-700">
                            <span class="block text-xs font-bold text-gray-400 uppercase tracking-wider">Workouts</span>
                            <span class="text-xl font-black text-gray-900 dark:text-white">{{ $totalWorkouts ?? 0 }}</span>
                        </div>
                        <div class="bg-gray-50 dark:bg-gray-900/50 rounded-xl px-4 py-2 border border-gray-100 dark:border-gray-700">
                            <span class="block text-xs font-bold text-gray-400 uppercase tracking-wider">Member Since</span>
                            <span class="text-xl font-black text-gray-900 dark:text-white">{{ $user->created_at->format('M Y') }}</span>
                        </div>
                    </div>
                </div>
                
                <!-- Edit Action -->
                <button onclick="document.getElementById('profile-section').scrollIntoView({behavior: 'smooth'})" 
                        class="p-3 bg-gray-100 dark:bg-gray-700 rounded-2xl text-gray-500 hover:text-emerald-500 transition-colors">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                </button>
            </div>
        </div>

        <!-- 2. Settings Sections (Grid) -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            
            <!-- Left Column: General & Security -->
            <div class="space-y-6">
                <!-- General Info -->
                <section id="profile-section" class="bg-white dark:bg-gray-800 rounded-[2rem] shadow-lg border border-gray-100 dark:border-gray-700 overflow-hidden">
                    <div class="p-6 border-b border-gray-100 dark:border-gray-700 flex items-center gap-3">
                        <div class="w-10 h-10 rounded-full bg-emerald-100 dark:bg-emerald-500/20 text-emerald-600 flex items-center justify-center">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                        </div>
                        <h3 class="text-lg font-bold text-gray-900 dark:text-white">Profile Information</h3>
                    </div>
                    <div class="p-6">
                         @include('profile.partials.update-profile-information-form')
                    </div>
                </section>

                <!-- Security -->
                <section class="bg-white dark:bg-gray-800 rounded-[2rem] shadow-lg border border-gray-100 dark:border-gray-700 overflow-hidden">
                    <div class="p-6 border-b border-gray-100 dark:border-gray-700 flex items-center gap-3">
                        <div class="w-10 h-10 rounded-full bg-blue-100 dark:bg-blue-500/20 text-blue-600 flex items-center justify-center">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
                        </div>
                        <h3 class="text-lg font-bold text-gray-900 dark:text-white">Security</h3>
                    </div>
                    <div class="p-6 space-y-8">
                        <div>
                            <h4 class="text-sm font-bold text-gray-500 uppercase tracking-wider mb-4">Update Password</h4>
                            @include('profile.partials.update-password-form')
                        </div>
                        <hr class="border-gray-100 dark:border-gray-700">
                        <div>
                            <h4 class="text-sm font-bold text-gray-500 uppercase tracking-wider mb-4">Biometrics / WebAuthn</h4>
                            @include('profile.partials.manage-webauthn')
                        </div>
                    </div>
                </section>
            </div>

            <!-- Right Column: Danger Zone -->
            <div class="space-y-6">
                <section class="bg-white dark:bg-gray-800 rounded-[2rem] shadow-lg border border-gray-100 dark:border-gray-700 overflow-hidden relative">
                    <div class="absolute top-0 right-0 w-32 h-32 bg-red-500/10 rounded-bl-full pointer-events-none"></div>
                    <div class="p-6 border-b border-gray-100 dark:border-gray-700 flex items-center gap-3">
                        <div class="w-10 h-10 rounded-full bg-red-100 dark:bg-red-500/20 text-red-600 flex items-center justify-center">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                        </div>
                        <h3 class="text-lg font-bold text-red-600 dark:text-red-400">Danger Zone</h3>
                    </div>
                    <div class="p-6">
                        <p class="text-sm text-gray-500 dark:text-gray-400 mb-6">
                            Once your account is deleted, all of your resources and data will be permanently deleted. Before deleting your account, please download any data or information that you wish to retain.
                        </p>
                        @include('profile.partials.delete-user-form')
                    </div>
                </section>
            </div>
        </div>
    </div>
</x-app-layout>
