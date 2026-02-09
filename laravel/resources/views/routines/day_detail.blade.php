<div class="bg-white rounded-lg shadow border border-gray-200">
    <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center bg-gray-50 rounded-t-lg">
        <h2 class="text-xl font-semibold text-gray-800">{{ $day->day_name }}</h2>
        <form action="{{ route('routine-days.destroy', $day) }}" method="POST" onsubmit="return confirm('Delete this day and all its exercises?');">
            @csrf
            @method('DELETE')
            <button type="submit" class="text-red-500 hover:text-red-700 text-sm">Remove Day</button>
        </form>
    </div>

    <div class="p-6">
        <!-- Exercises List -->
        @if($day->dayExercises->count() > 0)
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 mb-6">
                    <thead>
                        <tr>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Order</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Exercise</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Sets</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Reps</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Inc (kg)</th>
                            <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @foreach($day->dayExercises->sortBy('order_index') as $exercise)
                            <tr>
                                <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-500">{{ $exercise->order_index }}</td>
                                <td class="px-4 py-2 whitespace-nowrap text-sm font-medium text-gray-900">{{ $exercise->exercise->name }}</td>
                                <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-500">{{ $exercise->target_sets }}</td>
                                <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-500">{{ $exercise->rep_min }} - {{ $exercise->rep_max }}</td>
                                <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-500">
                                    @if($exercise->increment_override_kg)
                                        {{ $exercise->increment_override_kg }}
                                    @else
                                        <span class="text-gray-400">-</span>
                                    @endif
                                </td>
                                <td class="px-4 py-2 whitespace-nowrap text-right text-sm font-medium">
                                    <form action="{{ route('day-exercises.destroy', $exercise) }}" method="POST" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-600 hover:text-red-900">Remove</button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <p class="text-gray-500 italic mb-6">No exercises added to this day yet.</p>
        @endif

        <!-- Add Exercise Form -->
        <div class="mt-4 pt-4 border-t border-gray-100 pb-20">
            @include('partials.exercise-picker', ['day' => $day])
        </div>
    </div>
</div>
