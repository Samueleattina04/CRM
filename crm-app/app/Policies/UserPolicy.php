<?php
namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    public function viewAny(User $user): bool { return $user->hasRole(['admin','manager']); }
    public function update(User $user, User $model): bool {
        return $user->hasRole('admin') || (int) $user->id === (int) $model->id;
    }
}
