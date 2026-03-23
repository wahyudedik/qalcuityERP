<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Enforce tenant isolation — pastikan user hanya bisa akses data tenant sendiri.
 *
 * Cara kerja:
 * - Inject tenant_id ke semua query via Model::creating/updating observer
 * - Validasi route model binding yang punya tenant_id field
 * - Blokir akses jika tenant_id tidak cocok
 */
class EnforceTenantIsolation
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        // Super admin & guest tidak perlu isolasi
        if (!$user || $user->isSuperAdmin() || !$user->tenant_id) {
            return $next($request);
        }

        $tenantId = $user->tenant_id;

        // Daftar model yang perlu dicek tenant_id-nya dari route binding
        $tenantModels = [
            \App\Models\Product::class,
            \App\Models\Warehouse::class,
            \App\Models\SalesOrder::class,
            \App\Models\PurchaseOrder::class,
            \App\Models\Invoice::class,
            \App\Models\Employee::class,
            \App\Models\Customer::class,
            \App\Models\Supplier::class,
            \App\Models\Asset::class,
            \App\Models\Budget::class,
            \App\Models\Project::class,
            \App\Models\CrmLead::class,
            \App\Models\EcommerceChannel::class,
            \App\Models\ChatSession::class,
            \App\Models\ApprovalRequest::class,
            \App\Models\ApprovalWorkflow::class,
            \App\Models\BankAccount::class,
            \App\Models\BankStatement::class,
            \App\Models\Document::class,
        ];

        // Cek semua route parameters yang merupakan Eloquent model
        foreach ($request->route()->parameters() as $param) {
            if (!is_object($param)) continue;

            $modelClass = get_class($param);
            if (!in_array($modelClass, $tenantModels)) continue;

            // Model punya tenant_id tapi tidak cocok → 403
            if (isset($param->tenant_id) && (int) $param->tenant_id !== (int) $tenantId) {
                abort(403, 'Akses ditolak: data bukan milik tenant Anda.');
            }
        }

        return $next($request);
    }
}
