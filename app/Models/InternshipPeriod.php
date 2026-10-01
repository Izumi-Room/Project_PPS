<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InternshipPeriod extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'academic_year',
        'semester_type',
        'start_date',
        'end_date',
        'is_active',
        'description',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
            'is_active' => 'boolean',
        ];
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeSearch(Builder $query, ?string $term): Builder
    {
        if (blank($term)) {
            return $query;
        }

        $term = "%{$term}%";
        return $query->where(function ($q) use ($term) {
            $q->where('name', 'like', $term)
              ->orWhere('academic_year', 'like', $term);
        });
    }

    public function scopeAcademicYear(Builder $query, ?string $year): Builder
    {
        if (blank($year)) {
            return $query;
        }

        return $query->where('academic_year', $year);
    }

    /**
     * Check if this period is currently valid and active for new registration.
     */
    public function isAvailableForRegistration(): bool
    {
        if (!$this->is_active) {
            return false;
        }

        return true;
    }
}
