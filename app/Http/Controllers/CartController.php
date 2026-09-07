<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use Illuminate\Support\Facades\Session;

class CartController extends Controller
{
    /**
     * Display cart contents
     */
    public function index()
    {
        $cart = Session::get('cart', []);
        $cartItems = [];
        $total = 0;
        
        foreach ($cart as $id => $item) {
            $product = Product::find($id);
            if ($product) {
                // Hitung harga unit berdasarkan harga produk + total harga toppings
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
                    'toppings' => $item['toppings'] ?? [],
                    'subtotal' => $unitPrice * $item['quantity']
                ];
                $total += $unitPrice * $item['quantity'];
            }
        }
        
        return view('cart.index', compact('cartItems', 'total'));
    }
    
    /**
     * Add product to cart
     */
    public function add(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1',
            'toppings' => 'nullable|array'
        ]);
        
        $product = Product::findOrFail($request->product_id);
        
        if (!$product->is_active) {
            return response()->json([
                'success' => false,
                'message' => 'Produk tidak tersedia'
            ]);
        }
        
        $cart = Session::get('cart', []);
        $productId = $request->product_id;
        
        if (isset($cart[$productId])) {
            $cart[$productId]['quantity'] += $request->quantity;
            // update toppings jika dikirim (opsional)
            if ($request->has('toppings')) {
                $cart[$productId]['toppings'] = $request->toppings ?? [];
            }
        } else {
            $cart[$productId] = [
                'name' => $product->name,
                // Simpan price untuk kompatibilitas, tapi perhitungan selalu pakai harga produk + toppings
                'price' => $product->price,
                'image' => $product->image,
                'category' => $product->category->name ?? 'Uncategorized',
                'quantity' => $request->quantity,
                'toppings' => $request->toppings ?? []
            ];
        }
        
        Session::put('cart', $cart);
        
        return response()->json([
            'success' => true,
            'message' => 'Produk berhasil ditambahkan ke keranjang',
            'cart_count' => array_sum(array_column($cart, 'quantity'))
        ]);
    }
    
    /**
     * Update cart item quantity
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'quantity' => 'required|integer|min:0'
        ]);
        
        // Validate product exists
        $product = Product::findOrFail($id);
        
        $cart = Session::get('cart', []);
        $productId = $id;
        
        if ($request->quantity == 0) {
            unset($cart[$productId]);
        } else {
            if (isset($cart[$productId])) {
                $cart[$productId]['quantity'] = $request->quantity;
            }
        }
        
        Session::put('cart', $cart);
        
        // Calculate new totals using product price + toppings, avoiding direct access to ['price']
        $cartTotal = 0;
        $cartCount = 0;
        foreach ($cart as $pid => $ci) {
            $prod = Product::find($pid);
            $basePrice = $prod ? $prod->price : 0;
            $toppingsTotal = 0;
            if (!empty($ci['toppings']) && is_array($ci['toppings'])) {
                foreach ($ci['toppings'] as $t) {
                    $toppingsTotal += $t['price'] ?? 0;
                }
            }
            $unitPrice = $basePrice + $toppingsTotal;
            $qty = $ci['quantity'] ?? 0;
            $cartTotal += $unitPrice * $qty;
            $cartCount += $qty;
        }
        
        // Subtotal untuk produk yang sedang diupdate
        $currentToppingsTotal = 0;
        if (!empty($cart[$productId]['toppings']) && is_array($cart[$productId]['toppings'])) {
            foreach ($cart[$productId]['toppings'] as $t) {
                $currentToppingsTotal += $t['price'] ?? 0;
            }
        }
        $currentUnitPrice = $product->price + $currentToppingsTotal;
        
        return response()->json([
            'success' => true,
            'message' => 'Keranjang berhasil diperbarui',
            'subtotal' => $currentUnitPrice * ($request->quantity ?? 0),
            'cartTotal' => $cartTotal,
            'cartCount' => $cartCount
        ]);
    }
    
    /**
     * Remove item from cart
     */
    public function remove(Request $request, $id)
    {
        // Validate product exists
        $product = Product::findOrFail($id);
        
        $cart = Session::get('cart', []);
        $productId = $id;
        
        unset($cart[$productId]);
        Session::put('cart', $cart);
        
        // Recalculate totals after removal using product price + toppings
        $cartTotal = 0;
        $cartCount = 0;
        foreach ($cart as $pid => $ci) {
            $prod = Product::find($pid);
            $basePrice = $prod ? $prod->price : 0;
            $toppingsTotal = 0;
            if (!empty($ci['toppings']) && is_array($ci['toppings'])) {
                foreach ($ci['toppings'] as $t) {
                    $toppingsTotal += $t['price'] ?? 0;
                }
            }
            $unitPrice = $basePrice + $toppingsTotal;
            $qty = $ci['quantity'] ?? 0;
            $cartTotal += $unitPrice * $qty;
            $cartCount += $qty;
        }

        return response()->json([
            'success' => true,
            'message' => 'Produk berhasil dihapus dari keranjang',
            'cartTotal' => $cartTotal,
            'cartCount' => $cartCount
        ]);
    }
    
    /**
     * Clear entire cart
     */
    public function clear()
    {
        Session::forget('cart');
        
        return response()->json([
            'success' => true,
            'message' => 'Keranjang berhasil dikosongkan'
        ]);
    }
    
    /**
     * Get cart count
     */
    public function count()
    {
        $cart = Session::get('cart', []);
        $count = array_sum(array_column($cart, 'quantity'));
        
        return response()->json([
            'count' => $count
        ]);
    }
    
    /**
     * Show checkout page
     */
    public function checkout()
    {
        $cart = Session::get('cart', []);
        
        if (empty($cart)) {
            return redirect()->route('cart.index')
                ->with('error', 'Keranjang kosong');
        }
        
        $cartItems = [];
        $total = 0;
        
        foreach ($cart as $id => $item) {
            $product = Product::find($id);
            if ($product && $product->is_active) {
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
                    'toppings' => $item['toppings'] ?? [],
                    'subtotal' => $unitPrice * $item['quantity']
                ];
                $total += $unitPrice * $item['quantity'];
            }
        }
        
        if (empty($cartItems)) {
            Session::forget('cart');
            return redirect()->route('cart.index')
                ->with('error', 'Produk dalam keranjang tidak tersedia');
        }
        
        return view('cart.checkout', compact('cartItems', 'total'));
    }
}
