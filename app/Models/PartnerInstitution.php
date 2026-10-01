<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PartnerInstitution extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'address',
        'contact_person',
        'email',
        'phone',
        'website',
        'sector',
        'is_active',
        'description',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
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
              ->orWhere('contact_person', 'like', $term)
              ->orWhere('email', 'like', $term)
              ->orWhere('address', 'like', $term);
        });
    }

    public function scopeSector(Builder $query, ?string $sector): Builder
    {
        if (blank($sector)) {
            return $query;
        }

        return $query->where('sector', $sector);
    }
}
