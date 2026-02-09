<?php

namespace App\Http\Controllers;

use App\Models\Exercise;
use Illuminate\Http\Request;

class ExerciseController extends Controller
{
    public function search(Request $request)
    {
        $query = $request->get('q');
        $category = $request->get('category'); // mapped from muscle_group
        
        $exercises = Exercise::query()
            ->where(function ($q) {
                $q->where('user_id', auth()->id())
                  ->orWhereNull('user_id');
            });

        if ($query) {
            $exercises->where('name', 'like', "%{$query}%");
        }

        if ($category && $category !== 'All') {
            // Simple mapping for MVP. 
            // In a real app, we might have a distinct Category model or tighter mapping.
            // For now, we map UI categories to muscle groups.
            $map = [
                'Chest' => ['Chest'],
                'Back' => ['Back', 'Lats', 'Traps'],
                'Legs' => ['Quadriceps', 'Hamstrings', 'Calves', 'Glutes', 'Legs'],
                'Shoulders' => ['Shoulders'],
                'Arms' => ['Biceps', 'Triceps', 'Forearms'],
                'Core' => ['Abs', 'Core'],
                'Cardio' => ['Cardio'],
            ];

            if (isset($map[$category])) {
                $exercises->whereIn('muscle_group', $map[$category]);
            } else {
                 // Fallback: exact match if not mapped
                $exercises->where('muscle_group', $category);
            }
        }

        return response()->json($exercises->orderBy('name')->get());
    }
}
