<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\ExpenseCategory;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ExpenseController extends Controller
{
    private function tid(): int
    {
        return auth()->user()->tenant_id;
    }

    public function index(Request $request)
    {
        $tid   = $this->tid();
        $query = Transaction::with(['category', 'user'])
            ->where('tenant_id', $tid)
            ->where('type', 'expense');

        if ($request->filled('category_id')) {
            $query->where('expense_category_id', $request->category_id);
        }
        if ($request->filled('date_from')) {
            $query->whereDate('date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('date', '<=', $request->date_to);
        }
        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(fn($q) => $q->where('description', 'like', "%$s%")
                ->orWhere('number', 'like', "%$s%"));
        }

        $expenses   = $query->latest('date')->paginate(20)->withQueryString();
        $categories = ExpenseCategory::where('tenant_id', $tid)->where('is_active', true)->orderBy('name')->get();

        // Stats
        $thisMonth = Transaction::where('tenant_id', $tid)->where('type', 'expense')
            ->whereMonth('date', now()->month)->whereYear('date', now()->year)->sum('amount');
        $lastMonth = Transaction::where('tenant_id', $tid)->where('type', 'expense')
            ->whereMonth('date', now()->subMonth()->month)->whereYear('date', now()->subMonth()->year)->sum('amount');

        // Chart: 6 bulan terakhir per kategori
        $chartData = [];
        for ($i = 5; $i >= 0; $i--) {
            $d = now()->subMonths($i);
            $chartData[] = [
                'month'  => $d->format('M Y'),
                'amount' => Transaction::where('tenant_id', $tid)->where('type', 'expense')
                    ->whereMonth('date', $d->month)->whereYear('date', $d->year)->sum('amount'),
            ];
        }

        // Top categories this month
        $topCategories = Transaction::where('tenant_id', $tid)->where('type', 'expense')
            ->whereMonth('date', now()->month)->whereYear('date', now()->year)
            ->selectRaw('expense_category_id, SUM(amount) as total')
            ->groupBy('expense_category_id')
            ->with('category')
            ->orderByDesc('total')
            ->limit(5)
            ->get();

        return view('expenses.index', compact(
            'expenses', 'categories', 'thisMonth', 'lastMonth', 'chartData', 'topCategories'
        ));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'expense_category_id' => 'required|exists:expense_categories,id',
            'date'                => 'required|date',
            'amount'              => 'required|numeric|min:0.01',
            'payment_method'      => 'required|in:cash,transfer,card,other',
            'description'         => 'required|string|max:500',
            'reference'           => 'nullable|string|max:100',
            'attachment'          => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120',
        ]);

        $tid = $this->tid();

        // Validasi kategori milik tenant ini
        $category = ExpenseCategory::where('id', $data['expense_category_id'])
            ->where('tenant_id', $tid)->firstOrFail();

        $attachmentPath = null;
        if ($request->hasFile('attachment')) {
            $attachmentPath = $request->file('attachment')->store("expenses/{$tid}", 'public');
        }

        $number = 'EXP-' . date('Ymd') . '-' . str_pad(
            Transaction::where('tenant_id', $tid)->where('type', 'expense')->whereDate('date', today())->count() + 1,
            3, '0', STR_PAD_LEFT
        );

        $expense = Transaction::create([
            'tenant_id'           => $tid,
            'user_id'             => auth()->id(),
            'expense_category_id' => $data['expense_category_id'],
            'number'              => $number,
            'type'                => 'expense',
            'date'                => $data['date'],
            'amount'              => $data['amount'],
            'payment_method'      => $data['payment_method'],
            'description'         => $data['description'],
            'reference'           => $data['reference'] ?? null,
            'attachment'          => $attachmentPath ? Storage::url($attachmentPath) : null,
        ]);

        ActivityLog::record('expense_created',
            "Pengeluaran dicatat: {$number} - {$category->name} Rp " . number_format($data['amount'], 0, ',', '.'),
            $expense);

        return back()->with('success', "Pengeluaran {$number} berhasil dicatat.");
    }

    public function destroy(Transaction $expense)
    {
        abort_if($expense->tenant_id !== $this->tid(), 403);
        abort_if($expense->type !== 'expense', 403);

        if ($expense->attachment) {
            $path = str_replace('/storage/', '', $expense->attachment);
            Storage::disk('public')->delete($path);
        }

        ActivityLog::record('expense_deleted', "Pengeluaran dihapus: {$expense->number}", $expense, $expense->toArray());
        $expense->delete();

        return back()->with('success', 'Pengeluaran berhasil dihapus.');
    }

    // ── Expense Categories ────────────────────────────────────────

    public function categories()
    {
        $categories = ExpenseCategory::where('tenant_id', $this->tid())
            ->withCount(['transactions as expense_count' => fn($q) => $q->where('type', 'expense')])
            ->orderBy('name')->get();

        return view('expenses.categories', compact('categories'));
    }

    public function storeCategory(Request $request)
    {
        $data = $request->validate([
            'name'        => 'required|string|max:100',
            'code'        => 'required|string|max:20',
            'type'        => 'required|in:operational,cogs,marketing,hr,admin,other',
            'description' => 'nullable|string|max:255',
        ]);

        $tid = $this->tid();

        if (ExpenseCategory::where('tenant_id', $tid)->where('code', $data['code'])->exists()) {
            return back()->withErrors(['code' => 'Kode kategori sudah digunakan.'])->withInput();
        }

        ExpenseCategory::create(['tenant_id' => $tid, 'is_active' => true] + $data);

        return back()->with('success', "Kategori {$data['name']} berhasil ditambahkan.");
    }

    public function updateCategory(Request $request, ExpenseCategory $category)
    {
        abort_if($category->tenant_id !== $this->tid(), 403);

        $data = $request->validate([
            'name'        => 'required|string|max:100',
            'code'        => 'required|string|max:20',
            'type'        => 'required|in:operational,cogs,marketing,hr,admin,other',
            'description' => 'nullable|string|max:255',
            'is_active'   => 'boolean',
        ]);

        $data['is_active'] = $request->boolean('is_active');
        $category->update($data);

        return back()->with('success', "Kategori {$category->name} diperbarui.");
    }
}
