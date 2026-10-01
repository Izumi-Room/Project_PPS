<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class StudyProgram extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'degree_level',
        'faculty',
        'is_active',
        'description',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function courses(): HasMany
    {
        return $this->hasMany(Course::class);
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeSearch(Builder $query, ?string $term): Builder
    {
        if (blank($term)) {
            return $query;
        }

        $term = "%{$term}%";
        return $query->where(function ($q) use ($term) {
            $q->where('name', 'like', $term)
              ->orWhere('code', 'like', $term)
              ->orWhere('faculty', 'like', $term);
        });
    }
}
