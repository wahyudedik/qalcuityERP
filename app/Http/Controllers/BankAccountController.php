<?php

namespace App\Http\Controllers;

use App\Models\BankAccount;
use App\Models\ActivityLog;
use Illuminate\Http\Request;

class BankAccountController extends Controller
{
    // tenantId() inherited from parent Controller

    public function index(Request $request)
    {
        $tid   = $this->tenantId();
        $query = BankAccount::where('tenant_id', $tid)
            ->withCount('statements')
            ->withSum('statements', 'amount');

        if ($request->search) {
            $s = $request->search;
            $query->where(fn($q) => $q
                ->where('bank_name', 'like', "%$s%")
                ->orWhere('account_number', 'like', "%$s%")
                ->orWhere('account_name', 'like', "%$s%")
            );
        }
        if ($request->status === 'active') {
            $query->where('is_active', true);
        } elseif ($request->status === 'inactive') {
            $query->where('is_active', false);
        }

        $accounts       = $query->orderBy('bank_name')->paginate(20)->withQueryString();
        $totalAccounts  = BankAccount::where('tenant_id', $tid)->count();
        $activeAccounts = BankAccount::where('tenant_id', $tid)->where('is_active', true)->count();
        $totalBalance   = BankAccount::where('tenant_id', $tid)->where('is_active', true)->sum('balance');

        return view('bank-accounts.index', compact('accounts', 'totalAccounts', 'activeAccounts', 'totalBalance'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'bank_name'      => 'required|string|max:100',
            'account_number' => 'required|string|max:50',
            'account_name'   => 'required|string|max:100',
            'balance'        => 'nullable|numeric|min:0',
            'currency'       => 'nullable|string|max:10',
            'description'    => 'nullable|string|max:255',
        ]);

        $tid = $this->tenantId();

        if (BankAccount::where('tenant_id', $tid)->where('account_number', $data['account_number'])->exists()) {
            return back()->withErrors(['account_number' => 'Nomor rekening ini sudah terdaftar.'])->withInput();
        }

        $account = BankAccount::create([
            'tenant_id'      => $tid,
            'bank_name'      => $data['bank_name'],
            'account_number' => $data['account_number'],
            'account_name'   => $data['account_name'],
            'balance'        => $data['balance'] ?? 0,
            'is_active'      => true,
        ]);

        ActivityLog::record('bank_account_created', "Rekening baru: {$account->bank_name} - {$account->account_number}", $account, [], $account->toArray());

        return back()->with('success', "Rekening {$account->bank_name} ({$account->account_number}) berhasil ditambahkan.");
    }

    public function update(Request $request, BankAccount $bankAccount)
    {
        abort_unless($bankAccount->tenant_id === $this->tenantId(), 403);

        $data = $request->validate([
            'bank_name'    => 'required|string|max:100',
            'account_name' => 'required|string|max:100',
            'balance'      => 'nullable|numeric|min:0',
            'description'  => 'nullable|string|max:255',
        ]);

        $old = $bankAccount->getOriginal();
        $bankAccount->update($data);
        ActivityLog::record('bank_account_updated', "Rekening diperbarui: {$bankAccount->bank_name} - {$bankAccount->account_number}", $bankAccount, $old, $bankAccount->fresh()->toArray());

        return back()->with('success', "Rekening {$bankAccount->bank_name} berhasil diperbarui.");
    }

    public function toggleActive(BankAccount $bankAccount)
    {
        abort_unless($bankAccount->tenant_id === $this->tenantId(), 403);
        $bankAccount->update(['is_active' => !$bankAccount->is_active]);
        $status = $bankAccount->is_active ? 'diaktifkan' : 'dinonaktifkan';
        return back()->with('success', "Rekening {$bankAccount->bank_name} berhasil {$status}.");
    }

    public function destroy(BankAccount $bankAccount)
    {
        abort_unless($bankAccount->tenant_id === $this->tenantId(), 403);

        if ($bankAccount->statements()->exists()) {
            $bankAccount->update(['is_active' => false]);
            ActivityLog::record('bank_account_deactivated', "Rekening dinonaktifkan (ada mutasi): {$bankAccount->bank_name} - {$bankAccount->account_number}", $bankAccount);
            return back()->with('success', 'Rekening dinonaktifkan karena memiliki riwayat mutasi.');
        }

        ActivityLog::record('bank_account_deleted', "Rekening dihapus: {$bankAccount->bank_name} - {$bankAccount->account_number}", $bankAccount, $bankAccount->toArray());
        $bankAccount->delete();

        return back()->with('success', 'Rekening berhasil dihapus.');
    }
}
