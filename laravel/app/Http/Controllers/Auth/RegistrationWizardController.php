<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserProfile;
use App\Providers\RouteServiceProvider;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;

class RegistrationWizardController extends Controller
{
    public function showStep($step)
    {
        // Ensure steps are visited in order (simple check)
        // In a real app we might check if session has previous step data
        $step = (int) $step;
        if ($step < 1 || $step > 4) {
            return redirect()->route('register.wizard', ['step' => 1]);
        }

        // Get session data for pre-filling
        $data = session('register_data', []);
        
        return view('auth.register-wizard', compact('step', 'data'));
    }

    public function postStep(Request $request, $step)
    {
        $step = (int) $step;
        $data = session('register_data', []);

        if ($step === 1) {
            // Basics
            $validated = $request->validate([
                'name' => ['required', 'string', 'max:255'],
                'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            ]);
            $data = array_merge($data, $validated);
            session(['register_data' => $data]);
            return redirect()->route('register.wizard', ['step' => 2]);
        }

        if ($step === 2) {
            // Body
            $validated = $request->validate([
                'sex' => ['required', 'in:male,female,other'],
                'height_cm' => ['required', 'integer', 'min:100', 'max:250'],
                'current_weight_kg' => ['required', 'numeric', 'min:20', 'max:300'],
            ]);
            $data = array_merge($data, $validated);
            session(['register_data' => $data]);
            return redirect()->route('register.wizard', ['step' => 3]);
        }

        if ($step === 3) {
            // Goal
            $validated = $request->validate([
                'goal_type' => ['required', 'in:lose,gain,maintain'],
                'target_weight_kg' => ['nullable', 'required_if:goal_type,lose,gain', 'numeric', 'min:20', 'max:300'],
            ]);
            
            // If maintain, goal weight = current weight
            if ($validated['goal_type'] === 'maintain') {
                $validated['target_weight_kg'] = $data['current_weight_kg'] ?? null;
            }

            $data = array_merge($data, $validated);
            session(['register_data' => $data]);
            return redirect()->route('register.wizard', ['step' => 4]);
        }

        if ($step === 4) {
            // Security & Final Submit
            $validated = $request->validate([
                'password' => ['required', 'confirmed', Rules\Password::defaults()],
            ]);
            
            // Create User
            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => Hash::make($request->password),
            ]);

            // Create Profile
            $profile = new UserProfile([
                'user_id' => $user->id,
                'sex' => $data['sex'],
                'height_cm' => $data['height_cm'],
                'current_weight_kg' => $data['current_weight_kg'],
                'goal_type' => $data['goal_type'],
                'target_weight_kg' => $data['target_weight_kg'],
                
                // Defaults
                 'goal_preset' => 'strength', // default
                 'default_increment_kg' => 2.5,
            ]);
            $user->profile()->save($profile);

            event(new Registered($user));
            Auth::login($user);
            
            // Clear session
            session()->forget('register_data');

            return redirect(RouteServiceProvider::HOME);
        }

        return redirect()->route('register.wizard', ['step' => 1]);
    }
}
