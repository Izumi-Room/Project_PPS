<?php

namespace App\Services\Report;

use App\Models\InternshipApplication;
use App\Models\CourseConversion;
use Illuminate\Support\Facades\DB;

class ReportingService
{
    public function getSummary(array $filters = [])
    {
        $query = InternshipApplication::query();

        if (!empty($filters['period_id'])) {
            $query->where('internship_period_id', $filters['period_id']);
        }

        if (!empty($filters['study_program_id'])) {
            $query->where('study_program_id', $filters['study_program_id']);
        }

        return [
            'total_participants' => $query->count(),
            'approved' => $query->where('status', InternshipApplication::STATUS_APPROVED)->count(),
            'active' => $query->whereNotNull('advisor_id')->count(),
            'completed' => $query->where('status', 'COMPLETED')->count(), // Placeholder for final status
            'metrics' => [
                'conversions' => CourseConversion::whereIn('internship_application_id', (clone $query)->pluck('id'))->count(),
                'seminars' => DB::table('internship_seminars')->whereIn('internship_application_id', (clone $query)->pluck('id'))->count(),
            ]
        ];
    }
}
