<?php

namespace App\Http\Controllers;

use App\Models\CustomField;
use App\Services\CustomFieldService;
use Illuminate\Http\Request;

class CustomFieldController extends Controller
{
    private function tid(): int { return auth()->user()->tenant_id; }

    public function __construct(protected CustomFieldService $service) {}

    public function index(Request $request)
    {
        $module = $request->get('module', 'invoice');
        $fields = CustomField::where('tenant_id', $this->tid())
            ->where('module', $module)
            ->orderBy('sort_order')
            ->get();

        $modules = CustomField::supportedModules();
        $types   = CustomField::supportedTypes();

        return view('settings.custom-fields', compact('fields', 'modules', 'types', 'module'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'module'     => 'required|in:' . implode(',', array_keys(CustomField::supportedModules())),
            'label'      => 'required|string|max:100',
            'type'       => 'required|in:' . implode(',', array_keys(CustomField::supportedTypes())),
            'options'    => 'nullable|string',
            'required'   => 'boolean',
            'sort_order' => 'integer|min:0',
        ]);

        // Generate key dari label
        $key = \Illuminate\Support\Str::snake(\Illuminate\Support\Str::ascii($data['label']));
        $key = preg_replace('/[^a-z0-9_]/', '_', $key);

        // Pastikan key unik per modul
        $existing = CustomField::where('tenant_id', $this->tid())
            ->where('module', $data['module'])
            ->where('key', $key)
            ->exists();
        if ($existing) {
            $key .= '_' . time();
        }

        // Parse options untuk select
        $options = null;
        if ($data['type'] === 'select' && !empty($data['options'])) {
            $options = array_filter(array_map('trim', explode("\n", $data['options'])));
        }

        CustomField::create([
            'tenant_id'  => $this->tid(),
            'module'     => $data['module'],
            'key'        => $key,
            'label'      => $data['label'],
            'type'       => $data['type'],
            'options'    => $options,
            'required'   => $request->boolean('required'),
            'sort_order' => $data['sort_order'] ?? 0,
            'is_active'  => true,
        ]);

        $this->service->invalidateCache($this->tid(), $data['module']);

        return back()->with('success', 'Custom field berhasil ditambahkan.');
    }

    public function update(Request $request, CustomField $customField)
    {
        abort_if($customField->tenant_id !== $this->tid(), 403);

        $data = $request->validate([
            'label'      => 'required|string|max:100',
            'options'    => 'nullable|string',
            'required'   => 'boolean',
            'is_active'  => 'boolean',
            'sort_order' => 'integer|min:0',
        ]);

        $options = null;
        if ($customField->type === 'select' && !empty($data['options'])) {
            $options = array_filter(array_map('trim', explode("\n", $data['options'])));
        }

        $customField->update([
            'label'      => $data['label'],
            'options'    => $options,
            'required'   => $request->boolean('required'),
            'is_active'  => $request->boolean('is_active', true),
            'sort_order' => $data['sort_order'] ?? $customField->sort_order,
        ]);

        $this->service->invalidateCache($this->tid(), $customField->module);

        return back()->with('success', 'Custom field diperbarui.');
    }

    public function destroy(CustomField $customField)
    {
        abort_if($customField->tenant_id !== $this->tid(), 403);
        $module = $customField->module;
        $customField->values()->delete();
        $customField->delete();
        $this->service->invalidateCache($this->tid(), $module);
        return back()->with('success', 'Custom field dihapus.');
    }
}
