<?php

namespace App\Http\Controllers;

use App\Models\DocumentTemplate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DocumentTemplateController extends Controller
{
    /**
     * Display templates list
     */
    public function index()
    {
        $templates = DocumentTemplate::where('tenant_id', Auth::user()->tenant_id)
            ->with('creator:id,name,email')
            ->latest()
            ->paginate(20);

        return view('documents.templates.index', compact('templates'));
    }

    /**
     * Show create template form
     */
    public function create()
    {
        return view('documents.templates.create');
    }

    /**
     * Store new template
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:document_templates,name,NULL,id,tenant_id,' . Auth::user()->tenant_id,
            'description' => 'nullable|string',
            'content' => 'required|string',
            'category' => 'nullable|string|max:100',
            'variables' => 'nullable|array',
            'is_active' => 'boolean',
        ]);

        DocumentTemplate::create([
            'tenant_id' => Auth::user()->tenant_id,
            'created_by' => Auth::id(),
            'name' => $validated['name'],
            'description' => $validated['description'] ?? '',
            'content' => $validated['content'],
            'category' => $validated['category'] ?? 'general',
            'variables' => $validated['variables'] ?? [],
            'is_active' => $validated['is_active'] ?? true,
        ]);

        return redirect()->route('documents.templates.index')
            ->with('success', 'Document template created successfully');
    }

    /**
     * Display template
     */
    public function show(DocumentTemplate $template)
    {
        $this->authorize('view', $template);

        return view('documents.templates.show', compact('template'));
    }

    /**
     * Show edit template form
     */
    public function edit(DocumentTemplate $template)
    {
        $this->authorize('update', $template);

        return view('documents.templates.edit', compact('template'));
    }

    /**
     * Update template
     */
    public function update(Request $request, DocumentTemplate $template)
    {
        $this->authorize('update', $template);

        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:document_templates,name,' . $template->id . ',id,tenant_id,' . Auth::user()->tenant_id,
            'description' => 'nullable|string',
            'content' => 'required|string',
            'category' => 'nullable|string|max:100',
            'variables' => 'nullable|array',
            'is_active' => 'boolean',
        ]);

        $template->update($validated);

        return redirect()->route('documents.templates.index')
            ->with('success', 'Document template updated successfully');
    }

    /**
     * Delete template
     */
    public function destroy(DocumentTemplate $template)
    {
        $this->authorize('delete', $template);

        $template->delete();

        return redirect()->route('documents.templates.index')
            ->with('success', 'Document template deleted');
    }

    /**
     * Duplicate template
     */
    public function duplicate(DocumentTemplate $template)
    {
        $this->authorize('create', DocumentTemplate::class);

        $newTemplate = DocumentTemplate::create([
            'tenant_id' => Auth::user()->tenant_id,
            'created_by' => Auth::id(),
            'name' => $template->name . ' (Copy)',
            'description' => $template->description,
            'content' => $template->content,
            'category' => $template->category,
            'variables' => $template->variables,
            'is_active' => false,
        ]);

        return redirect()->route('documents.templates.edit', $newTemplate)
            ->with('success', 'Template duplicated successfully');
    }

    /**
     * Get template via API
     */
    public function getTemplate(DocumentTemplate $template)
    {
        $this->authorize('view', $template);

        return response()->json($template);
    }

    /**
     * Get templates by category
     */
    public function getByCategory(Request $request, string $category)
    {
        $templates = DocumentTemplate::where('tenant_id', Auth::user()->tenant_id)
            ->where('category', $category)
            ->where('is_active', true)
            ->get();

        return response()->json($templates);
    }
}
