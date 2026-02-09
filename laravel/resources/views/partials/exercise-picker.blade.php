<div id="exercise-picker" class="mt-8">
    <h3 class="text-lg font-bold text-gray-800 dark:text-white mb-4 px-1">Add Exercise</h3>

    <!-- Categories -->
    <div class="flex space-x-2 overflow-x-auto pb-4 no-scrollbar" id="category-list">
        @foreach(['All', 'Chest', 'Back', 'Legs', 'Shoulders', 'Arms', 'Core', 'Cardio'] as $cat)
            <button type="button" 
                    onclick="loadExercises('{{ $cat }}', this)"
                    class="category-chip px-5 py-2 rounded-full text-sm font-bold whitespace-nowrap transition-all
                           {{ $loop->first ? 'bg-emerald-600 text-white shadow-lg shadow-emerald-200' : 'bg-white text-gray-600 border border-gray-200 hover:border-emerald-500' }}">
                {{ $cat }}
            </button>
        @endforeach
    </div>

    <!-- Search (Optional) -->
    <div class="mb-4 relative">
        <input type="text" id="exercise-search" placeholder="Search exercises..." 
               class="w-full h-12 rounded-2xl border-gray-200 bg-gray-50 focus:bg-white focus:border-emerald-500 px-4 pl-11 text-sm shadow-sm">
        <svg class="w-5 h-5 text-gray-400 absolute left-4 top-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
    </div>

    <!-- Swipe Deck -->
    <div id="exercise-deck" class="flex overflow-x-auto snap-x snap-mandatory gap-4 pb-8 no-scrollbar" style="scroll-padding-left: 1rem; scroll-padding-right: 1rem;">
        <!-- Loading State -->
        <div class="snap-center shrink-0 w-[85vw] md:w-[320px] h-80 bg-gray-100 rounded-3xl animate-pulse flex items-center justify-center">
            <span class="text-gray-400 font-bold">Loading...</span>
        </div>
    </div>
</div>

<!-- Toast -->
<div id="toast" class="fixed bottom-24 left-1/2 transform -translate-x-1/2 bg-gray-900 text-white px-6 py-3 rounded-full shadow-xl opacity-0 transition-opacity duration-300 pointer-events-none z-50 flex items-center space-x-2">
    <svg class="w-5 h-5 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
    <span class="font-bold text-sm">Exercise Added</span>
</div>

<script>
    let currentCategory = 'All';
    let exercisesCache = {};
    const deck = document.getElementById('exercise-deck');
    const routineDayId = "{{ $day->id }}"; // Context from parent

    async function loadExercises(category, btn = null) {
        currentCategory = category;
        
        // Update UI chips
        if (btn) {
            document.querySelectorAll('.category-chip').forEach(c => {
                 c.className = 'category-chip px-5 py-2 rounded-full text-sm font-bold whitespace-nowrap transition-all bg-white text-gray-600 border border-gray-200 hover:border-emerald-500';
            });
            btn.className = 'category-chip px-5 py-2 rounded-full text-sm font-bold whitespace-nowrap transition-all bg-emerald-600 text-white shadow-lg shadow-emerald-200';
        }

        // Fetch
        deck.innerHTML = '<div class="snap-center shrink-0 w-[85vw] md:w-[320px] h-80 bg-gray-100 rounded-3xl animate-pulse flex items-center justify-center"><span class="text-gray-400 font-bold">Loading...</span></div>';
        
        try {
            const url = `/exercises/search?category=${encodeURIComponent(category)}`;
            const res = await fetch(url);
            const exercises = await res.json();
            renderCards(exercises);
        } catch (e) {
            deck.innerHTML = '<div class="snap-center w-full text-center text-gray-500 py-10">Error loading exercises.</div>';
        }
    }

    function renderCards(exercises) {
        deck.innerHTML = '';
        if (exercises.length === 0) {
            deck.innerHTML = '<div class="snap-center w-full text-center text-gray-400 py-10 font-bold">No exercises found.</div>';
            return;
        }

        // Add spacer for first item center alignment on mobile if needed, or just let scroll-padding handle it (done in CSS style)
        
        exercises.forEach(ex => {
            const card = document.createElement('div');
            card.className = 'snap-center shrink-0 w-[85vw] md:w-[320px] bg-white rounded-3xl shadow-sm border border-gray-100 p-6 flex flex-col justify-between relative overflow-hidden';
            
            card.innerHTML = `
                <div>
                    <div class="flex justify-between items-start mb-2">
                        <span class="bg-gray-100 text-gray-600 text-[10px] font-bold px-2 py-1 rounded-lg uppercase tracking-wider">${ex.muscle_group}</span>
                    </div>
                    <h4 class="text-xl font-extrabold text-gray-900 leading-tight mb-1">${ex.name}</h4>
                </div>

                <div class="space-y-3 mt-4">
                    <div class="grid grid-cols-2 gap-2">
                        <button onclick="setQuickAdd(this, 3, 8)" class="preset-btn py-2 bg-gray-50 rounded-xl text-xs font-bold text-gray-600 hover:bg-emerald-50 hover:text-emerald-700 transition-colors border border-transparent hover:border-emerald-200">3 x 8</button>
                        <button onclick="setQuickAdd(this, 3, 10)" class="preset-btn py-2 bg-emerald-50 rounded-xl text-xs font-bold text-emerald-700 border border-emerald-200 ring-1 ring-emerald-500">3 x 10</button>
                        <button onclick="setQuickAdd(this, 4, 8)" class="preset-btn py-2 bg-gray-50 rounded-xl text-xs font-bold text-gray-600 hover:bg-emerald-50 hover:text-emerald-700 transition-colors border border-transparent hover:border-emerald-200">4 x 8</button>
                        <button onclick="setQuickAdd(this, 4, 12)" class="preset-btn py-2 bg-gray-50 rounded-xl text-xs font-bold text-gray-600 hover:bg-emerald-50 hover:text-emerald-700 transition-colors border border-transparent hover:border-emerald-200">4 x 12</button>
                    </div>

                    <button onclick="addExercise(${ex.id}, this)" 
                            class="w-full py-3.5 bg-gray-900 text-white font-bold rounded-2xl shadow-lg shadow-gray-300 hover:bg-black active:scale-[0.98] transition-all flex justify-center items-center space-x-2 main-add-btn">
                        <span>Add to Day</span>
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
                    </button>
                </div>
            `;
            deck.appendChild(card);
        });
    }

    // Helper to toggle preset buttons
    window.setQuickAdd = function(btn, sets, reps) {
        // Find parent card
        const card = btn.closest('.snap-center');
        // Reset all presets in card
        card.querySelectorAll('.preset-btn').forEach(b => {
             b.className = 'preset-btn py-2 bg-gray-50 rounded-xl text-xs font-bold text-gray-600 hover:bg-emerald-50 hover:text-emerald-700 transition-colors border border-transparent hover:border-emerald-200';
        });
        // Highlight clicked
        btn.className = 'preset-btn py-2 bg-emerald-50 rounded-xl text-xs font-bold text-emerald-700 border border-emerald-200 ring-1 ring-emerald-500';
        
        // Store config in the add button
        const addBtn = card.querySelector('.main-add-btn');
        addBtn.dataset.sets = sets;
        addBtn.dataset.reps = reps;
    };

    async function addExercise(exerciseId, btn) {
        // Get defaults or selected
        const sets = btn.dataset.sets || 3;
        const reps = btn.dataset.reps || 10;
        
        // Animate button
        const originalContent = btn.innerHTML;
        btn.innerHTML = '<svg class="animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>';
        btn.disabled = true;

        try {
            const res = await fetch(`/routine-days/${routineDayId}/exercises`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({
                    exercise_id: exerciseId,
                    target_sets: sets,
                    rep_min: reps,
                    rep_max: reps, // simple flat reps for quick add
                    order_index: 999 // backend handles or appends
                })
            });

            if (res.ok) {
                showToast();
                // Optionally scroll to next
                deck.scrollBy({ left: deck.offsetWidth * 0.8, behavior: 'smooth' });
                // Reset button
                setTimeout(() => {
                    btn.innerHTML = 'Added';
                    btn.classList.add('bg-emerald-600', 'text-white');
                    btn.classList.remove('bg-gray-900');
                    // refresh page to see list? For now just toast. User asked to "optionally tap Edit sets/reps later in the day list", so maybe we need to refresh the list part. 
                    // Ideally we append to the table. For MVP simpler to just add.
                    // Let's reload the page slightly delayed so user sees feedback, OR just left it. User: "Automatically advance to next card"
                }, 500);
                 
                // If we want to show it in the list immediately, we'd need to fetch the row or reload. 
                // Let's rely on manual reload for now or next visit, as adding multiple is the goal.
            } else {
                 throw new Error('Failed');
            }
        } catch (e) {
            btn.innerHTML = 'Error';
            alert('Failed to add exercise');
            btn.disabled = false;
        }
    }

    function showToast() {
        const t = document.getElementById('toast');
        t.style.opacity = '1';
        t.style.transform = 'translate(-50%, -20px)';
        setTimeout(() => {
            t.style.opacity = '0';
            t.style.transform = 'translate(-50%, 0)';
        }, 2000);
    }

    // Init with All
    loadExercises('All');

    // Search Logic
    const searchInput = document.getElementById('exercise-search');
    let debounceTimer;
    searchInput.addEventListener('input', (e) => {
        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(() => {
             // For search we might want to keep category context? Or clear it?
             // Let's re-fetch with current category + query
             const val = e.target.value;
             const url = `/exercises/search?category=${encodeURIComponent(currentCategory)}&q=${encodeURIComponent(val)}`;
             fetch(url).then(r => r.json()).then(renderCards);
        }, 300);
    });

</script>
