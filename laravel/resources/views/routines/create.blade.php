@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gray-50 dark:bg-gray-900 pb-32">
    <!-- Top App Bar -->
    <div class="px-6 py-8">
        <h1 class="text-3xl font-bold text-gray-900 dark:text-white tracking-tight">New Split</h1>
        <p class="text-gray-500 dark:text-gray-400 mt-1">Design your perfect workout split.</p>
    </div>

    <!-- Main Content Sheet -->
    <div class="px-4">
        <div class="bg-white dark:bg-gray-800 rounded-3xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
            <form id="create-routine-form" action="{{ route('routines.store') }}" method="POST">
                @csrf
                
                <div class="p-6 space-y-6">
                    <!-- Title Input -->
                    <div class="relative group">
                        <label for="title" class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-2 ml-1">
                            Split Name <span class="text-red-500">*</span>
                        </label>
                        <input type="text" 
                               name="title" 
                               id="title" 
                               value="{{ old('title') }}"
                               class="block w-full h-14 rounded-2xl border-gray-200 bg-gray-50 focus:bg-white focus:border-emerald-500 focus:ring-emerald-500 text-lg px-4 shadow-sm transition-all dark:bg-gray-900 dark:border-gray-600 dark:text-white dark:focus:border-emerald-500"
                               placeholder="e.g. Summer Shred" 
                               required 
                               autofocus>
                        @error('title')
                            <p class="text-red-500 text-sm mt-2 ml-1 animate-pulse">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Template Selection -->
                    <div>
                        <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-3 ml-1">
                            Choose Split Type
                        </label>
                        <input type="hidden" name="template" id="template_input">
                        <div class="grid grid-cols-2 gap-3">
                            @foreach([
                                ['name' => 'Push Pull Legs', 'count' => '3 Workouts'],
                                ['name' => 'Upper Lower', 'count' => '2 Workouts'],
                                ['name' => 'Full Body', 'count' => '2 Workouts'],
                                ['name' => 'Split (4 Day)', 'count' => '4 Workouts'],
                                ['name' => 'Split (5 Day)', 'count' => '5 Workouts'],
                            ] as $t)
                                <button type="button" 
                                        onclick="selectTemplate(this, '{{ $t['name'] }}')"
                                        class="template-btn px-4 py-3 rounded-2xl bg-gray-100 text-gray-600 font-bold text-sm hover:bg-emerald-100 hover:text-emerald-700 active:scale-95 transition-all text-left border-2 border-transparent dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-emerald-900 dark:hover:text-emerald-300 flex flex-col justify-center">
                                    <span class="block text-sm leading-tight mb-1">{{ $t['name'] }}</span>
                                    <span class="block text-[10px] font-medium opacity-60">{{ $t['count'] }}</span>
                                </button>
                            @endforeach
                            <button type="button" 
                                    onclick="selectTemplate(this, '')"
                                    class="template-btn px-4 py-3 rounded-2xl bg-white border-2 border-dashed border-gray-300 text-gray-400 font-bold text-sm hover:border-emerald-400 hover:text-emerald-600 active:scale-95 transition-all text-left dark:bg-transparent dark:border-gray-600 dark:hover:border-emerald-500 dark:hover:text-emerald-400 flex flex-col justify-center">
                                <span class="block text-sm leading-tight mb-1">Custom Split</span>
                                <span class="block text-[10px] font-medium opacity-60">Start from scratch</span>
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Sticky Bottom Action Bar -->
<div class="fixed bottom-0 left-0 right-0 bg-white dark:bg-gray-800 border-t border-gray-200 dark:border-gray-700 p-4 pb-safe z-40 shadow-[0_-5px_20px_rgba(0,0,0,0.05)]">
    <div class="max-w-xl mx-auto flex items-center gap-4">
        <a href="{{ route('routines.index') }}" 
           class="flex-1 py-3.5 text-center text-gray-500 font-bold hover:text-gray-800 dark:text-gray-400 dark:hover:text-gray-200 transition-colors">
            Cancel
        </a>
        <button type="submit" 
                form="create-routine-form"
                id="submit-btn"
                disabled
                class="flex-[2] py-3.5 bg-emerald-600 text-white font-bold rounded-2xl shadow-lg shadow-emerald-200 hover:bg-emerald-700 active:scale-[0.98] transition-all disabled:opacity-50 disabled:cursor-not-allowed disabled:shadow-none flex justify-center items-center gap-2">
            <span id="btn-text">Create Split</span>
            <svg id="btn-spinner" class="animate-spin h-5 w-5 text-white hidden" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
            </svg>
        </button>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const form = document.getElementById('create-routine-form');
        const titleInput = document.getElementById('title');
        const templateInput = document.getElementById('template_input');
        const submitBtn = document.getElementById('submit-btn');
        const btnText = document.getElementById('btn-text');
        const btnSpinner = document.getElementById('btn-spinner');

        // Enable button if title has content
        function checkInput() {
            if (titleInput.value.trim().length > 0) {
                submitBtn.disabled = false;
            } else {
                submitBtn.disabled = true;
            }
        }

        // Template Selection Logic
        window.selectTemplate = function(btn, val) {
            // Reset visual state
            document.querySelectorAll('.template-btn').forEach(b => {
                b.classList.remove('ring-2', 'ring-emerald-500', 'bg-emerald-50', 'text-emerald-700', 'border-emerald-500');
                // Re-apply default classes if needed, simpler to just toggle select class
                 if(b.classList.contains('border-dashed')) {
                      // Custom btn
                 } else {
                      b.classList.add('bg-gray-100', 'text-gray-600', 'border-transparent');
                      b.classList.remove('bg-emerald-50', 'text-emerald-700', 'border-emerald-500');
                 }
            });

            // Set Input
            templateInput.value = val;
            
            // Set Title if empty or matches another template
            // (Optional UX: if user hasn't typed a custom name, auto-name it)
            if (titleInput.value === '' || titleInput.dataset.autoName === 'true') {
                 titleInput.value = val;
                 titleInput.dataset.autoName = 'true';
            }
            if (val === '') {
                 // if custom, don't clear title unless it was auto-set
                 if(titleInput.dataset.autoName === 'true') titleInput.value = '';
            }

            // Highlight selected
            if (val !== '') {
                btn.classList.remove('bg-gray-100', 'text-gray-600', 'border-transparent');
                btn.classList.add('ring-2', 'ring-emerald-500', 'bg-emerald-50', 'text-emerald-700', 'border-emerald-500');
            } else {
                 btn.classList.add('ring-2', 'ring-emerald-500', 'border-emerald-500', 'text-emerald-600');
            }

            checkInput();
            // titleInput.focus(); // Maybe annoying on mobile to jump focus
        };
        
        // Track manual input
        titleInput.addEventListener('input', () => {
             titleInput.dataset.autoName = 'false';
             checkInput();
        });

        titleInput.addEventListener('input', checkInput);
        
        // Initial check
        checkInput();

        // Handle Submit
        form.addEventListener('submit', (e) => {
            if (submitBtn.disabled) {
                e.preventDefault();
                return;
            }

            // Show loading state
            submitBtn.disabled = true;
            btnText.textContent = 'Creating...';
            btnSpinner.classList.remove('hidden');
        });
    });
</script>

<style>
    /* Safe area padding for iPhones without home button */
    .pb-safe {
        padding-bottom: env(safe-area-inset-bottom, 20px);
    }
</style>
@endsection
