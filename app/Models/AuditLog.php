<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AuditLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'actor_id',
        'actor_name',
        'user_id',
        'user_name',
        'action',
        'module',
        'entity_type',
        'entity_id',
        'old_values',
        'new_values',
        'description',
        'ip_address',
        'user_agent',
    ];

    protected function casts(): array
    {
        return [
            'old_values' => 'array',
            'new_values' => 'array',
        ];
    }

    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actor_id');
    }

    public function targetUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function scopeSearch(Builder $query, ?string $term): Builder
    {
        if (blank($term)) {
            return $query;
        }

        $term = "%{$term}%";
        return $query->where(function ($q) use ($term) {
            $q->where('actor_name', 'like', $term)
              ->orWhere('user_name', 'like', $term)
              ->orWhere('action', 'like', $term)
              ->orWhere('description', 'like', $term);
        });
    }

    public function scopeAction(Builder $query, ?string $action): Builder
    {
        if (blank($action)) {
            return $query;
        }

        return $query->where('action', $action);
    }

    /**
     * Helper method to record audit log.
     */
    public static function record(
        ?User $actor,
        ?User $targetUser,
        string $action,
        ?array $oldValues = null,
        ?array $newValues = null,
        ?string $description = null,
        ?string $module = null,
        ?Model $entity = null
    ): self {
        return self::create([
            'actor_id' => $actor?->id,
            'actor_name' => $actor?->name ?? 'System',
            'user_id' => $targetUser?->id,
            'user_name' => $targetUser?->name,
            'action' => $action,
            'module' => $module,
            'entity_type' => $entity ? get_class($entity) : null,
            'entity_id' => $entity ? $entity->getKey() : null,
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'description' => $description,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }
}
