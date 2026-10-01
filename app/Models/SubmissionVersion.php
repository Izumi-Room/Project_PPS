<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SubmissionVersion extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'student_submission_id',
        'version_number',
        'file_path',
        'file_name',
        'file_size',
        'link_url',
        'student_notes',
        'submitted_at',
        'status',
        'reviewer_feedback',
        'reviewed_by',
        'reviewed_at',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'submitted_at' => 'datetime',
            'reviewed_at' => 'datetime',
            'version_number' => 'integer',
            'file_size' => 'integer',
            'created_at' => 'datetime',
        ];
    }

    public function submission(): BelongsTo
    {
        return $this->belongsTo(StudentSubmission::class, 'student_submission_id');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function isFile(): bool
    {
        return ! empty($this->file_path);
    }

    public function isLink(): bool
    {
        return ! empty($this->link_url);
    }

    public function formattedFileSize(): string
    {
        if (! $this->file_size) {
            return '-';
        }
        if ($this->file_size >= 1048576) {
            return number_format($this->file_size / 1048576, 2) . ' MB';
        }
        if ($this->file_size >= 1024) {
            return number_format($this->file_size / 1024, 2) . ' KB';
        }
        return $this->file_size . ' bytes';
    }
}
