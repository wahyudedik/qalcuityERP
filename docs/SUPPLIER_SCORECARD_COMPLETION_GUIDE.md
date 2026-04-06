# 🏭 Multi-Supplier Management - Quick Completion Guide

## 🎯 What's Left to Do (2-3 hours)

This guide will help you complete the supplier scorecard module by adding the controller, routes, and remaining views.

---

## Step 1: Create Controller (30 min)

Create file: `app/Http/Controllers/Suppliers/SupplierScorecardController.php`

```php
<?php

namespace App\Http\Controllers\Suppliers;

use App\Http\Controllers\Controller;
use App\Services\SupplierScorecardService;
use App\Services\StrategicSourcingService;
use Illuminate\Http\Request;

class SupplierScorecardController extends Controller
{
    protected $scorecardService;
    protected $sourcingService;

    public function __construct(
        SupplierScorecardService $scorecardService,
        StrategicSourcingService $sourcingService
    ) {
        $this->scorecardService = $scorecardService;
        $this->sourcingService = $sourcingService;
    }

    /**
     * Display supplier scorecard dashboard
     */
    public function index(Request $request)
    {
        $period = $request->get('period', 'monthly');
        $dashboard = $this->scorecardService->getDashboardData(auth()->user()->tenant_id, $period);
        
        return view('suppliers.scorecard-dashboard', compact('dashboard'));
    }

    /**
     * Show detailed supplier performance report
     */
    public function detail($supplierId)
    {
        $report = $this->scorecardService->getSupplierPerformanceReport($supplierId, 12);
        
        return view('suppliers.scorecard-detail', compact('report'));
    }

    /**
     * Generate scorecards for all suppliers
     */
    public function generate(Request $request)
    {
        $validated = $request->validate([
            'period' => 'required|in:monthly,quarterly,yearly'
        ]);

        $generated = $this->scorecardService->generateBulkScorecards(
            auth()->user()->tenant_id,
            $validated['period']
        );

        return redirect()->back()
            ->with('success', "Successfully generated {$generated} scorecards!");
    }

    /**
     * Display strategic sourcing dashboard
     */
    public function sourcingDashboard()
    {
        $dashboard = $this->sourcingService->getSourcingDashboard(auth()->user()->tenant_id);
        $opportunities = $this->sourcingService->identifyOpportunities(auth()->user()->tenant_id);
        
        return view('suppliers.sourcing-dashboard', compact('dashboard', 'opportunities'));
    }

    /**
     * Analyze RFQ responses
     */
    public function analyzeRfq($rfqId)
    {
        $analysis = $this->sourcingService->analyzeRfqResponses($rfqId);
        
        return view('suppliers.rfq-analysis', compact('analysis'));
    }

    /**
     * Create new sourcing opportunity
     */
    public function createOpportunity(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'category' => 'nullable|string',
            'estimated_annual_spend' => 'required|numeric|min:0',
            'priority' => 'required|in:low,medium,high,critical',
        ]);

        $validated['tenant_id'] = auth()->user()->tenant_id;
        $opportunity = $this->sourcingService->createOpportunity($validated);

        return redirect()->route('suppliers.sourcing')
            ->with('success', 'Opportunity created successfully!');
    }
}
```

---

## Step 2: Add Routes (10 min)

Add to `routes/web.php`:

```php
// Supplier Scorecard & Sourcing Routes
Route::prefix('suppliers')->name('suppliers.')->middleware(['auth'])->group(function () {
    
    // Scorecard Dashboard
    Route::get('/scorecards', [\App\Http\Controllers\Suppliers\SupplierScorecardController::class, 'index'])
        ->name('scorecards.index');
    
    Route::get('/scorecards/{id}', [\App\Http\Controllers\Suppliers\SupplierScorecardController::class, 'detail'])
        ->name('scorecard.detail');
    
    Route::post('/scorecards/generate', [\App\Http\Controllers\Suppliers\SupplierScorecardController::class, 'generate'])
        ->name('scorecard.generate');
    
    // Strategic Sourcing
    Route::get('/sourcing', [\App\Http\Controllers\Suppliers\SupplierScorecardController::class, 'sourcingDashboard'])
        ->name('sourcing');
    
    Route::post('/opportunities', [\App\Http\Controllers\Suppliers\SupplierScorecardController::class, 'createOpportunity'])
        ->name('opportunities.create');
    
    Route::get('/rfq/{id}/analysis', [\App\Http\Controllers\Suppliers\SupplierScorecardController::class, 'analyzeRfq'])
        ->name('rfq.analysis');
});
```

---

## Step 3: Create Remaining Views (1 hour)

### A. Supplier Detail View
File: `resources/views/suppliers/scorecard-detail.blade.php`

Key sections:
- Supplier info header
- Current rating badge
- 12-month trend chart (simple bar chart with CSS)
- Score history table
- Recent incidents list
- Strengths & improvement areas

### B. Sourcing Dashboard
File: `resources/views/suppliers/sourcing-dashboard.blade.php`

Key sections:
- Active opportunities count
- Total potential savings
- Opportunities by status/priority
- Opportunity creation form
- Recent RFQ activity table
- Supplier participation rate

### C. RFQ Analysis View
File: `resources/views/suppliers/rfq-analysis.blade.php`

Key sections:
- Response summary stats
- Scored supplier comparison table
- Price range visualization
- Recommended supplier highlight
- Accept/reject actions

---

## Step 4: Add Navigation Link (5 min)

Add to sidebar in `resources/views/layouts/app.blade.php` after Purchasing section:

```blade
@can('view-suppliers')
    <div class="px-4 py-2 text-xs font-semibold text-gray-500 dark:text-slate-400 uppercase">
        Supplier Management
    </div>
    <a href="{{ route('suppliers.scorecards.index') }}" 
       class="flex items-center gap-3 px-4 py-3 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800 transition {{ request()->routeIs('suppliers.*') ? 'bg-blue-50 dark:bg-blue-900/20 text-blue-600 dark:text-blue-400' : 'text-gray-700 dark:text-slate-300' }}">
        <span>📊</span>
        <span>Supplier Scorecards</span>
    </a>
    <a href="{{ route('suppliers.sourcing') }}" 
       class="flex items-center gap-3 px-4 py-3 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800 transition {{ request()->routeIs('suppliers.sourcing') ? 'bg-blue-50 dark:bg-blue-900/20 text-blue-600 dark:text-blue-400' : 'text-gray-700 dark:text-slate-300' }}">
        <span>🎯</span>
        <span>Strategic Sourcing</span>
    </a>
@endcan
```

---

## Step 5: Run Migration (5 min)

```bash
php artisan migrate
```

Expected output:
```
Migrating: 2026_04_06_150000_create_supplier_scorecard_tables
Migrated:  2026_04_06_150000_create_supplier_scorecard_tables (XX.XXms)
```

---

## Step 6: Test the Module (30 min)

### Test Checklist:

1. **Access Dashboard**
   ```
   Navigate to: /suppliers/scorecards
   Expected: Dashboard loads with metrics
   ```

2. **Generate Scorecards**
   ```
   Click "Generate Scorecards" → Select "Monthly" → Submit
   Expected: Success message, scorecards appear in table
   ```

3. **View Supplier Detail**
   ```
   Click "View Detail →" on any supplier
   Expected: Detailed report with trends
   ```

4. **Check Sourcing Dashboard**
   ```
   Navigate to: /suppliers/sourcing
   Expected: Opportunities listed, metrics displayed
   ```

5. **Verify Data Accuracy**
   ```
   Compare scorecard scores with actual PO data
   Expected: Scores match calculations
   ```

---

## 🎨 Sample View Templates

### Supplier Detail View Skeleton

```blade
<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <span>📊 {{ $report['supplier']->name }} - Performance Report</span>
            <a href="{{ route('suppliers.scorecards.index') }}" class="px-3 py-1.5 text-sm bg-gray-200 dark:bg-gray-700 rounded-lg">← Back</a>
        </div>
    </x-slot>

    {{-- Current Rating Card --}}
    <div class="bg-white dark:bg-[#1e293b] rounded-2xl border p-6 mb-6">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-2xl font-bold">{{ $report['current_rating'] }}</h2>
                <p class="text-sm text-gray-500">Current Rating</p>
            </div>
            <div class="text-right">
                <p class="text-3xl font-bold">{{ number_format($report['current_score'], 1) }}/100</p>
                <p class="text-sm text-gray-500">Overall Score</p>
            </div>
        </div>
    </div>

    {{-- Trend Chart Placeholder --}}
    <div class="bg-white dark:bg-[#1e293b] rounded-2xl border p-6 mb-6">
        <h3 class="text-base font-semibold mb-4">📈 12-Month Trend</h3>
        <!-- Add simple bar chart here -->
    </div>

    {{-- Score History Table --}}
    <div class="bg-white dark:bg-[#1e293b] rounded-2xl border overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 dark:bg-[#0f172a]">
                <tr>
                    <th class="px-6 py-3 text-left">Period</th>
                    <th class="px-6 py-3 text-left">Score</th>
                    <th class="px-6 py-3 text-left">Rating</th>
                </tr>
            </thead>
            <tbody>
                @foreach($report['scorecards'] as $scorecard)
                <tr>
                    <td>{{ $scorecard->period_end->format('M Y') }}</td>
                    <td>{{ number_format($scorecard->overall_score, 1) }}</td>
                    <td><span class="badge">{{ $scorecard->rating }}</span></td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</x-app-layout>
```

---

## 🔧 Troubleshooting

### Common Issues:

**Issue**: "Class not found" error
```bash
composer dump-autoload
```

**Issue**: Routes not working
```bash
php artisan route:clear
php artisan cache:clear
```

**Issue**: No data showing
- Ensure you have purchase orders with delivery dates
- Check that suppliers are marked as active
- Verify tenant_id matches logged-in user

**Issue**: Scores seem incorrect
- Review calculation logic in SupplierScorecardService
- Check that PO data has proper delivery_date and expected_delivery_date
- Verify rejected_quantity is being tracked

---

## ✅ Completion Checklist

- [ ] Controller created with all methods
- [ ] Routes added to web.php
- [ ] Navigation link added to sidebar
- [ ] Migration run successfully
- [ ] Dashboard view accessible
- [ ] Scorecards can be generated
- [ ] Supplier detail page works
- [ ] Sourcing dashboard displays
- [ ] All links navigate correctly
- [ ] Mobile responsive tested
- [ ] Dark mode verified

---

## 🚀 After Completion

Once everything is working:

1. **Schedule Automatic Generation** (Optional)
   ```php
   // app/Console/Kernel.php or routes/console.php
   Schedule::call(function () {
       $service = app(SupplierScorecardService::class);
       $tenants = Tenant::all();
       foreach ($tenants as $tenant) {
           $service->generateBulkScorecards($tenant->id, 'monthly');
       }
   })->monthlyOn(1, '02:00');
   ```

2. **Add Email Notifications** (Optional)
   - Notify procurement when rating drops
   - Alert when incidents are reported
   - Weekly sourcing opportunity digest

3. **Export Functionality** (Optional)
   - Export scorecards to Excel
   - PDF reports for management
   - CSV data for analysis

---

## 📞 Support

If you encounter issues:
1. Check Laravel logs: `storage/logs/laravel.log`
2. Review service methods for calculation logic
3. Verify database schema with migration file
4. Check browser console for JavaScript errors

---

*Quick Completion Guide v1.0 | Estimated Time: 2-3 hours*
