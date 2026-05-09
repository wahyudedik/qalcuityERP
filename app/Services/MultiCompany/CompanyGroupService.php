<?php

namespace App\Services\MultiCompany;

use App\Models\CompanyGroup;
use App\Models\Tenant;
use App\Models\TenantGroupMember;

class CompanyGroupService
{
    /**
     * Create new company group
     */
    public function createGroup(int $parentTenantId, string $name, string $code, ?string $description = null): CompanyGroup
    {
        return CompanyGroup::create([
            'parent_tenant_id' => $parentTenantId,
            'name' => $name,
            'code' => $code,
            'description' => $description,
            'is_active' => true,
        ]);
    }

    /**
     * Add subsidiary to group
     */
    public function addSubsidiary(int $groupId, int $tenantId, float $ownershipPercentage, string $role = 'subsidiary'): bool
    {
        try {
            TenantGroupMember::create([
                'company_group_id' => $groupId,
                'tenant_id' => $tenantId,
                'ownership_percentage' => $ownershipPercentage,
                'joined_date' => now(),
                'is_active' => true,
                'role' => $role,
            ]);

            return true;
        } catch (\Exception $e) {
            \Log::error('Add subsidiary failed', [
                'group_id' => $groupId,
                'tenant_id' => $tenantId,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Remove subsidiary from group
     */
    public function removeSubsidiary(int $groupId, int $tenantId): bool
    {
        try {
            $member = TenantGroupMember::where('company_group_id', $groupId)
                ->where('tenant_id', $tenantId)
                ->first();

            if ($member) {
                $member->update([
                    'exited_date' => now(),
                    'is_active' => false,
                ]);
            }

            return true;
        } catch (\Exception $e) {
            \Log::error('Remove subsidiary failed', [
                'group_id' => $groupId,
                'tenant_id' => $tenantId,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Get all groups for tenant
     */
    public function getTenantGroups(int $tenantId): array
    {
        // Groups where tenant is parent
        $asParent = CompanyGroup::where('parent_tenant_id', $tenantId)
            ->with('members.tenant')
            ->get();

        // Groups where tenant is member
        $asMember = TenantGroupMember::where('tenant_id', $tenantId)
            ->with('companyGroup.parentTenant')
            ->get()
            ->pluck('companyGroup');

        return [
            'as_parent' => $asParent,
            'as_member' => $asMember,
        ];
    }

    /**
     * Get group structure
     */
    public function getGroupStructure(int $groupId): array
    {
        $group = CompanyGroup::with(['members.tenant', 'parentTenant'])->findOrFail($groupId);

        $subsidiaries = $group->members->map(function ($member) {
            return [
                'tenant_id' => $member->tenant_id,
                'tenant_name' => $member->tenant->name ?? 'Unknown',
                'ownership_percentage' => $member->ownership_percentage,
                'role' => $member->role,
                'joined_date' => $member->joined_date,
                'is_active' => $member->is_active,
            ];
        });

        return [
            'group' => $group,
            'parent' => $group->parentTenant,
            'subsidiaries' => $subsidiaries,
            'total_subsidiaries' => $subsidiaries->count(),
        ];
    }

    /**
     * Update ownership percentage
     */
    public function updateOwnership(int $groupId, int $tenantId, float $newPercentage): bool
    {
        try {
            $member = TenantGroupMember::where('company_group_id', $groupId)
                ->where('tenant_id', $tenantId)
                ->first();

            if ($member) {
                $member->update(['ownership_percentage' => $newPercentage]);

                return true;
            }

            return false;
        } catch (\Exception $e) {
            \Log::error('Update ownership failed', [
                'group_id' => $groupId,
                'tenant_id' => $tenantId,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Get active subsidiaries
     */
    public function getActiveSubsidiaries(int $groupId): array
    {
        return TenantGroupMember::where('company_group_id', $groupId)
            ->where('is_active', true)
            ->with('tenant')
            ->get()
            ->map(function ($member) {
                return [
                    'tenant_id' => $member->tenant_id,
                    'tenant_name' => $member->tenant->name ?? 'Unknown',
                    'ownership_percentage' => $member->ownership_percentage,
                    'role' => $member->role,
                ];
            })
            ->toArray();
    }
}
