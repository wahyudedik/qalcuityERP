<?php

namespace App\Services;

use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Validation\ValidationException;

/**
 * DateRangeValidator - Centralized date range validation for reports
 * 
 * BUG-REP-001 FIX: Ensures consistent date validation across all report endpoints
 */
class DateRangeValidator
{
    /**
     * Validate date range for reports
     * 
     * @param Request $request
     * @throws ValidationException
     * @return void
     */
    public function validate(Request $request): void
    {
        $rules = [
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
        ];

        $messages = [
            'start_date.required' => 'Tanggal awal wajib diisi.',
            'start_date.date' => 'Format tanggal awal tidak valid. Gunakan: YYYY-MM-DD',
            'end_date.required' => 'Tanggal akhir wajib diisi.',
            'end_date.date' => 'Format tanggal akhir tidak valid. Gunakan: YYYY-MM-DD',
            'end_date.after_or_equal' => 'Tanggal akhir harus sama dengan atau setelah tanggal awal.',
        ];

        $validator = validator($request->all(), $rules, $messages);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        // Additional business logic validations
        $this->validateDateRangeNotTooLarge($request->start_date, $request->end_date);
        $this->validateDatesNotInFuture($request->start_date, $request->end_date);
    }

    /**
     * Validate date range doesn't exceed 5 years
     */
    protected function validateDateRangeNotTooLarge(string $startDate, string $endDate): void
    {
        $start = Carbon::parse($startDate);
        $end = Carbon::parse($endDate);
        $monthsDiff = $start->diffInMonths($end);

        if ($monthsDiff > 60) {
            throw ValidationException::withMessages([
                'date_range' => [
                    'Rentang tanggal terlalu besar (' . $monthsDiff . ' bulan). Maksimal 5 tahun (60 bulan).'
                ],
            ]);
        }
    }

    /**
     * Validate dates are not in the future (for historical reports)
     */
    protected function validateDatesNotInFuture(string $startDate, string $endDate): void
    {
        $today = Carbon::today();
        $start = Carbon::parse($startDate);
        $end = Carbon::parse($endDate);

        if ($start->gt($today) || $end->gt($today)) {
            throw ValidationException::withMessages([
                'start_date' => ['Tanggal tidak boleh di masa depan.'],
            ]);
        }
    }

    /**
     * Parse and normalize date range
     * 
     * @param Request $request
     * @return array{start: Carbon, end: Carbon}
     */
    public function parseDateRange(Request $request): array
    {
        $this->validate($request);

        return [
            'start' => Carbon::parse($request->start_date)->startOfDay(),
            'end' => Carbon::parse($request->end_date)->endOfDay(),
        ];
    }

    /**
     * Get formatted date range string
     * 
     * @param Request $request
     * @return string
     */
    public function getFormattedRange(Request $request): string
    {
        $start = Carbon::parse($request->start_date);
        $end = Carbon::parse($request->end_date);

        return $start->format('d M Y') . ' s/d ' . $end->format('d M Y');
    }
}
