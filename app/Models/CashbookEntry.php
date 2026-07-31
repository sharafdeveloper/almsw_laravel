<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CashbookEntry extends Model
{
    use HasFactory;

    protected $fillable = [
        'entry_date',
        'type',
        'amount',
        'description',
        'source_type',
        'source_id',
    ];

    protected $casts = [
        'entry_date' => 'date',
        'amount'     => 'decimal:2',
    ];

    public function source()
    {
        return $this->morphTo();
    }
}
