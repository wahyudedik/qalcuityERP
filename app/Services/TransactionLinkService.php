<?php

namespace App\Services;

use App\Models\TransactionLink;
use Illuminate\Support\Collection;

/**
 * TransactionLinkService — Task 46
 *
 * Mengelola rantai transaksi: SO → DO → Invoice → Payment → GL.
 * Menyediakan full traceability untuk setiap transaksi.
 */
class TransactionLinkService
{
    /**
     * Catat link antara dua transaksi.
     */
    public function link(int $tenantId, object $source, object $target, string $linkType, ?float $amount = null): TransactionLink
    {
        return TransactionLink::link($tenantId, $source, $target, $linkType, $amount);
    }

    /**
     * Ambil full chain untuk sebuah transaksi.
     * Rekursif ke atas (upstream) dan ke bawah (downstream).
     */
    public function getChain(int $tenantId, object $model, int $depth = 0, int $maxDepth = 5): array
    {
        if ($depth >= $maxDepth) return [];

        $type = get_class($model);
        $id   = $model->id;

        $upstream = TransactionLink::where('tenant_id', $tenantId)
            ->where('target_type', $type)
            ->where('target_id', $id)
            ->get();

        $downstream = TransactionLink::where('tenant_id', $tenantId)
            ->where('source_type', $type)
            ->where('source_id', $id)
            ->get();

        return [
            'model'      => $model,
            'type'       => class_basename($type),
            'number'     => $model->number ?? null,
            'upstream'   => $upstream->map(fn($link) => [
                'link'     => $link,
                'label'    => $link->linkTypeLabel(),
                'number'   => $link->source_number,
                'type'     => $link->sourceShortType(),
            ])->values()->toArray(),
            'downstream' => $downstream->map(fn($link) => [
                'link'     => $link,
                'label'    => $link->linkTypeLabel(),
                'number'   => $link->target_number,
                'type'     => $link->targetShortType(),
            ])->values()->toArray(),
        ];
    }

    /**
     * Ambil semua transaksi yang terhubung ke sebuah model (flat list).
     * Berguna untuk menampilkan timeline transaksi.
     */
    public function getTimeline(int $tenantId, object $model): array
    {
        $type = get_class($model);
        $id   = $model->id;

        // Semua link yang melibatkan model ini (sebagai source atau target)
        $links = TransactionLink::where('tenant_id', $tenantId)
            ->where(fn($q) => $q
                ->where(fn($q2) => $q2->where('source_type', $type)->where('source_id', $id))
                ->orWhere(fn($q2) => $q2->where('target_type', $type)->where('target_id', $id))
            )
            ->orderBy('created_at')
            ->get();

        $timeline = [];
        foreach ($links as $link) {
            $isSource = ($link->source_type === $type && $link->source_id === $id);
            $timeline[] = [
                'direction'   => $isSource ? 'outgoing' : 'incoming',
                'link_type'   => $link->link_type,
                'label'       => $link->linkTypeLabel(),
                'other_type'  => $isSource ? $link->targetShortType() : $link->sourceShortType(),
                'other_number'=> $isSource ? $link->target_number : $link->source_number,
                'other_id'    => $isSource ? $link->target_id : $link->source_id,
                'other_model' => $isSource ? $link->target_type : $link->source_type,
                'amount'      => $link->amount,
                'created_at'  => $link->created_at,
            ];
        }

        return $timeline;
    }

    /**
     * Ambil root transaksi (paling awal dalam chain).
     * Berguna untuk "uang ini dari mana".
     */
    public function findRoot(int $tenantId, object $model, int $depth = 0): ?object
    {
        if ($depth > 10) return $model; // prevent infinite loop

        $type = get_class($model);
        $id   = $model->id;

        $upstream = TransactionLink::where('tenant_id', $tenantId)
            ->where('target_type', $type)
            ->where('target_id', $id)
            ->first();

        if (!$upstream) return $model;

        // Resolve source model
        $sourceClass = $upstream->source_type;
        $source = $sourceClass::find($upstream->source_id);
        if (!$source) return $model;

        return $this->findRoot($tenantId, $source, $depth + 1);
    }

    /**
     * Link SO → DO
     */
    public function linkSoToDo(int $tenantId, object $so, object $do): void
    {
        $this->link($tenantId, $so, $do, 'so_to_do');
    }

    /**
     * Link DO → Invoice
     */
    public function linkDoToInvoice(int $tenantId, object $do, object $invoice, float $amount): void
    {
        $this->link($tenantId, $do, $invoice, 'do_to_invoice', $amount);
    }

    /**
     * Link SO → Invoice (langsung, tanpa DO)
     */
    public function linkSoToInvoice(int $tenantId, object $so, object $invoice, float $amount): void
    {
        $this->link($tenantId, $so, $invoice, 'so_to_invoice', $amount);
    }

    /**
     * Link Invoice → Payment
     */
    public function linkInvoiceToPayment(int $tenantId, object $invoice, object $payment, float $amount): void
    {
        $this->link($tenantId, $invoice, $payment, 'invoice_to_payment', $amount);
    }

    /**
     * Link Invoice → GL Journal
     */
    public function linkInvoiceToGl(int $tenantId, object $invoice, object $journal): void
    {
        $this->link($tenantId, $invoice, $journal, 'invoice_to_gl');
    }

    /**
     * Link SO → GL Journal
     */
    public function linkSoToGl(int $tenantId, object $so, object $journal): void
    {
        $this->link($tenantId, $so, $journal, 'so_to_gl');
    }

    /**
     * Link Return → Invoice
     */
    public function linkReturnToInvoice(int $tenantId, object $return, object $invoice, float $amount): void
    {
        $this->link($tenantId, $return, $invoice, 'return_to_invoice', $amount);
    }

    /**
     * Link DP → Invoice
     */
    public function linkDpToInvoice(int $tenantId, object $dp, object $invoice, float $amount): void
    {
        $this->link($tenantId, $dp, $invoice, 'dp_to_invoice', $amount);
    }

    /**
     * Link BulkPayment → Invoice
     */
    public function linkBulkToInvoice(int $tenantId, object $bp, object $invoice, float $amount): void
    {
        $this->link($tenantId, $bp, $invoice, 'bulk_to_invoice', $amount);
    }
}
