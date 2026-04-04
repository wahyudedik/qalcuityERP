/**
 * POS Printer Integration
 * Supports both ESC/POS thermal printers and browser print fallback
 */

class PosPrinter {
    constructor(options = {}) {
        this.apiBaseUrl = options.apiBaseUrl || '/api';
        this.csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
        this.printMethod = options.method || 'auto'; // 'thermal', 'browser', 'auto'
    }

    /**
     * Print receipt via thermal printer or browser fallback
     */
    async printReceipt(orderData) {
        if (this.printMethod === 'thermal' || this.printMethod === 'auto') {
            try {
                const result = await this.printViaThermal(orderData.id);

                if (result.success) {
                    console.log('Receipt printed via thermal printer');
                    return result;
                }

                // If thermal fails and method is auto, fallback to browser
                if (this.printMethod === 'auto') {
                    console.warn('Thermal print failed, falling back to browser print');
                    return this.printViaBrowser(orderData);
                }

                return result;

            } catch (error) {
                console.error('Thermal print error:', error);

                if (this.printMethod === 'auto') {
                    console.warn('Falling back to browser print');
                    return this.printViaBrowser(orderData);
                }

                throw error;
            }
        } else {
            return this.printViaBrowser(orderData);
        }
    }

    /**
     * Print via thermal printer API
     */
    async printViaThermal(orderId) {
        const response = await fetch(`${this.apiBaseUrl}/pos/print/receipt/${orderId}`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': this.csrfToken,
                'Accept': 'application/json',
            },
        });

        return await response.json();
    }

    /**
     * Print via browser (fallback method)
     */
    printViaBrowser(orderData) {
        return new Promise((resolve, reject) => {
            try {
                const printWindow = window.open('', '_blank', 'width=400,height=600');

                if (!printWindow) {
                    reject(new Error('Popup blocked. Please allow popups for this site.'));
                    return;
                }

                const receiptHTML = this.generateReceiptHTML(orderData);
                printWindow.document.write(receiptHTML);
                printWindow.document.close();

                // Wait for content to load, then print
                printWindow.onload = () => {
                    setTimeout(() => {
                        printWindow.print();
                        printWindow.onafterprint = () => {
                            printWindow.close();
                            resolve({ success: true, method: 'browser' });
                        };
                    }, 250);
                };

            } catch (error) {
                reject(error);
            }
        });
    }

    /**
     * Generate receipt HTML for browser printing
     */
    generateReceiptHTML(orderData) {
        const items = orderData.items || [];
        const config = orderData.config || {};

        return `
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Receipt - ${orderData.order_number}</title>
    <style>
        @media print {
            @page {
                size: 80mm auto;
                margin: 0;
            }
            
            body {
                width: 80mm;
                font-family: 'Courier New', Courier, monospace;
                font-size: 12px;
                margin: 0;
                padding: 5mm;
                line-height: 1.4;
            }
            
            .receipt-header {
                text-align: center;
                margin-bottom: 10px;
            }
            
            .company-name {
                font-size: 16px;
                font-weight: bold;
                margin-bottom: 5px;
            }
            
            .company-info {
                font-size: 10px;
                margin-bottom: 3px;
            }
            
            .divider {
                border-top: 1px dashed #000;
                margin: 8px 0;
            }
            
            .order-info {
                margin-bottom: 8px;
            }
            
            .order-info div {
                margin-bottom: 2px;
            }
            
            .items-table {
                width: 100%;
                margin-bottom: 8px;
            }
            
            .item-row {
                margin-bottom: 5px;
            }
            
            .item-name {
                font-weight: bold;
            }
            
            .item-details {
                display: flex;
                justify-content: space-between;
                font-size: 11px;
            }
            
            .totals {
                margin-top: 8px;
            }
            
            .total-row {
                display: flex;
                justify-content: space-between;
                margin-bottom: 3px;
            }
            
            .grand-total {
                font-size: 14px;
                font-weight: bold;
                margin-top: 5px;
                padding-top: 5px;
                border-top: 1px dashed #000;
            }
            
            .payment-info {
                margin-top: 8px;
                font-size: 11px;
            }
            
            .footer {
                text-align: center;
                margin-top: 15px;
                font-size: 11px;
            }
            
            .qr-code {
                text-align: center;
                margin: 10px 0;
            }
            
            .qr-code img {
                max-width: 150px;
            }
            
            .timestamp {
                text-align: center;
                font-size: 9px;
                margin-top: 10px;
                color: #666;
            }
        }
        
        @media screen {
            body {
                max-width: 400px;
                margin: 20px auto;
                font-family: 'Courier New', Courier, monospace;
                font-size: 14px;
                padding: 20px;
                background: #f5f5f5;
            }
            
            .print-button {
                position: fixed;
                top: 20px;
                right: 20px;
                padding: 10px 20px;
                background: #007bff;
                color: white;
                border: none;
                border-radius: 5px;
                cursor: pointer;
                font-size: 14px;
            }
            
            .print-button:hover {
                background: #0056b3;
            }
        }
    </style>
</head>
<body>
    <button class="print-button" onclick="window.print()">🖨️ Print</button>
    
    <div class="receipt-header">
        <div class="company-name">${config.company_name || 'Company Name'}</div>
        <div class="company-info">${config.address || ''}</div>
        <div class="company-info">Tel: ${config.phone || ''}</div>
    </div>
    
    <div class="divider"></div>
    
    <div class="order-info">
        <div><strong>Order #:</strong> ${orderData.order_number}</div>
        <div><strong>Date:</strong> ${orderData.date || new Date().toLocaleString()}</div>
        <div><strong>Cashier:</strong> ${orderData.cashier || 'Unknown'}</div>
        ${orderData.customer_name ? `<div><strong>Customer:</strong> ${orderData.customer_name}</div>` : ''}
    </div>
    
    <div class="divider"></div>
    
    <div class="items">
        ${items.map(item => `
            <div class="item-row">
                <div class="item-name">${item.name}</div>
                <div class="item-details">
                    <span>${item.quantity} x ${this.formatCurrency(item.price)}</span>
                    <span>${this.formatCurrency(item.total)}</span>
                </div>
                ${item.notes ? `<div style="font-size: 10px; margin-left: 10px;">Note: ${item.notes}</div>` : ''}
            </div>
        `).join('')}
    </div>
    
    <div class="divider"></div>
    
    <div class="totals">
        <div class="total-row">
            <span>Subtotal:</span>
            <span>${this.formatCurrency(orderData.subtotal)}</span>
        </div>
        ${orderData.discount > 0 ? `
        <div class="total-row">
            <span>Discount:</span>
            <span>-${this.formatCurrency(orderData.discount)}</span>
        </div>
        ` : ''}
        ${orderData.tax > 0 ? `
        <div class="total-row">
            <span>Tax:</span>
            <span>${this.formatCurrency(orderData.tax)}</span>
        </div>
        ` : ''}
        ${orderData.service_charge > 0 ? `
        <div class="total-row">
            <span>Service:</span>
            <span>${this.formatCurrency(orderData.service_charge)}</span>
        </div>
        ` : ''}
        <div class="total-row grand-total">
            <span>TOTAL:</span>
            <span>${this.formatCurrency(orderData.grand_total)}</span>
        </div>
    </div>
    
    <div class="divider"></div>
    
    <div class="payment-info">
        <div><strong>Payment:</strong> ${(orderData.payment_method || 'CASH').toUpperCase()}</div>
        ${orderData.amount_paid ? `
        <div><strong>Paid:</strong> ${this.formatCurrency(orderData.amount_paid)}</div>
        <div><strong>Change:</strong> ${this.formatCurrency(orderData.change || 0)}</div>
        ` : ''}
        ${orderData.reference_number ? `<div><strong>Ref:</strong> ${orderData.reference_number}</div>` : ''}
    </div>
    
    ${orderData.qris_code ? `
    <div class="qr-code">
        <img src="${orderData.qris_code}" alt="QR Code">
    </div>
    ` : ''}
    
    <div class="footer">
        <div style="font-size: 14px; margin-bottom: 5px;">Thank You!</div>
        <div>${config.footer_text || 'Please Come Again'}</div>
    </div>
    
    <div class="timestamp">
        ${new Date().toISOString()}
    </div>
    
    <script>
        // Auto-print when page loads
        window.addEventListener('load', function() {
            // Uncomment the line below to auto-print
            // window.print();
        });
    </script>
</body>
</html>
        `;
    }

    /**
     * Print kitchen ticket
     */
    async printKitchenTicket(orderId) {
        const response = await fetch(`${this.apiBaseUrl}/pos/print/kitchen/${orderId}`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': this.csrfToken,
                'Accept': 'application/json',
            },
        });

        return await response.json();
    }

    /**
     * Print barcode label
     */
    async printBarcodeLabel(code, label = '', price = '') {
        const response = await fetch(`${this.apiBaseUrl}/pos/print/barcode`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': this.csrfToken,
                'Accept': 'application/json',
            },
            body: JSON.stringify({ code, label, price }),
        });

        return await response.json();
    }

    /**
     * Test printer connection
     */
    async testPrinter(printerType, printerDestination) {
        const response = await fetch(`${this.apiBaseUrl}/pos/print/test`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': this.csrfToken,
                'Accept': 'application/json',
            },
            body: JSON.stringify({
                printer_type: printerType,
                printer_destination: printerDestination,
            }),
        });

        return await response.json();
    }

    /**
     * Get print queue status
     */
    async getPrintQueue(status = null, limit = 50) {
        let url = `${this.apiBaseUrl}/pos/print/queue?limit=${limit}`;
        if (status) {
            url += `&status=${status}`;
        }

        const response = await fetch(url, {
            headers: {
                'Accept': 'application/json',
            },
        });

        return await response.json();
    }

    /**
     * Format currency
     */
    formatCurrency(amount) {
        return 'Rp ' + Number(amount).toLocaleString('id-ID');
    }
}

// Export for use in other files
if (typeof module !== 'undefined' && module.exports) {
    module.exports = PosPrinter;
}
