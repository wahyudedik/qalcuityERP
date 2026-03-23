<?php

namespace App\Http\Controllers;

use App\Models\DocumentTemplate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class CompanyProfileController extends Controller
{
    public function index()
    {
        $tenant = auth()->user()->tenant;
        $templates = DocumentTemplate::where('tenant_id', $tenant->id)
            ->orderBy('doc_type')->orderBy('name')->get();

        return view('settings.company-profile', compact('tenant', 'templates'));
    }

    public function update(Request $request)
    {
        $tenant = auth()->user()->tenant;

        $data = $request->validate([
            'name'                  => 'required|string|max:255',
            'email'                 => 'nullable|email|max:255',
            'phone'                 => 'nullable|string|max:30',
            'address'               => 'nullable|string|max:500',
            'city'                  => 'nullable|string|max:100',
            'province'              => 'nullable|string|max:100',
            'postal_code'           => 'nullable|string|max:10',
            'npwp'                  => 'nullable|string|max:30',
            'website'               => 'nullable|url|max:255',
            'tagline'               => 'nullable|string|max:255',
            'bank_name'             => 'nullable|string|max:100',
            'bank_account'          => 'nullable|string|max:50',
            'bank_account_name'     => 'nullable|string|max:255',
            'invoice_footer_notes'  => 'nullable|string|max:1000',
            'invoice_payment_terms' => 'nullable|string|max:255',
            'letter_head_color'     => 'nullable|string|max:7',
            'doc_number_prefix'     => 'nullable|string|max:20',
            'logo'                  => 'nullable|image|max:2048',
            'stamp_image'           => 'nullable|image|max:2048',
            'director_signature'    => 'nullable|image|max:2048',
        ]);

        // Handle file uploads
        foreach (['logo', 'stamp_image', 'director_signature'] as $field) {
            if ($request->hasFile($field)) {
                // Delete old file
                if ($tenant->$field) {
                    Storage::disk('public')->delete($tenant->$field);
                }
                $data[$field] = $request->file($field)->store("tenants/{$tenant->id}", 'public');
            } else {
                unset($data[$field]);
            }
        }

        $tenant->update($data);

        return back()->with('success', 'Profil perusahaan berhasil disimpan.');
    }

    public function removeLogo(Request $request, string $field)
    {
        $tenant = auth()->user()->tenant;
        $allowed = ['logo', 'stamp_image', 'director_signature'];

        if (!in_array($field, $allowed)) {
            abort(422);
        }

        if ($tenant->$field) {
            Storage::disk('public')->delete($tenant->$field);
            $tenant->update([$field => null]);
        }

        return back()->with('success', 'Gambar berhasil dihapus.');
    }

    // Document Templates
    public function storeTemplate(Request $request)
    {
        $tenant = auth()->user()->tenant;

        $data = $request->validate([
            'name'         => 'required|string|max:255',
            'doc_type'     => 'required|in:invoice,po,quotation,letter,memo',
            'html_content' => 'required|string',
            'is_default'   => 'boolean',
        ]);

        if (!empty($data['is_default'])) {
            DocumentTemplate::where('tenant_id', $tenant->id)
                ->where('doc_type', $data['doc_type'])
                ->update(['is_default' => false]);
        }

        DocumentTemplate::create(array_merge($data, ['tenant_id' => $tenant->id]));

        return back()->with('success', 'Template dokumen berhasil disimpan.');
    }

    public function updateTemplate(Request $request, DocumentTemplate $template)
    {
        $this->authorizeTemplate($template);

        $data = $request->validate([
            'name'         => 'required|string|max:255',
            'html_content' => 'required|string',
            'is_default'   => 'boolean',
        ]);

        if (!empty($data['is_default'])) {
            DocumentTemplate::where('tenant_id', $template->tenant_id)
                ->where('doc_type', $template->doc_type)
                ->where('id', '!=', $template->id)
                ->update(['is_default' => false]);
        }

        $template->update($data);

        return back()->with('success', 'Template berhasil diperbarui.');
    }

    public function destroyTemplate(DocumentTemplate $template)
    {
        $this->authorizeTemplate($template);
        $template->delete();

        return back()->with('success', 'Template berhasil dihapus.');
    }

    private function authorizeTemplate(DocumentTemplate $template): void
    {
        if ($template->tenant_id !== auth()->user()->tenant_id) {
            abort(403);
        }
    }
}
