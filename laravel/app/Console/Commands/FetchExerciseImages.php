<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Exercise;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;

class FetchExerciseImages extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'exercises:fetch-images';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fetch or ensure generic images for exercises based on slugs';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Scanning exercises...');

        $exercises = Exercise::all();
        $directory = public_path('images/exercises');

        if (!File::exists($directory)) {
            File::makeDirectory($directory, 0755, true);
        }

        // Ensure default image exists
        $defaultPath = $directory . '/default.webp';
        if (!File::exists($defaultPath)) {
            $this->info('Downloading default image...');
            // Use a generic gym placeholder from Unsplash
            $defaultUrl = 'https://images.unsplash.com/photo-1517836357463-d25dfeac3438?w=800&q=80&fm=webp';
            try {
                $content = file_get_contents($defaultUrl);
                if ($content) {
                    File::put($defaultPath, $content);
                    $this->info('Default image created.');
                }
            } catch (\Exception $e) {
                $this->error('Failed to download default image: ' . $e->getMessage());
            }
        }

        foreach ($exercises as $exercise) {
            $slug = Str::slug($exercise->name);
            $path = $directory . '/' . $slug . '.webp';

            if (File::exists($path)) {
                $this->line("Image exists for: {$exercise->name} ({$slug})");
                continue;
            }

            // For now, we won't strictly download specific images for every exercise dynamically 
            // to avoid hitting API limits or copyright issues in this automated script 
            // unless we have a specific source.
            // The constraint was "Do NOT hotlink random internet images at runtime".
            // Downloading them ONCE here is allowed.
            
            // Strategy: Try to find a relevant image from a free source or just leave it for the default fallback.
            // User asked: "(Optional) create an artisan command... Provide mapping approach".
            
            $this->warn("Missing image for: {$exercise->name} -> {$slug}.webp");
            
            // Optional: Uncomment to simple-fetch from a placeholder service text
            // $url = "https://placehold.co/800x600/1f2937/white/webp?text=" . urlencode($exercise->name);
            // File::put($path, file_get_contents($url));
            // $this->info("Generated placeholder for {$exercise->name}");
        }

        $this->info('Done. Add images manually to: ' . $directory);
    }
}
