<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\Auth;

abstract class Controller extends BaseController
{
    use AuthorizesRequests, ValidatesRequests;

    /**
     * Get authenticated user with proper type hint for IDE
     */
    protected function authenticatedUser(): User
    {
        return Auth::user();
    }

    /**
     * Get authenticated user ID
     */
    protected function authenticatedUserId(): int
    {
        return Auth::id();
    }

    /**
     * Get authenticated user tenant ID
     */
    protected function tenantId(): int
    {
        return Auth::user()->tenant_id;
    }
}
