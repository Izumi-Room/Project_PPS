<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class StudentSubmission extends Model
{
    use HasFactory;

    // Workflow status constants
    public const STATUS_NOT_SUBMITTED = 'BELUM_DIKUMPULKAN';
    public const STATUS_SUBMITTED = 'DIKUMPULKAN';
    public const STATUS_REVISION_NEEDED = 'PERLU_PERBAIKAN';
    public const STATUS_RESUBMITTED = 'DIKIRIM_ULANG';
    public const STATUS_APPROVED = 'DISETUJUI';

    protected $fillable = [
        'submission_component_id',
        'user_id',
        'course_conversion_id',
        'status',
        'current_version',
        'latest_submitted_at',
        'latest_feedback',
        'reviewed_by',
        'reviewed_at',
        'score',
    ];

    protected function casts(): array
    {
        return [
            'current_version' => 'integer',
            'latest_submitted_at' => 'datetime',
            'reviewed_at' => 'datetime',
            'score' => 'decimal:2',
        ];
    }

    // Relationships
    public function component(): BelongsTo
    {
        return $this->belongsTo(SubmissionComponent::class, 'submission_component_id');
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function courseConversion(): BelongsTo
    {
        return $this->belongsTo(CourseConversion::class, 'course_conversion_id');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function versions(): HasMany
    {
        return $this->hasMany(SubmissionVersion::class, 'student_submission_id')->orderBy('version_number', 'desc');
    }

    public function latestVersion(): HasOne
    {
        return $this->hasOne(SubmissionVersion::class, 'student_submission_id')->latestOfMany('version_number');
    }

    // Scopes
    public function scopeForUser(Builder $query, int $userId): Builder
    {
        return $query->where('user_id', $userId);
    }

    public function scopeForConversion(Builder $query, int $conversionId): Builder
    {
        return $query->where('course_conversion_id', $conversionId);
    }

    public function scopePendingReview(Builder $query): Builder
    {
        return $query->whereIn('status', [self::STATUS_SUBMITTED, self::STATUS_RESUBMITTED]);
    }

    // Status helpers
    public function isApproved(): bool
    {
        return $this->status === self::STATUS_APPROVED;
    }

    public function needsRevision(): bool
    {
        return $this->status === self::STATUS_REVISION_NEEDED;
    }

    public function isSubmitted(): bool
    {
        return $this->status === self::STATUS_SUBMITTED;
    }

    public function isResubmitted(): bool
    {
        return $this->status === self::STATUS_RESUBMITTED;
    }

    public function canBeSubmitted(): bool
    {
        return in_array($this->status, [self::STATUS_NOT_SUBMITTED, self::STATUS_REVISION_NEEDED], true);
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_NOT_SUBMITTED => 'Belum Dikumpulkan',
            self::STATUS_SUBMITTED => 'Dikumpulkan (Menunggu Review)',
            self::STATUS_REVISION_NEEDED => 'Perlu Perbaikan (Revisi)',
            self::STATUS_RESUBMITTED => 'Dikirim Ulang (Menunggu Review)',
            self::STATUS_APPROVED => 'Disetujui',
            default => $this->status,
        };
    }

    public function getStatusBadgeClassesAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_NOT_SUBMITTED => 'bg-slate-700/30 text-slate-400 border-slate-600/30',
            self::STATUS_SUBMITTED => 'bg-blue-500/20 text-blue-300 border-blue-500/30',
            self::STATUS_REVISION_NEEDED => 'bg-amber-500/20 text-amber-300 border-amber-500/30',
            self::STATUS_RESUBMITTED => 'bg-purple-500/20 text-purple-300 border-purple-500/30',
            self::STATUS_APPROVED => 'bg-emerald-500/20 text-emerald-300 border-emerald-500/30',
            default => 'bg-slate-700/20 text-slate-300 border-slate-600/30',
        };
    }
}
