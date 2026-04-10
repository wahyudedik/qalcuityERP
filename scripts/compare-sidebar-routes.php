<?php

/**
 * Script untuk compare sidebar routes dengan actual routes
 * Usage: php scripts/compare-sidebar-routes.php
 */

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\Route;

echo "========================================\n";
echo "SIDEBAR vs ACTUAL ROUTES COMPARISON\n";
echo "Generated: " . now()->format('Y-m-d H:i:s') . "\n";
echo "========================================\n\n";

// Get all registered routes
$allRoutes = Route::getRoutes();
$routeNames = [];

foreach ($allRoutes as $route) {
    if ($route->getName()) {
        $routeNames[] = $route->getName();
    }
}

// Sidebar routes dari app.blade.php (extract dari NAV_GROUPS)
$sidebarRoutes = [
    // Home
    'dashboard',
    'reports.index',
    'kpi.index',
    'forecast.index',
    'reports.cash-flow-projection',
    'anomalies.index',
    'zero-input.index',
    'simulations.index',

    // AI
    'chat.index',

    // Master Data
    'customers.index',
    'suppliers.index',
    'suppliers.scorecards.index',
    'suppliers.sourcing',
    'products.index',
    'warehouses.index',
    'price-lists.index',
    'categories.index',

    // Sales
    'sales.index',
    'quotations.index',
    'invoices.index',
    'delivery-orders.index',
    'down-payments.index',
    'sales-returns.index',
    'crm.index',
    'commission.index',
    'commission.rules',
    'helpdesk.index',
    'helpdesk.kb',
    'subscription-billing.index',
    'subscription-billing.plans',
    'loyalty.index',
    'pos.index',

    // Inventory
    'inventory.index',
    'inventory.transfers.index',
    'purchasing.orders',
    'purchasing.requisitions',
    'purchasing.rfq',
    'purchasing.goods-receipts',
    'purchasing.matching',
    'purchase-returns.index',
    'landed-cost.index',
    'consignment.index',
    'consignment.partners',
    'wms.index',
    'wms.picking',
    'wms.opname',
    'wms.putaway-rules',

    // Operations
    'production.index',
    'manufacturing.bom',
    'manufacturing.mix-design',
    'manufacturing.work-centers',
    'manufacturing.mrp',
    'printing.dashboard',
    'cosmetic.formulas.index',
    'cosmetic.batches.index',
    'cosmetic.qc.tests',
    'cosmetic.registrations.index',
    'cosmetic.variants.index',
    'cosmetic.packaging.index',
    'cosmetic.expiry.dashboard',
    'cosmetic.distribution.index',
    'cosmetic.analytics.dashboard',
    'tour-travel.packages.index',
    'tour-travel.bookings.index',
    'tour-travel.analytics',
    'livestock-enhancement.dairy.milk-records',
    'livestock-enhancement.poultry.flocks',
    'livestock-enhancement.breeding.records',
    'livestock-enhancement.health.treatments',
    'livestock-enhancement.waste.logs',
    'fleet.index',
    'fleet.drivers',
    'fleet.trips',
    'fleet.fuel-logs',
    'fleet.maintenance',
    'shipping.index',
    'farm.plots',
    'farm.cycles',
    'farm.harvests',
    'farm.analytics',
    'farm.livestock',
    'fisheries.index',
    'fisheries.cold-chain.index',
    'fisheries.operations.index',
    'fisheries.aquaculture.index',
    'fisheries.species.index',
    'fisheries.export.index',
    'fisheries.analytics',
    'contracts.index',
    'contracts.templates',
    'approvals.index',
    'ecommerce.index',
    'documents.index',
    'projects.index',
    'project-billing.index',
    'timesheets.index',

    // HRM
    'hrm.recruitment.index',
    'hrm.index',
    'hrm.leave',
    'hrm.performance',
    'hrm.orgchart',
    'hrm.shifts.index',
    'hrm.overtime.index',
    'hrm.training.index',
    'hrm.disciplinary.index',
    'payroll.index',
    'payroll.components.index',
    'reimbursement.index',
    'self-service.dashboard',
    'payroll.slip.index',
    'self-service.leave.index',
    'self-service.attendance.index',
    'reimbursement.my',

    // Finance
    'expenses.index',
    'receivables.index',
    'payables.index',
    'bulk-payments.index',
    'bank-accounts.index',
    'bank.reconciliation',
    'budget.index',
    'assets.index',
    'journals.index',
    'accounting.coa',
    'accounting.trial-balance',
    'accounting.balance-sheet',
    'accounting.income-statement',
    'accounting.cash-flow',
    'deferred.index',
    'writeoffs.index',
    'accounting.periods',
    'accounting.period-lock.index',

    // Hotel
    'hotel.dashboard',
    'hotel.room-types.index',
    'hotel.rooms.index',
    'hotel.rooms.availability',
    'hotel.reservations.index',
    'hotel.guests.index',
    'hotel.checkin-out.index',
    'hotel.housekeeping.room-board',
    'hotel.rates.index',
    'hotel.channels.index',
    'hotel.settings.edit',

    // Settings
    'company-profile.index',
    'settings.modules.index',
    'tenant.users.index',
    'reminders.index',
    'import.index',
    'audit.index',
    'notifications.index',
    'bot.settings',
    'settings.integrations.index',
    'api-settings.index',
    'cost-centers.index',
    'ai-memory.index',
    'taxes.index',
    'custom-fields.index',
    'constraints.index',
    'company-groups.index',
    'subscription.index',

    // Profile
    'profile.edit',
];

echo "📊 COMPARISON SUMMARY\n";
echo "----------------------------------------\n";
echo "Total Sidebar Routes: " . count($sidebarRoutes) . "\n";
echo "Total Registered Routes: " . count($routeNames) . "\n\n";

// Check for missing routes
$missingRoutes = [];
$existingRoutes = [];

foreach ($sidebarRoutes as $sidebarRoute) {
    // Check if route exists (with wildcard matching)
    $found = false;
    foreach ($routeNames as $registeredRoute) {
        // Exact match or wildcard match
        if (
            $registeredRoute === $sidebarRoute ||
            str_starts_with($registeredRoute, rtrim($sidebarRoute, '*')) ||
            str_starts_with($sidebarRoute, rtrim($registeredRoute, '*'))
        ) {
            $found = true;
            $existingRoutes[] = $sidebarRoute;
            break;
        }
    }

    if (!$found) {
        $missingRoutes[] = $sidebarRoute;
    }
}

if (count($missingRoutes) > 0) {
    echo "❌ MISSING ROUTES (Sidebar → Not Found in Routes)\n";
    echo "========================================\n\n";

    foreach ($missingRoutes as $missing) {
        echo "  - {$missing}\n";
    }

    echo "\n";
}

// Show existing routes count
echo "✅ EXISTING ROUTES\n";
echo "----------------------------------------\n";
echo "Found: " . count($existingRoutes) . " / " . count($sidebarRoutes) . " routes\n";

if (count($missingRoutes) === 0) {
    echo "\n🎉 All sidebar routes are valid!\n";
} else {
    echo "\n⚠️  Found " . count($missingRoutes) . " missing routes that need to be fixed\n";
}

echo "\n========================================\n";
echo "COMPARISON COMPLETE\n";
echo "========================================\n";
