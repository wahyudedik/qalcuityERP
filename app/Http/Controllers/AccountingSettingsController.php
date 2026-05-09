<?php

namespace App\Http\Controllers;

use App\Models\BankAccount;
use App\Models\ChartOfAccount;
use App\Models\Currency;
use App\Models\TaxRate;
use Illuminate\Http\Request;

class AccountingSettingsController extends Controller
{
    private function tid(): int
    {
        return auth()->user()->tenant_id;
    }

    public function index(Request $request)
    {
        $tab = $request->tab ?? 'coa';
        $tid = $this->tid();

        // COA
        $accounts = ChartOfAccount::where('tenant_id', $tid)
            ->with('parent')
            ->when($request->filled('search'), fn ($q) => $q->where(fn ($q2) => $q2->where('code', 'like', '%'.$request->search.'%')
                ->orWhere('name', 'like', '%'.$request->search.'%')
            ))
            ->when($request->filled('type'), fn ($q) => $q->where('type', $request->type))
            ->orderBy('code')
            ->get();

        $headers = ChartOfAccount::where('tenant_id', $tid)
            ->where('is_header', true)->orderBy('code')->get();

        // Bank Accounts
        $bankAccounts = BankAccount::where('tenant_id', $tid)
            ->orderBy('bank_name')->get();

        // Tax Rates
        $taxes = TaxRate::where('tenant_id', $tid)->orderBy('name')->get();

        // Currencies
        $currencies = Currency::where('tenant_id', $tid)->orderBy('code')->get();

        return view('settings.accounting', compact(
            'tab', 'accounts', 'headers', 'bankAccounts', 'taxes', 'currencies'
        ));
    }

    // ── Currency CRUD ─────────────────────────────────────────────

    public function storeCurrency(Request $request)
    {
        $data = $request->validate([
            'code' => 'required|string|max:10|uppercase',
            'name' => 'required|string|max:100',
            'symbol' => 'required|string|max:10',
            'rate_to_idr' => 'required|numeric|min:0',
            'is_active' => 'boolean',
        ]);

        $tid = $this->tid();

        if (Currency::where('tenant_id', $tid)->where('code', strtoupper($data['code']))->exists()) {
            return back()->withErrors(['code' => 'Kode mata uang sudah ada.'])->withInput();
        }

        Currency::create([
            'tenant_id' => $tid,
            'code' => strtoupper($data['code']),
            'name' => $data['name'],
            'symbol' => $data['symbol'],
            'rate_to_idr' => $data['rate_to_idr'],
            'is_active' => $request->boolean('is_active', true),
            'is_base' => false,
            'rate_updated_at' => now(),
        ]);

        return back()->with('success', "Mata uang {$data['code']} berhasil ditambahkan.")->withFragment('tab-currency');
    }

    public function updateCurrency(Request $request, Currency $currency)
    {
        abort_unless($currency->tenant_id === $this->tid(), 403);

        $data = $request->validate([
            'name' => 'required|string|max:100',
            'symbol' => 'required|string|max:10',
            'rate_to_idr' => 'required|numeric|min:0',
            'is_active' => 'boolean',
        ]);

        $currency->update([
            'name' => $data['name'],
            'symbol' => $data['symbol'],
            'rate_to_idr' => $data['rate_to_idr'],
            'is_active' => $request->boolean('is_active'),
            'rate_updated_at' => now(),
        ]);

        return back()->with('success', "Mata uang {$currency->code} berhasil diperbarui.")->withFragment('tab-currency');
    }

    public function destroyCurrency(Currency $currency)
    {
        abort_unless($currency->tenant_id === $this->tid(), 403);

        if ($currency->is_base) {
            return back()->with('error', 'Mata uang dasar tidak bisa dihapus.');
        }

        $currency->delete();

        return back()->with('success', "Mata uang {$currency->code} berhasil dihapus.")->withFragment('tab-currency');
    }
}
