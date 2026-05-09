<?php

namespace App\Http\Controllers\Telecom;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\TelecomSubscription;
use App\Models\UsageTracking;
use App\Services\Telecom\RouterAdapterFactory;
use App\Services\Telecom\UsageTrackingService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class CustomerController extends Controller
{
    protected UsageTrackingService $usageService;

    public function __construct()
    {
        $this->usageService = new UsageTrackingService;
    }

    /**
     * Display customer usage portal.
     */
    public function usage(Request $request)
    {
        $tenantId = auth()->user()->tenant_id;

        // Get customers with active subscriptions
        $customers = Customer::where('tenant_id', $tenantId)
            ->whereHas('telecomSubscriptions', function ($q) {
                $q->where('status', 'active');
            })
            ->with([
                'telecomSubscriptions' => function ($q) {
                    $q->where('status', 'active')
                        ->with(['package', 'device']);
                },
            ])
            ->orderBy('name')
            ->paginate(20);

        return view('telecom.customers.usage', compact('customers'));
    }

    /**
     * Show detailed usage for a specific customer.
     */
    public function showUsage(Request $request, Customer $customer)
    {
        if ($customer->tenant_id !== auth()->user()->tenant_id) {
            abort(403);
        }

        $period = $request->get('period', 'monthly');

        // Get active subscription
        $subscription = TelecomSubscription::where('customer_id', $customer->id)
            ->where('status', 'active')
            ->with(['package', 'device'])
            ->latest()
            ->first();

        if (! $subscription) {
            return redirect()->route('telecom.customers.usage')
                ->withErrors(['error' => 'Customer tidak memiliki subscription aktif.']);
        }

        // Get usage summary
        $usageSummary = $this->usageService->getUsageSummary($subscription, $period);

        // Get usage history (last 30 days)
        $usageHistory = UsageTracking::where('subscription_id', $subscription->id)
            ->where('period_start', '>=', now()->subDays(30))
            ->orderBy('period_start', 'desc')
            ->limit(30)
            ->get();

        // Chart data for usage trend
        $chartData = $this->getUsageChartData($subscription, 7);

        return view('telecom.customers.detail', compact(
            'customer',
            'subscription',
            'usageSummary',
            'usageHistory',
            'chartData',
            'period'
        ));
    }

    /**
     * Get usage chart data.
     */
    protected function getUsageChartData(TelecomSubscription $subscription, int $days = 7): array
    {
        $dates = collect(range(0, 6))->map(function ($i) {
            return now()->subDays(6 - $i)->format('Y-m-d');
        });

        $usage = UsageTracking::where('subscription_id', $subscription->id)
            ->where('period_start', '>=', now()->subDays($days))
            ->selectRaw('
                DATE(period_start) as date,
                SUM(bytes_in) as total_download,
                SUM(bytes_out) as total_upload
            ')
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->keyBy('date');

        $downloads = [];
        $uploads = [];

        foreach ($dates as $date) {
            $record = $usage->get($date);
            $downloads[] = $record ? round($record->total_download / 1048576, 2) : 0; // MB
            $uploads[] = $record ? round($record->total_upload / 1048576, 2) : 0;
        }

        return [
            'labels' => $dates->map(function ($date) {
                return Carbon::parse($date)->format('d M');
            })->toArray(),
            'downloads' => $downloads,
            'uploads' => $uploads,
        ];
    }

    /**
     * Reset customer quota manually.
     */
    public function resetQuota(Customer $customer)
    {
        if ($customer->tenant_id !== auth()->user()->tenant_id) {
            abort(403);
        }

        $subscription = TelecomSubscription::where('customer_id', $customer->id)
            ->where('status', 'active')
            ->latest()
            ->first();

        if (! $subscription) {
            return back()->withErrors(['error' => 'Tidak ada subscription aktif.']);
        }

        $subscription->resetQuota();

        return back()->with('success', 'Quota berhasil direset untuk '.$customer->name);
    }

    /**
     * Suspend customer subscription.
     */
    public function suspendSubscription(Customer $customer)
    {
        if ($customer->tenant_id !== auth()->user()->tenant_id) {
            abort(403);
        }

        $subscription = TelecomSubscription::where('customer_id', $customer->id)
            ->where('status', 'active')
            ->latest()
            ->first();

        if (! $subscription) {
            return back()->withErrors(['error' => 'Tidak ada subscription aktif.']);
        }

        $subscription->update(['status' => 'suspended']);

        // Disconnect from router if has hotspot username
        if ($subscription->hotspot_username) {
            try {
                $adapter = RouterAdapterFactory::create($subscription->device);
                $adapter->disconnectUser($subscription->hotspot_username);
            } catch (\Exception $e) {
                Log::warning('Failed to disconnect user: '.$e->getMessage());
            }
        }

        return back()->with('success', 'Subscription disuspend untuk '.$customer->name);
    }

    /**
     * Reactivate customer subscription.
     */
    public function reactivateSubscription(Customer $customer)
    {
        if ($customer->tenant_id !== auth()->user()->tenant_id) {
            abort(403);
        }

        $subscription = TelecomSubscription::where('customer_id', $customer->id)
            ->whereIn('status', ['suspended', 'cancelled'])
            ->latest()
            ->first();

        if (! $subscription) {
            return back()->withErrors(['error' => 'Tidak ada subscription yang bisa diaktifkan.']);
        }

        $subscription->update(['status' => 'active']);

        return back()->with('success', 'Subscription diaktifkan kembali untuk '.$customer->name);
    }
}
