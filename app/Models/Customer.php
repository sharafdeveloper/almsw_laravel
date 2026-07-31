<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    use HasFactory;

    protected $fillable = ['name','phone_number', 'city', 'opening_balance', 'is_deleted'];

    protected $casts = [
        'is_deleted' => 'boolean',
        'opening_balance' => 'decimal:2',
    ];

    public function scopeActive($query)
    {
        return $query->where('is_deleted', false);
    }

    public function saleInvoices()
    {
        return $this->hasMany(SaleInvoice::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function purchaseInvoices()
    {
        return $this->hasMany(PurchaseInvoice::class, 'supplier_id');
    }

    /**
     * Build the ledger (chronological) for this customer.
     * Each entry: ['date','bill_id','description','credit','debit','balance']
     * Sale Invoice -> Debit (customer owes us)
     * Payment received -> Credit (customer pays)
     * Purchase Invoice (supplier) -> Credit (we owe supplier)
     */
    public function buildLedger(?string $from = null, ?string $to = null): array
    {
        $entries = collect();

        foreach ($this->saleInvoices()->with('items.product')->orderBy('bill_date')->orderBy('id')->get() as $inv) {
            $products = $inv->items->map(fn ($it) => optional($it->product)->name)->filter()->unique()->implode(', ');
            $invoiceDescription = trim((string) $inv->description);
            $description = $products !== ''
                ? ($invoiceDescription !== '' ? $products . ' - ' . $invoiceDescription : $products)
                : ($invoiceDescription !== '' ? $invoiceDescription : 'Sale Invoice');
            $seq = optional($inv->created_at)->format('Y-m-d H:i:s.u') ?? $inv->bill_date->toDateString();

            // Row 1: the sale itself -> Debit only (no credit in same row).
            $entries->push([
                'date'         => $inv->bill_date->toDateString(),
                'bill_id'      => 'SI-' . str_pad($inv->id, 5, '0', STR_PAD_LEFT),
                'description'  => $description,
                'debit'        => (float) $inv->total,
                'credit'       => 0,
                'seq'          => $seq,
                'sub'          => 0,
                'ref_type'     => 'sale',
                'ref_id'       => $inv->id,
            ]);

            // Row 2 (separate): cash received against this invoice -> Credit only.
            if ((float) $inv->cash_received > 0) {
                $entries->push([
                    'date'         => $inv->bill_date->toDateString(),
                    'bill_id'      => 'SI-' . str_pad($inv->id, 5, '0', STR_PAD_LEFT),
                    'description'  => 'Cash received against SI-' . str_pad($inv->id, 5, '0', STR_PAD_LEFT),
                    'debit'        => 0,
                    'credit'       => (float) $inv->cash_received,
                    'seq'          => $seq,
                    'sub'          => 1,
                    'ref_type'     => 'sale',
                    'ref_id'       => $inv->id,
                ]);
            }
        }

        foreach ($this->payments()->orderBy('payment_date')->orderBy('id')->get() as $pay) {
            $method = trim((string) ($pay->method ?? ''));
            // If a method like "Online Transfer" is present use it as prefix,
            // otherwise fall back to the old "Payment received/paid" wording.
            if ($method !== '') {
                $descPrefix = $method;
            } else {
                $descPrefix = 'Payment ' . $pay->type;
            }

            $entries->push([
                'date'         => $pay->payment_date->toDateString(),
                'bill_id'      => 'PAY-' . str_pad($pay->id, 5, '0', STR_PAD_LEFT),
                'description'  => $descPrefix . ($pay->description ? ' - ' . $pay->description : ''),
                'debit'        => $pay->type === 'paid' ? (float) $pay->amount : 0,
                'credit'       => $pay->type === 'received' ? (float) $pay->amount : 0,
                'seq'          => optional($pay->created_at)->format('Y-m-d H:i:s.u') ?? $pay->payment_date->toDateString(),
                'sub'          => 0,
                'ref_type'     => 'payment',
                'ref_id'       => $pay->id,
            ]);
        }

        foreach ($this->purchaseInvoices()->with('items.product')->orderBy('bill_date')->orderBy('id')->get() as $pi) {
            $products = $pi->items->map(fn ($it) => optional($it->product)->name)->filter()->unique()->implode(', ');
            $invoiceDescription = trim((string) $pi->description);
            $description = $products !== ''
                ? ($invoiceDescription !== '' ? $products . ' - ' . $invoiceDescription : $products)
                : ($invoiceDescription !== '' ? $invoiceDescription : 'Purchase Invoice');
            $entries->push([
                'date'         => $pi->bill_date->toDateString(),
                'bill_id'      => 'PI-' . str_pad($pi->id, 5, '0', STR_PAD_LEFT),
                'description'  => $description,
                'debit'        => 0,
                'credit'       => (float) $pi->total_amount,
                'seq'          => optional($pi->created_at)->format('Y-m-d H:i:s.u') ?? $pi->bill_date->toDateString(),
                'sub'          => 0,
                'ref_type'     => 'purchase',
                'ref_id'       => $pi->id,
            ]);
        }

        // Chronological: by transaction date, then creation time (seq), then sub-order
        // (so a sale's "cash received" row appears right under the sale row).
        $sorted = $entries->sort(function ($a, $b) {
            return [$a['date'], $a['seq'], $a['sub']] <=> [$b['date'], $b['seq'], $b['sub']];
        })->values();

        $balance = (float) $this->opening_balance;
        $rows = [];
        foreach ($sorted as $e) {
            $balance = $balance + (float) $e['debit'] - (float) $e['credit'];
            $rows[] = array_merge($e, ['balance' => round($balance, 2)]);
        }

       // NAYA - sirf rows filter hon, balance bilkul touch na ho
        if ($from || $to) {
            $rows = array_values(array_filter($rows, function ($r) use ($from, $to) {
                if ($from && $r['date'] < $from) return false;
                if ($to && $r['date'] > $to) return false;
                return true;
            }));
        }

        return $rows;
    }
}
