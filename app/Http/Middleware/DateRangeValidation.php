<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Carbon\Carbon;

/**
 * DateRangeValidation - Ensure start_date <= end_date for all report requests
 * 
 * BUG-REP-001 FIX: Centralized date range validation
 */
class DateRangeValidation
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Check if request has date range parameters
        if ($request->filled('start_date') && $request->filled('end_date')) {
            $startDate = $request->input('start_date');
            $endDate = $request->input('end_date');

            // Validate date format
            if (!$this->isValidDate($startDate) || !$this->isValidDate($endDate)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Format tanggal tidak valid. Gunakan format: YYYY-MM-DD',
                    'errors' => [
                        'start_date' => !$this->isValidDate($startDate) ? ['Format tanggal tidak valid'] : [],
                        'end_date' => !$this->isValidDate($endDate) ? ['Format tanggal tidak valid'] : [],
                    ],
                ], 422);
            }

            // BUG-REP-001 FIX: Validate start_date <= end_date
            if (Carbon::parse($endDate)->lt(Carbon::parse($startDate))) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tanggal akhir harus sama dengan atau setelah tanggal awal',
                    'errors' => [
                        'end_date' => [
                            'Tanggal akhir (' . Carbon::parse($endDate)->format('d M Y') . ') tidak boleh lebih kecil dari tanggal awal (' . Carbon::parse($startDate)->format('d M Y') . ')'
                        ],
                    ],
                ], 422);
            }

            // Validate date range not too large (max 5 years)
            $start = Carbon::parse($startDate);
            $end = Carbon::parse($endDate);
            $monthsDiff = $start->diffInMonths($end);

            if ($monthsDiff > 60) {
                return response()->json([
                    'success' => false,
                    'message' => 'Rentang tanggal terlalu besar. Maksimal 5 tahun (60 bulan).',
                    'errors' => [
                        'date_range' => [
                            'Rentang tanggal: ' . $monthsDiff . ' bulan. Maksimal: 60 bulan.'
                        ],
                    ],
                ], 422);
            }

            // Validate dates are not in the future (for historical reports)
            $today = Carbon::today();
            if ($start->gt($today) || $end->gt($today)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tanggal tidak boleh di masa depan',
                    'errors' => [
                        'start_date' => $start->gt($today) ? ['Tanggal tidak boleh melebihi hari ini'] : [],
                        'end_date' => $end->gt($today) ? ['Tanggal tidak boleh melebihi hari ini'] : [],
                    ],
                ], 422);
            }
        }

        return $next($request);
    }

    /**
     * Check if date string is valid
     */
    protected function isValidDate(string $date): bool
    {
        $d = Carbon::parse($date);
        return $d && $d->format('Y-m-d') === $date || $d->format('Y-m-d') === date('Y-m-d', strtotime($date));
    }
}
