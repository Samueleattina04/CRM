<?php
namespace App\Policies;

use App\Models\Activity;
use App\Models\User;

class ActivityPolicy
{
    public function update(User $user, Activity $activity): bool {
        return $user->hasRole(['admin','manager']) || (int) $activity->user_id === (int) $user->id;
    }
    public function delete(User $user, Activity $activity): bool {
        return $user->hasRole(['admin','manager']) || (int) $activity->user_id === (int) $user->id;
    }
}
