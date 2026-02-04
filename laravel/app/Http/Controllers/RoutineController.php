<?php

namespace App\Http\Controllers;

use App\Models\Routine;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RoutineController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $routines = auth()->user()->routines()->latest()->get();
        return view('routines.index', compact('routines'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('routines.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'note' => 'nullable|string',
        ]);

        $routine = auth()->user()->routines()->create($validated);

        return redirect()->route('routines.show', $routine)->with('success', 'Routine created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Routine $routine)
    {
        $this->authorizeUser($routine);
        
        $routine->load(['routineDays.dayExercises.exercise']);
        $exercises = auth()->user()->exercises()->orderBy('name')->get(); // For adding to days
        // Merge with defaults if not custom only
        $defaultExercises = \App\Models\Exercise::whereNull('user_id')->orderBy('name')->get();
        $allExercises = $exercises->merge($defaultExercises)->sortBy('name');

        return view('routines.show', compact('routine', 'allExercises'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Routine $routine)
    {
        $this->authorizeUser($routine);
        return view('routines.edit', compact('routine'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Routine $routine)
    {
        $this->authorizeUser($routine);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'note' => 'nullable|string',
        ]);

        $routine->update($validated);

        return redirect()->route('routines.index')->with('success', 'Routine updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Routine $routine)
    {
        $this->authorizeUser($routine);
        
        $routine->delete();

        return redirect()->route('routines.index')->with('success', 'Routine deleted successfully.');
    }

    /**
     * Set the routine as active.
     */
    public function activate(Routine $routine)
    {
        $this->authorizeUser($routine);

        DB::transaction(function () use ($routine) {
            // Deactivate all user's routines
            auth()->user()->routines()->update(['is_active' => false]);
            // Activate current
            $routine->update(['is_active' => true]);
        });

        return redirect()->back()->with('success', 'Routine activated!');
    }

    private function authorizeUser(Routine $routine)
    {
        if ($routine->user_id !== auth()->id()) {
            abort(403, 'Unauthorized');
        }
    }
}
