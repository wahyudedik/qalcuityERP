<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Invoice;
use App\Models\JournalEntry;
use App\Models\Product;
use App\Models\SalesOrder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class QuickSearchController extends Controller
{
    /**
     * Universal search across all modules
     */
    public function search(Request $request)
    {
        $request->validate([
            'q' => 'required|string|min:1|max:100',
            'type' => 'nullable|string|in:all,products,invoices,customers,orders,journals,actions',
            'filters' => 'nullable|array',
        ]);

        $query = $request->get('q', '');
        $type = $request->get('type', 'all');
        $filters = $request->get('filters', []);
        $user = $request->user();
        $tenantId = $user->current_tenant_id ?? $user->tenant_id;

        $results = [];

        // Search products
        if ($type === 'all' || $type === 'products') {
            $results['products'] = Cache::remember("quick_search:products:{$query}", 60, function () use ($query, $tenantId, $filters) {
                $productQuery = Product::where('tenant_id', $tenantId)
                    ->where(function ($q) use ($query) {
                        $q->where('name', 'like', "%{$query}%")
                            ->orWhere('sku', 'like', "%{$query}%")
                            ->orWhere('barcode', 'like', "%{$query}%");
                    })
                    ->where('is_active', true);

                // Apply filters if provided
                if (! empty($filters['min_price'])) {
                    $productQuery->where('price', '>=', $filters['min_price']);
                }
                if (! empty($filters['max_price'])) {
                    $productQuery->where('price', '<=', $filters['max_price']);
                }
                if (! empty($filters['category_id'])) {
                    $productQuery->where('category_id', $filters['category_id']);
                }

                return $productQuery->limit(8)
                    ->get()
                    ->map(fn ($p) => [
                        'type' => 'product',
                        'id' => $p->id,
                        'title' => $p->name,
                        'subtitle' => $p->sku ? "SKU: {$p->sku}" : 'Product',
                        'icon' => 'fas fa-box',
                        'url' => route('products.show', $p->id),
                        'badge' => 'Product',
                    ]);
            });
        }

        // Search invoices
        if ($type === 'all' || $type === 'invoices') {
            $results['invoices'] = Cache::remember("quick_search:invoices:{$query}", 60, function () use ($query, $tenantId) {
                return Invoice::where('tenant_id', $tenantId)
                    ->where(function ($q) use ($query) {
                        $q->where('number', 'like', "%{$query}%")
                            ->orWhereHas('customer', function ($cq) use ($query) {
                                $cq->where('name', 'like', "%{$query}%");
                            });
                    })
                    ->limit(8)
                    ->get()
                    ->map(fn ($i) => [
                        'type' => 'invoice',
                        'id' => $i->id,
                        'title' => $i->number,
                        'subtitle' => $i->customer ? $i->customer->name : 'Invoice',
                        'icon' => 'fas fa-file-invoice-dollar',
                        'url' => route('invoices.show', $i->id),
                        'badge' => ucfirst($i->status ?? 'Draft'),
                    ]);
            });
        }

        // Search customers
        if ($type === 'all' || $type === 'customers') {
            $results['customers'] = Cache::remember("quick_search:customers:{$query}", 60, function () use ($query, $tenantId) {
                return Customer::where('tenant_id', $tenantId)
                    ->where(function ($q) use ($query) {
                        $q->where('name', 'like', "%{$query}%")
                            ->orWhere('email', 'like', "%{$query}%")
                            ->orWhere('phone', 'like', "%{$query}%");
                    })
                    ->limit(8)
                    ->get()
                    ->map(fn ($c) => [
                        'type' => 'customer',
                        'id' => $c->id,
                        'title' => $c->name,
                        'subtitle' => $c->email ?: 'Customer',
                        'icon' => 'fas fa-user',
                        'url' => route('customers.show', $c->id),
                        'badge' => 'Customer',
                    ]);
            });
        }

        // Search sales orders
        if ($type === 'all' || $type === 'orders') {
            $results['orders'] = Cache::remember("quick_search:orders:{$query}", 60, function () use ($query, $tenantId) {
                return SalesOrder::where('tenant_id', $tenantId)
                    ->where('number', 'like', "%{$query}%")
                    ->limit(5)
                    ->get()
                    ->map(fn ($o) => [
                        'type' => 'order',
                        'id' => $o->id,
                        'title' => $o->number,
                        'subtitle' => $o->customer ? $o->customer->name : 'Sales Order',
                        'icon' => 'fas fa-shopping-cart',
                        'url' => route('sales-orders.show', $o->id),
                        'badge' => ucfirst($o->status ?? 'Draft'),
                    ]);
            });
        }

        // Search journal entries
        if ($type === 'all' || $type === 'journals') {
            $results['journals'] = Cache::remember("quick_search:journals:{$query}", 60, function () use ($query, $tenantId) {
                return JournalEntry::where('tenant_id', $tenantId)
                    ->where('reference', 'like', "%{$query}%")
                    ->limit(5)
                    ->get()
                    ->map(fn ($j) => [
                        'type' => 'journal',
                        'id' => $j->id,
                        'title' => $j->reference ?: $j->number,
                        'subtitle' => $j->description ?: 'Journal Entry',
                        'icon' => 'fas fa-book',
                        'url' => route('journal-entries.show', $j->id),
                        'badge' => 'Journal',
                    ]);
            });
        }

        // Quick actions (always included)
        if ($type === 'all' || $type === 'actions') {
            $results['actions'] = $this->getQuickActions($query);
        }

        // Flatten results for display
        $allResults = collect($results)->flatten(1)->values();

        return response()->json([
            'query' => $query,
            'total' => $allResults->count(),
            'results' => $allResults,
            'categories' => array_map(fn ($r) => collect($r)->values(), $results),
        ]);
    }

    /**
     * Get quick actions based on search query
     */
    private function getQuickActions($query)
    {
        $actions = [
            [
                'type' => 'action',
                'id' => 'create-invoice',
                'title' => 'Create New Invoice',
                'subtitle' => 'Quick action',
                'icon' => 'fas fa-plus-circle',
                'url' => route('invoices.create'),
                'badge' => 'Action',
                'keywords' => ['invoice', 'create', 'new', 'bill'],
            ],
            [
                'type' => 'action',
                'id' => 'create-product',
                'title' => 'Create New Product',
                'subtitle' => 'Quick action',
                'icon' => 'fas fa-box-open',
                'url' => route('products.create'),
                'badge' => 'Action',
                'keywords' => ['product', 'create', 'new', 'item'],
            ],
            [
                'type' => 'action',
                'id' => 'create-customer',
                'title' => 'Create New Customer',
                'subtitle' => 'Quick action',
                'icon' => 'fas fa-user-plus',
                'url' => route('customers.create'),
                'badge' => 'Action',
                'keywords' => ['customer', 'create', 'new', 'client'],
            ],
            [
                'type' => 'action',
                'id' => 'dashboard',
                'title' => 'Go to Dashboard',
                'subtitle' => 'Navigation',
                'icon' => 'fas fa-chart-line',
                'url' => route('dashboard'),
                'badge' => 'Navigation',
                'keywords' => ['dashboard', 'home', 'main'],
            ],
            [
                'type' => 'action',
                'id' => 'toggle-theme',
                'title' => 'Toggle Dark/Light Mode',
                'subtitle' => 'Settings',
                'icon' => 'fas fa-moon',
                'url' => '#',
                'badge' => 'Settings',
                'keywords' => ['theme', 'dark', 'light', 'mode'],
                'action' => 'toggle-theme',
            ],
        ];

        // Filter actions based on query
        if ($query) {
            $actions = array_filter($actions, function ($action) use ($query) {
                $searchable = strtolower($action['title'].' '.$action['subtitle'].' '.implode(' ', $action['keywords']));

                return str_contains($searchable, strtolower($query));
            });
        }

        return array_values($actions);
    }
}
