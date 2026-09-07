<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use App\Models\Product;
use App\Models\Category;
use App\Models\Transaction;
use App\Models\TransactionDetail;

class HomeController extends Controller
{
    /**
     * Show the application dashboard.
     */
    public function index()
    {
        $categories = Category::where('is_active', true)
            ->withCount(['products' => function($query) {
                $query->where('is_active', true);
            }])
            ->get();

        $products = Product::where('is_active', true)
            ->with('category')
            ->latest()
            ->paginate(12);

        // Tambahkan produk unggulan untuk kebutuhan tampilan home
        $featuredProducts = Product::where('is_active', true)
            ->where('is_featured', true)
            ->with('category')
            ->latest()
            ->take(8)
            ->get();

        return view('home', compact('categories', 'products', 'featuredProducts'));
    }

    /**
     * Show products by category
     */
    public function category($id)
    {
        $category = Category::where('is_active', true)->findOrFail($id);
        
        $products = Product::where('is_active', true)
            ->where('category_id', $id)
            ->with('category')
            ->paginate(12);
            
        $categories = Category::where('is_active', true)
            ->withCount(['products' => function($query) {
                $query->where('is_active', true);
            }])
            ->get();
            
        return view('home.category', compact('category', 'products', 'categories'));
    }
    
    /**
     * Process checkout and create order
     */
    public function processCheckout(Request $request)
    {
        $request->validate([
            'customer_name' => 'required|string|max:255',
            'customer_phone' => 'required|string|max:20',
            'customer_address' => 'required|string',
            'payment_method' => 'required|in:transfer',
            'notes' => 'nullable|string',
        ]);
        
        $cart = Session::get('cart', []);
        
        if (empty($cart)) {
            return redirect()->route('cart.index')
                ->with('error', 'Keranjang kosong');
        }
        
        DB::beginTransaction();
        try {
            // Calculate total and prepare items
            $totalAmount = 0;
            $items = [];
            
            foreach ($cart as $id => $item) {
                $product = Product::where('is_active', true)->find($id);
                if ($product) {
                    $quantity = $item['quantity'];
                    $toppings = $item['toppings'] ?? [];
                    
                    $itemTotal = $product->price * $quantity;
                    $totalAmount += $itemTotal;
                    
                    $items[] = [
                        'product' => $product,
                        'quantity' => $quantity,
                        'price' => $product->price,
                        'toppings' => $toppings,
                        'subtotal' => $itemTotal
                    ];
                }
            }
            
            if (empty($items)) {
                throw new \Exception('Tidak ada produk yang valid dalam keranjang');
            }
            
            // Create transaction
            $transaction = Transaction::create([
                'user_id' => Auth::id(),
                'transaction_code' => 'ORD-' . date('Ymd') . '-' . str_pad(Transaction::whereDate('created_at', today())->count() + 1, 4, '0', STR_PAD_LEFT),
                'customer_name' => $request->customer_name,
                'customer_phone' => $request->customer_phone,
                'customer_address' => $request->customer_address,
                'total_amount' => $totalAmount,
                'payment_method' => $request->payment_method,
                'status' => 'pending',
                'notes' => $request->notes,
                'order_date' => now(),
            ]);
            
            // Create transaction details
            foreach ($items as $item) {
                TransactionDetail::create([
                    'transaction_id' => $transaction->id,
                    'product_id' => $item['product']->id,
                    'quantity' => $item['quantity'],
                    'price' => $item['price'],
                    'toppings' => json_encode($item['toppings']),
                    'subtotal' => $item['subtotal'],
                ]);
            }
            
            // Clear cart
            Session::forget('cart');
            
            DB::commit();
            
            return redirect()->route('order.success', $transaction->transaction_code)
                ->with('success', 'Pesanan berhasil dibuat!');
                
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage())
                ->withInput();
        }
    }
    
    /**
     * Display order success page
     */
    public function orderSuccess($transactionCode)
    {
        $transaction = Transaction::where('transaction_code', $transactionCode)
            ->with(['transactionDetails.product'])
            ->firstOrFail();
            
        return view('home.order-success', compact('transaction'));
    }
    
    /**
     * Track order status
     */
    public function trackOrder(Request $request)
    {
        if ($request->has('transaction_code')) {
            $transaction = Transaction::where('transaction_code', $request->transaction_code)
                ->with(['transactionDetails.product'])
                ->first();
                
            if ($transaction) {
                return view('home.track-order', compact('transaction'));
            } else {
                return view('home.track-order')
                    ->with('error', 'Kode transaksi tidak ditemukan');
            }
        }
        
        return view('home.track-order');
    }
}
