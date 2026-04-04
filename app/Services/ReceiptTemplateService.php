<?php

namespace App\Services;

use App\Models\SalesOrder;
use Mike42\Escpos\Printer;
use Mike42\Escpos\PrintConnectors\FilePrintConnector;
use Mike42\Escpos\PrintConnectors\NetworkPrintConnector;
use Mike42\Escpos\PrintConnectors\WindowsPrintConnector;
use Illuminate\Support\Facades\Log;

class ReceiptTemplateService
{
    protected Printer $printer;
    protected int $paperWidth; // 58 or 80 mm

    public function __construct(Printer $printer, int $paperWidth = 80)
    {
        $this->printer = $printer;
        $this->paperWidth = $paperWidth;
    }

    /**
     * Print sales receipt optimized for thermal printer
     */
    public function printSalesReceipt(SalesOrder $order): array
    {
        try {
            $p = $this->printer;

            // Initialize
            $p->initialize();

            // Print header
            $this->printHeader($order);

            // Print order info
            $this->printOrderInfo($order);

            // Print items
            $this->printItems($order);

            // Print totals
            $this->printTotals($order);

            // Print payment info
            $this->printPaymentInfo($order);

            // Print footer
            $this->printFooter();

            // Cut paper
            $p->cut();
            $p->close();

            return ['success' => true, 'message' => 'Receipt printed successfully'];

        } catch (\Exception $e) {
            Log::error("Receipt printing failed: {$e->getMessage()}");
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Print kitchen ticket (for F&B orders)
     */
    public function printKitchenTicket(array $ticketData): array
    {
        try {
            $p = $this->printer;

            $p->initialize();

            // Header
            $p->setJustification(Printer::JUSTIFY_CENTER);
            $p->setTextSize(1, 1);
            $p->text("KITCHEN ORDER\n");
            $p->setTextSize(1, 1);

            // Table/Room info
            if (isset($ticketData['table_number'])) {
                $p->setTextSize(2, 2);
                $p->text("Table: {$ticketData['table_number']}\n");
            }

            if (isset($ticketData['room_number'])) {
                $p->setTextSize(2, 2);
                $p->text("Room: {$ticketData['room_number']}\n");
            }

            $p->setTextSize(1, 1);
            $p->text(str_repeat('-', $this->getLineWidth()) . "\n");

            // Order number and time
            $p->setJustification(Printer::JUSTIFY_LEFT);
            $p->text("Order #: {$ticketData['order_number']}\n");
            $p->text("Time: " . date('H:i:s') . "\n");
            $p->text(str_repeat('-', $this->getLineWidth()) . "\n");

            // Items
            foreach ($ticketData['items'] as $item) {
                $p->setEmphasis(true);
                $p->text("{$item['name']} x{$item['quantity']}\n");
                $p->setEmphasis(false);

                if (!empty($item['notes'])) {
                    $p->text("   Note: {$item['notes']}\n");
                }

                if (!empty($item['modifiers'])) {
                    foreach ($item['modifiers'] as $modifier) {
                        $p->text("   - {$modifier}\n");
                    }
                }

                $p->text("\n");
            }

            // Special instructions
            if (!empty($ticketData['special_instructions'])) {
                $p->setJustification(Printer::JUSTIFY_CENTER);
                $p->setEmphasis(true);
                $p->text("*** SPECIAL INSTRUCTIONS ***\n");
                $p->setEmphasis(false);
                $p->text("{$ticketData['special_instructions']}\n");
                $p->text(str_repeat('-', $this->getLineWidth()) . "\n");
            }

            $p->cut();
            $p->close();

            return ['success' => true, 'message' => 'Kitchen ticket printed'];

        } catch (\Exception $e) {
            Log::error("Kitchen ticket printing failed: {$e->getMessage()}");
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Print barcode label
     */
    public function printBarcodeLabel(string $barcode, string $productName, float $price): array
    {
        try {
            $p = $this->printer;

            $p->initialize();
            $p->setJustification(Printer::JUSTIFY_CENTER);

            // Product name (truncated if too long)
            $name = strlen($productName) > 20 ? substr($productName, 0, 17) . '...' : $productName;
            $p->text($name . "\n");

            // Price
            $p->setTextSize(1, 1);
            $p->text("Rp " . number_format($price, 0, ',', '.') . "\n");

            // Barcode
            $p->setBarcodeHeight(40);
            $p->setBarcodeWidth(2);
            $p->barcode($barcode, Printer::BARCODE_CODE128);

            $p->feed(2);
            $p->cut();
            $p->close();

            return ['success' => true, 'message' => 'Barcode label printed'];

        } catch (\Exception $e) {
            Log::error("Barcode label printing failed: {$e->getMessage()}");
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Print QR code on receipt
     */
    public function printQrCode(string $qrData): void
    {
        try {
            $p = $this->printer;

            // QR code size depends on paper width
            $size = $this->paperWidth === 58 ? 4 : 6;

            $p->setJustification(Printer::JUSTIFY_CENTER);
            $p->qrCode($qrData, Printer::QR_ECLEVEL_L, $size);
            $p->feed(1);

        } catch (\Exception $e) {
            Log::warning("QR code printing failed: {$e->getMessage()}");
        }
    }

    /**
     * Print header section
     */
    private function printHeader(SalesOrder $order): void
    {
        $p = $this->printer;

        // Company logo (if configured)
        if (config('brand.receipt.show_logo')) {
            try {
                $logoPath = config('brand.logo.url');
                if (file_exists(public_path($logoPath))) {
                    $logo = \Mike42\Escpos\ImagickEscposImage::load(public_path($logoPath));
                    $p->bitImage($logo);
                    $p->feed(1);
                }
            } catch (\Exception $e) {
                Log::warning("Logo printing failed: {$e->getMessage()}");
            }
        }

        // Company name
        $p->setJustification(Printer::JUSTIFY_CENTER);
        $p->setTextSize(1, 1);
        $p->setEmphasis(true);
        $p->text(config('app.name', 'Qalcuity ERP') . "\n");
        $p->setEmphasis(false);

        // Address
        $address = config('pos_printer.receipt.address', '');
        if ($address) {
            $p->text($address . "\n");
        }

        // Phone
        $phone = config('pos_printer.receipt.phone', '');
        if ($phone) {
            $p->text("Tel: {$phone}\n");
        }

        $p->text(str_repeat('=', $this->getLineWidth()) . "\n");
    }

    /**
     * Print order information
     */
    private function printOrderInfo(SalesOrder $order): void
    {
        $p = $this->printer;

        $p->setJustification(Printer::JUSTIFY_LEFT);
        $p->text("Order #: {$order->number}\n");
        $p->text("Date: " . $order->date->format('d/m/Y H:i') . "\n");

        if ($order->customer) {
            $p->text("Customer: {$order->customer->name}\n");
        }

        $p->text("Cashier: " . ($order->user->name ?? 'N/A') . "\n");
        $p->text(str_repeat('-', $this->getLineWidth()) . "\n");
    }

    /**
     * Print order items
     */
    private function printItems(SalesOrder $order): void
    {
        $p = $this->printer;

        // Column widths based on paper size
        [$nameWidth, $qtyWidth, $priceWidth] = $this->getColumnWidths();

        // Header
        $p->setEmphasis(true);
        $header = str_pad('Item', $nameWidth) .
            str_pad('Qty', $qtyWidth, ' ', STR_PAD_LEFT) .
            str_pad('Price', $priceWidth, ' ', STR_PAD_LEFT);
        $p->text($header . "\n");
        $p->setEmphasis(false);
        $p->text(str_repeat('-', $this->getLineWidth()) . "\n");

        // Items
        foreach ($order->items as $item) {
            // Truncate product name if too long
            $itemName = strlen($item->product->name) > $nameWidth
                ? substr($item->product->name, 0, $nameWidth - 3) . '...'
                : $item->product->name;

            $line = str_pad($itemName, $nameWidth) .
                str_pad($item->quantity, $qtyWidth, ' ', STR_PAD_LEFT) .
                str_pad('Rp ' . number_format($item->total, 0, ',', '.'), $priceWidth, ' ', STR_PAD_LEFT);

            $p->text($line . "\n");
        }

        $p->text(str_repeat('-', $this->getLineWidth()) . "\n");
    }

    /**
     * Print totals section
     */
    private function printTotals(SalesOrder $order): void
    {
        $p = $this->printer;

        $width = $this->getLineWidth();

        // Subtotal
        $p->setJustification(Printer::JUSTIFY_RIGHT);
        $p->text("Subtotal: Rp " . number_format($order->subtotal, 0, ',', '.') . "\n");

        // Discount
        if ($order->discount > 0) {
            $p->text("Discount: -Rp " . number_format($order->discount, 0, ',', '.') . "\n");
        }

        // Tax
        if ($order->tax > 0) {
            $p->text("Tax: Rp " . number_format($order->tax, 0, ',', '.') . "\n");
        }

        // Total
        $p->setEmphasis(true);
        $p->setTextSize(1, 1);
        $p->text(str_repeat('=', $width) . "\n");
        $p->text("TOTAL: Rp " . number_format($order->total, 0, ',', '.') . "\n");
        $p->text(str_repeat('=', $width) . "\n");
        $p->setEmphasis(false);
        $p->setTextSize(1, 1);
    }

    /**
     * Print payment information
     */
    private function printPaymentInfo(SalesOrder $order): void
    {
        $p = $this->printer;

        $p->text("\n");
        $p->setJustification(Printer::JUSTIFY_LEFT);
        $p->text("Payment Method: " . ucfirst($order->payment_method ?? 'N/A') . "\n");

        if ($order->paid_amount) {
            $p->text("Paid: Rp " . number_format($order->paid_amount, 0, ',', '.') . "\n");
        }

        if ($order->change_amount > 0) {
            $p->text("Change: Rp " . number_format($order->change_amount, 0, ',', '.') . "\n");
        }

        $p->text(str_repeat('-', $this->getLineWidth()) . "\n");
    }

    /**
     * Print footer section
     */
    private function printFooter(): void
    {
        $p = $this->printer;

        $p->setJustification(Printer::JUSTIFY_CENTER);

        // Thank you message
        $footerMessage = config('pos_printer.receipt.footer_text', 'Thank you for your purchase!');
        $p->text($footerMessage . "\n");

        // Return policy
        $p->setTextSize(1, 1);
        $p->text("Please keep this receipt\n");
        $p->text("for your records.\n");

        // Website/Social media
        $website = config('app.url', '');
        if ($website) {
            $p->text("\n" . parse_url($website, PHP_URL_HOST) . "\n");
        }

        $p->feed(2);
    }

    /**
     * Get line width based on paper size
     */
    private function getLineWidth(): int
    {
        return $this->paperWidth === 58 ? 32 : 48;
    }

    /**
     * Get column widths for item listing
     */
    private function getColumnWidths(): array
    {
        if ($this->paperWidth === 58) {
            return [16, 4, 10]; // Name, Qty, Price
        }

        return [24, 6, 16]; // Name, Qty, Price
    }

    /**
     * Create printer instance for specific type
     */
    public static function createPrinter(string $type, string $destination, int $paperWidth = 80): ?self
    {
        try {
            $connector = match ($type) {
                'usb' => new WindowsPrintConnector($destination),
                'network' => new NetworkPrintConnector(...explode(':', $destination)),
                'file' => new FilePrintConnector($destination),
                default => throw new \Exception("Unsupported printer type: {$type}"),
            };

            $printer = new Printer($connector);
            return new self($printer, $paperWidth);

        } catch (\Exception $e) {
            Log::error("Failed to create printer: {$e->getMessage()}");
            return null;
        }
    }
}
