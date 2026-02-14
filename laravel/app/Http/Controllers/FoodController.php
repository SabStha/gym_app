<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Food;
use App\Models\FoodEntry;

use Illuminate\Support\Facades\Auth;

class FoodController extends Controller
{
    public function index(Request $request)
    {
        // JSON API for picker
        $query = $request->input('q');
        
        $foods = Food::where('user_id', Auth::id())
            ->where('name', 'like', "%{$query}%")
            ->orderBy('name')
            ->limit(20)
            ->get();
            
        return response()->json($foods);
    }
    
    public function recents(Request $request)
    {
        $recentIds = FoodEntry::where('user_id', Auth::id())
            ->orderBy('created_at', 'desc')
            ->limit(50)
            ->pluck('food_id')
            ->unique();
            
        $recents = Food::whereIn('id', $recentIds)->get(); // Restore order?
        
        return response()->json($recents);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'brand' => 'nullable|string|max:255',
            'calories' => 'required|integer|min:0',
            'protein' => 'required|numeric|min:0',
            'carbs' => 'required|numeric|min:0',
            'fat' => 'required|numeric|min:0',
            'serving_size' => 'required|integer|min:1',
        ]);

        $food = $request->user()->foods()->create($validated);

        return response()->json($food);
    }
}
