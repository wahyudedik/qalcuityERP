<?php

namespace App\Services;

/**
 * PayrollCalculationService — Kalkulasi komponen gaji, BPJS, dan PPh 21.
 *
 * Mencakup:
 * - Evaluasi formula komponen gaji (null-safe)
 * - Kalkulasi BPJS Kesehatan dan Ketenagakerjaan sesuai regulasi Indonesia
 * - Kalkulasi PPh 21 dengan tarif progresif (UU HPP 2021)
 * - Kalkulasi gaji bersih (net salary)
 */
class PayrollCalculationService
{
    // ── BPJS Kesehatan ────────────────────────────────────────────
    // Peraturan BPJS Kesehatan: Perpres No. 64 Tahun 2020
    const BPJS_KESEHATAN_EMPLOYER_RATE = 0.04;  // 4% ditanggung perusahaan

    const BPJS_KESEHATAN_EMPLOYEE_RATE = 0.01;  // 1% ditanggung karyawan

    const BPJS_KESEHATAN_MAX_SALARY = 12000000; // Batas atas gaji untuk BPJS Kesehatan

    // ── BPJS Ketenagakerjaan ──────────────────────────────────────
    // Peraturan PP No. 44 Tahun 2015 & PP No. 46 Tahun 2015
    const BPJS_JHT_EMPLOYER_RATE = 0.037; // JHT 3.7% perusahaan

    const BPJS_JHT_EMPLOYEE_RATE = 0.02;  // JHT 2% karyawan

    const BPJS_JP_EMPLOYER_RATE = 0.02;  // JP 2% perusahaan

    const BPJS_JP_EMPLOYEE_RATE = 0.01;  // JP 1% karyawan

    const BPJS_JKK_RATE = 0.0024; // JKK 0.24% (risiko sedang, default)

    const BPJS_JKM_RATE = 0.003;  // JKM 0.3% perusahaan

    const BPJS_JP_MAX_SALARY = 9559600; // Batas atas gaji untuk JP (2024)

    // ── PPh 21 — Tarif Progresif (UU HPP No. 7 Tahun 2021) ───────
    // PTKP TK/0 = Rp 54.000.000/tahun
    const PTKP_TK0 = 54000000;

    const PTKP_K0 = 58500000;  // Kawin tanpa tanggungan

    const PTKP_K1 = 63000000;  // Kawin + 1 tanggungan

    const PTKP_K2 = 67500000;  // Kawin + 2 tanggungan

    const PTKP_K3 = 72000000;  // Kawin + 3 tanggungan

    /**
     * Hitung BPJS Kesehatan (iuran karyawan dan perusahaan).
     *
     * @param  float  $grossSalary  Gaji bruto karyawan
     * @return array{employee: float, employer: float, total: float}
     */
    public function calculateBpjsKesehatan(float $grossSalary): array
    {
        // Batas atas gaji untuk BPJS Kesehatan
        $baseSalary = min($grossSalary, self::BPJS_KESEHATAN_MAX_SALARY);

        $employee = round($baseSalary * self::BPJS_KESEHATAN_EMPLOYEE_RATE);
        $employer = round($baseSalary * self::BPJS_KESEHATAN_EMPLOYER_RATE);

        return [
            'employee' => $employee,
            'employer' => $employer,
            'total' => $employee + $employer,
        ];
    }

    /**
     * Hitung BPJS Ketenagakerjaan (JHT + JP + JKK + JKM).
     *
     * @param  float  $grossSalary  Gaji bruto karyawan
     * @return array{
     *   jht_employee: float, jht_employer: float,
     *   jp_employee: float,  jp_employer: float,
     *   jkk: float, jkm: float,
     *   total_employee: float, total_employer: float, total: float
     * }
     */
    public function calculateBpjsKetenagakerjaan(float $grossSalary): array
    {
        // JHT — tidak ada batas atas
        $jhtEmployee = round($grossSalary * self::BPJS_JHT_EMPLOYEE_RATE);
        $jhtEmployer = round($grossSalary * self::BPJS_JHT_EMPLOYER_RATE);

        // JP — ada batas atas gaji
        $jpBase = min($grossSalary, self::BPJS_JP_MAX_SALARY);
        $jpEmployee = round($jpBase * self::BPJS_JP_EMPLOYEE_RATE);
        $jpEmployer = round($jpBase * self::BPJS_JP_EMPLOYER_RATE);

        // JKK & JKM — ditanggung perusahaan
        $jkk = round($grossSalary * self::BPJS_JKK_RATE);
        $jkm = round($grossSalary * self::BPJS_JKM_RATE);

        $totalEmployee = $jhtEmployee + $jpEmployee;
        $totalEmployer = $jhtEmployer + $jpEmployer + $jkk + $jkm;

        return [
            'jht_employee' => $jhtEmployee,
            'jht_employer' => $jhtEmployer,
            'jp_employee' => $jpEmployee,
            'jp_employer' => $jpEmployer,
            'jkk' => $jkk,
            'jkm' => $jkm,
            'total_employee' => $totalEmployee,
            'total_employer' => $totalEmployer,
            'total' => $totalEmployee + $totalEmployer,
        ];
    }

    /**
     * Hitung total iuran BPJS karyawan (Kesehatan + Ketenagakerjaan).
     *
     * @return float Total potongan BPJS dari gaji karyawan
     */
    public function calculateTotalBpjsEmployee(float $grossSalary): float
    {
        $kesehatan = $this->calculateBpjsKesehatan($grossSalary);
        $ketenagakerjaan = $this->calculateBpjsKetenagakerjaan($grossSalary);

        return $kesehatan['employee'] + $ketenagakerjaan['total_employee'];
    }

    /**
     * Hitung PPh 21 bulanan menggunakan tarif progresif UU HPP 2021.
     *
     * Tarif:
     *   0 – 60 juta/tahun       : 5%
     *   60 – 250 juta/tahun     : 15%
     *   250 – 500 juta/tahun    : 25%
     *   500 juta – 5 miliar/tahun: 30%
     *   > 5 miliar/tahun        : 35%
     *
     * @param  float  $grossSalary  Gaji bruto bulanan
     * @param  float  $bpjsEmployee  Total BPJS yang ditanggung karyawan (pengurang PKP)
     * @param  int  $ptkp  PTKP tahunan (default TK/0 = 54.000.000)
     * @return float PPh 21 bulanan
     */
    public function calculatePph21(float $grossSalary, float $bpjsEmployee = 0, int $ptkp = self::PTKP_TK0): float
    {
        // Penghasilan bruto setahun
        $annualGross = $grossSalary * 12;

        // Biaya jabatan: 5% dari bruto, maks Rp 6.000.000/tahun
        $biayaJabatan = min($annualGross * 0.05, 6000000);

        // Penghasilan neto setahun
        $annualNeto = $annualGross - $biayaJabatan - ($bpjsEmployee * 12);

        // Penghasilan Kena Pajak (PKP)
        $pkp = max(0, $annualNeto - $ptkp);

        // Hitung PPh 21 tahunan dengan tarif progresif
        $annualTax = $this->progressiveTax($pkp);

        // Kembalikan PPh 21 bulanan
        return round($annualTax / 12);
    }

    /**
     * Hitung pajak progresif berdasarkan PKP tahunan (UU HPP 2021).
     *
     * @param  float  $pkp  Penghasilan Kena Pajak tahunan
     * @return float Pajak tahunan
     */
    public function progressiveTax(float $pkp): float
    {
        if ($pkp <= 0) {
            return 0;
        }

        $tax = 0;

        // Bracket 1: 0 – 60 juta @ 5%
        if ($pkp > 0) {
            $taxable = min($pkp, 60_000_000);
            $tax += $taxable * 0.05;
            $pkp -= $taxable;
        }

        // Bracket 2: 60 – 250 juta @ 15%
        if ($pkp > 0) {
            $taxable = min($pkp, 190_000_000);
            $tax += $taxable * 0.15;
            $pkp -= $taxable;
        }

        // Bracket 3: 250 – 500 juta @ 25%
        if ($pkp > 0) {
            $taxable = min($pkp, 250_000_000);
            $tax += $taxable * 0.25;
            $pkp -= $taxable;
        }

        // Bracket 4: 500 juta – 5 miliar @ 30%
        if ($pkp > 0) {
            $taxable = min($pkp, 4_500_000_000);
            $tax += $taxable * 0.30;
            $pkp -= $taxable;
        }

        // Bracket 5: > 5 miliar @ 35%
        if ($pkp > 0) {
            $tax += $pkp * 0.35;
        }

        return $tax;
    }

    /**
     * Hitung gaji bersih (net salary) lengkap.
     *
     * @param  float  $baseSalary  Gaji pokok
     * @param  float  $allowances  Total tunjangan
     * @param  float  $overtimePay  Upah lembur
     * @param  float  $deductAbsent  Potongan absen
     * @param  float  $deductLate  Potongan terlambat
     * @param  float  $deductOther  Potongan lain (komponen deduction)
     * @param  bool  $includeBpjs  Apakah BPJS dihitung
     * @param  int  $ptkp  PTKP tahunan
     * @return array{
     *   gross_salary: float,
     *   bpjs_employee: float,
     *   tax_pph21: float,
     *   net_salary: float,
     *   bpjs_detail: array,
     * }
     */
    public function calculateNetSalary(
        float $baseSalary,
        float $allowances = 0,
        float $overtimePay = 0,
        float $deductAbsent = 0,
        float $deductLate = 0,
        float $deductOther = 0,
        bool $includeBpjs = true,
        int $ptkp = self::PTKP_TK0
    ): array {
        $grossSalary = $baseSalary + $allowances + $overtimePay
            - $deductAbsent - $deductLate - $deductOther;

        $bpjsEmployee = 0;
        $bpjsDetail = [];

        if ($includeBpjs) {
            $kesehatan = $this->calculateBpjsKesehatan($grossSalary);
            $ketenagakerjaan = $this->calculateBpjsKetenagakerjaan($grossSalary);
            $bpjsEmployee = $kesehatan['employee'] + $ketenagakerjaan['total_employee'];
            $bpjsDetail = [
                'kesehatan' => $kesehatan,
                'ketenagakerjaan' => $ketenagakerjaan,
            ];
        }

        $pph21 = $this->calculatePph21($grossSalary, $bpjsEmployee, $ptkp);
        $netSalary = $grossSalary - $bpjsEmployee - $pph21;

        return [
            'gross_salary' => $grossSalary,
            'bpjs_employee' => $bpjsEmployee,
            'tax_pph21' => $pph21,
            'net_salary' => $netSalary,
            'bpjs_detail' => $bpjsDetail,
        ];
    }

    // ── Formula Evaluator ─────────────────────────────────────────

    /**
     * Evaluasi formula payroll dengan null-safe component handling.
     *
     * Bug_Condition: module = 'payroll' AND nullComponentUnhandled(input)
     * Expected_Behavior: nilai null diganti 0, formula dievaluasi tanpa error
     *
     * @param  string  $formula  Formula string, misal "basic_salary + allowance"
     * @param  array  $components  Map nama komponen ke nilai (bisa null)
     * @return float Hasil evaluasi formula
     *
     * @throws \DomainException jika formula tidak valid
     */
    public function evaluateFormula(string $formula, array $components): float
    {
        // Null-safe: ganti semua nilai null dengan 0.0 sebelum evaluasi
        $safeComponents = array_map(fn ($v) => $v ?? 0.0, $components);

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
     * @param  array  $components  Map nama variabel ke nilai float
     *
     * @throws \RuntimeException jika ekspresi tidak bisa dievaluasi
     */
    protected function evaluate(string $formula, array $components): float
    {
        // Substitusi variabel dalam formula dengan nilai numerik
        $expression = $formula;
        foreach ($components as $name => $value) {
            $expression = str_replace($name, (string) (float) $value, $expression);
        }

        // Validasi: hanya izinkan karakter numerik dan operator aritmatika
        if (! preg_match('/^[\d\s\+\-\*\/\.\(\)]+$/', $expression)) {
            throw new \RuntimeException(
                "Ekspresi mengandung karakter tidak valid setelah substitusi: '{$expression}'"
            );
        }

        // Evaluasi ekspresi aritmatika
        $result = @eval("return (float)({$expression});");

        if ($result === false || is_nan($result) || is_infinite($result)) {
            throw new \RuntimeException("Hasil evaluasi tidak valid untuk ekspresi: '{$expression}'");
        }

        return (float) $result;
    }
}
