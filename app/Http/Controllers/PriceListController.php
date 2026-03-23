<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\PriceList;
use App\Models\PriceListItem;
use App\Models\Product;
use App\Services\PriceListService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PriceListController extends Controller
{
    private function tid(): int
    {
        return auth()->user()->tenant_id;
    }

    public function index()
    {
        $priceLists = PriceList::where('tenant_id', $this->tid())
            ->withCount('items')
            ->with('customers')
            ->latest()
            ->get();

        return view('price-lists.index', compact('priceLists'));
    }

    public function create()
    {
        $products  = Product::where('tenant_id', $this->tid())->where('is_active', true)->orderBy('name')->get();
        $customers = Customer::where('tenant_id', $this->tid())->where('is_active', true)->orderBy('name')->get();
        return view('price-lists.create', compact('products', 'customers'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'        => 'required|string|max:100',
            'code'        => 'nullable|string|max:30',
            'type'        => 'required|in:tier,contract,promo',
            'description' => 'nullable|string|max:500',
            'valid_from'  => 'nullable|date',
            'valid_until' => 'nullable|date|after_or_equal:valid_from',
            'items'       => 'required|array|min:1',
            'items.*.product_id'       => 'required|exists:products,id',
            'items.*.price'            => 'required|numeric|min:0',
            'items.*.discount_percent' => 'nullable|numeric|min:0|max:100',
            'items.*.min_qty'          => 'nullable|numeric|min:1',
            'customer_ids'             => 'nullable|array',
            'customer_ids.*'           => 'exists:customers,id',
        ]);

        $tid = $this->tid();

        // Cek unique code
        if (! empty($data['code'])) {
            if (PriceList::where('tenant_id', $tid)->where('code', $data['code'])->exists()) {
                return back()->withErrors(['code' => 'Kode price list sudah digunakan.'])->withInput();
            }
        }

        DB::transaction(function () use ($data, $tid, $request) {
            $priceList = PriceList::create([
                'tenant_id'   => $tid,
                'name'        => $data['name'],
                'code'        => $data['code'] ?? null,
                'type'        => $data['type'],
                'description' => $data['description'] ?? null,
                'valid_from'  => $data['valid_from'] ?? null,
                'valid_until' => $data['valid_until'] ?? null,
                'is_active'   => true,
            ]);

            foreach ($data['items'] as $item) {
                PriceListItem::create([
                    'price_list_id'    => $priceList->id,
                    'product_id'       => $item['product_id'],
                    'price'            => $item['price'],
                    'discount_percent' => $item['discount_percent'] ?? 0,
                    'min_qty'          => $item['min_qty'] ?? 1,
                ]);
            }

            // Assign ke customers
            if (! empty($data['customer_ids'])) {
                $sync = [];
                foreach ($data['customer_ids'] as $i => $cid) {
                    $sync[$cid] = ['priority' => $i + 1];
                }
                $priceList->customers()->sync($sync);
            }
        });

        return redirect()->route('price-lists.index')->with('success', "Price list {$data['name']} berhasil dibuat.");
    }

    public function show(PriceList $priceList)
    {
        abort_if($priceList->tenant_id !== $this->tid(), 403);
        $priceList->load(['items.product', 'customers']);
        return view('price-lists.show', compact('priceList'));
    }

    public function update(Request $request, PriceList $priceList)
    {
        abort_if($priceList->tenant_id !== $this->tid(), 403);

        $data = $request->validate([
            'name'        => 'required|string|max:100',
            'description' => 'nullable|string|max:500',
            'valid_from'  => 'nullable|date',
            'valid_until' => 'nullable|date|after_or_equal:valid_from',
            'is_active'   => 'boolean',
        ]);

        $priceList->update($data);

        return back()->with('success', 'Price list berhasil diperbarui.');
    }

    public function destroy(PriceList $priceList)
    {
        abort_if($priceList->tenant_id !== $this->tid(), 403);
        $priceList->delete();
        return back()->with('success', 'Price list dihapus.');
    }

    public function assignCustomer(Request $request, PriceList $priceList)
    {
        abort_if($priceList->tenant_id !== $this->tid(), 403);

        $data = $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'priority'    => 'nullable|integer|min:1',
        ]);

        $priceList->customers()->syncWithoutDetaching([
            $data['customer_id'] => ['priority' => $data['priority'] ?? 1],
        ]);

        return back()->with('success', 'Customer berhasil ditambahkan ke price list.');
    }

    public function removeCustomer(PriceList $priceList, Customer $customer)
    {
        abort_if($priceList->tenant_id !== $this->tid(), 403);
        $priceList->customers()->detach($customer->id);
        return back()->with('success', 'Customer dihapus dari price list.');
    }

    /**
     * API endpoint: dapatkan harga untuk customer + produk (dipakai di form quotation/invoice via AJAX).
     */
    public function getPrice(Request $request, PriceListService $service)
    {
        $data = $request->validate([
            'customer_id' => 'required|integer',
            'product_id'  => 'required|integer',
            'qty'         => 'nullable|numeric|min:1',
        ]);

        // Pastikan customer milik tenant ini
        $customer = Customer::where('tenant_id', $this->tid())->find($data['customer_id']);
        if (! $customer) return response()->json(['error' => 'Customer tidak ditemukan'], 404);

        $result = $service->getPrice($data['customer_id'], $data['product_id'], $data['qty'] ?? 1);

        return response()->json($result);
    }
}
