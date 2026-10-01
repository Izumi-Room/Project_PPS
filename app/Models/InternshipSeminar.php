<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InternshipSeminar extends Model
{
    use HasFactory;

    // Status Constants
    public const STATUS_WAITING_DECISION = 'MENUNGGU_KEPUTUSAN';
    public const STATUS_NOT_REQUIRED = 'TIDAK_DIPERLUKAN';
    public const STATUS_SCHEDULED = 'TERJADWAL';
    public const STATUS_ACKNOWLEDGED = 'DIKETAHUI';
    public const STATUS_APPROVED = 'DISETUJUI';
    public const STATUS_CONDUCTED = 'DILAKSANAKAN';
    public const STATUS_EVALUATED = 'DINILAI';
    public const STATUS_REJECTED = 'DITOLAK';

    protected $fillable = [
        'internship_application_id',
        'course_conversion_id',
        'course_id',
        'user_id',
        'dosen_mk_id',
        'is_required',
        'status',
        'scheduled_date',
        'scheduled_time',
        'location_or_link',
        'information',
        'dosbing_id',
        'dosbing_acknowledged_at',
        'dosbing_notes',
        'kaprodi_id',
        'kaprodi_acknowledged_at',
        'kaprodi_notes',
        'wadek1_id',
        'wadek1_approved_at',
        'wadek1_rejected_at',
        'rejection_reason',
        'conducted_at',
        'rescheduled_at',
        'reschedule_count',
    ];

    protected function casts(): array
    {
        return [
            'is_required' => 'boolean',
            'scheduled_date' => 'date',
            'dosbing_acknowledged_at' => 'datetime',
            'kaprodi_acknowledged_at' => 'datetime',
            'wadek1_approved_at' => 'datetime',
            'wadek1_rejected_at' => 'datetime',
            'conducted_at' => 'datetime',
            'rescheduled_at' => 'datetime',
            'reschedule_count' => 'integer',
        ];
    }

    // Relationships
    public function internshipApplication(): BelongsTo
    {
        return $this->belongsTo(InternshipApplication::class, 'internship_application_id');
    }

    public function courseConversion(): BelongsTo
    {
        return $this->belongsTo(CourseConversion::class, 'course_conversion_id');
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class, 'course_id');
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function dosenMk(): BelongsTo
    {
        return $this->belongsTo(User::class, 'dosen_mk_id');
    }

    public function dosbing(): BelongsTo
    {
        return $this->belongsTo(User::class, 'dosbing_id');
    }

    public function kaprodi(): BelongsTo
    {
        return $this->belongsTo(User::class, 'kaprodi_id');
    }

    public function wadek1(): BelongsTo
    {
        return $this->belongsTo(User::class, 'wadek1_id');
    }

    // Helpers
    public function isNotRequired(): bool
    {
        return $this->status === self::STATUS_NOT_REQUIRED || ! $this->is_required;
    }

    public function isScheduled(): bool
    {
        return $this->status === self::STATUS_SCHEDULED;
    }

    public function isAcknowledged(): bool
    {
        return $this->status === self::STATUS_ACKNOWLEDGED;
    }

    public function isApproved(): bool
    {
        return $this->status === self::STATUS_APPROVED;
    }

    public function isConducted(): bool
    {
        return $this->status === self::STATUS_CONDUCTED;
    }

    public function isRejected(): bool
    {
        return $this->status === self::STATUS_REJECTED;
    }

    public function canBeConducted(): bool
    {
        return $this->status === self::STATUS_APPROVED;
    }

    public function statusLabel(): string
    {
        return match ($this->status) {
            self::STATUS_WAITING_DECISION => 'Menunggu Keputusan',
            self::STATUS_NOT_REQUIRED => 'Tidak Diperlukan',
            self::STATUS_SCHEDULED => 'Terjadwal',
            self::STATUS_ACKNOWLEDGED => 'Diketahui',
            self::STATUS_APPROVED => 'Disetujui',
            self::STATUS_CONDUCTED => 'Dilaksanakan',
            self::STATUS_EVALUATED => 'Dinilai',
            self::STATUS_REJECTED => 'Ditolak',
            default => $this->status,
        };
    }

    public function statusBadgeColor(): string
    {
        return match ($this->status) {
            self::STATUS_WAITING_DECISION => 'bg-amber-500/10 text-amber-400 border-amber-500/20',
            self::STATUS_NOT_REQUIRED => 'bg-slate-500/10 text-slate-400 border-slate-500/20',
            self::STATUS_SCHEDULED => 'bg-blue-500/10 text-blue-400 border-blue-500/20',
            self::STATUS_ACKNOWLEDGED => 'bg-purple-500/10 text-purple-400 border-purple-500/20',
            self::STATUS_APPROVED => 'bg-emerald-500/10 text-emerald-400 border-emerald-500/20',
            self::STATUS_CONDUCTED => 'bg-teal-500/10 text-teal-400 border-teal-500/20',
            self::STATUS_EVALUATED => 'bg-indigo-500/10 text-indigo-400 border-indigo-500/20',
            self::STATUS_REJECTED => 'bg-rose-500/10 text-rose-400 border-rose-500/20',
            default => 'bg-slate-500/10 text-slate-400 border-slate-500/20',
        };
    }
}
