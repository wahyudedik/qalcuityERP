<?php

namespace App\Services;

use App\Models\Medicine;
use App\Models\MedicineAlert;
use App\Models\MedicineInteraction;
use App\Models\MedicineStock;
use App\Models\PharmacyDispensing;
use App\Models\Prescription;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PharmacyService
{
    /**
     * Add medicine stock
     */
    public function addStock(int $medicineId, array $stockData): MedicineStock
    {
        return DB::transaction(function () use ($medicineId, $stockData) {
            $medicine = Medicine::findOrFail($medicineId);

            $stock = MedicineStock::create([
                'medicine_id' => $medicineId,
                'supplier_id' => $stockData['supplier_id'] ?? null,
                'batch_number' => $stockData['batch_number'],
                'manufacturing_date' => $stockData['manufacturing_date'] ?? null,
                'expiry_date' => $stockData['expiry_date'],
                'quantity' => $stockData['quantity'],
                'quantity_available' => $stockData['quantity'],
                'purchase_price' => $stockData['purchase_price'] ?? $medicine->purchase_price,
                'unit_cost' => $stockData['purchase_price'] ?? $medicine->purchase_price,
                'storage_location' => $stockData['storage_location'] ?? null,
                'rack_number' => $stockData['rack_number'] ?? null,
                'shelf_number' => $stockData['shelf_number'] ?? null,
                'status' => 'available',
            ]);

            // Update medicine total stock
            $medicine->increment('total_stock', $stockData['quantity']);

            Log::info('Medicine stock added', [
                'medicine_id' => $medicineId,
                'batch_number' => $stockData['batch_number'],
                'quantity' => $stockData['quantity'],
            ]);

            return $stock;
        });
    }

    /**
     * Dispense medicine from prescription
     */
    public function dispenseMedicine(int $prescriptionId, array $dispensingData): PharmacyDispensing
    {
        return DB::transaction(function () use ($prescriptionId, $dispensingData) {
            $prescription = Prescription::findOrFail($prescriptionId);

            // Check drug interactions
            $interactions = $this->checkDrugInteractions($dispensingData['items'] ?? []);

            if (! empty($interactions['critical']) || ! empty($interactions['major'])) {
                throw new Exception('Critical drug interactions detected. Please review before dispensing.');
            }

            // Create dispensing record
            $dispensing = PharmacyDispensing::create([
                'prescription_id' => $prescriptionId,
                'patient_id' => $prescription->patient_id,
                'dispensed_by' => $dispensingData['dispensed_by'],
                'dispense_date' => now(),
                'status' => 'pending',
                'dispensed_items' => $dispensingData['items'] ?? [],
                'subtotal' => $dispensingData['subtotal'] ?? 0,
                'discount' => $dispensingData['discount'] ?? 0,
                'tax' => $dispensingData['tax'] ?? 0,
                'total_amount' => $dispensingData['total_amount'] ?? 0,
                'counseling_provided' => $dispensingData['counseling_provided'] ?? false,
                'counseling_notes' => $dispensingData['counseling_notes'] ?? null,
                'special_instructions' => $dispensingData['special_instructions'] ?? null,
            ]);

            // Deduct stock for each item
            foreach ($dispensingData['items'] as $item) {
                $this->deductStock($item['medicine_id'], $item['quantity']);
            }

            // Update prescription status
            $prescription->markAsDispensed($dispensingData['dispensed_by']);

            Log::info('Medicine dispensed', [
                'dispensing_number' => $dispensing->dispensing_number,
                'prescription_id' => $prescriptionId,
            ]);

            return $dispensing;
        });
    }

    /**
     * Deduct medicine stock (FIFO - First Expired First Out)
     */
    protected function deductStock(int $medicineId, int $quantity): void
    {
        $medicine = Medicine::findOrFail($medicineId);
        $remaining = $quantity;

        // Get available stocks ordered by expiry date (FEFO - First Expired First Out)
        $stocks = MedicineStock::where('medicine_id', $medicineId)
            ->where('status', 'available')
            ->where('is_expired', false)
            ->where('quantity_available', '>', 0)
            ->orderBy('expiry_date', 'asc')
            ->get();

        foreach ($stocks as $stock) {
            if ($remaining <= 0) {
                break;
            }

            $deductFromStock = min($remaining, $stock->quantity_available);

            $stock->decrement('quantity_available', $deductFromStock);
            $stock->increment('quantity_reserved', $deductFromStock);

            $remaining -= $deductFromStock;
        }

        if ($remaining > 0) {
            throw new Exception("Insufficient stock for {$medicine->name}. Missing: {$remaining}");
        }

        // Update medicine total stock
        $medicine->decrement('total_stock', $quantity);
    }

    /**
     * Check drug interactions
     */
    public function checkDrugInteractions(array $medicineIds): array
    {
        $interactions = [
            'contraindicated' => [],
            'major' => [],
            'moderate' => [],
            'minor' => [],
        ];

        // Check all combinations
        for ($i = 0; $i < count($medicineIds); $i++) {
            for ($j = $i + 1; $j < count($medicineIds); $j++) {
                $interaction = MedicineInteraction::where(function ($q) use ($medicineIds, $i, $j) {
                    $q->where('medicine_a_id', $medicineIds[$i])
                        ->where('medicine_b_id', $medicineIds[$j]);
                })
                    ->orWhere(function ($q) use ($medicineIds, $i, $j) {
                        $q->where('medicine_a_id', $medicineIds[$j])
                            ->where('medicine_b_id', $medicineIds[$i]);
                    })
                    ->where('is_active', true)
                    ->first();

                if ($interaction) {
                    $interactions[$interaction->severity][] = [
                        'medicine_a_id' => $interaction->medicine_a_id,
                        'medicine_b_id' => $interaction->medicine_b_id,
                        'severity' => $interaction->severity,
                        'description' => $interaction->description,
                        'management' => $interaction->management,
                        'avoid_combination' => $interaction->avoid_combination,
                    ];
                }
            }
        }

        return $interactions;
    }

    /**
     * Get medicines expiring soon
     */
    public function getExpiringSoon(int $days = 90): array
    {
        $expiryDate = now()->addDays($days);

        $expiringStocks = MedicineStock::where('is_expired', false)
            ->where('expiry_date', '<=', $expiryDate)
            ->where('expiry_date', '>', now())
            ->where('quantity_available', '>', 0)
            ->with(['medicine'])
            ->orderBy('expiry_date', 'asc')
            ->get();

        $expiringStocks->each(function ($stock) {
            $stock->days_until_expiry = now()->diffInDays($stock->expiry_date, false);

            // Send alert if not already sent
            if (! $stock->expiry_alert_sent && $stock->days_until_expiry <= 30) {
                $this->createExpiryAlert($stock);
            }
        });

        return $expiringStocks->toArray();
    }

    /**
     * Mark expired stocks
     */
    public function markExpiredStocks(): int
    {
        $expiredCount = MedicineStock::where('is_expired', false)
            ->where('expiry_date', '<', now())
            ->where('quantity_available', '>', 0)
            ->update([
                'is_expired' => true,
                'status' => 'expired',
                'expired_at' => now(),
            ]);

        if ($expiredCount > 0) {
            Log::warning("Marked {$expiredCount} medicine stocks as expired");
        }

        return $expiredCount;
    }

    /**
     * Get low stock medicines
     */
    public function getLowStockMedicines(): array
    {
        return Medicine::active()
            ->lowStock()
            ->with(['category'])
            ->orderByRaw('(total_stock - reorder_point) ASC')
            ->get()
            ->map(function ($medicine) {
                return [
                    'id' => $medicine->id,
                    'medicine_code' => $medicine->medicine_code,
                    'name' => $medicine->full_name,
                    'current_stock' => $medicine->total_stock,
                    'reorder_point' => $medicine->reorder_point,
                    'minimum_stock' => $medicine->minimum_stock,
                    'stock_status' => $medicine->stock_status,
                    'shortage' => max(0, $medicine->reorder_point - $medicine->total_stock),
                ];
            })
            ->toArray();
    }

    /**
     * Generate daily pharmacy analytics
     */
    public function generateDailyAnalytics($date = null): array
    {
        $date = $date ? Carbon::parse($date) : today();

        $dispensings = PharmacyDispensing::whereDate('dispense_date', $date)->get();

        $analytics = [
            'analytics_date' => $date,
            'total_prescriptions' => $dispensings->count(),
            'total_dispensed' => $dispensings->where('status', 'completed')->count(),
            'total_pending' => $dispensings->where('status', 'pending')->count(),
            'total_cancelled' => $dispensings->where('status', 'cancelled')->count(),
            'total_returned' => $dispensings->where('status', 'returned')->count(),

            'total_revenue' => $dispensings->where('status', 'completed')->sum('total_amount'),
            'total_cost' => 0, // Calculate from dispensed items
            'total_profit' => 0,
            'average_prescription_value' => $dispensings->where('status', 'completed')->count() > 0
                ? $dispensings->where('status', 'completed')->avg('total_amount')
                : 0,

            'total_medicines' => Medicine::active()->count(),
            'low_stock_count' => Medicine::active()->lowStock()->count(),
            'out_of_stock_count' => Medicine::active()->outOfStock()->count(),
            'expired_count' => MedicineStock::where('is_expired', true)->count(),
            'expiring_soon_count' => MedicineStock::where('expiry_date', '<=', now()->addDays(30))
                ->where('is_expired', false)
                ->count(),
        ];

        return $analytics;
    }

    /**
     * Search medicines
     */
    public function searchMedicines(string $query, array $filters = []): array
    {
        $medicines = Medicine::active()->search($query);

        if (isset($filters['category_id'])) {
            $medicines->category($filters['category_id']);
        }

        if (isset($filters['in_stock_only']) && $filters['in_stock_only']) {
            $medicines->where('total_stock', '>', 0);
        }

        if (isset($filters['requires_prescription']) && $filters['requires_prescription']) {
            $medicines->where('requires_prescription', true);
        }

        return $medicines->limit(50)->get()->toArray();
    }

    /**
     * Create expiry alert
     */
    protected function createExpiryAlert(MedicineStock $stock): void
    {
        MedicineAlert::create([
            'medicine_id' => $stock->medicine_id,
            'medicine_stock_id' => $stock->id,
            'created_by' => 1, // System user
            'alert_type' => 'expiring_soon',
            'alert_title' => "Medicine Expiring Soon: {$stock->medicine->name}",
            'alert_message' => "Batch {$stock->batch_number} expires in {$stock->days_until_expiry} days ({$stock->expiry_date->format('Y-m-d')})",
            'priority' => $stock->days_until_expiry <= 7 ? 'critical' : 'high',
            'status' => 'active',
            'alerted_at' => now(),
            'expiry_date' => $stock->expiry_date,
            'days_until_expiry' => $stock->days_until_expiry,
            'current_stock' => $stock->quantity_available,
        ]);

        $stock->update([
            'expiry_alert_sent' => true,
            'expiry_alert_sent_at' => now(),
        ]);
    }

    /**
     * Get pharmacy dashboard data
     */
    public function getDashboardData(): array
    {
        return [
            'total_medicines' => Medicine::active()->count(),
            'total_stock_value' => Medicine::active()->sum(DB::raw('total_stock * purchase_price')),
            'low_stock_count' => Medicine::active()->lowStock()->count(),
            'out_of_stock_count' => Medicine::active()->outOfStock()->count(),
            'expiring_soon_count' => MedicineStock::where('expiry_date', '<=', now()->addDays(30))
                ->where('is_expired', false)
                ->count(),
            'expired_count' => MedicineStock::where('is_expired', true)->count(),
            'pending_dispensings' => PharmacyDispensing::where('status', 'pending')->count(),
            'today_dispensings' => PharmacyDispensing::whereDate('dispense_date', today())->count(),
            'today_revenue' => PharmacyDispensing::whereDate('dispense_date', today())
                ->where('status', 'completed')
                ->sum('total_amount'),
            'active_alerts' => MedicineAlert::where('status', 'active')->count(),
            'low_stock_medicines' => $this->getLowStockMedicines(),
            'expiring_soon' => $this->getExpiringSoon(30),
        ];
    }
}
