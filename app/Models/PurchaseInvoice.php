<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PurchaseInvoice extends Model
{
    use HasFactory;

    protected $fillable = [
        'supplier_id',
        'bill_date',
        'description',
        'total_amount',
    ];

    protected $casts = [
        'bill_date'    => 'date',
        'total_amount' => 'decimal:2',
    ];

    public function supplier()
    {
        return $this->belongsTo(Customer::class, 'supplier_id');
    }

    public function items()
    {
        return $this->hasMany(PurchaseInvoiceItem::class);
    }

    public function formattedId(): string
    {
        return 'PI-' . str_pad($this->id, 5, '0', STR_PAD_LEFT);
    }
}
