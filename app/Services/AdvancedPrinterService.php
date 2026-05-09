<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Mike42\Escpos\CapabilityProfile;
use Mike42\Escpos\EscposImage;
use Mike42\Escpos\PrintConnectors\FilePrintConnector;
use Mike42\Escpos\PrintConnectors\NetworkPrintConnector;
use Mike42\Escpos\PrintConnectors\WindowsPrintConnector;
use Mike42\Escpos\Printer;

class AdvancedPrinterService
{
    protected $printer;

    protected $connector;

    protected $profile;

    /**
     * Connect to printer
     */
    public function connect(string $type, string $destination, int $paperWidth = 80): bool
    {
        try {
            $this->profile = CapabilityProfile::load('default');

            $this->connector = match ($type) {
                'usb', 'serial' => new WindowsPrintConnector($destination),
                'network' => new NetworkPrintConnector($destination, 9100),
                'file' => new FilePrintConnector($destination),
                default => throw new \Exception("Unsupported printer type: {$type}"),
            };

            $this->printer = new Printer($this->connector, $this->profile);

            // Set paper width
            if ($paperWidth === 58) {
                $this->printer->setPrintWidth(384); // 58mm
            } else {
                $this->printer->setPrintWidth(576); // 80mm
            }

            return true;
        } catch (\Exception $e) {
            Log::error('Printer Connection Error', [
                'type' => $type,
                'destination' => $destination,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Print product barcode label
     */
    public function printBarcodeLabel(
        string $barcode,
        string $productName,
        float $price,
        ?string $sku = null,
        int $quantity = 1
    ): bool {
        try {
            if (! $this->printer) {
                throw new \Exception('Printer not connected');
            }

            $p = $this->printer;

            // Initialize
            $p->initialize();
            $p->setJustification(Printer::JUSTIFY_CENTER);

            // Product name (small)
            $p->setTextSize(1, 1);
            $p->text(substr($productName, 0, 30)."\n");

            // SKU if available
            if ($sku) {
                $p->setTextSize(1, 1);
                $p->text("SKU: {$sku}\n");
            }

            // Price (large, bold)
            $p->setEmphasis(true);
            $p->setTextSize(2, 2);
            $p->text('Rp '.number_format($price, 0, ',', '.')."\n");
            $p->setEmphasis(false);

            // Barcode
            $p->setBarcodeHeight(60);
            $p->setBarcodeWidth(2);
            $p->barcode($barcode, Printer::BARCODE_CODE128);

            // Quantity
            if ($quantity > 1) {
                $p->feed();
                $p->setTextSize(1, 1);
                $p->text("Qty: {$quantity} pcs\n");
            }

            // Cut paper
            $p->feed(2);
            $p->cut();

            return true;
        } catch (\Exception $e) {
            Log::error('Barcode Label Print Error', ['error' => $e->getMessage()]);

            return false;
        }
    }

    /**
     * Print QR code on receipt
     */
    public function printQRCode(string $data, int $size = 6): bool
    {
        try {
            if (! $this->printer) {
                throw new \Exception('Printer not connected');
            }

            $this->printer->setJustification(Printer::JUSTIFY_CENTER);
            $this->printer->qrCode($data, Printer::QR_ECLEVEL_L, $size);
            $this->printer->feed();

            return true;
        } catch (\Exception $e) {
            Log::error('QR Code Print Error', ['error' => $e->getMessage()]);

            return false;
        }
    }

    /**
     * Print logo image
     */
    public function printLogo(string $imagePath, ?int $width = null): bool
    {
        try {
            if (! $this->printer) {
                throw new \Exception('Printer not connected');
            }

            if (! file_exists($imagePath)) {
                throw new \Exception("Logo file not found: {$imagePath}");
            }

            $logo = EscposImage::load($imagePath);

            if ($width) {
                $this->printer->bitImage($logo, $width);
            } else {
                $this->printer->bitImage($logo);
            }

            $this->printer->feed();

            return true;
        } catch (\Exception $e) {
            Log::error('Logo Print Error', ['error' => $e->getMessage()]);

            return false;
        }
    }

    /**
     * Print kitchen ticket with item modifiers
     */
    public function printKitchenTicket(array $orderData): bool
    {
        try {
            if (! $this->printer) {
                throw new \Exception('Printer not connected');
            }

            $p = $this->printer;

            // Header
            $p->initialize();
            $p->setJustification(Printer::JUSTIFY_CENTER);
            $p->setTextSize(2, 2);
            $p->text("KITCHEN ORDER\n");
            $p->setTextSize(1, 1);
            $p->text("--------------------------------\n");

            // Order info
            $p->setJustification(Printer::JUSTIFY_LEFT);
            $p->text('Order #: '.$orderData['order_number']."\n");
            $p->text('Table: '.($orderData['table'] ?? 'N/A')."\n");
            $p->text('Time: '.date('H:i:s')."\n");
            $p->text("--------------------------------\n");

            // Items
            foreach ($orderData['items'] as $item) {
                $p->setEmphasis(true);
                $p->text($item['quantity'].'x '.$item['name']."\n");
                $p->setEmphasis(false);

                // Modifiers/notes
                if (! empty($item['modifiers'])) {
                    foreach ($item['modifiers'] as $modifier) {
                        $p->text('   - '.$modifier."\n");
                    }
                }

                // Special instructions
                if (! empty($item['notes'])) {
                    $p->text('   Note: '.$item['notes']."\n");
                }

                $p->text("\n");
            }

            $p->text("--------------------------------\n");
            $p->feed(2);
            $p->cut();

            return true;
        } catch (\Exception $e) {
            Log::error('Kitchen Ticket Print Error', ['error' => $e->getMessage()]);

            return false;
        }
    }

    /**
     * Print inventory picking list
     */
    public function printPickingList(array $pickingData): bool
    {
        try {
            if (! $this->printer) {
                throw new \Exception('Printer not connected');
            }

            $p = $this->printer;

            $p->initialize();
            $p->setJustification(Printer::JUSTIFY_CENTER);
            $p->setTextSize(2, 1);
            $p->text("PICKING LIST\n");
            $p->setTextSize(1, 1);
            $p->text("--------------------------------\n");

            $p->setJustification(Printer::JUSTIFY_LEFT);
            $p->text('SO#: '.$pickingData['sales_order']."\n");
            $p->text('Date: '.date('Y-m-d')."\n");
            $p->text('Warehouse: '.$pickingData['warehouse']."\n");
            $p->text("--------------------------------\n\n");

            // Items with locations
            foreach ($pickingData['items'] as $item) {
                $p->setEmphasis(true);
                $p->text($item['product_code']."\n");
                $p->setEmphasis(false);
                $p->text($item['product_name']."\n");
                $p->text('Qty: '.$item['quantity'].' | Loc: '.$item['bin_location']."\n");

                // Print barcode for product
                if (! empty($item['barcode'])) {
                    $p->setBarcodeHeight(40);
                    $p->barcode($item['barcode'], Printer::BARCODE_CODE128);
                }

                $p->text("\n--------------------------------\n");
            }

            $p->feed(2);
            $p->cut();

            return true;
        } catch (\Exception $e) {
            Log::error('Picking List Print Error', ['error' => $e->getMessage()]);

            return false;
        }
    }

    /**
     * Print asset tag label
     */
    public function printAssetTag(array $assetData): bool
    {
        try {
            if (! $this->printer) {
                throw new \Exception('Printer not connected');
            }

            $p = $this->printer;

            $p->initialize();
            $p->setJustification(Printer::JUSTIFY_CENTER);

            // Company name
            $p->setTextSize(1, 1);
            $p->text($assetData['company'] ?? 'ASSET TAG'."\n");
            $p->text("--------------------------------\n");

            // Asset info
            $p->setJustification(Printer::JUSTIFY_LEFT);
            $p->text('ID: '.$assetData['asset_id']."\n");
            $p->text('Name: '.substr($assetData['name'], 0, 25)."\n");
            $p->text('Category: '.$assetData['category']."\n");
            $p->text('Location: '.$assetData['location']."\n");
            $p->text('Purchase: '.$assetData['purchase_date']."\n");

            // QR code with asset ID
            $p->feed();
            $p->setJustification(Printer::JUSTIFY_CENTER);
            $p->qrCode($assetData['asset_id'], Printer::QR_ECLEVEL_M, 5);

            $p->feed(2);
            $p->cut();

            return true;
        } catch (\Exception $e) {
            Log::error('Asset Tag Print Error', ['error' => $e->getMessage()]);

            return false;
        }
    }

    /**
     * Cash drawer kick
     */
    public function kickCashDrawer(): bool
    {
        try {
            if (! $this->printer) {
                throw new \Exception('Printer not connected');
            }

            $this->printer->pulse(0, 60, 120);

            return true;
        } catch (\Exception $e) {
            Log::error('Cash Drawer Kick Error', ['error' => $e->getMessage()]);

            return false;
        }
    }

    /**
     * Close printer connection
     */
    public function close(): void
    {
        if ($this->printer) {
            try {
                $this->printer->close();
            } catch (\Exception $e) {
                Log::warning('Printer Close Error', ['error' => $e->getMessage()]);
            }
        }
    }

    /**
     * Destructor - ensure printer is closed
     */
    public function __destruct()
    {
        $this->close();
    }
}
