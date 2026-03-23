<?php

namespace App\Http\Controllers\Api;

use App\Models\Product;
use App\Models\ProductStock;
use Illuminate\Http\Request;

class ApiProductController extends ApiBaseController
{
    public function index(Request $request)
    {
        $products = Product::where('tenant_id', $this->tenantId())
            ->where('is_active', true)
            ->with(['stocks.warehouse'])
            ->paginate(50);

        return $this->ok($products);
    }

    public function show(int $id)
    {
        $product = Product::where('tenant_id', $this->tenantId())
            ->with(['stocks.warehouse'])
            ->findOrFail($id);

        return $this->ok($product);
    }
}
