@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-6 max-w-4xl">
    <h1 class="text-2xl font-bold text-gray-800 mb-6">Progress & Analysis</h1>

    <!-- Exercise Selector -->
    <div class="bg-white rounded-lg shadow p-6 mb-6">
        <form action="{{ route('progress.index') }}" method="GET">
            <label for="exercise_id" class="block text-sm font-medium text-gray-700 mb-2">Select Exercise to Analyze</label>
            <div class="flex gap-4">
                <select name="exercise_id" id="exercise_id" class="w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500" onchange="this.form.submit()">
                    <option value="">Select an exercise...</option>
                    @foreach($exercises as $exercise)
                        <option value="{{ $exercise->id }}" {{ (isset($selectedExercise) && $selectedExercise->id == $exercise->id) ? 'selected' : '' }}>
                            {{ $exercise->name }}
                        </option>
                    @endforeach
                </select>
                <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded hover:bg-blue-700">Analyze</button>
            </div>
        </form>
    </div>

    @if($selectedExercise && $analysis)
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <!-- Status Card -->
            <div class="bg-white rounded-lg shadow p-6 border-t-4 
                @if($analysis['status'] == 'Progressing') border-green-500
                @elseif($analysis['status'] == 'Plateau') border-yellow-500
                @elseif($analysis['status'] == 'Regress') border-red-500
                @else border-gray-500 @endif">
                <h3 class="text-gray-500 text-sm uppercase tracking-wide font-semibold mb-2">Current Status</h3>
                <div class="text-2xl font-bold text-gray-800 mb-1">{{ $analysis['status'] }}</div>
            </div>

            <!-- Recommendation Card -->
            <div class="col-span-1 md:col-span-2 bg-white rounded-lg shadow p-6 border-t-4 border-blue-500">
                <h3 class="text-gray-500 text-sm uppercase tracking-wide font-semibold mb-2">Recommendation for Next Session</h3>
                <div class="flex items-center gap-4">
                    @if($analysis['next_weight'])
                        <div class="bg-blue-50 text-blue-800 px-4 py-2 rounded-lg text-center">
                            <span class="block text-xs font-bold uppercase">Weight</span>
                            <span class="text-xl font-bold">{{ $analysis['next_weight'] }} kg</span>
                        </div>
                    @endif
                    
                    @if($analysis['next_reps'])
                        <div class="bg-blue-50 text-blue-800 px-4 py-2 rounded-lg text-center">
                            <span class="block text-xs font-bold uppercase">Target Reps</span>
                            <span class="text-xl font-bold">{{ $analysis['next_reps'] }}</span>
                        </div>
                    @endif
                    
                    <p class="text-gray-700 italic flex-grow ml-4 border-l pl-4 border-gray-200">
                        "{{ $analysis['recommendation'] }}"
                    </p>
                </div>
            </div>
        </div>

        <!-- Chart -->
        <div class="bg-white rounded-lg shadow p-6 mb-8">
            <h3 class="font-bold text-gray-800 mb-4">Strength Progress</h3>
            <canvas id="progressChart" height="100"></canvas>
        </div>

        <!-- Chart.js -->
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        <script>
            const ctx = document.getElementById('progressChart');
            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: @json($chartData['labels']),
                    datasets: [{
                        label: 'Weight (kg)',
                        data: @json($chartData['data']),
                        borderColor: 'rgb(59, 130, 246)',
                        backgroundColor: 'rgba(59, 130, 246, 0.1)',
                        borderWidth: 2,
                        fill: true,
                        tension: 0.1
                    }]
                },
                options: {
                    responsive: true,
                    scales: {
                        y: {
                            beginAtZero: false
                        }
                    }
                }
            });
        </script>

        <!-- History Table -->
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                <h3 class="font-bold text-gray-800">Recent Performance (Best Set)</h3>
            </div>
            @if(count($history) > 0)
                <table class="min-w-full divide-y divide-gray-200">
                    <thead>
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                            <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Weight (kg)</th>
                            <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Reps</th>
                            <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">1RM Est.</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($history as $session)
                            @php
                                // Epley Formula for 1RM
                                $oneRm = $session['weight'] * (1 + $session['reps'] / 30);
                            @endphp
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $session['date']->format('M d, Y') }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-center font-bold text-gray-800">{{ $session['weight'] }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-center text-gray-600">{{ $session['reps'] }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-center text-gray-400">{{ round($oneRm, 1) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <p class="p-6 text-gray-500 italic">No history found for this exercise.</p>
            @endif
        </div>
    @elseif(isset($exercises) && count($exercises) == 0)
        <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4">
            <div class="flex">
                <div class="ml-3">
                    <p class="text-sm text-yellow-700">
                        You haven't completed any workouts yet. <a href="{{ route('workouts.create') }}" class="font-medium underline text-yellow-700 hover:text-yellow-600">Start a workout</a> to see progress.
                    </p>
                </div>
            </div>
        </div>
    @endif
</div>
@endsection
