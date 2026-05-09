<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Mike42\Escpos\CapabilityProfile;
use Mike42\Escpos\PrintConnectors\CupsPrintConnector;
use Mike42\Escpos\PrintConnectors\FilePrintConnector;
use Mike42\Escpos\PrintConnectors\NetworkPrintConnector;
use Mike42\Escpos\PrintConnectors\WindowsPrintConnector;
use Mike42\Escpos\Printer;

class PosPrinterService
{
    private $connector;

    private $printer;

    private $profile;

    // Printer types
    const TYPE_USB = 'usb';

    const TYPE_NETWORK = 'network';

    const TYPE_FILE = 'file';

    const TYPE_CUPS = 'cups';

    // Paper sizes
    const PAPER_58MM = 58;

    const PAPER_80MM = 80;

    public function __construct()
    {
        // Load capability profile for better printer compatibility
        try {
            $this->profile = CapabilityProfile::load('default');
        } catch (\Exception $e) {
            Log::warning('Failed to load capability profile: '.$e->getMessage());
            $this->profile = null;
        }
    }

    /**
     * Initialize printer connection
     */
    public function connect(string $type, string $destination): bool
    {
        try {
            switch ($type) {
                case self::TYPE_USB:
                    $this->connector = new WindowsPrintConnector($destination);
                    break;

                case self::TYPE_NETWORK:
                    // Format: "IP_ADDRESS" or "IP_ADDRESS:PORT"
                    $parts = explode(':', $destination);
                    $ip = $parts[0];
                    $port = $parts[1] ?? 9100; // Default ESC/POS port
                    $this->connector = new NetworkPrintConnector($ip, $port);
                    break;

                case self::TYPE_FILE:
                    $this->connector = new FilePrintConnector($destination);
                    break;

                case self::TYPE_CUPS:
                    $this->connector = new CupsPrintConnector($destination);
                    break;

                default:
                    throw new \Exception("Unsupported printer type: {$type}");
            }

            $this->printer = new Printer($this->connector, $this->profile);

            return true;

        } catch (\Exception $e) {
            Log::error("Printer connection failed: {$e->getMessage()}");

            return false;
        }
    }

    /**
     * Print sales receipt
     */
    public function printSalesReceipt(array $orderData, int $paperWidth = self::PAPER_80MM): array
    {
        try {
            if (! $this->printer) {
                throw new \Exception('Printer not connected. Call connect() first.');
            }

            $p = $this->printer;

            // Initialize
            $p->initialize();

            // Header
            $this->printHeader($p, $orderData, $paperWidth);

            // Order info
            $this->printOrderInfo($p, $orderData);

            // Items
            $this->printItems($p, $orderData['items'], $paperWidth);

            // Totals
            $this->printTotals($p, $orderData);

            // Payment info
            if (isset($orderData['payment_method'])) {
                $this->printPaymentInfo($p, $orderData);
            }

            // QR Code (if QRIS payment)
            if (isset($orderData['qris_code']) && $orderData['payment_method'] === 'qris') {
                $this->printQrCode($p, $orderData['qris_code']);
            }

            // Footer
            $this->printFooter($p, $orderData);

            // Cut paper
            $p->cut();

            // Close connection
            $p->close();

            return ['success' => true, 'message' => 'Receipt printed successfully'];

        } catch (\Exception $e) {
            Log::error("Print receipt failed: {$e->getMessage()}");

            // Try to close printer if open
            if ($this->printer) {
                try {
                    $this->printer->close();
                } catch (\Exception $e) {
                    // Ignore close errors
                }
            }

            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Print kitchen order ticket
     */
    public function printKitchenTicket(array $orderData): array
    {
        try {
            if (! $this->printer) {
                throw new \Exception('Printer not connected.');
            }

            $p = $this->printer;
            $p->initialize();

            // Kitchen header
            $p->setJustification(Printer::JUSTIFY_CENTER);
            $p->setTextSize(2, 2);
            $p->text("KITCHEN\n");
            $p->setTextSize(1, 1);
            $p->feed();

            // Order info
            $p->setJustification(Printer::JUSTIFY_LEFT);
            $p->setEmphasis(true);
            $p->text('Order #: '.$orderData['order_number']."\n");
            $p->setEmphasis(false);
            $p->text('Table: '.($orderData['table_number'] ?? 'N/A')."\n");
            $p->text('Time: '.date('H:i:s')."\n");
            $p->text('Server: '.($orderData['server'] ?? 'Unknown')."\n");
            $p->feed();

            // Items
            $p->setEmphasis(true);
            $p->text("ITEMS:\n");
            $p->setEmphasis(false);

            foreach ($orderData['items'] as $item) {
                $p->text("\n");
                $p->setEmphasis(true);
                $p->text($item['quantity'].'x '.$item['name']."\n");
                $p->setEmphasis(false);

                // Special instructions
                if (! empty($item['special_instructions'])) {
                    $p->setTextSize(1, 1);
                    $p->text('   Note: '.$item['special_instructions']."\n");
                }

                // Modifiers
                if (! empty($item['modifiers'])) {
                    foreach ($item['modifiers'] as $modifier) {
                        $p->text('   - '.$modifier."\n");
                    }
                }
            }

            $p->feed(2);
            $p->setJustification(Printer::JUSTIFY_CENTER);
            $p->text("--- END ---\n");
            $p->cut();
            $p->close();

            return ['success' => true, 'message' => 'Kitchen ticket printed'];

        } catch (\Exception $e) {
            Log::error("Print kitchen ticket failed: {$e->getMessage()}");

            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Print barcode label
     */
    public function printBarcodeLabel(string $code, string $label = '', string $price = ''): array
    {
        try {
            if (! $this->printer) {
                throw new \Exception('Printer not connected.');
            }

            $p = $this->printer;
            $p->initialize();

            // Product name
            if ($label) {
                $p->setJustification(Printer::JUSTIFY_CENTER);
                $p->text(substr($label, 0, 30)."\n");
            }

            // Barcode
            $p->setJustification(Printer::JUSTIFY_CENTER);
            $p->barcode($code, Printer::BARCODE_CODE128);
            $p->feed();

            // Price
            if ($price) {
                $p->setJustification(Printer::JUSTIFY_CENTER);
                $p->setEmphasis(true);
                $p->text('Rp '.number_format($price, 0, ',', '.')."\n");
                $p->setEmphasis(false);
            }

            $p->cut();
            $p->close();

            return ['success' => true, 'message' => 'Barcode label printed'];

        } catch (\Exception $e) {
            Log::error("Print barcode failed: {$e->getMessage()}");

            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Print QR code
     */
    public function printQrCode(Printer $printer, string $data): void
    {
        try {
            $printer->feed();
            $printer->setJustification(Printer::JUSTIFY_CENTER);

            // Generate QR code
            $printer->qrCode($data, Printer::QR_ECLEVEL_L, 8);
            $printer->feed();

        } catch (\Exception $e) {
            Log::warning("QR code printing failed: {$e->getMessage()}");
            // Continue without QR code
        }
    }

    /**
     * Print test page
     */
    public function printTestPage(): array
    {
        try {
            if (! $this->printer) {
                throw new \Exception('Printer not connected.');
            }

            $p = $this->printer;
            $p->initialize();

            $p->setJustification(Printer::JUSTIFY_CENTER);
            $p->setTextSize(2, 2);
            $p->text("PRINTER TEST\n");
            $p->setTextSize(1, 1);
            $p->feed();

            $p->setJustification(Printer::JUSTIFY_LEFT);
            $p->text('Date: '.date('Y-m-d H:i:s')."\n");
            $p->text("Status: OK\n");
            $p->feed();

            // Test different text styles
            $p->setEmphasis(true);
            $p->text("Bold Text\n");
            $p->setEmphasis(false);

            $p->setUnderline(true);
            $p->text("Underlined Text\n");
            $p->setUnderline(false);

            $p->setDoubleStrike(true);
            $p->text("Double Strike\n");
            $p->setDoubleStrike(false);
            $p->feed();

            // Test barcode
            $p->setJustification(Printer::JUSTIFY_CENTER);
            $p->text("Barcode Test:\n");
            $p->barcode('123456789', Printer::BARCODE_CODE128);
            $p->feed();

            // Test QR code
            $p->text("QR Code Test:\n");
            $p->qrCode('https://example.com', Printer::QR_ECLEVEL_L, 6);
            $p->feed(2);

            $p->cut();
            $p->close();

            return ['success' => true, 'message' => 'Test page printed successfully'];

        } catch (\Exception $e) {
            Log::error("Print test page failed: {$e->getMessage()}");

            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Save receipt to file (for debugging or archive)
     */
    public function saveReceiptToFile(array $orderData, ?string $filename = null): string
    {
        $filename = $filename ?? 'receipt_'.date('Ymd_His').'.txt';

        $content = $this->generateReceiptText($orderData);

        Storage::disk('local')->put("receipts/{$filename}", $content);

        return storage_path("app/receipts/{$filename}");
    }

    // ==================== PRIVATE METHODS ====================

    private function printHeader(Printer $p, array $orderData, int $paperWidth): void
    {
        $p->setJustification(Printer::JUSTIFY_CENTER);

        // Logo or company name
        $companyName = $orderData['company_name'] ?? 'Company Name';
        $p->setTextSize(2, 2);
        $p->text(mb_strimwidth($companyName, 0, $paperWidth === 80 ? 32 : 24, '...')."\n");
        $p->setTextSize(1, 1);

        // Address
        if (! empty($orderData['address'])) {
            $p->text(mb_strimwidth($orderData['address'], 0, $paperWidth === 80 ? 40 : 30, '...')."\n");
        }

        // Phone
        if (! empty($orderData['phone'])) {
            $p->text('Tel: '.$orderData['phone']."\n");
        }

        $p->feed();
    }

    private function printOrderInfo(Printer $p, array $orderData): void
    {
        $p->setJustification(Printer::JUSTIFY_LEFT);
        $p->text('Order #: '.$orderData['order_number']."\n");
        $p->text('Date: '.($orderData['date'] ?? date('Y-m-d H:i:s'))."\n");
        $p->text('Cashier: '.($orderData['cashier'] ?? 'Unknown')."\n");

        if (! empty($orderData['customer_name'])) {
            $p->text('Customer: '.$orderData['customer_name']."\n");
        }

        $p->feed();
        $this->printDivider($p);
        $p->feed();
    }

    private function printItems(Printer $p, array $items, int $paperWidth): void
    {
        $maxNameLength = $paperWidth === 80 ? 20 : 14;
        $maxPriceLength = $paperWidth === 80 ? 12 : 10;

        foreach ($items as $item) {
            $name = mb_strimwidth($item['name'], 0, $maxNameLength, '...');
            $qty = $item['quantity'] ?? 1;
            $price = $item['price'] ?? 0;
            $total = $item['total'] ?? ($qty * $price);

            // Item name and quantity
            $p->text(sprintf("%-{$maxNameLength}s", $name));
            $p->text(sprintf(' %2dx', $qty)."\n");

            // Price per unit and total
            $priceStr = number_format($price, 0, ',', '.');
            $totalStr = number_format($total, 0, ',', '.');

            $p->text('  @'.str_pad($priceStr, 8, ' ', STR_PAD_LEFT));
            $p->text(str_pad($totalStr, $maxPriceLength, ' ', STR_PAD_LEFT)."\n");

            // Modifiers or notes
            if (! empty($item['modifiers'])) {
                foreach ($item['modifiers'] as $modifier) {
                    $p->text('    + '.mb_strimwidth($modifier, 0, $maxNameLength - 4, '...')."\n");
                }
            }

            if (! empty($item['notes'])) {
                $p->text('    Note: '.mb_strimwidth($item['notes'], 0, $maxNameLength - 8, '...')."\n");
            }

            $p->feed();
        }

        $this->printDivider($p);
        $p->feed();
    }

    private function printTotals(Printer $p, array $orderData): void
    {
        $p->setJustification(Printer::JUSTIFY_RIGHT);

        // Subtotal
        if (isset($orderData['subtotal'])) {
            $p->text('Subtotal: ');
            $p->text(number_format($orderData['subtotal'], 0, ',', '.')."\n");
        }

        // Discount
        if (isset($orderData['discount']) && $orderData['discount'] > 0) {
            $p->text('Discount: -');
            $p->text(number_format($orderData['discount'], 0, ',', '.')."\n");
        }

        // Tax
        if (isset($orderData['tax']) && $orderData['tax'] > 0) {
            $p->text('Tax: ');
            $p->text(number_format($orderData['tax'], 0, ',', '.')."\n");
        }

        // Service charge
        if (isset($orderData['service_charge']) && $orderData['service_charge'] > 0) {
            $p->text('Service: ');
            $p->text(number_format($orderData['service_charge'], 0, ',', '.')."\n");
        }

        $p->feed();

        // Grand total
        $p->setEmphasis(true);
        $p->setTextSize(2, 2);
        $p->text('TOTAL: ');
        $p->text(number_format($orderData['grand_total'] ?? $orderData['total'], 0, ',', '.')."\n");
        $p->setTextSize(1, 1);
        $p->setEmphasis(false);

        $p->feed();
        $this->printDivider($p);
        $p->feed();
    }

    private function printPaymentInfo(Printer $p, array $orderData): void
    {
        $p->setJustification(Printer::JUSTIFY_LEFT);
        $p->text('Payment Method: '.strtoupper($orderData['payment_method'])."\n");

        if (isset($orderData['amount_paid'])) {
            $p->text('Amount Paid: Rp '.number_format($orderData['amount_paid'], 0, ',', '.')."\n");
        }

        if (isset($orderData['change'])) {
            $p->text('Change: Rp '.number_format($orderData['change'], 0, ',', '.')."\n");
        }

        if (isset($orderData['reference_number'])) {
            $p->text('Ref: '.$orderData['reference_number']."\n");
        }

        $p->feed();
    }

    private function printFooter(Printer $p, array $orderData): void
    {
        $p->setJustification(Printer::JUSTIFY_CENTER);
        $p->feed();

        // Thank you message
        $p->text("Thank You!\n");
        $p->text("Please Come Again\n");
        $p->feed();

        // Additional info
        if (! empty($orderData['footer_text'])) {
            $p->text($orderData['footer_text']."\n");
            $p->feed();
        }

        // Timestamp
        $p->setTextSize(1, 1);
        $p->text(date('Y-m-d H:i:s')."\n");
    }

    private function printDivider(Printer $p): void
    {
        $p->text(str_repeat('-', 32)."\n");
    }

    private function generateReceiptText(array $orderData): string
    {
        $text = '';
        $text .= str_repeat('=', 40)."\n";
        $text .= str_pad($orderData['company_name'] ?? 'Company', 40, ' ', STR_PAD_BOTH)."\n";
        $text .= str_repeat('=', 40)."\n\n";

        $text .= 'Order #: '.$orderData['order_number']."\n";
        $text .= 'Date: '.($orderData['date'] ?? date('Y-m-d H:i:s'))."\n";
        $text .= 'Cashier: '.($orderData['cashier'] ?? 'Unknown')."\n\n";

        $text .= str_repeat('-', 40)."\n";

        foreach ($orderData['items'] as $item) {
            $text .= $item['name']."\n";
            $text .= '  '.$item['quantity'].' x '.
                number_format($item['price'], 0, ',', '.').' = '.
                number_format($item['total'], 0, ',', '.')."\n";
        }

        $text .= str_repeat('-', 40)."\n";
        $text .= 'TOTAL: Rp '.number_format($orderData['grand_total'] ?? $orderData['total'], 0, ',', '.')."\n\n";
        $text .= 'Payment: '.strtoupper($orderData['payment_method'] ?? 'CASH')."\n";
        $text .= str_repeat('=', 40)."\n";
        $text .= "Thank You!\n";

        return $text;
    }

    /**
     * Disconnect printer
     */
    public function disconnect(): void
    {
        if ($this->printer) {
            try {
                $this->printer->close();
            } catch (\Exception $e) {
                Log::warning("Printer disconnect error: {$e->getMessage()}");
            }
            $this->printer = null;
            $this->connector = null;
        }
    }

    /**
     * Get printer status
     */
    public function getStatus(): array
    {
        return [
            'connected' => $this->printer !== null,
            'has_connector' => $this->connector !== null,
        ];
    }
}
