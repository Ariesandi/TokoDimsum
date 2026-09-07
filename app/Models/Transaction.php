<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;

class Transaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'transaction_code',
        'user_id',
        'customer_name',
        'customer_email',
        'total_amount',
        'status',
        'payment_method',
        'payment_proof',
        'payment_status',
        'payment_notes',
        'payment_verified_at',
        'tracking_number',
        'notes',
        'customer_address',
        'customer_phone',
    ];

    protected $casts = [
        'total_amount' => 'decimal:2',
        'payment_verified_at' => 'datetime',
    ];

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($transaction) {
            if (empty($transaction->transaction_code)) {
                $transaction->transaction_code = 'TRX-' . strtoupper(Str::random(8));
            }
        });
    }

    /**
     * Get the user that owns the transaction.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the transaction details for the transaction.
     */
    public function transactionDetails()
    {
        return $this->hasMany(TransactionDetail::class);
    }

    /**
     * Calculate total amount from transaction details.
     */
    public function calculateTotal()
    {
        return $this->transactionDetails->sum('subtotal');
    }

    /**
     * Check if transaction is completed.
     */
    public function isCompleted()
    {
        return $this->status === 'completed';
    }

    /**
     * Check if transaction is pending.
     */
    public function isPending()
    {
        return $this->status === 'pending';
    }
}
