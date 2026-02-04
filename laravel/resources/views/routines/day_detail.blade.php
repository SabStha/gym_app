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
        <div class="mt-4 pt-4 border-t border-gray-100">
            <h4 class="text-sm font-bold text-gray-700 mb-2">Add Exercise</h4>
            <form action="{{ route('day-exercises.store', $day) }}" method="POST" class="grid grid-cols-1 md:grid-cols-12 gap-3 items-end">
                @csrf
                
                <div class="md:col-span-3">
                    <label class="block text-xs font-medium text-gray-500 mb-1">Exercise</label>
                    <select name="exercise_id" class="w-full border-gray-300 rounded shadow-sm text-sm focus:ring-blue-500 focus:border-blue-500" required>
                        <option value="">Select...</option>
                        @foreach($exercises as $ex)
                            <option value="{{ $ex->id }}">{{ $ex->name }} ({{ $ex->muscle_group }})</option>
                        @endforeach
                    </select>
                </div>

                <div class="md:col-span-1">
                    <label class="block text-xs font-medium text-gray-500 mb-1">Order</label>
                    <input type="number" name="order_index" value="{{ $day->dayExercises->count() + 1 }}" class="w-full border-gray-300 rounded shadow-sm text-sm p-2" required>
                </div>

                <div class="md:col-span-2">
                    <label class="block text-xs font-medium text-gray-500 mb-1">Sets</label>
                    <input type="number" name="target_sets" value="3" class="w-full border-gray-300 rounded shadow-sm text-sm p-2" required>
                </div>

                <div class="md:col-span-2">
                    <label class="block text-xs font-medium text-gray-500 mb-1">Reps</label>
                    <div class="flex space-x-1">
                        <input type="number" name="rep_min" placeholder="Min" value="8" class="w-1/2 border-gray-300 rounded shadow-sm text-sm p-2" required>
                        <input type="number" name="rep_max" placeholder="Max" value="12" class="w-1/2 border-gray-300 rounded shadow-sm text-sm p-2" required>
                    </div>
                </div>

                <div class="md:col-span-2">
                    <label class="block text-xs font-medium text-gray-500 mb-1">Inc Override</label>
                    <input type="number" step="0.5" name="increment_override_kg" placeholder="Default" class="w-full border-gray-300 rounded shadow-sm text-sm p-2">
                </div>

                <div class="md:col-span-2">
                    <button type="submit" class="w-full bg-gray-800 text-white rounded shadow-sm text-sm py-2 hover:bg-gray-700">Add</button>
                </div>
            </form>
        </div>
    </div>
</div>
