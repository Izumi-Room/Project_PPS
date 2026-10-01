<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CourseConversionStatusHistory extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'course_conversion_id',
        'actor_id',
        'action',
        'from_status',
        'to_status',
        'notes',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
        ];
    }

    public function conversion(): BelongsTo
    {
        return $this->belongsTo(CourseConversion::class, 'course_conversion_id');
    }

    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actor_id');
    }
}
