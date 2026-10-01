<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CourseConversion extends Model
{
    use HasFactory;

    // Status Constants
    public const STATUS_SUBMITTED = 'DIAJUKAN';
    public const STATUS_APPROVED_DOSEN_MK = 'DISETUJUI_DOSEN_MK';
    public const STATUS_VERIFIED_DOSBING = 'DIVERIFIKASI_DOSBING';
    public const STATUS_APPROVED_WADEK1 = 'DISETUJUI_WADEK1';
    public const STATUS_ACKNOWLEDGED_KAPRODI = 'DIKETAHUI_KAPRODI';
    public const STATUS_REJECTED = 'DITOLAK';

    protected $fillable = [
        'internship_application_id',
        'user_id',
        'course_id',
        'activity_plan',
        'status',
        'rejection_stage',
        'rejection_reason',
        'dosen_mk_id',
        'dosen_mk_approved_at',
        'dosen_mk_notes',
        'dosbing_id',
        'dosbing_verified_at',
        'dosbing_notes',
        'wadek1_id',
        'wadek1_approved_at',
        'wadek1_notes',
        'kaprodi_id',
        'kaprodi_acknowledged_at',
        'kaprodi_notes',
    ];

    protected function casts(): array
    {
        return [
            'dosen_mk_approved_at' => 'datetime',
            'dosbing_verified_at' => 'datetime',
            'wadek1_approved_at' => 'datetime',
            'kaprodi_acknowledged_at' => 'datetime',
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

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class, 'course_id');
    }

    public function dosenMk(): BelongsTo
    {
        return $this->belongsTo(User::class, 'dosen_mk_id');
    }

    public function dosbing(): BelongsTo
    {
        return $this->belongsTo(User::class, 'dosbing_id');
    }

    public function wadek1(): BelongsTo
    {
        return $this->belongsTo(User::class, 'wadek1_id');
    }

    public function kaprodi(): BelongsTo
    {
        return $this->belongsTo(User::class, 'kaprodi_id');
    }

    public function statusHistories(): HasMany
    {
        return $this->hasMany(CourseConversionStatusHistory::class, 'course_conversion_id')->orderBy('created_at', 'desc');
    }

    public function studentSubmissions(): HasMany
    {
        return $this->hasMany(StudentSubmission::class, 'course_conversion_id');
    }

    public function seminars(): HasMany
    {
        return $this->hasMany(InternshipSeminar::class, 'course_conversion_id')->latest();
    }

    // Helper checks
    public function isSubmitted(): bool
    {
        return $this->status === self::STATUS_SUBMITTED;
    }

    public function isApprovedByDosenMk(): bool
    {
        return $this->status === self::STATUS_APPROVED_DOSEN_MK;
    }

    public function isVerifiedByDosbing(): bool
    {
        return $this->status === self::STATUS_VERIFIED_DOSBING;
    }

    public function isApprovedByWadek1(): bool
    {
        return $this->status === self::STATUS_APPROVED_WADEK1;
    }

    public function isAcknowledgedByKaprodi(): bool
    {
        return $this->status === self::STATUS_ACKNOWLEDGED_KAPRODI;
    }

    public function isRejected(): bool
    {
        return $this->status === self::STATUS_REJECTED;
    }

    public function canBeResubmitted(): bool
    {
        return $this->status === self::STATUS_REJECTED;
    }

    // Scopes
    public function scopeForUser(Builder $query, int $userId): Builder
    {
        return $query->where('user_id', $userId);
    }

    public function scopeQueueForDosenMk(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_SUBMITTED);
    }

    public function scopeQueueForDosbing(Builder $query, ?int $advisorId = null): Builder
    {
        $q = $query->where('status', self::STATUS_APPROVED_DOSEN_MK);
        if ($advisorId) {
            $q->whereHas('internshipApplication', function ($app) use ($advisorId) {
                $app->where('advisor_id', $advisorId)
                    ->where('advisor_status', InternshipApplication::STATUS_ADVISOR_ACCEPTED);
            });
        }
        return $q;
    }

    public function scopeQueueForWadek1(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_VERIFIED_DOSBING);
    }

    public function scopeQueueForKaprodi(Builder $query, ?int $studyProgramId = null): Builder
    {
        $q = $query->where('status', self::STATUS_APPROVED_WADEK1);
        if ($studyProgramId) {
            $q->whereHas('internshipApplication', fn ($app) => $app->where('study_program_id', $studyProgramId));
        }
        return $q;
    }

    // UI Badges & Labels
    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_SUBMITTED => 'Diajukan (Menunggu Dosen MK)',
            self::STATUS_APPROVED_DOSEN_MK => 'Disetujui Dosen MK (Menunggu Verifikasi Dosbing)',
            self::STATUS_VERIFIED_DOSBING => 'Diverifikasi Dosbing (Menunggu Wadek 1)',
            self::STATUS_APPROVED_WADEK1 => 'Disetujui Wadek 1 (Menunggu Diketahui Kaprodi)',
            self::STATUS_ACKNOWLEDGED_KAPRODI => 'Diketahui Kaprodi (Selesai)',
            self::STATUS_REJECTED => 'Ditolak (' . ($this->rejection_stage ?? 'Reviewer') . ')',
            default => $this->status,
        };
    }

    public function getStatusBadgeClassesAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_SUBMITTED => 'bg-amber-500/20 text-amber-300 border-amber-500/30',
            self::STATUS_APPROVED_DOSEN_MK => 'bg-blue-500/20 text-blue-300 border-blue-500/30',
            self::STATUS_VERIFIED_DOSBING => 'bg-indigo-500/20 text-indigo-300 border-indigo-500/30',
            self::STATUS_APPROVED_WADEK1 => 'bg-purple-500/20 text-purple-300 border-purple-500/30',
            self::STATUS_ACKNOWLEDGED_KAPRODI => 'bg-emerald-500/20 text-emerald-300 border-emerald-500/30',
            self::STATUS_REJECTED => 'bg-rose-500/20 text-rose-300 border-rose-500/30',
            default => 'bg-slate-700/20 text-slate-300 border-slate-600/30',
        };
    }
}
