<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class InternshipDocument extends Model
{
    use HasFactory;

    protected $fillable = [
        'internship_application_id',
        'document_type',
        'document_name',
        'file_path',
        'file_size',
        'mime_type',
        'is_verified',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'file_size' => 'integer',
            'is_verified' => 'boolean',
        ];
    }

    public function application(): BelongsTo
    {
        return $this->belongsTo(InternshipApplication::class, 'internship_application_id');
    }

    public function getFormattedFileSizeAttribute(): string
    {
        $bytes = $this->file_size;
        if ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 2) . ' MB';
        }
        if ($bytes >= 1024) {
            return number_format($bytes / 1024, 2) . ' KB';
        }
        return $bytes . ' bytes';
    }

    public function getIsPdfAttribute(): bool
    {
        return str_contains($this->mime_type, 'pdf');
    }

    public function getIsImageAttribute(): bool
    {
        return str_starts_with($this->mime_type, 'image/');
    }
}
