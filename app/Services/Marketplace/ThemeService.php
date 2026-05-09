<?php

namespace App\Services\Marketplace;

use App\Models\Theme;
use App\Models\ThemeInstallation;

class ThemeService
{
    /**
     * List all published themes
     */
    public function listThemes(array $filters = []): array
    {
        $query = Theme::where('status', 'published')
            ->with('author');

        if (! empty($filters['search'])) {
            $query->where('name', 'like', '%'.$filters['search'].'%');
        }

        $sortBy = $filters['sort_by'] ?? 'published_at';
        $query->orderBy($sortBy, 'desc');

        return $query->paginate($filters['per_page'] ?? 20)->toArray();
    }

    /**
     * Install theme for tenant
     */
    public function installTheme(int $themeId, int $tenantId): ThemeInstallation
    {
        return ThemeInstallation::updateOrCreate(
            [
                'theme_id' => $themeId,
                'tenant_id' => $tenantId,
            ],
            [
                'is_active' => true,
                'customizations' => [],
            ]
        );
    }

    /**
     * Customize theme
     */
    public function customizeTheme(int $installationId, array $customizations): bool
    {
        try {
            $installation = ThemeInstallation::findOrFail($installationId);
            $installation->update(['customizations' => $customizations]);

            return true;
        } catch (\Exception $e) {
            \Log::error('Customize theme failed', [
                'installation_id' => $installationId,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Get tenant's active theme
     */
    public function getActiveTheme(int $tenantId): ?ThemeInstallation
    {
        return ThemeInstallation::where('tenant_id', $tenantId)
            ->where('is_active', true)
            ->with('theme')
            ->first();
    }

    /**
     * Deactivate all themes for tenant
     */
    public function deactivateAllThemes(int $tenantId): bool
    {
        ThemeInstallation::where('tenant_id', $tenantId)
            ->update(['is_active' => false]);

        return true;
    }
}
