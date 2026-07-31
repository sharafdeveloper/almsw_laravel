<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SaleInvoiceItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'sale_invoice_id',
        'product_id',
        'rate',
        'quantity',
        'weight',
        'amount',
    ];

    protected $casts = [
        'rate'     => 'decimal:2',
        'quantity' => 'decimal:2',
        'weight'   => 'decimal:2',
        'amount'   => 'decimal:2',
    ];

    public function invoice()
    {
        return $this->belongsTo(SaleInvoice::class, 'sale_invoice_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
