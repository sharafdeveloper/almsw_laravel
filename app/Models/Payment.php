<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_id',
        'payment_date',
        'amount',
        'type',
        'method',
        'description',
    ];

    protected $casts = [
        'payment_date' => 'date',
        'amount'       => 'decimal:2',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function formattedId(): string
    {
        return 'PAY-' . str_pad($this->id, 5, '0', STR_PAD_LEFT);
    }
}
