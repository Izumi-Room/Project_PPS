<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SubmissionComponent extends Model
{
    use HasFactory;

    protected $fillable = [
        'course_id',
        'created_by',
        'name',
        'submission_type',
        'deadline',
        'weight',
        'is_required',
        'instructions',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'deadline' => 'datetime',
            'weight' => 'integer',
            'is_required' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class, 'course_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function submissions(): HasMany
    {
        return $this->hasMany(StudentSubmission::class, 'submission_component_id');
    }

    public function isPassedDeadline(): bool
    {
        return now()->isAfter($this->deadline);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeForCourse(Builder $query, int $courseId): Builder
    {
        return $query->where('course_id', $courseId);
    }
}
