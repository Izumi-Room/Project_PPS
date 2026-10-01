<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Course extends Model
{
    use HasFactory;

    protected $fillable = [
        'study_program_id',
        'code',
        'name',
        'credits',
        'semester',
        'is_active',
        'description',
    ];

    protected function casts(): array
    {
        return [
            'credits' => 'integer',
            'semester' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function studyProgram(): BelongsTo
    {
        return $this->belongsTo(StudyProgram::class, 'study_program_id');
    }

    public function submissionComponents(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(SubmissionComponent::class, 'course_id')->orderBy('deadline', 'asc');
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
              ->orWhere('code', 'like', $term);
        });
    }

    public function scopeStudyProgram(Builder $query, mixed $prodiId): Builder
    {
        if (blank($prodiId)) {
            return $query;
        }

        return $query->where('study_program_id', $prodiId);
    }

    public function scopeSemester(Builder $query, mixed $semester): Builder
    {
        if (blank($semester)) {
            return $query;
        }

        return $query->where('semester', $semester);
    }
}
