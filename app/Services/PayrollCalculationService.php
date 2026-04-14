<?php

namespace App\Services;

/**
 * PayrollCalculationService — Evaluasi formula komponen gaji.
 *
 * Bug 1.17 Fix: Null-safe handling sebelum evaluasi formula.
 * Nilai null pada komponen opsional diganti dengan 0.0 menggunakan
 * null coalescing, sehingga kalkulasi tidak error.
 */
class PayrollCalculationService
{
    /**
     * Evaluasi formula payroll dengan null-safe component handling.
     *
     * Bug_Condition: module = 'payroll' AND nullComponentUnhandled(input)
     * Expected_Behavior: nilai null diganti 0, formula dievaluasi tanpa error
     *
     * @param  string $formula     Formula string, misal "basic_salary + allowance"
     * @param  array  $components  Map nama komponen ke nilai (bisa null)
     * @return float               Hasil evaluasi formula
     *
     * @throws \DomainException jika formula tidak valid
     */
    public function evaluateFormula(string $formula, array $components): float
    {
        // Null-safe: ganti semua nilai null dengan 0.0 sebelum evaluasi
        $safeComponents = array_map(fn($v) => $v ?? 0.0, $components);

        try {
            return $this->evaluate($formula, $safeComponents);
        } catch (\Throwable $e) {
            throw new \DomainException(
                "Formula payroll tidak valid: '{$formula}'. Error: {$e->getMessage()}"
            );
        }
    }

    /**
     * Evaluasi ekspresi aritmatika sederhana dengan substitusi variabel.
     *
     * @param  string $formula
     * @param  array  $components  Map nama variabel ke nilai float
     * @return float
     *
     * @throws \RuntimeException jika ekspresi tidak bisa dievaluasi
     */
    protected function evaluate(string $formula, array $components): float
    {
        // Substitusi variabel dalam formula dengan nilai numerik
        $expression = $formula;
        foreach ($components as $name => $value) {
            $expression = str_replace($name, (string)(float)$value, $expression);
        }

        // Validasi: hanya izinkan karakter numerik dan operator aritmatika
        if (!preg_match('/^[\d\s\+\-\*\/\.\(\)]+$/', $expression)) {
            throw new \RuntimeException(
                "Ekspresi mengandung karakter tidak valid setelah substitusi: '{$expression}'"
            );
        }

        // Evaluasi ekspresi aritmatika
        $result = @eval("return (float)({$expression});");

        if ($result === false || is_nan($result) || is_infinite($result)) {
            throw new \RuntimeException("Hasil evaluasi tidak valid untuk ekspresi: '{$expression}'");
        }

        return (float)$result;
    }
}
