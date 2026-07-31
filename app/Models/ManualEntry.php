<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ManualEntry extends Model
{
    protected $fillable = ['entry_type', 'name', 'amount', 'entry_date', 'notes'];

    protected $casts = [
        'entry_date' => 'date',
        'amount'     => 'decimal:2',
    ];

    public const TYPE_OTHER_ASSET     = 'other_asset';
    public const TYPE_OTHER_LIABILITY = 'other_liability';
    public const TYPE_DRAWING         = 'drawing';
    public const TYPE_OTHER_EXPENSE   = 'other_expense';
    public const TYPE_OTHER_REVENUE   = 'other_revenue';
}
