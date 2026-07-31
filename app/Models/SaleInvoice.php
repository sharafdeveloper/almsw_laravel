<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SaleInvoice extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_id',
        'bill_date',
        'description',
        'sub_total',
        'labour_cost',
        'loading',
        'total',
        'discount',
        'cash_received',
    ];

    protected $casts = [
        'bill_date'     => 'date',
        'sub_total'     => 'decimal:2',
        'labour_cost'   => 'decimal:2',
        'loading'       => 'decimal:2',
        'total'         => 'decimal:2',
        'cash_received' => 'decimal:2',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function items()
    {
        return $this->hasMany(SaleInvoiceItem::class);
    }

    public function cashbookEntries()
    {
        return $this->morphMany(CashbookEntry::class, 'source', 'source_type', 'source_id');
    }

    public function formattedId(): string
    {
        return 'SI-' . str_pad($this->id, 5, '0', STR_PAD_LEFT);
    }
}
