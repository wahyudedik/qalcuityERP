<?php

namespace App\Http\Controllers\Pos;

use App\Http\Controllers\Controller;
use App\Models\PrintJob;
use App\Models\PrinterSetting;
use App\Models\SalesOrder;
use App\Services\PosPrinterService;
use App\Jobs\ProcessPrintJob;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PrintController extends Controller
{
    protected $printerService;

    public function __construct()
    {
        $this->printerService = new PosPrinterService();
    }

    /**
     * Print sales receipt
     */
    public function printReceipt(Request $request, SalesOrder $order)
    {
        try {
            // Get printer settings
            $printerSetting = PrinterSetting::getDefaultPrinter($this->tenantId(), 'receipt_printer');

            if (!$printerSetting) {
                return response()->json([
                    'success' => false,
                    'error' => 'No receipt printer configured',
                ], 400);
            }

            // Prepare print data
            $printData = $this->prepareReceiptData($order);

            // Check if queue is enabled
            if (config('pos_printer.queue.enabled', true)) {
                // Create print job
                $printJob = PrintJob::create([
                    'tenant_id' => $this->tenantId(),
                    'job_type' => 'receipt',
                    'reference_id' => $order->id,
                    'reference_number' => $order->order_number,
                    'printer_type' => $printerSetting->printer_type,
                    'printer_destination' => $printerSetting->printer_destination,
                    'print_data' => $printData,
                    'status' => 'pending',
                ]);

                // Dispatch to queue
                ProcessPrintJob::dispatch($printJob);

                return response()->json([
                    'success' => true,
                    'message' => 'Print job queued',
                    'job_id' => $printJob->id,
                ]);
            } else {
                // Print immediately
                $connected = $this->printerService->connect(
                    $printerSetting->printer_type,
                    $printerSetting->printer_destination
                );

                if (!$connected) {
                    return response()->json([
                        'success' => false,
                        'error' => 'Failed to connect to printer',
                    ], 500);
                }

                $result = $this->printerService->printSalesReceipt(
                    $printData,
                    $printerSetting->paper_width
                );

                $this->printerService->disconnect();

                return response()->json($result);
            }

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Print kitchen ticket
     */
    public function printKitchenTicket(Request $request, SalesOrder $order)
    {
        try {
            // Get kitchen printer settings
            $printerSetting = PrinterSetting::getDefaultPrinter($this->tenantId(), 'kitchen_printer');

            if (!$printerSetting || !$printerSetting->is_active) {
                return response()->json([
                    'success' => false,
                    'error' => 'Kitchen printer not configured or inactive',
                ], 400);
            }

            // Prepare kitchen ticket data
            $printData = [
                'order_number' => $order->order_number,
                'table_number' => $order->table_number ?? 'N/A',
                'server' => $order->createdBy ? $order->createdBy->name : 'Unknown',
                'items' => $order->items->map(function ($item) {
                    return [
                        'quantity' => $item->quantity,
                        'name' => $item->product->name ?? $item->product_name,
                        'special_instructions' => $item->notes,
                        'modifiers' => [], // Add modifiers if available
                    ];
                })->toArray(),
            ];

            // Create print job
            $printJob = PrintJob::create([
                'tenant_id' => $this->tenantId(),
                'job_type' => 'kitchen_ticket',
                'reference_id' => $order->id,
                'reference_number' => $order->order_number,
                'printer_type' => $printerSetting->printer_type,
                'printer_destination' => $printerSetting->printer_destination,
                'print_data' => $printData,
                'status' => 'pending',
            ]);

            // Dispatch to queue
            ProcessPrintJob::dispatch($printJob);

            return response()->json([
                'success' => true,
                'message' => 'Kitchen ticket queued for printing',
                'job_id' => $printJob->id,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Print barcode label
     */
    public function printBarcodeLabel(Request $request)
    {
        $validated = $request->validate([
            'code' => 'required|string',
            'label' => 'nullable|string',
            'price' => 'nullable|numeric',
        ]);

        try {
            // Get barcode printer settings
            $printerSetting = PrinterSetting::getDefaultPrinter($this->tenantId(), 'barcode_printer');

            if (!$printerSetting || !$printerSetting->is_active) {
                return response()->json([
                    'success' => false,
                    'error' => 'Barcode printer not configured or inactive',
                ], 400);
            }

            $printData = [
                'code' => $validated['code'],
                'label' => $validated['label'] ?? '',
                'price' => $validated['price'] ?? '',
            ];

            // Create print job
            $printJob = PrintJob::create([
                'tenant_id' => $this->tenantId(),
                'job_type' => 'barcode_label',
                'printer_type' => $printerSetting->printer_type,
                'printer_destination' => $printerSetting->printer_destination,
                'print_data' => $printData,
                'status' => 'pending',
            ]);

            ProcessPrintJob::dispatch($printJob);

            return response()->json([
                'success' => true,
                'message' => 'Barcode label queued for printing',
                'job_id' => $printJob->id,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Test printer connection
     */
    public function testPrinter(Request $request)
    {
        $validated = $request->validate([
            'printer_type' => 'required|in:usb,network,file,cups',
            'printer_destination' => 'required|string',
        ]);

        try {
            $connected = $this->printerService->connect(
                $validated['printer_type'],
                $validated['printer_destination']
            );

            if (!$connected) {
                return response()->json([
                    'success' => false,
                    'error' => 'Failed to connect to printer',
                ], 500);
            }

            $result = $this->printerService->printTestPage();
            $this->printerService->disconnect();

            return response()->json($result);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get print jobs queue
     */
    public function getPrintQueue(Request $request)
    {
        $status = $request->input('status');
        $limit = $request->input('limit', 50);

        $query = PrintJob::where('tenant_id', $this->tenantId())
            ->with('tenant')
            ->orderBy('created_at', 'desc');

        if ($status) {
            $query->where('status', $status);
        }

        $jobs = $query->paginate($limit);

        return response()->json([
            'success' => true,
            'data' => $jobs,
        ]);
    }

    /**
     * Retry failed print job
     */
    public function retryPrintJob(PrintJob $job)
    {
        // Authorization check
        if ($job->tenant_id !== $this->tenantId()) {
            return response()->json([
                'success' => false,
                'error' => 'Unauthorized',
            ], 403);
        }

        if (!$job->canRetry()) {
            return response()->json([
                'success' => false,
                'error' => 'Job cannot be retried (max retries reached)',
            ], 400);
        }

        $job->retry();
        ProcessPrintJob::dispatch($job);

        return response()->json([
            'success' => true,
            'message' => 'Job requeued',
        ]);
    }

    /**
     * Cancel print job
     */
    public function cancelPrintJob(PrintJob $job)
    {
        // Authorization check
        if ($job->tenant_id !== $this->tenantId()) {
            return response()->json([
                'success' => false,
                'error' => 'Unauthorized',
            ], 403);
        }

        if (!in_array($job->status, ['pending', 'processing'])) {
            return response()->json([
                'success' => false,
                'error' => 'Cannot cancel job in current status',
            ], 400);
        }

        $job->cancel();

        return response()->json([
            'success' => true,
            'message' => 'Job cancelled',
        ]);
    }

    /**
     * Get printer settings
     */
    public function getPrinterSettings()
    {
        $settings = PrinterSetting::where('tenant_id', $this->tenantId())->get();

        return response()->json([
            'success' => true,
            'data' => $settings,
        ]);
    }

    /**
     * Save printer settings
     */
    public function savePrinterSettings(Request $request)
    {
        $validated = $request->validate([
            'printer_name' => 'required|string|in:receipt_printer,kitchen_printer,barcode_printer',
            'printer_type' => 'required|in:usb,network,file,cups',
            'printer_destination' => 'required|string',
            'paper_width' => 'nullable|integer|in:58,80',
            'is_active' => 'boolean',
            'is_default' => 'boolean',
            'settings' => 'nullable|array',
        ]);

        DB::transaction(function () use ($validated) {
            $setting = PrinterSetting::updateOrCreate(
                [
                    'tenant_id' => $this->tenantId(),
                    'printer_name' => $validated['printer_name'],
                ],
                $validated
            );

            if ($validated['is_default'] ?? false) {
                $setting->setAsDefault();
            }
        });

        return response()->json([
            'success' => true,
            'message' => 'Printer settings saved',
        ]);
    }

    /**
     * Prepare receipt data from order
     */
    private function prepareReceiptData(SalesOrder $order): array
    {
        return [
            'company_name' => config('pos_printer.receipt.company_name'),
            'address' => config('pos_printer.receipt.address'),
            'phone' => config('pos_printer.receipt.phone'),
            'order_number' => $order->order_number,
            'date' => $order->created_at->format('Y-m-d H:i:s'),
            'cashier' => $order->createdBy ? $order->createdBy->name : 'Unknown',
            'customer_name' => $order->customer_name ?? null,
            'items' => $order->items->map(function ($item) {
                return [
                    'name' => $item->product->name ?? $item->product_name,
                    'quantity' => $item->quantity,
                    'price' => $item->unit_price,
                    'total' => $item->total_price,
                    'modifiers' => [],
                    'notes' => $item->notes,
                ];
            })->toArray(),
            'subtotal' => $order->subtotal,
            'discount' => $order->discount_amount ?? 0,
            'tax' => $order->tax_amount ?? 0,
            'service_charge' => $order->service_charge ?? 0,
            'grand_total' => $order->grand_total,
            'payment_method' => $order->payment_method ?? 'cash',
            'amount_paid' => $order->amount_paid ?? $order->grand_total,
            'change' => ($order->amount_paid ?? $order->grand_total) - $order->grand_total,
            'reference_number' => $order->payment_reference ?? null,
            'footer_text' => config('pos_printer.receipt.footer_text'),
            'paper_width' => 80,
        ];
    }
}
