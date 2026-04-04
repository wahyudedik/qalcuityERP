<?php

namespace App\Jobs;

use App\Models\PrintJob;
use App\Services\PosPrinterService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcessPrintJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 120; // 2 minutes timeout

    protected $printJob;

    public function __construct(PrintJob $printJob)
    {
        $this->printJob = $printJob;
    }

    public function handle(): void
    {
        $printerService = new PosPrinterService();

        try {
            // Mark as processing
            $this->printJob->markAsProcessing();

            // Connect to printer
            $connected = $printerService->connect(
                $this->printJob->printer_type,
                $this->printJob->printer_destination
            );

            if (!$connected) {
                throw new \Exception("Failed to connect to printer");
            }

            // Print based on job type
            $result = match ($this->printJob->job_type) {
                'receipt' => $printerService->printSalesReceipt(
                    $this->printJob->print_data,
                    $this->printJob->print_data['paper_width'] ?? 80
                ),
                'kitchen_ticket' => $printerService->printKitchenTicket(
                    $this->printJob->print_data
                ),
                'barcode_label' => $printerService->printBarcodeLabel(
                    $this->printJob->print_data['code'],
                    $this->printJob->print_data['label'] ?? '',
                    $this->printJob->print_data['price'] ?? ''
                ),
                'test_page' => $printerService->printTestPage(),
                default => throw new \Exception("Unknown job type: {$this->printJob->job_type}"),
            };

            // Check result
            if ($result['success']) {
                $this->printJob->markAsCompleted();

                Log::info("Print job completed successfully", [
                    'job_id' => $this->printJob->id,
                    'job_type' => $this->printJob->job_type,
                    'reference_number' => $this->printJob->reference_number,
                ]);
            } else {
                throw new \Exception($result['error'] ?? 'Unknown error');
            }

        } catch (\Exception $e) {
            $errorMessage = $e->getMessage();

            Log::error("Print job failed", [
                'job_id' => $this->printJob->id,
                'error' => $errorMessage,
                'retry_count' => $this->printJob->retry_count,
            ]);

            // Check if can retry
            $maxRetries = config('pos_printer.queue.retry_attempts', 3);

            if ($this->printJob->retry_count < $maxRetries) {
                // Retry
                $delay = config('pos_printer.queue.retry_delay', 5);
                $this->release($delay);
            } else {
                // Mark as failed
                $this->printJob->markAsFailed($errorMessage);
            }

        } finally {
            // Always disconnect
            $printerService->disconnect();
        }
    }

    public function failed(\Throwable $exception): void
    {
        Log::error("Print job permanently failed", [
            'job_id' => $this->printJob->id,
            'error' => $exception->getMessage(),
        ]);

        $this->printJob->markAsFailed($exception->getMessage());
    }
}
