<?php

namespace App\Policies;

use App\Models\CompanyGroup;
use App\Models\User;

class CompanyGroupPolicy
{
    public function viewAny(User $user): bool
    {
        // Only Enterprise/Ultimate plans can access consolidation
        return in_array($user->tenant->plan, ['enterprise', 'ultimate']);
    }

    public function view(User $user, CompanyGroup $group): bool
    {
        // User can view if they own the group or their tenant is a member
        return $group->owner_user_id === $user->id
            || $group->members()->where('tenant_id', $user->tenant_id)->exists();
    }

    public function create(User $user): bool
    {
        return in_array($user->tenant->plan, ['enterprise', 'ultimate']);
    }

    public function update(User $user, CompanyGroup $group): bool
    {
        // Only owner can update
        return $group->owner_user_id === $user->id;
    }

    public function delete(User $user, CompanyGroup $group): bool
    {
        return $group->owner_user_id === $user->id;
    }
}
