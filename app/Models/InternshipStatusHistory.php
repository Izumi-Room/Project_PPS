<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InternshipStatusHistory extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'internship_application_id',
        'actor_id',
        'old_status',
        'new_status',
        'reason',
        'metadata',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'metadata' => 'array',
            'created_at' => 'datetime',
        ];
    }

    public function application(): BelongsTo
    {
        return $this->belongsTo(InternshipApplication::class, 'internship_application_id');
    }

    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actor_id');
    }

    public function getStatusLabel(string $status): string
    {
        return match ($status) {
            'DRAFT' => 'Draft',
            'DIAJUKAN' => 'Diajukan (Menunggu Validasi TU)',
            'TIDAK_LENGKAP' => 'Tidak Lengkap (Dikembalikan)',
            'LOLOS_TU' => 'Lolos Validasi TU (Menunggu Verifikasi Kaprodi)',
            'VERIFIKASI_KAPRODI' => 'Terverifikasi Kaprodi (Menunggu Approval Wadek 1)',
            'DISETUJUI' => 'Disetujui Wadek 1',
            'DITOLAK' => 'Ditolak',
            default => $status,
        };
    }
}
