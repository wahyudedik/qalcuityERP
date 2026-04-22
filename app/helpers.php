<?php

use App\Helpers\NumberHelper;

if (!function_exists('format_number_id')) {
    /**
     * Format angka dengan format Indonesia (titik ribuan, koma desimal)
     * 
     * @param float|int|string|null $number
     * @param int $decimals
     * @param bool $showZero
     * @return string
     */
    function format_number_id($number, int $decimals = 0, bool $showZero = true): string
    {
        return NumberHelper::format($number, $decimals, $showZero);
    }
}

if (!function_exists('format_currency_id')) {
    /**
     * Format mata uang Rupiah
     * 
     * @param float|int|string|null $amount
     * @param bool $showSymbol
     * @return string
     */
    function format_currency_id($amount, bool $showSymbol = true): string
    {
        return NumberHelper::currency($amount, $showSymbol);
    }
}

if (!function_exists('format_percentage_id')) {
    /**
     * Format persentase
     * 
     * @param float|int|string|null $number
     * @param int $decimals
     * @return string
     */
    function format_percentage_id($number, int $decimals = 2): string
    {
        return NumberHelper::percentage($number, $decimals);
    }
}

if (!function_exists('abbreviate_number_id')) {
    /**
     * Format angka dengan suffix (Rb, Jt, M)
     * 
     * @param float|int|string|null $number
     * @param int $decimals
     * @return string
     */
    function abbreviate_number_id($number, int $decimals = 1): string
    {
        return NumberHelper::abbreviate($number, $decimals);
    }
}

if (!function_exists('get_tenant_subscription_status')) {
    /**
     * Get tenant subscription status from cached object
     * 
     * @param object $tenant
     * @return string
     */
    function get_tenant_subscription_status($tenant): string
    {
        if (!$tenant->is_active) {
            return 'nonaktif';
        }
        
        // Check trial expired
        if ($tenant->plan === 'trial' 
            && isset($tenant->trial_ends_at) 
            && $tenant->trial_ends_at 
            && $tenant->trial_ends_at->isPast()) {
            return 'trial_expired';
        }
        
        // Check plan expired
        if ($tenant->plan !== 'trial' 
            && isset($tenant->plan_expires_at) 
            && $tenant->plan_expires_at 
            && $tenant->plan_expires_at->isPast()) {
            return 'expired';
        }
        
        if ($tenant->plan === 'trial') {
            return 'trial';
        }
        
        return 'active';
    }
}
