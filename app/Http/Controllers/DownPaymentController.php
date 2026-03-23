<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Customer;
use App\Models\DownPayment;
use App\Models\DownPaymentApplication;
use App\Models\Invoice;
use App\Models\Supplier;
use App\Services\DocumentNumberService;
use App\Services\GlPostingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DownPaymentController extends Controller
{
    private function tid(): int { return auth()->user()->tenant_id; }

    public function index(Request $request)
    {
        $query = DownPayment::where('tenant_id', $this->tid());

        if ($request->filled('type'))   $query->where('type', $request->type);
        if ($request->filled('status')) $query->where('status', $request->status);
        if ($request->filled('search')) {
            $s = $request->search;
            $query->where('number', 'like', "%$s%");
        }

        $downPayments = $query->with('party')->latest('payment_date')->paginate(20)->withQueryString();

        $stats = [
            'customer_pending' => DownPayment::where('tenant_id', $this->tid())->where('type', 'customer')->where('status', 'pending')->sum('remaining_amount'),
            'supplier_pending' => DownPayment::where('tenant_id', $this->tid())->where('type', 'supplier')->where('status', 'pending')->sum('remaining_amount'),
        ];

        $customers = Customer::where('tenant_id', $this->tid())->where('is_active', true)->orderBy('name')->get();
        $suppliers = Supplier::where('tenant_id', $this->tid())->where('is_active', true)->orderBy('name')->get();

        return view('down-payments.index', compact('downPayments', 'stats', 'customers', 'suppliers'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'type'           => 'required|in:customer,supplier',
            'party_id'       => 'required|integer',
            'payment_date'   => 'required|date',
            'amount'         => 'required|numeric|min:1',
            'payment_method' => 'required|in:cash,transfer,qris,other',
            'notes'          => 'nullable|string|max:500',
        ]);

        $tid = $this->tid();

        // Validasi party milik tenant
        if ($data['type'] === 'customer') {
            Customer::where('tenant_id', $tid)->findOrFail($data['party_id']);
            $partyType = Customer::class;
        } else {
            Supplier::where('tenant_id', $tid)->findOrFail($data['party_id']);
            $partyType = Supplier::class;
        }

        DB::transaction(function () use ($data, $tid, $partyType) {
            $number = app(DocumentNumberService::class)->generate($tid, 'DP');

            $dp = DownPayment::create([
                'tenant_id'        => $tid,
                'number'           => $number,
                'type'             => $data['type'],
                'party_id'         => $data['party_id'],
                'party_type'       => $partyType,
                'payment_date'     => $data['payment_date'],
                'amount'           => $data['amount'],
                'applied_amount'   => 0,
                'remaining_amount' => $data['amount'],
                'status'           => 'pending',
                'payment_method'   => $data['payment_method'],
                'created_by'       => auth()->id(),
                'notes'            => $data['notes'] ?? null,
            ]);

            // GL Posting
            $gl = app(GlPostingService::class);
            if ($data['type'] === 'customer') {
                $gl->postDownPaymentReceived($tid, auth()->id(), $number, $dp->id, (float) $data['amount'], $data['payment_method'], $data['payment_date']);
            } else {
                $gl->postDownPaymentPaid($tid, auth()->id(), $number, $dp->id, (float) $data['amount'], $data['payment_method'], $data['payment_date']);
            }

            ActivityLog::record('down_payment_created', "Uang muka {$number} dibuat (Rp " . number_format($data['amount'], 0, ',', '.') . ")", $dp);
        });

        return back()->with('success', 'Uang muka berhasil dicatat.');
    }

    /** Apply DP ke invoice */
    public function apply(Request $request, DownPayment $downPayment)
    {
        abort_if($downPayment->tenant_id !== $this->tid(), 403);
        abort_if($downPayment->type !== 'customer', 422, 'Hanya DP customer yang bisa diaplikasikan ke invoice.');
        abort_if(in_array($downPayment->status, ['applied', 'refunded']), 422, 'DP ini sudah habis dipakai.');

        $data = $request->validate([
            'invoice_id' => 'required|exists:invoices,id',
            'amount'     => 'required|numeric|min:1|max:' . $downPayment->remaining_amount,
        ]);

        $tid     = $this->tid();
        $invoice = Invoice::where('tenant_id', $tid)->findOrFail($data['invoice_id']);

        abort_if($data['amount'] > $invoice->remaining_amount, 422, 'Jumlah melebihi sisa tagihan invoice.');

        DB::transaction(function () use ($downPayment, $invoice, $data, $tid) {
            DownPaymentApplication::create([
                'down_payment_id' => $downPayment->id,
                'invoice_id'      => $invoice->id,
                'amount'          => $data['amount'],
                'applied_at'      => now(),
                'applied_by'      => auth()->id(),
            ]);

            $downPayment->recalculate();

            // Update invoice payment
            $invoice->payments()->create([
                'tenant_id'      => $tid,
                'amount'         => $data['amount'],
                'payment_method' => 'down_payment',
                'notes'          => "Aplikasi DP {$downPayment->number}",
                'payment_date'   => today(),
                'user_id'        => auth()->id(),
            ]);
            $invoice->updatePaymentStatus();

            // GL Posting
            app(GlPostingService::class)->postDownPaymentApplied(
                $tid, auth()->id(),
                $downPayment->number,
                $downPayment->id,
                (float) $data['amount'],
                today()->toDateString()
            );

            ActivityLog::record('down_payment_applied', "DP {$downPayment->number} diaplikasikan ke invoice {$invoice->number} (Rp " . number_format($data['amount'], 0, ',', '.') . ")", $downPayment);
        });

        return back()->with('success', "DP {$downPayment->number} berhasil diaplikasikan ke invoice {$invoice->number}.");
    }
}
