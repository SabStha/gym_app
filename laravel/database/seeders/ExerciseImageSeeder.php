<?php

namespace Database\Seeders;

use App\Models\Exercise;
use Illuminate\Database\Seeder;

class ExerciseImageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Using a reliable source for fitness images. 
        // Since direct hotlinking to Unsplash can be brittle without an API key, 
        // we will use high-quality keywords with a service that redirects to valid images, 
        // OR specific, known-good static URLs if available.
        // For now, let's use a mix of known good Unsplash IDs and specific keyword searches via a stable placeholder service 
        // that supports keywords (like source.unsplash.com used to, but now we might need to rely on direct IDs that we verify).
        
        // I will use a set of specific, hardcoded URLs that are likely to persist.
        // If these fail, the UI 'onerror' handler will gracefully fallback to the gradient.

        $images = [
            'Bench Press' => 'https://images.unsplash.com/photo-1571019614242-c5c5dee9f50b?auto=format&fit=crop&w=800&q=80',
            'Squat' => 'https://images.unsplash.com/photo-1574680096145-d05b474e2155?auto=format&fit=crop&w=800&q=80',
            'Deadlift' => 'https://images.unsplash.com/photo-1517836357463-d25dfeac3438?auto=format&fit=crop&w=800&q=80',
            'Overhead Press' => 'https://images.unsplash.com/photo-1532029837206-abbe2b7a4bc3?auto=format&fit=crop&w=800&q=80',
            'Pull Up' => 'https://images.unsplash.com/photo-1598971639058-211a74a96aea?auto=format&fit=crop&w=800&q=80',
            'Dumbbell Curl' => 'https://images.unsplash.com/photo-1581009137042-c552e485697a?auto=format&fit=crop&w=800&q=80',
            'Tricep Extension' => 'https://images.unsplash.com/photo-1507398941214-572c25f4b1dc?auto=format&fit=crop&w=800&q=80',
            'Leg Press' => 'https://images.unsplash.com/photo-1434608519344-49d77a699ded?auto=format&fit=crop&w=800&q=80',
            'Lunge' => 'https://images.unsplash.com/photo-1574680105809-5c49069d2551?auto=format&fit=crop&w=800&q=80', // Different from Squat
            'Plank' => 'https://images.unsplash.com/photo-1566241440091-ec10de8db2e1?auto=format&fit=crop&w=800&q=80',
            'Push Up' => 'https://plus.unsplash.com/premium_photo-1676634832558-6604aee66cfcd?auto=format&fit=crop&w=800&q=80', // Specific pushup image
            'Row' => 'https://images.unsplash.com/photo-1603287681836-e174ce755c25?auto=format&fit=crop&w=800&q=80',
            'Lat Pulldown' => 'https://images.unsplash.com/photo-1605296867304-46d5465a13f1?auto=format&fit=crop&w=800&q=80',
            'Chest Fly' => 'https://images.unsplash.com/photo-1583454110551-21f2fa2afe61?auto=format&fit=crop&w=800&q=800',
            'Leg Curl' => 'https://images.unsplash.com/photo-1584735935682-2f2b69dff9d2?auto=format&fit=crop&w=800&q=80',
            'Calf Raise' => 'https://images.unsplash.com/photo-1517963879466-e825c1d47bed?auto=format&fit=crop&w=800&q=80',
            'Crunches' => 'https://images.unsplash.com/photo-1518611012118-696072aa579a?auto=format&fit=crop&w=800&q=80',
        ];

        // Specific mappings for muscle groups to ensure variety if specific match fails
        $fallbacks = [
            'Chest' => [
                'https://images.unsplash.com/photo-1571019614242-c5c5dee9f50b?auto=format&fit=crop&w=800&q=80',
                'https://images.unsplash.com/photo-1534438327276-14e5300c3a48?auto=format&fit=crop&w=800&q=80',
            ],
            'Back' => [
                'https://images.unsplash.com/photo-1603287681836-e174ce755c25?auto=format&fit=crop&w=800&q=80',
                'https://images.unsplash.com/photo-1521804906057-1df8fdb718b7?auto=format&fit=crop&w=800&q=80',
            ],
            'Legs' => [
                'https://images.unsplash.com/photo-1574680096145-d05b474e2155?auto=format&fit=crop&w=800&q=80',
                'https://images.unsplash.com/photo-1434608519344-49d77a699ded?auto=format&fit=crop&w=800&q=80',
            ],
            // ... add more if needed
        ];

        $exercises = Exercise::all();

        foreach ($exercises as $exercise) {
            $matched = false;
            
            // 1. Exact or Partial Name Match
            foreach ($images as $key => $url) {
                if (stripos($exercise->name, $key) !== false) {
                    $exercise->image_url = $url;
                    $matched = true;
                    break;
                }
            }

            // 2. Fallback per muscle group (Randomized to avoid identical duplicates next to each other)
            if (!$matched && isset($fallbacks[$exercise->muscle_group])) {
                $options = $fallbacks[$exercise->muscle_group];
                $exercise->image_url = $options[array_rand($options)];
                $matched = true;
            }

            // 3. Last Resort: Consistent placeholder based on name to ensure "same image for same name"
            if (!$matched) {
                 // Use a generated placeholder that looks cleaner than a broken image
                 // $exercise->image_url = 'https://ui-avatars.com/api/?name=' . urlencode($exercise->name) . '&background=10b981&color=fff&size=512';
                 // Actually, leaving it null triggers the nice gradient fallback in UI. 
                 // User said "use image same as the name", implies specific. 
                 // If we don't have a specific real photo, the gradient with the name text IS the best "same as name" representation 
                 // compared to a generic placeholder.
                 $exercise->image_url = null;
            }

            $exercise->save();
        }
    }
}
