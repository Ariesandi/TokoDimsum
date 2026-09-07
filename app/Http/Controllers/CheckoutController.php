<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use App\Models\Transaction;
use App\Models\Product;
use App\Models\Category;

class CheckoutController extends Controller
{
    /**
     * Display the checkout page
     */
    public function index()
    {
        $cart = Session::get('cart', []);
        
        if (empty($cart)) {
            return redirect()->route('cart.index')->with('error', 'Keranjang belanja Anda kosong.');
        }

        $cartItems = [];
        $total = 0;

        foreach ($cart as $id => $item) {
            $product = Product::find($id);
            if ($product) {
                // Hitung harga unit dari produk + total harga toppings
                $toppingsTotal = 0;
                if (!empty($item['toppings']) && is_array($item['toppings'])) {
                    foreach ($item['toppings'] as $topping) {
                        $toppingsTotal += $topping['price'] ?? 0;
                    }
                }
                $unitPrice = $product->price + $toppingsTotal;

                $cartItems[] = [
                    'product' => $product,
                    'quantity' => $item['quantity'],
                    'price' => $unitPrice, // Use calculated unit price
                    'toppings' => $item['toppings'] ?? [],
                    'subtotal' => $item['quantity'] * $unitPrice
                ];
                $total += $item['quantity'] * $unitPrice;
            }
        }

        return view('checkout.index', compact('cartItems', 'total'));
    }

    /**
     * Process the checkout
     */
    public function process(Request $request)
    {
        $request->validate([
            // Align field name with the checkout form (name="address")
            'address' => 'required|string|max:500',
            'phone' => 'required|string|max:20',
            'notes' => 'nullable|string|max:500',
            'payment_method' => 'required|in:transfer'
        ]);

        $cart = Session::get('cart', []);
        
        if (empty($cart)) {
            return redirect()->route('cart.index')->with('error', 'Keranjang belanja Anda kosong.');
        }

        DB::beginTransaction();
        
        try {
            // Calculate total
            $total = 0;
            $cartItems = [];
            
            foreach ($cart as $id => $item) {
                $product = Product::find($id);
                if ($product) {
                    // Check stock availability
                    if ($product->stock < $item['quantity']) {
                        throw new \Exception("Stok produk {$product->name} tidak mencukupi. Stok tersedia: {$product->stock}");
                    }
                    
                    // Hitung harga unit dari produk + total harga toppings
                    $toppingsTotal = 0;
                    if (!empty($item['toppings']) && is_array($item['toppings'])) {
                        foreach ($item['toppings'] as $topping) {
                            $toppingsTotal += $topping['price'] ?? 0;
                        }
                    }
                    $unitPrice = $product->price + $toppingsTotal;
                    
                    $cartItems[] = [
                        'product_id' => $id,
                        'product' => $product,
                        'quantity' => $item['quantity'],
                        'price' => $unitPrice, // Use calculated unit price
                        'toppings' => $item['toppings'] ?? []
                    ];
                    $total += $item['quantity'] * $unitPrice;
                }
            }

            // Create transaction
            $transaction = Transaction::create([
                'user_id' => Auth::id(),
                'total_amount' => $total,
                'status' => 'pending',
                'payment_method' => $request->payment_method,
                // Map form fields to DB columns
                'customer_name' => optional(Auth::user())->name ?? $request->name,
                'customer_email' => optional(Auth::user())->email ?? $request->email,
                'customer_address' => $request->address,
                'customer_phone' => $request->phone,
                'notes' => $request->notes,
                'order_date' => now()
            ]);

            // Create transaction details and update stock
            foreach ($cartItems as $item) {
                $transaction->transactionDetails()->create([
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    // map to DB schema
                    'unit_price' => $item['price'],
                    'selected_toppings' => $item['toppings'],
                    // subtotal will be auto-calculated in TransactionDetail::saving()
                ]);

                // Update product stock
                $item['product']->decrement('stock', $item['quantity']);
            }

            // Clear cart
            Session::forget('cart');

            DB::commit();

            // If AJAX request, return JSON with redirect URL
            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'redirect_url' => route('orders.success', $transaction->id),
                    'message' => 'Pesanan berhasil dibuat! Silakan lakukan pembayaran.'
                ]);
            }

            return redirect()->route('orders.success', $transaction->id)
                           ->with('success', 'Pesanan berhasil dibuat! Silakan lakukan pembayaran.');
        } catch (\Exception $e) {
            DB::rollback();

            // If AJAX request, return JSON error response
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Terjadi kesalahan: ' . $e->getMessage()
                ], 422);
            }

            return redirect()->back()
                           ->withInput()
                           ->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    /**
     * Calculate shipping cost (placeholder)
     */
    private function calculateShipping($address)
    {
        // Simple shipping calculation - can be enhanced
        return 10000; // Rp 10,000 flat rate
    }

    // Payment method is already aligned with DB enum ('transfer'), so no extra validation/normalization helpers are needed here.
}