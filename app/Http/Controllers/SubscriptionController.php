<?php

namespace App\Http\Controllers;

use App\Models\SubscriptionPlan;
use Illuminate\View\View;

class SubscriptionController extends Controller
{
    public function index(): View
    {
        $user   = auth()->user();
        $tenant = $user->tenant;
        $plans  = SubscriptionPlan::where('is_active', true)->orderBy('sort_order')->get();

        return view('subscription.index', compact('tenant', 'plans'));
    }
}
