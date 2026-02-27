<?php
namespace App\Policies;

use App\Models\Activity;
use App\Models\User;

class ActivityPolicy
{
    public function update(User $user, Activity $activity): bool {
        return $user->hasRole(['admin','manager']) || $activity->user_id === $user->id;
    }
    public function delete(User $user, Activity $activity): bool {
        return $user->hasRole(['admin','manager']) || $activity->user_id === $user->id;
    }
}
