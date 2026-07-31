<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'is_deleted',
    ];

    protected $casts = [
        'is_deleted' => 'boolean',
    ];

    /**
     * Scope to only not-deleted products.
     */
    public function scopeActive($query)
    {
        return $query->where('is_deleted', false);
    }

    /**
     * Soft-delete the product using is_deleted flag.
     */
    public function softDelete(): bool
    {
        $this->is_deleted = true;
        return $this->save();
    }

    public function inventory()
    {
        return $this->hasOne(Inventory::class);
    }
}
