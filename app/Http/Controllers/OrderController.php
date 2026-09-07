<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use App\Models\Transaction;
use App\Models\Product;

class OrderController extends Controller
{
    /**
     * Display user's orders
     */
    public function index(Request $request)
    {
        $query = Transaction::where('user_id', Auth::id())
                           ->with(['transactionDetails.product', 'user'])
                           ->orderBy('created_at', 'desc');

        // Filter by status if provided
        if ($request->has('status') && $request->status != '') {
            $query->where('status', $request->status);
        }

        // Search by order ID or product name
        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('id', 'like', '%' . $search . '%')
                  ->orWhereHas('transactionDetails.product', function($q2) use ($search) {
                      $q2->where('name', 'like', '%' . $search . '%');
                  });
            });
        }

        $orders = $query->paginate(10);
        $statusCounts = $this->getStatusCounts();

        return view('orders.index', compact('orders', 'statusCounts'));
    }

    /**
     * Display order details
     */
    public function show(Transaction $order)
    {
        // Check if order belongs to current user
        if ($order->user_id !== Auth::id()) {
            abort(403, 'Akses ke pesanan tidak diizinkan.');
        }

        $order->load(['transactionDetails.product', 'user']);
        
        return view('orders.show', compact('order'));
    }

    /**
     * Track order status
     */
    public function track(Transaction $order)
    {
        // Check if order belongs to current user
        if ($order->user_id !== Auth::id()) {
            abort(403, 'Akses ke pesanan tidak diizinkan.');
        }

        $order->load(['transactionDetails.product']);
        $trackingSteps = $this->getTrackingSteps($order->status);
        
        return view('orders.track', compact('order', 'trackingSteps'));
    }

    /**
     * Order success page
     */
    public function success(Transaction $order)
    {
        // Check if order belongs to current user
        if ($order->user_id !== Auth::id()) {
            abort(403, 'Akses ke pesanan tidak diizinkan.');
        }

        $order->load(['transactionDetails.product']);
        
        return view('orders.success', compact('order'));
    }

    /**
     * Cancel order
     */
    public function cancel(Request $request, Transaction $order)
    {
        // Check if order belongs to current user
        if ($order->user_id !== Auth::id()) {
            abort(403, 'Akses ke pesanan tidak diizinkan.');
        }

        // Check if order can be cancelled
        if (!in_array($order->status, ['pending', 'confirmed'])) {
            return redirect()->back()->with('error', 'Pesanan tidak dapat dibatalkan pada status ini.');
        }

        $request->validate([
            'cancel_reason' => 'required|string|max:500'
        ]);

        // Update order status
        $order->update([
            'status' => 'cancelled',
            'notes' => ($order->notes ? $order->notes . '\n\n' : '') . 'Dibatalkan oleh pelanggan: ' . $request->cancel_reason
        ]);

        // Restore product stock
        foreach ($order->transactionDetails as $detail) {
            $detail->product->increment('stock', $detail->quantity);
        }

        return redirect()->route('orders.index')
                        ->with('success', 'Pesanan berhasil dibatalkan.');
    }

    /**
     * Reorder - add items to cart
     */
    public function reorder(Transaction $order)
    {
        // Check if order belongs to current user
        if ($order->user_id !== Auth::id()) {
            abort(403, 'Akses ke pesanan tidak diizinkan.');
        }

        $cart = Session::get('cart', []);
        $addedItems = 0;
        $unavailableItems = [];

        foreach ($order->transactionDetails as $detail) {
            $product = $detail->product;
            
            // Check if product is still available and active
            if ($product && $product->is_active && $product->stock > 0) {
                $toppings = json_decode($detail->toppings, true) ?? [];
                
                // Calculate price with toppings
                $price = $product->price;
                foreach ($toppings as $topping) {
                    $price += $topping['price'] ?? 0;
                }

                $cart[$product->id] = [
                    'name' => $product->name,
                    'price' => $price,
                    'quantity' => min($detail->quantity, $product->stock), // Don't exceed available stock
                    'image' => $product->image,
                    'toppings' => $toppings
                ];
                
                $addedItems++;
            } else {
                $unavailableItems[] = $product ? $product->name : 'Produk tidak tersedia';
            }
        }

        Session::put('cart', $cart);

        $message = "$addedItems item berhasil ditambahkan ke keranjang.";
        if (!empty($unavailableItems)) {
            $message .= ' Beberapa item tidak tersedia: ' . implode(', ', $unavailableItems);
        }

        return redirect()->route('cart.index')->with('success', $message);
    }

    /**
     * Get status counts for filtering
     */
    private function getStatusCounts()
    {
        $userId = Auth::id();
        
        return [
            'all' => Transaction::where('user_id', $userId)->count(),
            'pending' => Transaction::where('user_id', $userId)->where('status', 'pending')->count(),
            'confirmed' => Transaction::where('user_id', $userId)->where('status', 'confirmed')->count(),
            'processing' => Transaction::where('user_id', $userId)->where('status', 'processing')->count(),
            'shipped' => Transaction::where('user_id', $userId)->where('status', 'shipped')->count(),
            'delivered' => Transaction::where('user_id', $userId)->where('status', 'delivered')->count(),
            'cancelled' => Transaction::where('user_id', $userId)->where('status', 'cancelled')->count(),
        ];
    }

    /**
     * Get tracking steps based on order status
     */
    private function getTrackingSteps($currentStatus)
    {
        $steps = [
            'pending' => [
                'title' => 'Pesanan Diterima',
                'description' => 'Pesanan Anda telah diterima dan sedang menunggu konfirmasi',
                'completed' => true,
                'active' => $currentStatus === 'pending'
            ],
            'confirmed' => [
                'title' => 'Pesanan Dikonfirmasi',
                'description' => 'Pesanan Anda telah dikonfirmasi dan akan segera diproses',
                'completed' => in_array($currentStatus, ['confirmed', 'processing', 'shipped', 'delivered']),
                'active' => $currentStatus === 'confirmed'
            ],
            'processing' => [
                'title' => 'Sedang Diproses',
                'description' => 'Pesanan Anda sedang disiapkan oleh dapur',
                'completed' => in_array($currentStatus, ['processing', 'shipped', 'delivered']),
                'active' => $currentStatus === 'processing'
            ],
            'shipped' => [
                'title' => 'Dalam Pengiriman',
                'description' => 'Pesanan Anda sedang dalam perjalanan',
                'completed' => in_array($currentStatus, ['shipped', 'delivered']),
                'active' => $currentStatus === 'shipped'
            ],
            'delivered' => [
                'title' => 'Pesanan Selesai',
                'description' => 'Pesanan Anda telah sampai di tujuan',
                'completed' => $currentStatus === 'delivered',
                'active' => $currentStatus === 'delivered'
            ]
        ];

        // Handle cancelled status
        if ($currentStatus === 'cancelled') {
            return [
                'cancelled' => [
                    'title' => 'Pesanan Dibatalkan',
                    'description' => 'Pesanan Anda telah dibatalkan',
                    'completed' => true,
                    'active' => true,
                    'cancelled' => true
                ]
            ];
        }

        return $steps;
    }

    public function uploadPaymentProof(Request $request, Transaction $order)
    {
        // Pastikan order milik user yang sedang login
        if ($order->user_id !== Auth::id()) {
            abort(403, 'Percobaan unggah tidak diizinkan.');
        }

        $request->validate([
            'payment_proof' => 'required|image|mimes:jpg,jpeg,png,webp|max:2048',
            'notes' => 'nullable|string|max:500'
        ]);

        if ($order->payment_status === 'verified') {
            return back()->with('error', 'Pembayaran sudah diverifikasi.');
        }

        if ($request->hasFile('payment_proof')) {
            $path = $request->file('payment_proof')->store('payment_proofs', 'public');
            $order->payment_proof = $path;
            $order->payment_status = 'pending';
            if ($request->filled('notes')) {
                $order->payment_notes = trim(($order->payment_notes ? $order->payment_notes."\n\n" : '') . '[User] ' . $request->notes);
            }
            $order->save();
        }

        return back()->with('success', 'Bukti pembayaran berhasil diunggah. Menunggu verifikasi admin.');
    }
}