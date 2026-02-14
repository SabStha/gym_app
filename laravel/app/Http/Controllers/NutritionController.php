<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;


use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class NutritionController extends Controller
{
    public function index(Request $request)
    {
        $date = $request->input('date') ? Carbon::parse($request->input('date')) : Carbon::today();
        $user = Auth::user();
        
        $entries = $user->foodEntries()
            ->with('food')
            ->whereDate('date', $date)
            ->get(); // Ordered by time? FoodEntry handles order? created_at usually.

        $totals = [
            'calories' => $entries->sum('calories'),
            'protein' => $entries->sum('protein'),
            'carbs' => $entries->sum('carbs'),
            'fat' => $entries->sum('fat'),
        ];

        $targets = [
            'calories' => $user->userProfile->target_calories ?? 2000,
            'protein' => $user->userProfile->target_protein ?? 150,
            'carbs' => $user->userProfile->target_carbs ?? 200,
            'fat' => $user->userProfile->target_fat ?? 60,
        ];

        return view('nutrition.index', compact('date', 'entries', 'totals', 'targets'));
    }
}
