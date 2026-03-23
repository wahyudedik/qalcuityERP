<?php

namespace App\Services;

use App\DTOs\ChainNodeDTO;
use App\DTOs\TransactionChainDTO;
use App\Models\DeliveryOrder;
use App\Models\GoodsReceipt;
use App\Models\Invoice;
use App\Models\JournalEntry;
use App\Models\Payable;
use App\Models\Payment;
use App\Models\PurchaseOrder;
use App\Models\Quotation;
use App\Models\SalesOrder;
use Illuminate\Database\Eloquent\Model;

class TransactionChainService
{
    /**
     * Bangun rantai lengkap dari sebuah dokumen (upstream + downstream).
     *
     * @param string $modelClass  Fully-qualified class name (e.g. Invoice::class)
     * @param int    $modelId
     * @return TransactionChainDTO
     */
    public function buildChain(string $modelClass, int $modelId): TransactionChainDTO
    {
        $model      = $modelClass::findOrFail($modelId);
        $type       = $this->getTypeKey($modelClass);
        $current    = $this->toNode($model, $type);
        $upstream   = $this->resolveUpstream($type, $model);
        $downstream = $this->resolveDownstream($type, $model);

        return new TransactionChainDTO(
            current:    $current,
            upstream:   $upstream,
            downstream: $downstream,
            all:        array_merge($upstream, [$current], $downstream),
        );
    }

    /**
     * Resolusi upstream: temukan dokumen-dokumen yang mendahului dokumen ini.
     */
    private function resolveUpstream(string $type, Model $model): array
    {
        $nodes = [];

        switch ($type) {
            case 'sales_order':
                if ($model->quotation_id && $model->quotation) {
                    $nodes[] = $this->toNode($model->quotation, 'quotation');
                }
                break;

            case 'delivery_order':
            case 'invoice':
                if ($model->sales_order_id && $model->salesOrder) {
                    $so = $model->salesOrder;
                    if ($so->quotation_id && $so->quotation) {
                        $nodes[] = $this->toNode($so->quotation, 'quotation');
                    }
                    $nodes[] = $this->toNode($so, 'sales_order');
                }
                break;

            case 'payment':
                $payable = $model->payable;
                if ($payable instanceof Invoice) {
                    $nodes = array_merge($nodes, $this->resolveUpstream('invoice', $payable));
                    $nodes[] = $this->toNode($payable, 'invoice');
                } elseif ($payable instanceof Payable) {
                    $nodes = array_merge($nodes, $this->resolveUpstream('payable', $payable));
                    $nodes[] = $this->toNode($payable, 'payable');
                }
                break;

            case 'journal_entry':
                if ($model->reference_type && $model->reference_id) {
                    $refClass = $this->resolveReferenceClass($model->reference_type);
                    if ($refClass) {
                        $ref = $refClass::find($model->reference_id);
                        if ($ref) {
                            $nodes = array_merge($nodes, $this->resolveUpstream($model->reference_type, $ref));
                            $nodes[] = $this->toNode($ref, $model->reference_type);
                        }
                    }
                }
                break;

            case 'goods_receipt':
            case 'payable':
                if ($model->purchase_order_id && $model->purchaseOrder) {
                    $nodes[] = $this->toNode($model->purchaseOrder, 'purchase_order');
                }
                break;
        }

        return $nodes;
    }

    /**
     * Resolusi downstream: temukan dokumen-dokumen yang mengikuti dokumen ini.
     */
    private function resolveDownstream(string $type, Model $model): array
    {
        $nodes = [];

        switch ($type) {
            case 'quotation':
                foreach ($model->salesOrders as $so) {
                    $nodes[] = $this->toNode($so, 'sales_order');
                    $nodes   = array_merge($nodes, $this->resolveDownstream('sales_order', $so));
                }
                break;

            case 'sales_order':
                foreach ($model->deliveryOrders as $do) {
                    $nodes[] = $this->toNode($do, 'delivery_order');
                }
                foreach ($model->invoice as $inv) {
                    $nodes[] = $this->toNode($inv, 'invoice');
                    $nodes   = array_merge($nodes, $this->resolveDownstream('invoice', $inv));
                }
                break;

            case 'invoice':
                foreach ($model->payments as $payment) {
                    $nodes[] = $this->toNode($payment, 'payment');
                    $nodes   = array_merge($nodes, $this->resolveDownstream('payment', $payment));
                }
                // GL Posting langsung dari invoice
                $jes = JournalEntry::where('reference_type', 'invoice')
                    ->where('reference_id', $model->id)
                    ->get();
                foreach ($jes as $je) {
                    $nodes[] = $this->toNode($je, 'journal_entry');
                }
                break;

            case 'payment':
                $jes = JournalEntry::where('reference_type', 'payment')
                    ->where('reference_id', $model->id)
                    ->get();
                foreach ($jes as $je) {
                    $nodes[] = $this->toNode($je, 'journal_entry');
                }
                break;

            case 'purchase_order':
                foreach ($model->goodsReceipts as $gr) {
                    $nodes[] = $this->toNode($gr, 'goods_receipt');
                }
                foreach ($model->payable as $payable) {
                    $nodes[] = $this->toNode($payable, 'payable');
                    $nodes   = array_merge($nodes, $this->resolveDownstream('payable', $payable));
                }
                break;

            case 'payable':
                foreach ($model->payments as $payment) {
                    $nodes[] = $this->toNode($payment, 'payment');
                    $nodes   = array_merge($nodes, $this->resolveDownstream('payment', $payment));
                }
                break;
        }

        return $nodes;
    }

    /**
     * Konversi model Eloquent ke ChainNodeDTO.
     */
    private function toNode(Model $model, string $type): ChainNodeDTO
    {
        $date = $model->date
            ?? $model->payment_date
            ?? $model->delivery_date
            ?? $model->receipt_date
            ?? $model->due_date
            ?? $model->created_at;

        return new ChainNodeDTO(
            type:   $type,
            id:     $model->id,
            number: $model->number ?? ('#' . $model->id),
            date:   $date?->toDateString() ?? now()->toDateString(),
            status: $model->status ?? $model->posting_status ?? 'unknown',
            amount: (float) ($model->total ?? $model->total_amount ?? $model->amount ?? 0),
            url:    $this->resolveUrl($type, $model->id),
        );
    }

    /**
     * Hasilkan URL route untuk setiap tipe dokumen.
     */
    private function resolveUrl(string $type, int $id): string
    {
        return match ($type) {
            'quotation'      => route('quotations.show', $id),
            'sales_order'    => route('sales.show', $id),
            'delivery_order' => route('delivery-orders.index'),
            'invoice'        => route('invoices.show', $id),
            'payment'        => route('invoices.index'),
            'journal_entry'  => route('journals.show', $id),
            'purchase_order' => route('purchasing.orders'),
            'goods_receipt'  => route('purchasing.goods-receipts'),
            'payable'        => route('payables.index'),
            default          => '#',
        };
    }

    /**
     * Petakan class name ke string type.
     */
    private function getTypeKey(string $modelClass): string
    {
        return match ($modelClass) {
            Quotation::class     => 'quotation',
            SalesOrder::class    => 'sales_order',
            DeliveryOrder::class => 'delivery_order',
            Invoice::class       => 'invoice',
            Payment::class       => 'payment',
            JournalEntry::class  => 'journal_entry',
            PurchaseOrder::class => 'purchase_order',
            GoodsReceipt::class  => 'goods_receipt',
            Payable::class       => 'payable',
            default              => class_basename($modelClass),
        };
    }

    /**
     * Resolve fully-qualified class dari reference_type string.
     */
    private function resolveReferenceClass(string $referenceType): ?string
    {
        return match ($referenceType) {
            'invoice'        => Invoice::class,
            'payment'        => Payment::class,
            'payable'        => Payable::class,
            'sales_order'    => SalesOrder::class,
            'purchase_order' => PurchaseOrder::class,
            'goods_receipt'  => GoodsReceipt::class,
            default          => null,
        };
    }
}
