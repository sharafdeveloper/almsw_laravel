<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Inventory extends Model
{
    use HasFactory;

    protected $table = 'inventories';

    protected $fillable = [
        'product_id',
        'quantity',
        'price',
        'weight',
        'avg_price',
    ];

    protected $casts = [
        'quantity'  => 'decimal:2',
        'price'     => 'decimal:2',
        'weight'    => 'decimal:2',
        'avg_price' => 'decimal:2',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Weighted average cost:
     *   avg_price = (sum of all purchase amounts) / (sum of all purchase weights)
     * Computed from purchase_invoice_items so it always reflects true cost.
     */
    public function recalcWeightedAvg(): void
    {
        $agg = PurchaseInvoiceItem::where('product_id', $this->product_id)
            ->selectRaw('COALESCE(SUM(amount),0) as total_amount, COALESCE(SUM(weight),0) as total_weight')
            ->first();

        $totalAmount = (float) ($agg->total_amount ?? 0);
        $totalWeight = (float) ($agg->total_weight ?? 0);

        $this->avg_price = $totalWeight > 0 ? round($totalAmount / $totalWeight, 2) : 0;
        $this->save();
    }
}
