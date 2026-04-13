<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

/**
 * Temporary route to help clear oversized cookies
 * 
 * Usage: Visit http://qalcuityerp.test/clear-cookies-temp
 * This will clear all cookies and redirect to login
 * 
 * IMPORTANT: Delete this file after using it!
 */

Route::get('/clear-cookies-temp', function (Request $request) {
    // Get all cookies from the request
    $cookies = $request->cookies->all();

    $cleared = [];

    // Expire all cookies by setting them with past expiration
    foreach ($cookies as $name => $value) {
        Cookie::queue(Cookie::forget($name));
        $cleared[] = $name;
    }

    // Also clear common Laravel cookie names
    $commonCookies = [
        'laravel_session',
        'XSRF-TOKEN',
        'qalcuity-erp-session',
    ];

    foreach ($commonCookies as $cookieName) {
        Cookie::queue(Cookie::forget($cookieName));
        if (!in_array($cookieName, $cleared)) {
            $cleared[] = $cookieName;
        }
    }

    // Clear the session
    session()->flush();

    // Check session table size
    $sessionCount = DB::table('sessions')->count();
    $largeSessions = DB::table('sessions')
        ->select('id', DB::raw('LENGTH(payload) as payload_size'))
        ->whereRaw('LENGTH(payload) > 4096')
        ->get();

    return response()->json([
        'message' => 'All cookies have been cleared. Please visit /cookie-diagnostic.html to verify.',
        'cleared_cookies' => $cleared,
        'count' => count($cleared),
        'session_driver' => config('session.driver'),
        'sessions_in_db' => $sessionCount,
        'large_sessions' => $largeSessions->count(),
        'next_step' => 'Visit http://qalcuityerp.test/cookie-diagnostic.html',
    ])->withCookie(Cookie::forget('laravel_session'))
        ->withCookie(Cookie::forget('XSRF-TOKEN'));
})->name('clear.cookies.temp');

