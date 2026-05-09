<?php

namespace App\Console\Commands;

use App\Models\LabEquipment;
use App\Models\LabOrder;
use App\Models\LabResult;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PollLabEquipment extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'healthcare:lab:poll-equipment
                            {--tenant= : Specific tenant ID}
                            {--equipment= : Specific equipment ID}
                            {--dry-run : Test mode without importing}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Poll lab equipment and import results automatically';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $tenantId = $this->option('tenant');
        $equipmentId = $this->option('equipment');
        $dryRun = $this->option('dry-run');

        $this->info('🔬 Polling lab equipment for results...');

        if ($dryRun) {
            $this->warn('⚠️ DRY RUN MODE - No data will be imported');
        }

        // Get lab equipment to poll
        $query = LabEquipment::where('is_active', true)
            ->where('auto_poll', true);

        if ($tenantId) {
            $query->where('tenant_id', $tenantId);
        }

        if ($equipmentId) {
            $query->where('id', $equipmentId);
        }

        $equipment = $query->get();

        if ($equipment->isEmpty()) {
            $this->info('✅ No lab equipment to poll');

            return Command::SUCCESS;
        }

        $this->info("Found {$equipment->count()} equipment(s) to poll");

        $importedCount = 0;
        $failedCount = 0;

        foreach ($equipment as $device) {
            try {
                $this->info("\nPolling: {$device->name} ({$device->model})");

                // Poll based on equipment type
                $results = match ($device->connection_type) {
                    'hl7' => $this->pollHL7Device($device),
                    'api' => $this->pollAPIDevice($device),
                    'file' => $this->pollFileDevice($device),
                    default => [],
                };

                if (empty($results)) {
                    $this->line("  No new results from {$device->name}");

                    continue;
                }

                $this->info('  Found '.count($results).' result(s)');

                if ($dryRun) {
                    $this->line('  Would import '.count($results).' result(s)');
                    $importedCount += count($results);

                    continue;
                }

                // Import results
                foreach ($results as $resultData) {
                    $this->importLabResult($device, $resultData);
                    $importedCount++;
                }

                // Update last poll time
                $device->update(['last_polled_at' => now()]);

            } catch (\Exception $e) {
                $failedCount++;
                $this->error("❌ Failed to poll {$device->name}: {$e->getMessage()}");

                Log::error('Lab equipment poll failed', [
                    'equipment_id' => $device->id,
                    'equipment_name' => $device->name,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        $this->info("\n📊 Poll Summary:");
        $this->line("   ✓ Imported: {$importedCount}");
        $this->line("   ✗ Failed: {$failedCount}");
        $this->line("   Equipment: {$equipment->count()}");

        return Command::SUCCESS;
    }

    /**
     * Poll HL7 connected device
     */
    protected function pollHL7Device($device): array
    {
        // HL7 protocol implementation
        // Connect to device via TCP/IP and parse HL7 messages

        Log::info('Polling HL7 device', [
            'equipment_id' => $device->id,
            'ip' => $device->ip_address,
            'port' => $device->port,
        ]);

        // Placeholder - implement HL7 parser
        return [];
    }

    /**
     * Poll API connected device
     */
    protected function pollAPIDevice($device): array
    {
        // HTTP API call to device
        $response = Http::timeout(30)
            ->get("http://{$device->ip_address}:{$device->port}/api/results", [
                'from' => $device->last_polled_at?->toIso8601String(),
            ]);

        if ($response->successful()) {
            return $response->json('results', []);
        }

        return [];
    }

    /**
     * Poll file-based device
     */
    protected function pollFileDevice($device): array
    {
        // Read results from shared folder or FTP
        $directory = $device->file_path;

        if (! is_dir($directory)) {
            return [];
        }

        $files = glob("{$directory}/*.csv");
        $results = [];

        foreach ($files as $file) {
            if (($handle = fopen($file, 'r')) !== false) {
                while (($data = fgetcsv($handle)) !== false) {
                    $results[] = $this->parseCSVRow($data);
                }
                fclose($handle);

                // Archive processed file
                rename($file, str_replace('.csv', '.processed.csv', $file));
            }
        }

        return $results;
    }

    /**
     * Parse CSV row to result data
     */
    protected function parseCSVRow(array $data): array
    {
        return [
            'test_code' => $data[0] ?? null,
            'patient_id' => $data[1] ?? null,
            'result_value' => $data[2] ?? null,
            'unit' => $data[3] ?? null,
            'reference_range' => $data[4] ?? null,
            'timestamp' => $data[5] ?? now(),
        ];
    }

    /**
     * Import lab result into system
     */
    protected function importLabResult($device, array $resultData): void
    {
        // Find matching lab order
        $order = LabOrder::where('tenant_id', $device->tenant_id)
            ->where('accession_number', $resultData['patient_id'])
            ->where('status', 'pending')
            ->first();

        if (! $order) {
            Log::warning('No matching lab order found', [
                'accession_number' => $resultData['patient_id'],
                'equipment_id' => $device->id,
            ]);

            return;
        }

        // Create lab result
        LabResult::create([
            'tenant_id' => $device->tenant_id,
            'lab_order_id' => $order->id,
            'patient_id' => $order->patient_id,
            'test_name' => $order->test_type,
            'result_value' => $resultData['result_value'],
            'unit' => $resultData['unit'],
            'reference_range' => $resultData['reference_range'],
            'result_date' => $resultData['timestamp'],
            'status' => 'completed',
            'auto_imported' => true,
            'equipment_id' => $device->id,
        ]);

        // Update order status
        $order->update(['status' => 'completed']);

        $this->line("  ✓ Imported result for order #{$order->id}");
    }
}
