<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Food;
use App\Models\FoodEntry;

use Illuminate\Support\Facades\Auth;

class FoodEntryController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'food_id' => 'required|exists:foods,id',
            'grams' => 'required|integer|min:1',
            'date' => 'required|date',
        ]);

        $food = Food::findOrFail($validated['food_id']);
        $ratio = $validated['grams'] / 100;

        // Snapshot macros
        $request->user()->foodEntries()->create([
            'food_id' => $food->id,
            'grams' => $validated['grams'],
            'date' => $validated['date'],
            'calories' => round($food->calories * $ratio),
            'protein' => round($food->protein * $ratio, 1),
            'carbs' => round($food->carbs * $ratio, 1),
            'fat' => round($food->fat * $ratio, 1),
        ]);

        return back()->with('status', 'food-added'); 
    }

    public function destroy(FoodEntry $foodEntry)
    {
        if ($foodEntry->user_id !== Auth::id()) {
            abort(403);
        }

        $foodEntry->delete();

        return back()->with('status', 'food-removed');
    }
}
