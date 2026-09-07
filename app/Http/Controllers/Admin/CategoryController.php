<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Support\Facades\Storage;

class CategoryController extends Controller
{
    /**
     * Display a listing of categories
     */
    public function index(Request $request)
    {
        $query = Category::withCount('products');
        
        // Search functionality
        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%')
                  ->orWhere('description', 'like', '%' . $search . '%');
            });
        }
        
        // Filter by status
        if ($request->has('status') && $request->status != '') {
            if ($request->status === 'active') {
                $query->where('is_active', true);
            } elseif ($request->status === 'inactive') {
                $query->where('is_active', false);
            }
        }
        
        // Sort functionality
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);
        
        $categories = $query->paginate(15);
        
        // Statistics
        $stats = [
            'total' => Category::count(),
            'active' => Category::where('is_active', true)->count(),
            'inactive' => Category::where('is_active', false)->count(),
            'with_products' => Category::has('products')->count(),
            'empty' => Category::doesntHave('products')->count(),
        ];
        
        return view('admin.categories.index', compact('categories', 'stats'));
    }

    /**
     * Show the form for creating a new category
     */
    public function create()
    {
        return view('admin.categories.create');
    }

    /**
     * Store a newly created category
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:categories,name',
            'description' => 'nullable|string|max:500',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'is_active' => 'boolean',
        ]);

        // Handle image upload
        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('categories', 'public');
        }

        Category::create([
            'name' => $request->name,
            'description' => $request->description,
            'image' => $imagePath,
            'is_active' => $request->has('is_active'),
        ]);

        return redirect()->route('admin.categories.index')
                        ->with('success', 'Kategori berhasil ditambahkan.');
    }

    /**
     * Display the specified category
     */
    public function show(Category $category)
    {
        $category->loadCount('products');
        
        // Get products in this category
        $products = Product::where('category_id', $category->id)
                          ->with('category')
                          ->orderBy('created_at', 'desc')
                          ->paginate(12);
        
        // Get category statistics
        $stats = [
            'total_products' => $category->products_count,
            'active_products' => Product::where('category_id', $category->id)->where('is_active', true)->count(),
            'inactive_products' => Product::where('category_id', $category->id)->where('is_active', false)->count(),
            'featured_products' => Product::where('category_id', $category->id)->where('is_featured', true)->count(),
            'low_stock_products' => Product::where('category_id', $category->id)->where('stock', '<=', 10)->count(),
        ];
        
        return view('admin.categories.show', compact('category', 'products', 'stats'));
    }

    /**
     * Show the form for editing the specified category
     */
    public function edit(Category $category)
    {
        return view('admin.categories.edit', compact('category'));
    }

    /**
     * Update the specified category
     */
    public function update(Request $request, Category $category)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:categories,name,' . $category->id,
            'description' => 'nullable|string|max:500',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'is_active' => 'boolean',
        ]);

        // Handle image upload
        $imagePath = $category->image;
        if ($request->hasFile('image')) {
            // Delete old image
            if ($category->image && Storage::disk('public')->exists($category->image)) {
                Storage::disk('public')->delete($category->image);
            }
            $imagePath = $request->file('image')->store('categories', 'public');
        }

        $category->update([
            'name' => $request->name,
            'description' => $request->description,
            'image' => $imagePath,
            'is_active' => $request->has('is_active'),
        ]);

        return redirect()->route('admin.categories.index')
                        ->with('success', 'Kategori berhasil diperbarui.');
    }

    /**
     * Remove the specified category
     */
    public function destroy(Category $category)
    {
        // Check if category has any products
        $hasProducts = Product::where('category_id', $category->id)->exists();
        
        if ($hasProducts) {
            return redirect()->route('admin.categories.index')
                           ->with('error', 'Kategori tidak dapat dihapus karena masih memiliki produk.');
        }

        $category->delete();

        return redirect()->route('admin.categories.index')
                        ->with('success', 'Kategori berhasil dihapus.');
    }

    /**
     * Toggle category status (active/inactive)
     */
    public function toggleStatus(Category $category)
    {
        $category->update([
            'is_active' => !$category->is_active
        ]);

        $status = $category->is_active ? 'diaktifkan' : 'dinonaktifkan';
        
        return redirect()->back()
                        ->with('success', "Kategori berhasil {$status}.");
    }

    /**
     * Bulk actions for categories
     */
    public function bulkAction(Request $request)
    {
        $request->validate([
            'action' => 'required|in:activate,deactivate,delete',
            'category_ids' => 'required|array',
            'category_ids.*' => 'exists:categories,id'
        ]);

        $categoryIds = $request->category_ids;
        $action = $request->action;
        $count = 0;

        switch ($action) {
            case 'activate':
                Category::whereIn('id', $categoryIds)->update(['is_active' => true]);
                $count = count($categoryIds);
                $message = "{$count} kategori berhasil diaktifkan.";
                break;
                
            case 'deactivate':
                Category::whereIn('id', $categoryIds)->update(['is_active' => false]);
                $count = count($categoryIds);
                $message = "{$count} kategori berhasil dinonaktifkan.";
                break;
                
            case 'delete':
                // Check if any categories have products
                $categoriesWithProducts = Product::whereIn('category_id', $categoryIds)
                                                ->pluck('category_id')
                                                ->unique()
                                                ->toArray();
                
                $deletableIds = array_diff($categoryIds, $categoriesWithProducts);
                $count = Category::whereIn('id', $deletableIds)->delete();
                
                if (count($categoriesWithProducts) > 0) {
                    $message = "{$count} kategori dihapus. Beberapa kategori tidak dapat dihapus karena masih memiliki produk.";
                } else {
                    $message = "{$count} kategori berhasil dihapus.";
                }
                break;
        }

        return redirect()->back()->with('success', $message);
    }
}