<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\RoutineController;
use App\Http\Controllers\RoutineDayController;
use App\Http\Controllers\DayExerciseController;
use App\Http\Controllers\WorkoutController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\HistoryController;
use App\Http\Controllers\ProgressController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

// Redirect root to dashboard if logged in, otherwise login
// Public Landing Page
// Splash Screen (Root)
Route::get('/', function () {
    return view('intro');
});

// Onboarding Flow
Route::get('/onboarding/{step}', function ($step) {
    if (!in_array($step, ['1', '2', '3'])) {
        return redirect('/onboarding/1');
    }
    return view('onboarding', compact('step'));
});

Route::view('/offline', 'offline');

// Dashboard Route with Controller
Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Routine Routes
    Route::get('/routines', [RoutineController::class, 'index'])->name('routines.index');
    Route::get('/routines/create', [RoutineController::class, 'create'])->name('routines.create');
    Route::post('/routines', [RoutineController::class, 'store'])->name('routines.store');
    Route::get('/routines/{routine}', [RoutineController::class, 'show'])->name('routines.show');
    Route::get('/routines/{routine}/edit', [RoutineController::class, 'edit'])->name('routines.edit');
    Route::put('/routines/{routine}', [RoutineController::class, 'update'])->name('routines.update');
    Route::delete('/routines/{routine}', [RoutineController::class, 'destroy'])->name('routines.destroy');
    Route::post('/routines/{routine}/activate', [RoutineController::class, 'activate'])->name('routines.activate');

    // Routine Day Routes
    Route::get('/routines/{routine}/days/{day}', [RoutineDayController::class, 'show'])->name('routine-days.show');
    Route::post('/routines/{routine}/days', [RoutineDayController::class, 'store'])->name('routine-days.store');
    Route::put('/routine-days/{routineDay}', [RoutineDayController::class, 'update'])->name('routine-days.update');
    Route::delete('/routine-days/{routineDay}', [RoutineDayController::class, 'destroy'])->name('routine-days.destroy');

    // Day Exercise Routes
    Route::post('/routine-days/{routineDay}/exercises', [DayExerciseController::class, 'store'])->name('day-exercises.store');
    Route::delete('/day-exercises/{dayExercise}', [DayExerciseController::class, 'destroy'])->name('day-exercises.destroy');
    
    // Exercise Search
    Route::get('/exercises/search', [App\Http\Controllers\ExerciseController::class, 'search'])->name('exercises.search');

    // Workout Routes
    Route::get('/workouts/start', [WorkoutController::class, 'create'])->name('workouts.create');
    Route::post('/workouts/start', [WorkoutController::class, 'start'])->name('workouts.start');
    Route::get('/workouts/{workout}', [WorkoutController::class, 'show'])->name('workouts.show');
    Route::post('/workouts/{workout}/finish', [WorkoutController::class, 'finish'])->name('workouts.finish');
    
    // Set Logic (AJAX)
    Route::post('/workouts/{workout}/sets', [App\Http\Controllers\WorkoutSetController::class, 'store'])->name('workout-sets.store');
    Route::get('/workouts/{workout}/suggest', [App\Http\Controllers\WorkoutSetController::class, 'suggest'])->name('workout-sets.suggest');

    // History Routes
    Route::get('/history', [HistoryController::class, 'index'])->name('history.index');
    Route::get('/history/{workout}', [HistoryController::class, 'show'])->name('history.show');

    // Progress Routes
    Route::get('/progress', [ProgressController::class, 'index'])->name('progress.index');
});

require __DIR__.'/auth.php';
