<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Workflow;

class WorkflowPolicy
{
    /**
     * Determine if the user can view any workflows.
     */
    public function viewAny(User $user): bool
    {
        return in_array($user->role, ['admin', 'manager', 'super_admin']);
    }

    /**
     * Determine if the user can view the workflow.
     */
    public function view(User $user, Workflow $workflow): bool
    {
        // SuperAdmin can view any workflow
        if ($user->isSuperAdmin()) {
            return true;
        }

        // User must belong to the same tenant
        return $user->tenant_id === $workflow->tenant_id;
    }

    /**
     * Determine if the user can create workflows.
     */
    public function create(User $user): bool
    {
        return in_array($user->role, ['admin', 'manager', 'super_admin']);
    }

    /**
     * Determine if the user can update the workflow.
     */
    public function update(User $user, Workflow $workflow): bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        return $user->tenant_id === $workflow->tenant_id
            && in_array($user->role, ['admin', 'manager']);
    }

    /**
     * Determine if the user can delete the workflow.
     */
    public function delete(User $user, Workflow $workflow): bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        return $user->tenant_id === $workflow->tenant_id
            && in_array($user->role, ['admin', 'manager']);
    }
}
