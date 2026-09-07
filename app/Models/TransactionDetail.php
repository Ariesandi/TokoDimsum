<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class TransactionDetail extends Model
{
    use HasFactory;

    protected $fillable = [
        'transaction_id',
        'product_id',
        'quantity',
        'unit_price',
        'subtotal',
        'selected_toppings',
    ];

    protected $casts = [
        'unit_price' => 'decimal:2',
        'subtotal' => 'decimal:2',
        'selected_toppings' => 'array',
    ];

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        static::saving(function ($detail) {
            $detail->subtotal = $detail->quantity * $detail->unit_price;
        });
    }

    /**
     * Get the transaction that owns the transaction detail.
     */
    public function transaction()
    {
        return $this->belongsTo(Transaction::class);
    }

    /**
     * Get the product that owns the transaction detail.
     */
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get formatted toppings.
     */
    public function getFormattedToppingsAttribute()
    {
        if (empty($this->selected_toppings)) {
            return 'Tanpa topping';
        }
        
        return implode(', ', $this->selected_toppings);
    }
}
