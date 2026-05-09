<?php

namespace App\Services;

use App\Models\TaxRate;

/**
 * TaxCalculationService - Comprehensive tax calculation with edge case handling
 *
 * BUG-FIN-004 FIX: Handle multiple tax types, withholding taxes, and tax-inclusive pricing
 *
 * Tax Calculation Rules (Indonesian Tax Law):
 * 1. PPN (VAT) is calculated on DPP (Dasar Pengenaan Pajak) = Subtotal - Discount
 * 2. PPh 23 is withholding tax (2% of gross amount BEFORE PPN)
 * 3. PPh Final is calculated on gross amount
 * 4. Tax-inclusive pricing: PPN is already included in the price
 * 5. Multiple taxes can apply simultaneously (e.g., PPN 11% + PPh 23 2%)
 *
 * Calculation Order:
 * Subtotal
 *   - Discount
 *   = DPP (Tax Base)
 *     + PPN (11% of DPP)
 *     + Other taxes
 *     - Withholding taxes (PPh 23, PPh 21)
 *     = Total Payable
 */
class TaxCalculationService
{
    /**
     * BUG-FIN-004 FIX: Calculate all applicable taxes for an invoice
     *
     * @param  float  $subtotal  Subtotal before any deductions
     * @param  float  $discount  Discount amount
     * @param  array  $taxRateIds  Array of tax rate IDs to apply
     * @param  bool  $taxInclusive  Whether prices are tax-inclusive
     * @return array Complete tax breakdown
     */
    public function calculateAllTaxes(
        float $subtotal,
        float $discount = 0,
        array $taxRateIds = [],
        bool $taxInclusive = false
    ): array {
        // Calculate DPP (Dasar Pengenaan Pajak)
        $dpp = max(0, $subtotal - $discount);

        $result = [
            'subtotal' => $subtotal,
            'discount' => $discount,
            'dpp' => $dpp,
            'taxes' => [],
            'withholding_taxes' => [],
            'total_tax' => 0,
            'total_withholding' => 0,
            'grand_total' => 0,
            'tax_inclusive' => $taxInclusive,
        ];

        // If no tax rates specified, just return DPP as total
        if (empty($taxRateIds)) {
            $result['grand_total'] = $this->roundAccounting($dpp);

            return $result;
        }

        // Load all tax rates
        $taxRates = TaxRate::whereIn('id', $taxRateIds)
            ->where('is_active', true)
            ->get();

        // Separate regular taxes and withholding taxes
        $regularTaxes = $taxRates->filter(fn ($rate) => ! $rate->is_withholding);
        $withholdingTaxes = $taxRates->filter(fn ($rate) => $rate->is_withholding);

        // Calculate regular taxes (PPN, etc.)
        foreach ($regularTaxes as $taxRate) {
            $taxAmount = $this->calculateTaxAmount($dpp, $taxRate, $taxInclusive);

            $result['taxes'][] = [
                'tax_rate_id' => $taxRate->id,
                'tax_type' => $taxRate->tax_type,
                'tax_name' => $taxRate->getTypeLabel(),
                'rate' => $taxRate->rate,
                'base_amount' => $dpp,
                'tax_amount' => $taxAmount,
                'is_withholding' => false,
            ];

            $result['total_tax'] += $taxAmount;
        }

        // Calculate withholding taxes (PPh 23, PPh 21, etc.)
        // BUG-FIN-004 FIX: Withholding tax is calculated on gross amount (before PPN)
        foreach ($withholdingTaxes as $taxRate) {
            // PPh is calculated on gross amount (subtotal - discount), NOT including PPN
            $withholdingAmount = $this->roundAccounting($dpp * ($taxRate->rate / 100));

            $result['withholding_taxes'][] = [
                'tax_rate_id' => $taxRate->id,
                'tax_type' => $taxRate->tax_type,
                'tax_name' => $taxRate->getTypeLabel(),
                'rate' => $taxRate->rate,
                'base_amount' => $dpp,
                'tax_amount' => $withholdingAmount,
                'is_withholding' => true,
            ];

            $result['total_withholding'] += $withholdingAmount;
        }

        // Calculate grand total
        if ($taxInclusive) {
            // Tax-inclusive: total is already DPP (taxes are inside)
            $result['grand_total'] = $this->roundAccounting($dpp);
            $result['taxes_included'] = true;
        } else {
            // Tax-exclusive: add taxes, subtract withholding
            $result['grand_total'] = $this->roundAccounting(
                $dpp + $result['total_tax'] - $result['total_withholding']
            );
            $result['taxes_included'] = false;
        }

        return $result;
    }

    /**
     * BUG-FIN-004 FIX: Calculate tax amount with proper edge case handling
     *
     * @param  float  $baseAmount  Tax base amount (DPP)
     * @param  TaxRate  $taxRate  Tax rate model
     * @param  bool  $taxInclusive  Whether price is tax-inclusive
     * @return float Tax amount
     */
    public function calculateTaxAmount(float $baseAmount, TaxRate $taxRate, bool $taxInclusive = false): float
    {
        if ($taxRate->rate <= 0) {
            return 0.0;
        }

        if ($taxInclusive) {
            // Tax-inclusive: extract tax from total
            // Formula: Tax = Total - (Total / (1 + rate/100))
            $taxAmount = $baseAmount - ($baseAmount / (1 + $taxRate->rate / 100));
        } else {
            // Tax-exclusive: calculate tax on base
            $taxAmount = $baseAmount * ($taxRate->rate / 100);
        }

        return $this->roundAccounting($taxAmount);
    }

    /**
     * BUG-FIN-004 FIX: Calculate PPN with tax-inclusive support
     *
     * @param  float  $amount  Amount (DPP or total depending on $inclusive)
     * @param  int  $tenantId  Tenant ID
     * @param  bool  $inclusive  Whether amount is tax-inclusive
     * @return array PPN calculation result
     */
    public function calculatePpn(float $amount, int $tenantId, bool $inclusive = false): array
    {
        $taxRate = TaxRate::where('tenant_id', $tenantId)
            ->where('tax_type', 'ppn')
            ->where('is_active', true)
            ->first();

        if (! $taxRate) {
            return [
                'dpp' => $amount,
                'ppn' => 0,
                'total' => $amount,
                'rate' => 0,
                'inclusive' => $inclusive,
            ];
        }

        if ($inclusive) {
            // Extract PPN from tax-inclusive amount
            $dpp = $amount / (1 + $taxRate->rate / 100);
            $ppn = $amount - $dpp;
        } else {
            // Calculate PPN on DPP
            $dpp = $amount;
            $ppn = $dpp * ($taxRate->rate / 100);
        }

        return [
            'dpp' => $this->roundAccounting($dpp),
            'ppn' => $this->roundAccounting($ppn),
            'total' => $this->roundAccounting($dpp + $ppn),
            'rate' => $taxRate->rate,
            'inclusive' => $inclusive,
        ];
    }

    /**
     * BUG-FIN-004 FIX: Calculate PPh 23 withholding tax
     *
     * PPh 23 is calculated on gross amount BEFORE PPN
     * Standard rate: 2% for services, 15% for dividends
     *
     * @param  float  $grossAmount  Gross amount (before PPN)
     * @param  int  $tenantId  Tenant ID
     * @param  string  $taxType  Tax type (pph23, pph21, pph4ayat2)
     * @return array PPh calculation result
     */
    public function calculatePph(float $grossAmount, int $tenantId, string $taxType = 'pph23'): array
    {
        $taxRate = TaxRate::where('tenant_id', $tenantId)
            ->where('tax_type', $taxType)
            ->where('is_active', true)
            ->where('is_withholding', true)
            ->first();

        if (! $taxRate) {
            return [
                'tax_type' => $taxType,
                'tax_name' => strtoupper($taxType),
                'base_amount' => $grossAmount,
                'tax_amount' => 0,
                'rate' => 0,
                'is_withholding' => true,
            ];
        }

        // BUG-FIN-004 FIX: PPh is ALWAYS calculated on gross amount (before PPN)
        $taxAmount = $this->roundAccounting($grossAmount * ($taxRate->rate / 100));

        return [
            'tax_type' => $taxRate->tax_type,
            'tax_name' => $taxRate->getTypeLabel(),
            'base_amount' => $grossAmount,
            'tax_amount' => $taxAmount,
            'rate' => $taxRate->rate,
            'is_withholding' => true,
        ];
    }

    /**
     * BUG-FIN-004 FIX: Calculate combined PPN + PPh 23
     *
     * This is the most common scenario in Indonesia:
     * - PPN 11% added to invoice
     * - PPh 23 2% withheld from payment
     *
     * Example:
     * Subtotal: Rp 1,000,000
     * PPN (11%): Rp 110,000 (added)
     * PPh 23 (2%): Rp 20,000 (withheld)
     * Total Payable: Rp 1,090,000 (1,000,000 + 110,000 - 20,000)
     *
     * @param  float  $subtotal  Subtotal amount
     * @param  float  $discount  Discount amount
     * @param  int  $tenantId  Tenant ID
     * @return array Combined tax calculation
     */
    public function calculatePpnPlusPph23(float $subtotal, float $discount, int $tenantId): array
    {
        $dpp = max(0, $subtotal - $discount);

        // Calculate PPN
        $ppnResult = $this->calculatePpn($dpp, $tenantId, false);

        // Calculate PPh 23 (on DPP, BEFORE PPN)
        $pphResult = $this->calculatePph($dpp, $tenantId, 'pph23');

        // Calculate total payable
        $totalPayable = $dpp + $ppnResult['ppn'] - $pphResult['tax_amount'];

        return [
            'dpp' => $this->roundAccounting($dpp),
            'ppn' => $ppnResult,
            'pph23' => $pphResult,
            'total_tax_additions' => $ppnResult['ppn'],
            'total_tax_deductions' => $pphResult['tax_amount'],
            'grand_total' => $this->roundAccounting($totalPayable),
            'explanation' => "DPP: {$this->formatRupiah($dpp)} + ".
                "PPN {$ppnResult['rate']}%: {$this->formatRupiah($ppnResult['ppn'])} - ".
                "PPh 23 {$pphResult['rate']}%: {$this->formatRupiah($pphResult['tax_amount'])} = ".
                "Total: {$this->formatRupiah($totalPayable)}",
        ];
    }

    /**
     * BUG-FIN-004 FIX: Accounting-compliant rounding (HALF_UP)
     *
     * @param  float  $value  Value to round
     * @param  int  $precision  Decimal precision (default: 2)
     * @return float Rounded value
     */
    public function roundAccounting(float $value, int $precision = 2): float
    {
        $multiplier = pow(10, $precision);

        return floor($value * $multiplier + 0.5) / $multiplier;
    }

    /**
     * Format amount to Rupiah
     */
    private function formatRupiah(float $amount): string
    {
        return 'Rp '.number_format($amount, 0, ',', '.');
    }
}
