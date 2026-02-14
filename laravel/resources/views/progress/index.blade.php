@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-6 max-w-4xl" x-data="{ activeTab: '{{ request('tab') === 'body' ? 'body' : 'strength' }}' }">
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-bold text-gray-800 dark:text-white">Progress & Analysis</h1>
        
        <!-- Tab Switcher -->
        <div class="bg-gray-100 dark:bg-gray-700 p-1 rounded-xl flex">
            <button @click="activeTab = 'strength'" 
                    :class="activeTab === 'strength' ? 'bg-white dark:bg-gray-600 shadow text-emerald-600 dark:text-emerald-400' : 'text-gray-500 dark:text-gray-400 hover:text-gray-700'"
                    class="px-4 py-2 rounded-lg text-sm font-bold transition-all">
                Strength
            </button>
            <button @click="activeTab = 'body'" 
                    :class="activeTab === 'body' ? 'bg-white dark:bg-gray-600 shadow text-emerald-600 dark:text-emerald-400' : 'text-gray-500 dark:text-gray-400 hover:text-gray-700'"
                    class="px-4 py-2 rounded-lg text-sm font-bold transition-all">
                Body & Daily
            </button>
        </div>
    </div>

    <!-- STRENGTH TAB -->
    <div x-show="activeTab === 'strength'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0">
        <!-- Exercise Selector -->
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 p-6 mb-6">
            <form action="{{ route('progress.index') }}" method="GET">
                <label for="exercise_id" class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-2 uppercase tracking-wide">Select Exercise</label>
                <div class="flex gap-4">
                    <select name="exercise_id" id="exercise_id" class="w-full bg-gray-50 dark:bg-gray-700 border-none rounded-xl text-gray-900 dark:text-white focus:ring-2 focus:ring-emerald-500" onchange="this.form.submit()">
                        <option value="">Choose exercise...</option>
                        @foreach($exercises as $exercise)
                            <option value="{{ $exercise->id }}" {{ (isset($selectedExercise) && $selectedExercise->id == $exercise->id) ? 'selected' : '' }}>
                                {{ $exercise->name }}
                            </option>
                        @endforeach
                    </select>
                    <button type="submit" class="bg-emerald-600 text-white px-6 py-2 rounded-xl font-bold hover:bg-emerald-700 shadow-lg shadow-emerald-600/30 transition-all">Go</button>
                </div>
            </form>
        </div>

        @if($selectedExercise && $analysis)
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                <!-- Status Card -->
                <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm p-6 border-l-4 
                    @if($analysis['status'] == 'Progressing') border-emerald-500
                    @elseif($analysis['status'] == 'Plateau') border-yellow-500
                    @elseif($analysis['status'] == 'Regress') border-red-500
                    @else border-gray-500 @endif">
                    <h3 class="text-gray-400 text-xs font-bold uppercase tracking-wider mb-2">Current Status</h3>
                    <div class="text-2xl font-black text-gray-900 dark:text-white">{{ $analysis['status'] }}</div>
                </div>

                <!-- Recommendation Card -->
                <div class="col-span-1 md:col-span-2 bg-emerald-50 dark:bg-emerald-900/20 rounded-2xl shadow-sm p-6 border border-emerald-100 dark:border-emerald-800">
                    <h3 class="text-emerald-600 dark:text-emerald-400 text-xs font-bold uppercase tracking-wider mb-2">Next Session Target</h3>
                    <div class="flex items-center gap-6 flex-wrap">
                        @if($analysis['next_weight'])
                            <div>
                                <span class="block text-3xl font-black text-gray-900 dark:text-white">{{ $analysis['next_weight'] }}<span class="text-lg text-gray-500">kg</span></span>
                                <span class="text-xs text-gray-500 font-bold uppercase">Weight</span>
                            </div>
                        @endif
                        
                        @if($analysis['next_reps'])
                            <div>
                                <span class="block text-3xl font-black text-gray-900 dark:text-white">{{ $analysis['next_reps'] }}</span>
                                <span class="text-xs text-gray-500 font-bold uppercase">Reps</span>
                            </div>
                        @endif
                        
                        <div class="flex-grow border-l-2 border-emerald-200 dark:border-emerald-700 pl-4">
                            <p class="text-emerald-800 dark:text-emerald-200 italic font-medium">"{{ $analysis['recommendation'] }}"</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Chart -->
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm p-6 mb-8 border border-gray-100 dark:border-gray-700">
                <h3 class="font-bold text-gray-900 dark:text-white mb-4">Strength Trend</h3>
                <div class="h-64">
                    <canvas id="strengthChart"></canvas>
                </div>
            </div>

            <!-- History Table -->
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm overflow-hidden border border-gray-100 dark:border-gray-700">
                <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700 bg-gray-50 dark:bg-gray-700/50">
                    <h3 class="font-bold text-gray-900 dark:text-white">Recent Bests</h3>
                </div>
                @if(count($history) > 0)
                    <table class="min-w-full divide-y divide-gray-100 dark:divide-gray-700">
                        <thead>
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-bold text-gray-400 uppercase tracking-wider">Date</th>
                                <th class="px-6 py-3 text-center text-xs font-bold text-gray-400 uppercase tracking-wider">Weight</th>
                                <th class="px-6 py-3 text-center text-xs font-bold text-gray-400 uppercase tracking-wider">Reps</th>
                                <th class="px-6 py-3 text-center text-xs font-bold text-gray-400 uppercase tracking-wider">1RM</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                            @foreach($history as $session)
                                @php $oneRm = $session['weight'] * (1 + $session['reps'] / 30); @endphp
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-gray-200">{{ $session['date']->format('M d') }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-center font-bold text-gray-900 dark:text-white">{{ $session['weight'] }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-center text-gray-600 dark:text-gray-400">{{ $session['reps'] }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-center text-gray-400">{{ round($oneRm, 1) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @else
                    <p class="p-6 text-gray-500 italic text-center">No history found for this exercise.</p>
                @endif
            </div>
        @elseif(isset($exercises) && count($exercises) == 0)
            <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 rounded-r-xl">
                 <p class="text-sm text-yellow-700">No workout data available yet. <a href="{{ route('workouts.create') }}" class="font-bold underline">Start training!</a></p>
            </div>
        @elseif(!$selectedExercise)
             <div class="text-center py-12">
                 <div class="bg-gray-100 dark:bg-gray-800 rounded-full w-20 h-20 flex items-center justify-center mx-auto mb-4 text-gray-400">
                     <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 002 2h2a2 2 0 002-2z"></path></svg>
                 </div>
                 <h3 class="text-lg font-bold text-gray-900 dark:text-white">Select an exercise to see progress</h3>
             </div>
        @endif
    </div>

    <!-- BODY & NUTRITION TAB -->
    <div x-show="activeTab === 'body'" x-cloak x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0">
        
        <!-- Weight Chart -->
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm p-6 mb-6 border border-gray-100 dark:border-gray-700">
            <div class="flex items-center justify-between mb-4">
                <h3 class="font-bold text-gray-900 dark:text-white">Body Weight (30 Days)</h3>
                <!-- Add Weight Button (Placeholder for Future) -->
                <button class="text-xs font-bold text-emerald-600 dark:text-emerald-400 bg-emerald-50 dark:bg-emerald-900/30 px-3 py-1 rounded-full">+ Log Weight</button>
            </div>
            <div class="h-64">
                <canvas id="weightChart"></canvas>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Calorie Adherence -->
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm p-6 border border-gray-100 dark:border-gray-700">
                 <h3 class="font-bold text-gray-900 dark:text-white mb-4">Calorie Adherence (7 Days)</h3>
                 <div class="h-48">
                     <canvas id="calorieChart"></canvas>
                 </div>
            </div>

            <!-- Average Macros -->
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm p-6 border border-gray-100 dark:border-gray-700">
                <h3 class="font-bold text-gray-900 dark:text-white mb-4">Avg. Macro Split</h3>
                <div class="flex items-center justify-around h-48">
                    <!-- Simple Stat Blocks instead of chart for clean look -->
                    <div class="text-center">
                        <div class="w-3 h-3 rounded-full bg-blue-500 mx-auto mb-2"></div>
                        <span class="block text-2xl font-black text-gray-900 dark:text-white">{{ round($macroAverages['protein']) }}g</span>
                        <span class="text-xs text-gray-500 font-bold">Protein</span>
                    </div>
                    <div class="text-center">
                        <div class="w-3 h-3 rounded-full bg-amber-500 mx-auto mb-2"></div>
                        <span class="block text-2xl font-black text-gray-900 dark:text-white">{{ round($macroAverages['carbs']) }}g</span>
                        <span class="text-xs text-gray-500 font-bold">Carbs</span>
                    </div>
                    <div class="text-center">
                        <div class="w-3 h-3 rounded-full bg-rose-500 mx-auto mb-2"></div>
                        <span class="block text-2xl font-black text-gray-900 dark:text-white">{{ round($macroAverages['fat']) }}g</span>
                        <span class="text-xs text-gray-500 font-bold">Fat</span>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', () => {
        
        // --- Strength Chart ---
        @if(isset($strengthChartData) && count($strengthChartData['labels']) > 0)
        new Chart(document.getElementById('strengthChart'), {
            type: 'line',
            data: {
                labels: @json($strengthChartData['labels']),
                datasets: [{
                    label: 'Weight (kg)',
                    data: @json($strengthChartData['data']),
                    borderColor: '#10B981', // Emerald 500
                    backgroundColor: 'rgba(16, 185, 129, 0.1)',
                    borderWidth: 3,
                    pointBackgroundColor: '#fff',
                    pointBorderColor: '#10B981',
                    pointRadius: 4,
                    fill: true,
                    tension: 0.3
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: { 
                    y: { 
                        beginAtZero: false,
                        grid: { color: 'rgba(0,0,0,0.05)' } 
                    },
                    x: { grid: { display: false } }
                }
            }
        });
        @endif

        // --- Weight Chart ---
        const weightCtx = document.getElementById('weightChart');
        if(weightCtx) {
            new Chart(weightCtx, {
                type: 'line',
                data: {
                    labels: @json($weightChartData['labels']),
                    datasets: [{
                        label: 'Weight (kg)',
                        data: @json($weightChartData['data']),
                        borderColor: '#3B82F6', // Blue 500
                        backgroundColor: 'rgba(59, 130, 246, 0.05)',
                        borderWidth: 3,
                        pointRadius: 0,
                        pointHoverRadius: 4,
                        fill: true,
                        tension: 0.4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { display: false } },
                    scales: { 
                        y: { beginAtZero: false, grid: { color: 'rgba(0,0,0,0.05)' } },
                        x: { grid: { display: false } }
                    }
                }
            });
        }

        // --- Calorie Chart ---
        const calorieCtx = document.getElementById('calorieChart');
        if(calorieCtx) {
            new Chart(calorieCtx, {
                type: 'bar',
                data: {
                    labels: @json($calorieChartData['labels']),
                    datasets: [
                        {
                            label: 'Actual',
                            data: @json($calorieChartData['actual']),
                            backgroundColor: '#10B981',
                            borderRadius: 4
                        },
                        {
                            label: 'Goal',
                            data: @json($calorieChartData['target']),
                            backgroundColor: '#E5E7EB',
                            borderRadius: 4,
                            barThickness: 10,
                            grouped: false, // Overlap
                            order: 1
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { display: false } },
                    scales: { 
                        y: { display: false },
                        x: { grid: { display: false } }
                    }
                }
            });
        }
    });
</script>
@endsection
