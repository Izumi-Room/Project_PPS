<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class InternshipApplication extends Model
{
    use HasFactory;

    // Workflow Status Constants
    public const STATUS_DRAFT = 'DRAFT';
    public const STATUS_SUBMITTED = 'DIAJUKAN';
    public const STATUS_INCOMPLETE = 'TIDAK_LENGKAP';
    public const STATUS_TU_VERIFIED = 'LOLOS_TU';
    public const STATUS_KAPRODI_VERIFIED = 'VERIFIKASI_KAPRODI';
    public const STATUS_APPROVED = 'DISETUJUI';
    public const STATUS_REJECTED = 'DITOLAK';

    // Advisor Assignment Status Constants
    public const STATUS_ADVISOR_UNASSIGNED = 'BELUM_DITENTUKAN';
    public const STATUS_ADVISOR_PENDING = 'DIAJUKAN';
    public const STATUS_ADVISOR_ACCEPTED = 'DITERIMA';
    public const STATUS_ADVISOR_REJECTED = 'DITOLAK';

    protected $fillable = [
        'user_id',
        'study_program_id',
        'partner_institution_id',
        'internship_period_id',
        'student_name',
        'student_nim',
        'student_phone',
        'start_date',
        'end_date',
        'proposal_title',
        'internship_plan',
        'status',
        'advisor_id',
        'advisor_status',
        'reference_letter_number',
        'reference_letter_path',
        'reference_letter_issued_at',
        'acceptance_letter_path',
        'acceptance_letter_uploaded_at',
        'review_notes',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
            'reference_letter_issued_at' => 'datetime',
            'acceptance_letter_uploaded_at' => 'datetime',
        ];
    }

    // Relationships
    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function studyProgram(): BelongsTo
    {
        return $this->belongsTo(StudyProgram::class, 'study_program_id');
    }

    public function partnerInstitution(): BelongsTo
    {
        return $this->belongsTo(PartnerInstitution::class, 'partner_institution_id');
    }

    public function internshipPeriod(): BelongsTo
    {
        return $this->belongsTo(InternshipPeriod::class, 'internship_period_id');
    }

    public function documents(): HasMany
    {
        return $this->hasMany(InternshipDocument::class, 'internship_application_id');
    }

    public function statusHistories(): HasMany
    {
        return $this->hasMany(InternshipStatusHistory::class, 'internship_application_id')->orderBy('created_at', 'desc');
    }

    public function advisor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'advisor_id');
    }

    public function advisorAssignments(): HasMany
    {
        return $this->hasMany(InternshipAdvisorAssignment::class, 'internship_application_id')->latest();
    }

    public function activeAdvisorAssignment(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(InternshipAdvisorAssignment::class, 'internship_application_id')->latestOfMany();
    }

    public function courseConversions(): HasMany
    {
        return $this->hasMany(CourseConversion::class, 'internship_application_id')->latest();
    }

    public function logbooks(): HasMany
    {
        return $this->hasMany(InternshipLogbook::class, 'internship_application_id')->orderBy('week_number', 'asc')->orderBy('activity_date', 'asc');
    }

    public function seminars(): HasMany
    {
        return $this->hasMany(InternshipSeminar::class, 'internship_application_id')->latest();
    }

    // Scopes
    public function scopeForUser(Builder $query, int $userId): Builder
    {
        return $query->where('user_id', $userId);
    }

    public function scopeByStatus(Builder $query, ?string $status): Builder
    {
        if (blank($status)) {
            return $query;
        }
        return $query->where('status', $status);
    }

    public function scopeFilterProdi(Builder $query, mixed $prodiId): Builder
    {
        if (blank($prodiId)) {
            return $query;
        }
        return $query->where('study_program_id', $prodiId);
    }

    public function scopeFilterPeriod(Builder $query, mixed $periodId): Builder
    {
        if (blank($periodId)) {
            return $query;
        }
        return $query->where('internship_period_id', $periodId);
    }

    public function scopeSearch(Builder $query, ?string $term): Builder
    {
        if (blank($term)) {
            return $query;
        }
        $term = "%{$term}%";
        return $query->where(function ($q) use ($term) {
            $q->where('student_name', 'like', $term)
              ->orWhere('student_nim', 'like', $term)
              ->orWhere('proposal_title', 'like', $term)
              ->orWhere('reference_letter_number', 'like', $term)
              ->orWhereHas('partnerInstitution', fn ($pi) => $pi->where('name', 'like', $term));
        });
    }

    // Role-specific queues
    public function scopeQueueForTu(Builder $query): Builder
    {
        return $query->whereIn('status', [self::STATUS_SUBMITTED]);
    }

    public function scopeQueueForKaprodi(Builder $query, ?int $studyProgramId = null): Builder
    {
        $q = $query->whereIn('status', [self::STATUS_TU_VERIFIED]);
        if ($studyProgramId) {
            $q->where('study_program_id', $studyProgramId);
        }
        return $q;
    }

    public function scopeQueueForWadek1(Builder $query): Builder
    {
        return $query->whereIn('status', [self::STATUS_KAPRODI_VERIFIED]);
    }

    // Workflow helper checks
    public function isDraft(): bool
    {
        return $this->status === self::STATUS_DRAFT;
    }

    public function isEditableByStudent(): bool
    {
        return in_array($this->status, [self::STATUS_DRAFT, self::STATUS_INCOMPLETE], true);
    }

    public function canBeReviewedByTu(): bool
    {
        return $this->status === self::STATUS_SUBMITTED;
    }

    public function canBeReviewedByKaprodi(): bool
    {
        return $this->status === self::STATUS_TU_VERIFIED;
    }

    public function canBeReviewedByWadek1(): bool
    {
        return $this->status === self::STATUS_KAPRODI_VERIFIED;
    }

    public function canDownloadReferenceLetter(): bool
    {
        return !empty($this->reference_letter_number) || !empty($this->reference_letter_path);
    }

    public function canUploadAcceptanceLetter(): bool
    {
        return in_array($this->status, [self::STATUS_TU_VERIFIED, self::STATUS_KAPRODI_VERIFIED, self::STATUS_APPROVED], true)
            && $this->canDownloadReferenceLetter();
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_DRAFT => 'Draft',
            self::STATUS_SUBMITTED => 'Diajukan (Validasi TU)',
            self::STATUS_INCOMPLETE => 'Tidak Lengkap (Perlu Revisi)',
            self::STATUS_TU_VERIFIED => 'Lolos TU (Menunggu Kaprodi)',
            self::STATUS_KAPRODI_VERIFIED => 'Verifikasi Kaprodi (Menunggu Wadek 1)',
            self::STATUS_APPROVED => 'Disetujui Wadek 1',
            self::STATUS_REJECTED => 'Ditolak',
            default => $this->status,
        };
    }

    public function getStatusBadgeClassesAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_DRAFT => 'bg-slate-500/20 text-slate-300 border-slate-500/30',
            self::STATUS_SUBMITTED => 'bg-amber-500/20 text-amber-300 border-amber-500/30',
            self::STATUS_INCOMPLETE => 'bg-rose-500/20 text-rose-300 border-rose-500/30',
            self::STATUS_TU_VERIFIED => 'bg-blue-500/20 text-blue-300 border-blue-500/30',
            self::STATUS_KAPRODI_VERIFIED => 'bg-purple-500/20 text-purple-300 border-purple-500/30',
            self::STATUS_APPROVED => 'bg-emerald-500/20 text-emerald-300 border-emerald-500/30',
            self::STATUS_REJECTED => 'bg-rose-700/20 text-rose-400 border-rose-600/30',
            default => 'bg-slate-700/20 text-slate-300 border-slate-600/30',
        };
    }

    public function scopeReadyForAdvisorAssignment(Builder $query, ?int $studyProgramId = null): Builder
    {
        $q = $query->where('status', self::STATUS_APPROVED)
            ->where(function ($sq) {
                $sq->whereNull('advisor_status')
                   ->orWhere('advisor_status', self::STATUS_ADVISOR_UNASSIGNED)
                   ->orWhere('advisor_status', self::STATUS_ADVISOR_REJECTED);
            });

        if ($studyProgramId) {
            $q->where('study_program_id', $studyProgramId);
        }

        return $q;
    }

    public function isReadyForAdvisorAssignment(): bool
    {
        return $this->status === self::STATUS_APPROVED
            && (empty($this->advisor_status) || in_array($this->advisor_status, [self::STATUS_ADVISOR_UNASSIGNED, self::STATUS_ADVISOR_REJECTED], true));
    }

    public function hasActiveAdvisor(): bool
    {
        return $this->advisor_id !== null && $this->advisor_status === self::STATUS_ADVISOR_ACCEPTED;
    }

    public function getAdvisorStatusLabelAttribute(): string
    {
        return match ($this->advisor_status) {
            self::STATUS_ADVISOR_PENDING => 'Diajukan (Menunggu Konfirmasi Dosen)',
            self::STATUS_ADVISOR_ACCEPTED => 'Diterima Dosen (Aktif Membimbing)',
            self::STATUS_ADVISOR_REJECTED => 'Ditolak Dosen (Perlu Penentuan Ulang)',
            default => 'Belum Ditentukan',
        };
    }

    public function getAdvisorStatusBadgeClassesAttribute(): string
    {
        return match ($this->advisor_status) {
            self::STATUS_ADVISOR_PENDING => 'bg-amber-500/20 text-amber-300 border-amber-500/30',
            self::STATUS_ADVISOR_ACCEPTED => 'bg-emerald-500/20 text-emerald-300 border-emerald-500/30',
            self::STATUS_ADVISOR_REJECTED => 'bg-rose-500/20 text-rose-300 border-rose-500/30',
            default => 'bg-slate-700/20 text-slate-400 border-slate-600/30',
        };
    }
}
