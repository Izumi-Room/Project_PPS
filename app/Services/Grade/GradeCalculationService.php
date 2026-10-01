<?php

namespace App\Services\Grade;

use App\Models\CourseConversion;

class GradeCalculationService
{
    /**
     * Calculate final grade for a course conversion.
     * 
     * Formula: Σ(nilai × bobot) / Σ(bobot)
     */
    public function calculate(CourseConversion $conversion): float
    {
        $submissions = $conversion->studentSubmissions()
            ->whereHas('component', function ($q) {
                $q->where('is_active', true);
            })
            ->with('component')
            ->get();

        // Include seminar if required
        $seminar = $conversion->seminars()
            ->where('status', 'DISETUJUI')
            ->first();

        $totalWeightedScore = 0;
        $totalWeight = 0;

        foreach ($submissions as $submission) {
            $score = (float) ($submission->score ?? 0);
            $weight = (int) ($submission->component->weight ?? 0);
            
            $totalWeightedScore += ($score * $weight);
            $totalWeight += $weight;
        }

        if ($seminar && $seminar->score) {
            $weight = (int) ($seminar->weight ?? 20); // Default seminar weight
            $totalWeightedScore += ((float) $seminar->score * $weight);
            $totalWeight += $weight;
        }

        return $totalWeight > 0 ? ($totalWeightedScore / $totalWeight) : 0;
    }
}
