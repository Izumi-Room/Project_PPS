<?php

namespace App\Services\Notification;

use App\Models\AppNotification;
use App\Models\Role;
use App\Models\User;

class NotificationService
{
    /**
     * Send in-app notification to a specific user.
     */
    public function notifyUser(User $user, string $title, string $message, string $type, ?string $actionUrl = null, ?User $actor = null): AppNotification
    {
        return AppNotification::create([
            'user_id' => $user->id,
            'actor_id' => $actor?->id,
            'title' => $title,
            'message' => $message,
            'type' => strtoupper($type),
            'action_url' => $actionUrl,
            'is_read' => false,
        ]);
    }

    /**
     * Send in-app notification to all users holding a specific role.
     */
    public function notifyRole(string $roleName, string $title, string $message, string $type, ?string $actionUrl = null, ?User $actor = null): void
    {
        $users = User::whereHas('roles', function ($q) use ($roleName) {
            $q->where('name', strtoupper($roleName));
        })->active()->get();

        foreach ($users as $user) {
            $this->notifyUser($user, $title, $message, $type, $actionUrl, $actor);
        }
    }
}
