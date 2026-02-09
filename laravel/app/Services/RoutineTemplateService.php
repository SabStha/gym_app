<?php

namespace App\Services;

class RoutineTemplateService
{
    /**
     * Get available templates and their day structures.
     */
    public static function getTemplates()
    {
        return [
            'Push Pull Legs' => ['Push Day', 'Pull Day', 'Leg Day'],
            'Upper Lower' => ['Upper Body', 'Lower Body'],
            'Full Body' => ['Full Body A', 'Full Body B'],
            'Split (4 Day)' => ['Chest & Tris', 'Back & Bis', 'Legs', 'Shoulders & Abs'],
            'Split (5 Day)' => ['Chest', 'Back', 'Arms', 'Legs', 'Shoulders'],
        ];
    }

    /**
     * Apply a template to a routine.
     */
    public static function applyTemplate($routine, $templateKey)
    {
        $templates = self::getTemplates();

        if (!isset($templates[$templateKey])) {
            return;
        }

        $days = $templates[$templateKey];

        foreach ($days as $index => $dayName) {
            $routine->routineDays()->create([
                'day_name' => $dayName,
                'order_index' => $index,
            ]);
        }
    }
}
