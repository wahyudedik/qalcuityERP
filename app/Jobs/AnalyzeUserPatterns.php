<?php

namespace App\Jobs;

use App\Models\AiLearnedPattern;
use App\Models\PurchaseOrder;
use App\Models\SalesOrder;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AnalyzeUserPatterns implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 3600;

    public int $tries = 1;

    public function handle(): void
    {
        Tenant::where('is_active', true)->cursor()->each(function ($tenant) {
            try {
                User::where('tenant_id', $tenant->id)
                    ->where('is_active', true)
                    ->cursor()
                    ->each(function ($user) use ($tenant) {
                        $this->analyzeCustomerBehavior($tenant->id, $user->id);
                        $this->analyzeSupplierPreference($tenant->id, $user->id);
                        $this->analyzeProductAffinity($tenant->id, $user->id);
                        $this->analyzeOrderPatterns($tenant->id, $user->id);
                    });
            } catch (\Throwable $e) {
                Log::warning("AnalyzeUserPatterns failed for tenant {$tenant->id}: ".$e->getMessage());
            }
        });
    }

    // ─── Customer Behavior ────────────────────────────────────────────────────

    private function analyzeCustomerBehavior(int $tenantId, int $userId): void
    {
        try {
            $rows = SalesOrder::where('tenant_id', $tenantId)
                ->where('user_id', $userId)
                ->where('created_at', '>=', now()->subDays(90))
                ->whereNotNull('customer_id')
                ->select(
                    'customer_id',
                    DB::raw('COUNT(*) as frequency'),
                    DB::raw('AVG(total) as avg_order_value'),
                    DB::raw('MAX(created_at) as last_order_date')
                )
                ->groupBy('customer_id')
                ->orderByDesc('frequency')
                ->limit(5)
                ->get();

            foreach ($rows as $row) {
                $preferredPayment = SalesOrder::where('tenant_id', $tenantId)
                    ->where('user_id', $userId)
                    ->where('customer_id', $row->customer_id)
                    ->where('created_at', '>=', now()->subDays(90))
                    ->select('payment_method', DB::raw('COUNT(*) as cnt'))
                    ->groupBy('payment_method')
                    ->orderByDesc('cnt')
                    ->value('payment_method');

                AiLearnedPattern::updateOrCreate(
                    [
                        'tenant_id' => $tenantId,
                        'user_id' => $userId,
                        'pattern_type' => 'customer_behavior',
                        'entity_type' => 'customer',
                        'entity_id' => $row->customer_id,
                    ],
                    [
                        'pattern_data' => [
                            'frequency' => (int) $row->frequency,
                            'avg_order_value' => round((float) $row->avg_order_value, 2),
                            'preferred_payment' => $preferredPayment,
                            'last_order_date' => $row->last_order_date,
                        ],
                        'confidence' => min(1.0, $row->frequency / 10),
                        'analyzed_at' => now(),
                    ]
                );
            }
        } catch (\Throwable $e) {
            Log::warning("analyzeCustomerBehavior failed tenant={$tenantId} user={$userId}: ".$e->getMessage());
        }
    }

    // ─── Supplier Preference ──────────────────────────────────────────────────

    private function analyzeSupplierPreference(int $tenantId, int $userId): void
    {
        try {
            $rows = PurchaseOrder::where('tenant_id', $tenantId)
                ->where('user_id', $userId)
                ->where('created_at', '>=', now()->subDays(90))
                ->whereNotNull('supplier_id')
                ->select(
                    'supplier_id',
                    DB::raw('COUNT(*) as frequency'),
                    DB::raw('AVG(total) as avg_order_value'),
                    DB::raw('MAX(created_at) as last_order_date')
                )
                ->groupBy('supplier_id')
                ->orderByDesc('frequency')
                ->limit(5)
                ->get();

            foreach ($rows as $row) {
                AiLearnedPattern::updateOrCreate(
                    [
                        'tenant_id' => $tenantId,
                        'user_id' => $userId,
                        'pattern_type' => 'supplier_preference',
                        'entity_type' => 'supplier',
                        'entity_id' => $row->supplier_id,
                    ],
                    [
                        'pattern_data' => [
                            'frequency' => (int) $row->frequency,
                            'avg_order_value' => round((float) $row->avg_order_value, 2),
                            'last_order_date' => $row->last_order_date,
                        ],
                        'confidence' => min(1.0, $row->frequency / 10),
                        'analyzed_at' => now(),
                    ]
                );
            }
        } catch (\Throwable $e) {
            Log::warning("analyzeSupplierPreference failed tenant={$tenantId} user={$userId}: ".$e->getMessage());
        }
    }

    // ─── Product Affinity ─────────────────────────────────────────────────────

    private function analyzeProductAffinity(int $tenantId, int $userId): void
    {
        try {
            $orderIds = SalesOrder::where('tenant_id', $tenantId)
                ->where('user_id', $userId)
                ->where('created_at', '>=', now()->subDays(90))
                ->pluck('id');

            if ($orderIds->isEmpty()) {
                return;
            }

            // Self-join on sales_order_items to find product pairs co-occurring in same order
            $pairs = DB::table('sales_order_items as a')
                ->join('sales_order_items as b', function ($join) {
                    $join->on('a.sales_order_id', '=', 'b.sales_order_id')
                        ->whereColumn('a.product_id', '<', 'b.product_id');
                })
                ->whereIn('a.sales_order_id', $orderIds)
                ->select(
                    'a.product_id as product_a',
                    'b.product_id as product_b',
                    DB::raw('COUNT(*) as co_occurrence')
                )
                ->groupBy('a.product_id', 'b.product_id')
                ->orderByDesc('co_occurrence')
                ->limit(5)
                ->get();

            foreach ($pairs as $pair) {
                AiLearnedPattern::updateOrCreate(
                    [
                        'tenant_id' => $tenantId,
                        'user_id' => $userId,
                        'pattern_type' => 'product_affinity',
                        'entity_type' => 'product',
                        'entity_id' => $pair->product_a,
                    ],
                    [
                        'pattern_data' => [
                            'product_a' => $pair->product_a,
                            'product_b' => $pair->product_b,
                            'co_occurrence' => (int) $pair->co_occurrence,
                        ],
                        'confidence' => min(1.0, $pair->co_occurrence / 5),
                        'analyzed_at' => now(),
                    ]
                );
            }
        } catch (\Throwable $e) {
            Log::warning("analyzeProductAffinity failed tenant={$tenantId} user={$userId}: ".$e->getMessage());
        }
    }

    // ─── Order Patterns ───────────────────────────────────────────────────────

    private function analyzeOrderPatterns(int $tenantId, int $userId): void
    {
        try {
            $orders = SalesOrder::where('tenant_id', $tenantId)
                ->where('user_id', $userId)
                ->where('created_at', '>=', now()->subDays(90))
                ->select('created_at')
                ->get();

            if ($orders->isEmpty()) {
                return;
            }

            $dayCount = array_fill(0, 7, 0);
            $hourCount = array_fill(0, 24, 0);

            foreach ($orders as $order) {
                $dayCount[$order->created_at->dayOfWeek]++;
                $hourCount[$order->created_at->hour]++;
            }

            $preferredDayIndex = (int) array_search(max($dayCount), $dayCount);
            $peakHour = (int) array_search(max($hourCount), $hourCount);

            $dayNames = ['sunday', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday'];
            $preferredDay = $dayNames[$preferredDayIndex];
            $avgPerWeek = round($orders->count() / (90 / 7), 2);

            AiLearnedPattern::updateOrCreate(
                [
                    'tenant_id' => $tenantId,
                    'user_id' => $userId,
                    'pattern_type' => 'order_pattern',
                    'entity_type' => 'user',
                    'entity_id' => $userId,
                ],
                [
                    'pattern_data' => [
                        'preferred_day' => $preferredDay,
                        'avg_orders_per_week' => $avgPerWeek,
                        'peak_hour' => $peakHour,
                        'total_orders_90d' => $orders->count(),
                    ],
                    'confidence' => min(1.0, $orders->count() / 20),
                    'analyzed_at' => now(),
                ]
            );
        } catch (\Throwable $e) {
            Log::warning("analyzeOrderPatterns failed tenant={$tenantId} user={$userId}: ".$e->getMessage());
        }
    }
}
