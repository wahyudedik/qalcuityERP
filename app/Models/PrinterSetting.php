<?php

namespace App\Models;

use App\Services\PosPrinterService;
use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PrinterSetting extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'printer_name',
        'printer_type',
        'printer_destination',
        'paper_width',
        'is_active',
        'is_default',
        'settings',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'is_default' => 'boolean',
            'settings' => 'array',
        ];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * Get default printer for tenant
     */
    public static function getDefaultPrinter(int $tenantId, string $printerName = 'receipt_printer'): ?self
    {
        return static::where('tenant_id', $tenantId)
            ->where('printer_name', $printerName)
            ->where('is_active', true)
            ->first();
    }

    /**
     * Set as default printer
     */
    public function setAsDefault(): void
    {
        // Unset other defaults
        static::where('tenant_id', $this->tenant_id)
            ->where('printer_name', $this->printer_name)
            ->update(['is_default' => false]);

        // Set this as default
        $this->update(['is_default' => true]);
    }

    /**
     * Test printer connection
     */
    public function testConnection(): array
    {
        try {
            $printerService = new PosPrinterService;
            $connected = $printerService->connect($this->printer_type, $this->printer_destination);

            if ($connected) {
                $result = $printerService->printTestPage();
                $printerService->disconnect();

                return $result;
            }

            return ['success' => false, 'error' => 'Failed to connect to printer'];

        } catch (\Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
}
