<?php

namespace App\Http\Controllers;

use App\Models\ProductCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProductCategoryController extends Controller
{
    public function index()
    {
        $categories = ProductCategory::where('tenant_id', Auth::user()->tenant_id)
            ->withCount('products')
            ->orderBy('name')
            ->get();

        return view('categories.index', compact('categories'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'parent_id' => 'nullable|exists:product_categories,id',
        ]);

        $validated['tenant_id'] = Auth::user()->tenant_id;
        $validated['created_by'] = Auth::id();

        ProductCategory::create($validated);

        return redirect()->route('categories.index')
            ->with('success', 'Kategori produk berhasil ditambahkan.');
    }

    public function update(Request $request, ProductCategory $category)
    {
        $this->authorize('update', $category);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'parent_id' => 'nullable|exists:product_categories,id',
        ]);

        $category->update($validated);

        return redirect()->route('categories.index')
            ->with('success', 'Kategori produk berhasil diperbarui.');
    }

    public function destroy(ProductCategory $category)
    {
        $this->authorize('delete', $category);

        if ($category->products()->count() > 0) {
            return redirect()->route('categories.index')
                ->with('error', 'Tidak dapat menghapus kategori yang masih memiliki produk.');
        }

        $category->delete();

        return redirect()->route('categories.index')
            ->with('success', 'Kategori produk berhasil dihapus.');
    }
}
