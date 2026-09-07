<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Models\Product;
use App\Models\Category;
use App\Models\TransactionDetail;

class ProductController extends Controller
{
    /**
     * Display a listing of products
     */
    public function index(Request $request)
    {
        $query = Product::with('category');
        
        // Search functionality
        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%')
                  ->orWhere('description', 'like', '%' . $search . '%')
                  ->orWhereHas('category', function($q2) use ($search) {
                      $q2->where('name', 'like', '%' . $search . '%');
                  });
            });
        }
        
        // Filter by category
        if ($request->has('category') && $request->category != '') {
            $query->where('category_id', $request->category);
        }
        
        // Filter by status
        if ($request->has('status') && $request->status != '') {
            if ($request->status === 'active') {
                $query->where('is_active', true);
            } elseif ($request->status === 'inactive') {
                $query->where('is_active', false);
            } elseif ($request->status === 'featured') {
                $query->where('is_featured', true);
            } elseif ($request->status === 'low_stock') {
                $query->where('stock', '<=', 10);
            }
        }
        
        // Sort functionality
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);
        
        $products = $query->paginate(15);
        $categories = Category::where('is_active', true)->get();
        
        // Statistics
        $stats = [
            'total' => Product::count(),
            'active' => Product::where('is_active', true)->count(),
            'inactive' => Product::where('is_active', false)->count(),
            'featured' => Product::where('is_featured', true)->count(),
            'low_stock' => Product::where('stock', '<=', 10)->count(),
        ];
        
        return view('admin.products.index', compact('products', 'categories', 'stats'));
    }

    /**
     * Show the form for creating a new product
     */
    public function create()
    {
        $categories = Category::where('is_active', true)->get();
        return view('admin.products.create', compact('categories'));
    }

    /**
     * Store a newly created product
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'category_id' => 'required|exists:categories,id',
            'price' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
            'toppings' => 'nullable|json',
            'is_active' => 'boolean',
            'is_featured' => 'boolean',
        ]);

        // Handle image upload
        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('products', 'public');
        }

        // Process toppings
        $toppings = null;
        if ($request->has('toppings') && !empty($request->toppings)) {
            // Try to decode JSON from hidden field
            $toppingsData = json_decode($request->toppings, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($toppingsData)) {
                // Filter out empty toppings
                $validToppings = array_filter($toppingsData, function($topping) {
                    return !empty($topping['name']);
                });
                if (!empty($validToppings)) {
                    $toppings = json_encode(array_values($validToppings));
                }
            }
        }
        
        // Fallback: process from array inputs if JSON didn't work
        if (is_null($toppings) && $request->has('toppings_array')) {
            $toppingsArray = $request->toppings_array;
            if (is_array($toppingsArray)) {
                $validToppings = [];
                foreach ($toppingsArray as $topping) {
                    if (!empty($topping['name'])) {
                        $validToppings[] = [
                            'name' => $topping['name'],
                            'price' => floatval($topping['price'] ?? 0)
                        ];
                    }
                }
                if (!empty($validToppings)) {
                    $toppings = json_encode($validToppings);
                }
            }
        }

        Product::create([
            'name' => $request->name,
            'slug' => Str::slug($request->name),
            'description' => $request->description,
            'category_id' => $request->category_id,
            'price' => $request->price,
            'stock' => $request->stock,
            'image' => $imagePath,
            'toppings' => $toppings,
            'is_active' => $request->has('is_active'),
            'is_featured' => $request->has('is_featured'),
        ]);

        return redirect()->route('admin.products.index')
                        ->with('success', 'Produk berhasil ditambahkan.');
    }

    /**
     * Display the specified product
     */
    public function show(Product $product)
    {
        $product->load('category');
        
        // Get sales statistics
        $salesStats = $this->getProductSalesStats($product->id);
        
        // Get recent orders for this product
        $recentOrders = TransactionDetail::where('product_id', $product->id)
                                        ->with(['transaction.user'])
                                        ->orderBy('created_at', 'desc')
                                        ->limit(10)
                                        ->get();
        
        return view('admin.products.show', compact('product', 'salesStats', 'recentOrders'));
    }

    /**
     * Show the form for editing the specified product
     */
    public function edit(Product $product)
    {
        $categories = Category::where('is_active', true)->get();
        return view('admin.products.edit', compact('product', 'categories'));
    }

    /**
     * Update the specified product
     */
    public function update(Request $request, Product $product)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'category_id' => 'required|exists:categories,id',
            'price' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'toppings' => 'nullable|json',
            'is_active' => 'boolean',
            'is_featured' => 'boolean',
        ]);

        // Handle image upload
        $imagePath = $product->image;
        if ($request->hasFile('image')) {
            // Delete old image
            if ($product->image && Storage::disk('public')->exists($product->image)) {
                Storage::disk('public')->delete($product->image);
            }
            $imagePath = $request->file('image')->store('products', 'public');
        }

        // Process toppings
        $toppings = $product->toppings;
        if ($request->has('toppings') && !empty($request->toppings)) {
            // Try to decode JSON from hidden field
            $toppingsData = json_decode($request->toppings, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($toppingsData)) {
                // Filter out empty toppings
                $validToppings = array_filter($toppingsData, function($topping) {
                    return !empty($topping['name']);
                });
                if (!empty($validToppings)) {
                    $toppings = json_encode(array_values($validToppings));
                }
            }
        } else {
            $toppings = null;
        }
        
        // Fallback: process from array inputs if JSON didn't work
        if (is_null($toppings) && $request->has('toppings_array')) {
            $toppingsArray = $request->toppings_array;
            if (is_array($toppingsArray)) {
                $validToppings = [];
                foreach ($toppingsArray as $topping) {
                    if (!empty($topping['name'])) {
                        $validToppings[] = [
                            'name' => $topping['name'],
                            'price' => floatval($topping['price'] ?? 0)
                        ];
                    }
                }
                if (!empty($validToppings)) {
                    $toppings = json_encode($validToppings);
                }
            }
        }

        $product->update([
            'name' => $request->name,
            'slug' => Str::slug($request->name),
            'description' => $request->description,
            'category_id' => $request->category_id,
            'price' => $request->price,
            'stock' => $request->stock,
            'image' => $imagePath,
            'toppings' => $toppings,
            'is_active' => $request->has('is_active'),
            'is_featured' => $request->has('is_featured'),
        ]);

        return redirect()->route('admin.products.index')
                        ->with('success', 'Produk berhasil diperbarui.');
    }

    /**
     * Remove the specified product
     */
    public function destroy(Product $product)
    {
        // Check if product has any orders
        $hasOrders = TransactionDetail::where('product_id', $product->id)->exists();
        
        if ($hasOrders) {
            return redirect()->route('admin.products.index')
                           ->with('error', 'Produk tidak dapat dihapus karena sudah memiliki riwayat pesanan.');
        }

        // Delete image if exists
        if ($product->image && Storage::disk('public')->exists($product->image)) {
            Storage::disk('public')->delete($product->image);
        }

        $product->delete();

        return redirect()->route('admin.products.index')
                        ->with('success', 'Produk berhasil dihapus.');
    }

    /**
     * Get product sales statistics
     */
    private function getProductSalesStats($productId)
    {
        $totalSold = TransactionDetail::where('product_id', $productId)
                                    ->whereHas('transaction', function($query) {
                                        $query->where('status', 'delivered');
                                    })
                                    ->sum('quantity');
        
        $totalRevenue = TransactionDetail::where('product_id', $productId)
                                       ->whereHas('transaction', function($query) {
                                           $query->where('status', 'delivered');
                                       })
                                       ->sum('subtotal');
        
        $totalOrders = TransactionDetail::where('product_id', $productId)
                                      ->whereHas('transaction', function($query) {
                                          $query->where('status', 'delivered');
                                      })
                                      ->count();
        
        return [
            'total_sold' => $totalSold,
            'total_revenue' => $totalRevenue,
            'total_orders' => $totalOrders,
            'average_quantity_per_order' => $totalOrders > 0 ? round($totalSold / $totalOrders, 2) : 0,
        ];
    }
}
