<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InternshipAdvisorAssignment extends Model
{
    use HasFactory;

    public const STATUS_PENDING = 'DIAJUKAN';
    public const STATUS_ACCEPTED = 'DITERIMA';
    public const STATUS_REJECTED = 'DITOLAK';

    protected $fillable = [
        'internship_application_id',
        'advisor_id',
        'assigned_by',
        'status',
        'assigned_at',
        'responded_at',
        'rejection_reason',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'assigned_at' => 'datetime',
            'responded_at' => 'datetime',
        ];
    }

    public function application(): BelongsTo
    {
        return $this->belongsTo(InternshipApplication::class, 'internship_application_id');
    }

    public function advisor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'advisor_id');
    }

    public function assigner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }

    public function scopeForAdvisor(Builder $query, int $advisorId): Builder
    {
        return $query->where('advisor_id', $advisorId);
    }

    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    public function scopeAccepted(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_ACCEPTED);
    }

    public function scopeRejected(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_REJECTED);
    }

    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    public function isAccepted(): bool
    {
        return $this->status === self::STATUS_ACCEPTED;
    }

    public function isRejected(): bool
    {
        return $this->status === self::STATUS_REJECTED;
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_PENDING => 'Diajukan (Menunggu Konfirmasi Dosen)',
            self::STATUS_ACCEPTED => 'Diterima (Aktif Membimbing)',
            self::STATUS_REJECTED => 'Ditolak Dosen',
            default => $this->status,
        };
    }

    public function getStatusBadgeClassesAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_PENDING => 'bg-amber-500/20 text-amber-300 border-amber-500/30',
            self::STATUS_ACCEPTED => 'bg-emerald-500/20 text-emerald-300 border-emerald-500/30',
            self::STATUS_REJECTED => 'bg-rose-500/20 text-rose-300 border-rose-500/30',
            default => 'bg-slate-700/20 text-slate-300 border-slate-600/30',
        };
    }
}
