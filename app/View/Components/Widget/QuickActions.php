<?php

namespace App\View\Components\Widget;

use Illuminate\View\Component;
use Illuminate\View\View;

/**
 * QuickActionsWidget - Komponen untuk menampilkan tombol aksi cepat
 *
 * Menyediakan widget quick actions dengan fitur:
 * - Button grid layout dengan icons dan labels (2-4 kolom responsif)
 * - Permission-based action filtering
 * - Keyboard shortcuts untuk common actions (Ctrl+key)
 * - Loading states untuk async actions
 * - Konfirmasi dialog untuk destructive actions
 *
 * Usage:
 * <x-widget.quick-actions :actions="$quickActions" title="Aksi Cepat" />
 *
 * Actions format:
 * $quickActions = [
 *     ['label' => 'Tandai Semua Dibaca', 'icon' => 'check-circle', 'url' => '/notifications/mark-all-read', 'method' => 'POST', 'permission' => 'notifications.edit', 'shortcut' => 'Ctrl+M'],
 *     ['label' => 'Filter Prioritas', 'icon' => 'exclamation', 'url' => '/notifications?priority=high', 'permission' => null],
 *     ['label' => 'Arsipkan Lama', 'icon' => 'folder', 'url' => '/notifications/archive-old', 'method' => 'POST', 'permission' => 'notifications.delete', 'confirm' => true],
 * ];
 *
 * @see Task 4.3: Create QuickActionsWidget component
 * @see Requirements 7 (Sistem Widget Management)
 */
class QuickActions extends Component
{
    /**
     * Filtered actions based on user permissions.
     */
    public array $filteredActions;

    /**
     * Grid column classes based on action count.
     */
    public string $gridClasses;

    /**
     * Supported HTTP methods for actions.
     */
    public const SUPPORTED_METHODS = ['GET', 'POST', 'PUT', 'PATCH', 'DELETE'];

    /**
     * Konstruktor QuickActionsWidget component
     *
     * @param  array       $actions      Array of action items
     * @param  string      $title        Widget title
     * @param  bool        $loading      Initial loading state
     * @param  bool        $error        Initial error state
     * @param  string|null $errorMessage Custom error message
     * @param  int         $columns      Number of grid columns (auto-detected if 0)
     */
    public function __construct(
        public array $actions = [],
        public string $title = '',
        public bool $loading = false,
        public bool $error = false,
        public ?string $errorMessage = null,
        public int $columns = 0,
    ) {
        $this->filteredActions = $this->filterActionsByPermission($this->actions);
        $this->gridClasses = $this->buildGridClasses();
    }

    /**
     * Filter actions based on user permissions.
     *
     * Actions with null permission are always shown.
     * Actions with a permission string are only shown if the user has that permission.
     */
    public function filterActionsByPermission(array $actions): array
    {
        $user = auth()->user();

        return array_values(array_filter($actions, function (array $action) use ($user) {
            $permission = $action['permission'] ?? null;

            // No permission required - always show
            if ($permission === null || $permission === '') {
                return true;
            }

            // No authenticated user - hide permission-gated actions
            if (!$user) {
                return false;
            }

            // Check user permission
            return $user->can($permission);
        }));
    }

    /**
     * Normalize a single action with defaults.
     */
    public static function normalizeAction(array $action): array
    {
        $method = strtoupper(trim($action['method'] ?? 'GET'));

        if (!in_array($method, self::SUPPORTED_METHODS)) {
            $method = 'GET';
        }

        return [
            'label' => $action['label'] ?? '',
            'icon' => $action['icon'] ?? null,
            'url' => $action['url'] ?? '#',
            'method' => $method,
            'permission' => $action['permission'] ?? null,
            'shortcut' => $action['shortcut'] ?? null,
            'confirm' => (bool) ($action['confirm'] ?? false),
            'confirmMessage' => $action['confirmMessage'] ?? 'Apakah Anda yakin ingin melakukan aksi ini?',
        ];
    }

    /**
     * Get all normalized filtered actions.
     */
    public function getNormalizedActions(): array
    {
        return array_map([self::class, 'normalizeAction'], $this->filteredActions);
    }

    /**
     * Get keyboard shortcuts mapping for Alpine.js.
     */
    public function getShortcutsJson(): string
    {
        $shortcuts = [];

        foreach ($this->getNormalizedActions() as $index => $action) {
            if (!empty($action['shortcut'])) {
                $shortcuts[] = [
                    'key' => $this->parseShortcutKey($action['shortcut']),
                    'ctrl' => $this->shortcutRequiresCtrl($action['shortcut']),
                    'alt' => $this->shortcutRequiresAlt($action['shortcut']),
                    'shift' => $this->shortcutRequiresShift($action['shortcut']),
                    'index' => $index,
                    'label' => $action['label'],
                ];
            }
        }

        return json_encode($shortcuts, JSON_THROW_ON_ERROR);
    }

    /**
     * Parse the key character from a shortcut string like "Ctrl+M".
     */
    public static function parseShortcutKey(string $shortcut): string
    {
        $parts = explode('+', $shortcut);
        $key = end($parts);

        return strtolower(trim($key));
    }

    /**
     * Check if shortcut requires Ctrl modifier.
     */
    public static function shortcutRequiresCtrl(string $shortcut): bool
    {
        return stripos($shortcut, 'ctrl') !== false;
    }

    /**
     * Check if shortcut requires Alt modifier.
     */
    public static function shortcutRequiresAlt(string $shortcut): bool
    {
        return stripos($shortcut, 'alt') !== false;
    }

    /**
     * Check if shortcut requires Shift modifier.
     */
    public static function shortcutRequiresShift(string $shortcut): bool
    {
        return stripos($shortcut, 'shift') !== false;
    }

    /**
     * Build responsive grid classes based on action count or explicit columns.
     */
    private function buildGridClasses(): string
    {
        $count = $this->columns > 0 ? $this->columns : count($this->filteredActions);

        return match (true) {
            $count <= 1 => 'grid-cols-1',
            $count === 2 => 'grid-cols-1 sm:grid-cols-2',
            $count === 3 => 'grid-cols-2 sm:grid-cols-3',
            default => 'grid-cols-2 sm:grid-cols-3 lg:grid-cols-4',
        };
    }

    /**
     * Check if an action is a navigation (GET) or an async action (POST/PUT/etc).
     */
    public static function isAsyncAction(array $action): bool
    {
        $method = strtoupper($action['method'] ?? 'GET');

        return $method !== 'GET';
    }

    /**
     * Get the default error message.
     */
    public function getErrorMessage(): string
    {
        return $this->errorMessage ?? 'Gagal memuat aksi cepat';
    }

    /**
     * Get the ARIA label for the widget.
     */
    public function getAriaLabel(): string
    {
        return $this->title
            ? "Aksi cepat: {$this->title}"
            : 'Widget Aksi Cepat';
    }

    /**
     * Render the component.
     */
    public function render(): View
    {
        return view('components.widget.quick-actions');
    }
}
