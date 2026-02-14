<div id="exercise-picker" class="mt-4 pb-24" x-data="exercisePicker()">
    <!-- 1. Header & Search -->
    <div class="flex items-center justify-between gap-3 mb-4 px-1">
        <h3 class="text-lg font-black text-gray-900 dark:text-white">Add Exercise</h3>
        
        <!-- Collapsible Search -->
        <div class="relative flex-1 max-w-[40px] transition-all duration-300 ease-out" 
             :class="searchOpen ? 'max-w-full' : 'max-w-[40px]'">
            <button @click="toggleSearch" 
                    class="absolute right-0 top-0 w-10 h-10 rounded-full bg-gray-100 dark:bg-gray-800 flex items-center justify-center text-gray-500 hover:text-emerald-500 transition-colors z-10">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
            </button>
            <input x-ref="searchInput" 
                   type="text" 
                   x-model="searchQuery" 
                   @input.debounce.300ms="fetchExercises"
                   placeholder="Search..." 
                   class="w-full h-10 rounded-full border-none bg-gray-100 dark:bg-gray-800 focus:ring-2 focus:ring-emerald-500 pl-4 pr-12 text-sm transition-opacity duration-300"
                   :class="searchOpen ? 'opacity-100' : 'opacity-0 pointer-events-none'">
        </div>
    </div>

    <!-- 2. Category Filters (Horizontal Scroll) -->
    <div class="flex space-x-2 overflow-x-auto pb-4 no-scrollbar -mx-6 px-6" id="category-list">
        <template x-for="cat in categories" :key="cat">
            <button @click="setCategory(cat)"
                    class="px-4 py-2 rounded-full text-xs font-bold whitespace-nowrap transition-all border"
                    :class="activeCategory === cat 
                        ? 'bg-gray-900 text-white border-gray-900 dark:bg-emerald-500 dark:border-emerald-500 shadow-md transform scale-105' 
                        : 'bg-white dark:bg-gray-800 text-gray-500 border-gray-200 dark:border-gray-700 hover:border-emerald-400'">
                <span x-text="cat"></span>
            </button>
        </template>
    </div>

    <!-- 3. Exercise Grid -->
    <div class="grid grid-cols-2 gap-3 min-h-[50vh]">
        <!-- Loading Skeleton -->
        <template x-if="loading">
            <div class="col-span-2 grid grid-cols-2 gap-3">
                <div class="h-32 bg-gray-100 dark:bg-gray-800 rounded-2xl animate-pulse"></div>
                <div class="h-32 bg-gray-100 dark:bg-gray-800 rounded-2xl animate-pulse"></div>
                <div class="h-32 bg-gray-100 dark:bg-gray-800 rounded-2xl animate-pulse"></div>
                <div class="h-32 bg-gray-100 dark:bg-gray-800 rounded-2xl animate-pulse"></div>
            </div>
        </template>

        <!-- Cards -->
        <template x-for="ex in exercises" :key="ex.id">
            <div class="relative group h-40">
                <!-- Main Tap Area (Instant Add) -->
                <button @click="addExercise(ex)" 
                        class="w-full h-full text-left bg-gray-900 rounded-2xl shadow-md border border-gray-200 dark:border-gray-700 hover:shadow-xl transition-all active:scale-95 flex flex-col justify-end overflow-hidden relative group-hover:ring-2 ring-emerald-500 ring-offset-2 dark:ring-offset-gray-900">
                    
                    <!-- Background Image -->
                    <template x-if="ex.image_url">
                        <img :src="ex.image_url" 
                             onerror="this.style.display='none'"
                             class="absolute inset-0 w-full h-full object-cover opacity-60 group-hover:opacity-40 transition-opacity" 
                             alt="Exercise Image">
                    </template>
                    <template x-if="!ex.image_url">
                         <div class="absolute inset-0 bg-gradient-to-br from-gray-800 to-black opacity-80"></div>
                    </template>

                    <!-- Gradient Overlay -->
                    <div class="absolute inset-0 bg-gradient-to-t from-black via-black/50 to-transparent"></div>

                    <!-- Flash Overlay on Click -->
                    <div class="absolute inset-0 bg-emerald-500/30 opacity-0 transition-opacity duration-200 z-20" 
                         :class="ex.justAdded ? 'opacity-100' : ''"></div>

                    <!-- Content -->
                    <div class="relative z-10 p-4">
                        <span class="text-[10px] font-bold text-emerald-400 uppercase tracking-wider block mb-1" x-text="ex.muscle_group"></span>
                        <h4 class="text-lg font-black text-white leading-tight line-clamp-2 shadow-sm" x-text="ex.name" style="text-shadow: 0 2px 4px rgba(0,0,0,0.5);"></h4>
                    </div>

                    <!-- Add Icon (Visual Cue) -->
                    <div class="absolute top-3 right-3 text-white opacity-0 group-hover:opacity-100 transition-opacity z-10">
                        <div class="bg-emerald-500 rounded-full p-1 shadow-lg">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"></path></svg>
                        </div>
                    </div>
                </button>
                
                <!-- Footer: Quick Templates (Floating over bottom) - ACTUALLY, move to bottom sheet or below card? 
                     User said: "Template chips become optional modifiers". 
                     If card is image-based, putting buttons INSIDE might clutter image.
                     Let's put small chips BELOW the card or overlaid at bottom?
                     Overlaid at bottom might be hard to hit.
                     Let's try putting them below the card title inside the padding?
                     Or maybe just keep it simple: Tap card = add default. Long press = options? 
                     No, web doesn't do long press reliably.
                     Let's add a small "More" or "Options" dot?
                     Actually, user asked for "template chips".
                     Let's place them as bubbles on the image bottom right?
                     Or below the card grid item?
                     Grid gap is small.
                     Let's put them absolute bottom row z-20.
                -->
                 <div class="absolute bottom-3 right-3 flex gap-1 z-20" @click.stop>
                        <button @click="setTemplate(ex, 3, 12)" 
                                class="text-[9px] font-black px-2 py-0.5 rounded-md bg-white/20 backdrop-blur-md text-white border border-white/10 hover:bg-emerald-500 transition-colors"
                >
                            3x12
                        </button>
                        <button @click="setTemplate(ex, 4, 8)" 
                                class="text-[9px] font-black px-2 py-0.5 rounded-md bg-white/20 backdrop-blur-md text-white border border-white/10 hover:bg-emerald-500 transition-colors"
             >
                            4x8
                        </button>
                 </div>
            </div>
        </template>
        
        <!-- Empty State -->
        <div x-show="!loading && exercises.length === 0" class="col-span-2 text-center py-12 text-gray-400" x-cloak>
            <p class="mb-4">No exercises found.</p>
            <button @click="openCreateModal()" 
                    class="px-6 py-3 bg-gray-900 dark:bg-emerald-600 text-white rounded-xl font-bold hover:scale-105 transition-transform shadow-lg">
                Create "<span x-text="searchQuery || 'New Exercise'"></span>"
            </button>
        </div>
    </div>

    <!-- Custom Exercise Modal -->
    <div class="fixed inset-0 z-[110] flex items-center justify-center px-4" 
         x-show="modalOpen" 
         x-cloak>
        
        <!-- Backdrop -->
        <div class="absolute inset-0 bg-black/60 backdrop-blur-sm" 
             x-show="modalOpen"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             @click="closeCreateModal()"></div>

        <!-- Modal Card -->
        <div class="bg-white dark:bg-gray-900 w-full max-w-sm rounded-3xl p-6 shadow-2xl transform transition-all relative z-10"
             x-show="modalOpen"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 scale-95 translate-y-4"
             x-transition:enter-end="opacity-100 scale-100 translate-y-0"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100 scale-100 translate-y-0"
             x-transition:leave-end="opacity-0 scale-95 translate-y-4">
            
            <h3 class="text-xl font-black text-gray-900 dark:text-white mb-4">New Custom Exercise</h3>
            
            <div class="space-y-4">
                <div>
                    <label class="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">Name</label>
                    <input type="text" 
                           x-ref="newExerciseInput"
                           x-model="newExerciseName" 
                           class="w-full bg-gray-50 dark:bg-gray-800 border-transparent focus:bg-white dark:focus:bg-gray-700 focus:ring-2 focus:ring-emerald-500 rounded-xl h-12 px-4 font-bold text-gray-900 dark:text-white transition-all"
                           placeholder="e.g. Zottman Curl">
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">Muscle Group</label>
                    <div class="flex flex-wrap gap-2">
                        <template x-for="m in muscleGroups" :key="m">
                            <button @click="newExerciseMuscle = m"
                                    type="button"
                                    class="px-3 py-1.5 rounded-lg text-xs font-bold border transition-all"
                                    :class="newExerciseMuscle === m 
                                        ? 'bg-emerald-500 text-white border-emerald-500 shadow-md' 
                                        : 'bg-gray-50 dark:bg-gray-800 text-gray-500 border-gray-200 dark:border-gray-700 hover:border-emerald-400'">
                                <span x-text="m"></span>
                            </button>
                        </template>
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">Image (Optional)</label>
                    <input type="file" 
                           x-ref="newExerciseImage"
                           accept="image/*"
                           class="w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-xs file:font-semibold file:bg-emerald-50 file:text-emerald-700 hover:file:bg-emerald-100 transition-all">
                </div>
            </div>

            <div class="flex gap-3 mt-8">
                <button @click="closeCreateModal()" class="flex-1 py-3 bg-gray-100 dark:bg-gray-800 text-gray-500 hover:text-gray-900 dark:hover:text-white font-bold rounded-xl transition-colors">
                    Cancel
                </button>
                <button @click="submitCreateExercise()" class="flex-1 py-3 bg-emerald-500 hover:bg-emerald-600 text-white font-bold rounded-xl shadow-lg shadow-emerald-500/20 transition-all">
                    Create & Add
                </button>
            </div>
        </div>
    </div>

    <!-- Floating Toast -->
    <div class="fixed bottom-24 left-1/2 -translate-x-1/2 px-6 py-3 bg-gray-900 dark:bg-white dark:text-gray-900 text-white rounded-full shadow-2xl flex items-center gap-2 pointer-events-none transition-all duration-300 z-[100]"
         x-show="toast.visible"
         x-transition:enter="translate-y-10 opacity-0"
         x-transition:enter-end="translate-y-0 opacity-100"
         x-transition:leave="translate-y-0 opacity-100"
         x-transition:leave-end="translate-y-10 opacity-0"
         x-cloak>
        <svg class="w-5 h-5 text-emerald-400 dark:text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path></svg>
        <span class="font-bold text-sm" x-text="toast.message">Added!</span>
    </div>
</div>

<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('exercisePicker', () => ({
            categories: ['All', 'Chest', 'Back', 'Legs', 'Shoulders', 'Arms', 'Core', 'Cardio'],
            activeCategory: 'All',
            searchOpen: false,
            searchQuery: '',
            exercises: [],
            loading: true,
            toast: { visible: false, message: '' },
            dayId: {{ $day->id }},
            
            // Modal State
            modalOpen: false,
            newExerciseName: '',
            newExerciseMuscle: 'Chest',
            muscleGroups: ['Chest', 'Back', 'Legs', 'Shoulders', 'Arms', 'Core', 'Cardio'],

            init() {
                this.fetchExercises();
            },

            openCreateModal() {
                this.newExerciseName = this.searchQuery;
                this.newExerciseMuscle = this.activeCategory === 'All' ? 'Chest' : this.activeCategory;
                this.modalOpen = true;
                this.$nextTick(() => this.$refs.newExerciseInput.focus());
            },

            closeCreateModal() {
                this.modalOpen = false;
                // Reset file input if possible
                if(this.$refs.newExerciseImage) this.$refs.newExerciseImage.value = '';
            },

            async submitCreateExercise() {
                if (!this.newExerciseName) return;
                
                try {
                    const formData = new FormData();
                    formData.append('name', this.newExerciseName);
                    formData.append('muscle_group', this.newExerciseMuscle);
                    
                    const fileInput = this.$refs.newExerciseImage;
                    if (fileInput && fileInput.files.length > 0) {
                        formData.append('image', fileInput.files[0]);
                    }

                    const res = await fetch('{{ route('exercises.store') }}', {
                        method: 'POST',
                        headers: {
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                            // No Content-Type header! Let browser set boundary.
                        },
                        body: formData
                    });
                    
                    if (!res.ok) throw new Error('Failed to create');
                    
                    const newExercise = await res.json();
                    
                    // Add UI fields
                    const exerciseWithUi = {
                         ...newExercise,
                         justAdded: false,
                         selectedTemplate: null,
                         sets: 3,
                         reps: 10
                    };

                    this.exercises.unshift(exerciseWithUi);
                    await this.addExercise(exerciseWithUi);
                    
                    this.searchQuery = '';
                    this.fetchExercises();
                    this.closeCreateModal();
                    
                } catch (e) {
                    alert('Error creating exercise. Please try again.');
                }
            },

            toggleSearch() {
                this.searchOpen = !this.searchOpen;
                if (this.searchOpen) {
                    this.$nextTick(() => this.$refs.searchInput.focus());
                } else {
                    this.searchQuery = '';
                    this.fetchExercises();
                }
            },

            setCategory(cat) {
                this.activeCategory = cat;
                this.fetchExercises();
            },

            async fetchExercises() {
                this.loading = true;
                try {
                    const params = new URLSearchParams({
                        category: this.activeCategory,
                        q: this.searchQuery
                    });
                    const res = await fetch(`/exercises/search?${params}`);
                    const data = await res.json();
                    
                    // Add UI state fields
                    this.exercises = data.map(e => ({
                        ...e,
                        justAdded: false,
                        selectedTemplate: null, // default
                        sets: 3, // default
                        reps: 10 // default
                    }));
                } catch (e) {
                    console.error('Fetch error:', e);
                } finally {
                    this.loading = false;
                }
            },

            setTemplate(exercise, sets, reps) {
                exercise.sets = sets;
                exercise.reps = reps;
                exercise.selectedTemplate = `${sets}x${reps}`;
                // Optional: Auto-add on template click? 
                // User requirement: "Template chips become optional modifiers, not required step".
                // So clicking them just selects logic, doesn't add yet?
                // OR clicking them IS the add action?
                // "Tapping exercise name or + instantly adds... Template chips become optional modifiers"
                // Implies tap adds default. Tap chip sets modifiers for subsequent tap? 
                // Or tap chip adds WITH those modifiers?
                // Let's make tap chip add WITH those modifiers for speed.
                this.addExercise(exercise);
            },

            async addExercise(exercise) {
                // Determine values
                const sets = exercise.sets || 3;
                const reps = exercise.reps || 10;

                try {
                    const res = await fetch(`/routine-days/${this.dayId}/exercises`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        },
                        body: JSON.stringify({
                            exercise_id: exercise.id,
                            target_sets: sets,
                            rep_min: reps,
                            rep_max: reps,
                            order_index: 999 
                        })
                    });

                    if (res.ok) {
                        // Success Feedback
                        exercise.justAdded = true;
                        this.showToast(`Added ${exercise.name}`);
                        
                        // Reset flash
                        setTimeout(() => {
                            exercise.justAdded = false;
                        }, 300);
                        
                        // Fire event for parent to optionally reload list
                        window.dispatchEvent(new CustomEvent('exercise-added'));
                    }
                } catch (e) {
                    alert('Failed to add');
                }
            },



            showToast(msg) {
                this.toast.message = msg;
                this.toast.visible = true;
                setTimeout(() => this.toast.visible = false, 2000);
            }
        }));
    });
</script>
