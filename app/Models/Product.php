<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'category_id',
        'name',
        'description',
        'price',
        'stock',
        'image',
        'toppings',
        'is_active',
        'is_featured',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'toppings' => 'array',
        'is_active' => 'boolean',
        'is_featured' => 'boolean',
    ];

    /**
     * Get the category that owns the product.
     */
    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Get the transaction details for the product.
     */
    public function transactionDetails()
    {
        return $this->hasMany(TransactionDetail::class);
    }

    /**
     * Check if product is in stock.
     */
    public function inStock()
    {
        return $this->stock > 0;
    }

    /**
     * Decrease stock.
     */
    public function decreaseStock($quantity)
    {
        $this->stock -= $quantity;
        $this->save();
    }

    /**
     * Provide a safe string representation to prevent accidental JSON dump in Blade
     * when the entire model is echoed (e.g., `{{ $detail->product }}`).
     */
    public function __toString(): string
    {
        try {
            $id = $this->id ?? '';
            $name = $this->name ?? null;
            return $name ? (string) $name : ('Product' . ($id ? " #{$id}" : ''));
        } catch (\Throwable $e) {
            return 'Product';
        }
    }
}
