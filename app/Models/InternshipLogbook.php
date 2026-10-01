<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InternshipLogbook extends Model
{
    use HasFactory;

    protected $fillable = [
        'internship_application_id',
        'user_id',
        'week_number',
        'activity_date',
        'activity_title',
        'description',
        'attachment_path',
        'dosbing_feedback',
        'dosbing_reviewed_at',
        'reviewed_by',
    ];

    protected function casts(): array
    {
        return [
            'activity_date' => 'date',
            'dosbing_reviewed_at' => 'datetime',
        ];
    }

    // Relationships
    public function internshipApplication(): BelongsTo
    {
        return $this->belongsTo(InternshipApplication::class, 'internship_application_id');
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    // Scopes
    public function scopeForUser(Builder $query, int $userId): Builder
    {
        return $query->where('user_id', $userId);
    }

    public function scopeForInternship(Builder $query, int $internshipId): Builder
    {
        return $query->where('internship_application_id', $internshipId);
    }

    public function hasFeedback(): bool
    {
        return !empty($this->dosbing_feedback);
    }
}
