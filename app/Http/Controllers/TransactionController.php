<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Transaction;
use App\Models\TransactionDetail;
use App\Models\Product;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class TransactionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Transaction::with(['user', 'transactionDetails.product'])
            ->latest();

        // Filter by status
        if ($request->has('status') && $request->status != '') {
            $query->where('status', $request->status);
        }

        // Filter by date range
        if ($request->has('date_from') && $request->date_from != '') {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->has('date_to') && $request->date_to != '') {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $transactions = $query->paginate(15);
        
        return view('admin.transactions.index', compact('transactions'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $products = Product::where('is_active', true)->get();
        return view('admin.transactions.create', compact('products'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'customer_name' => 'required|string|max:255',
            'customer_phone' => 'required|string|max:20',
            'customer_address' => 'required|string',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.toppings' => 'nullable|array',
            'payment_method' => 'required|in:transfer',
            'notes' => 'nullable|string',
        ]);

        DB::beginTransaction();
        try {
            // Calculate total
            $totalAmount = 0;
            $items = [];
            
            foreach ($request->items as $item) {
                $product = Product::findOrFail($item['product_id']);
                $quantity = $item['quantity'];
                $toppings = $item['toppings'] ?? [];
                
                // Calculate toppings additional price if provided
                $toppingsTotal = 0;
                if (is_array($toppings)) {
                    foreach ($toppings as $topping) {
                        // support both [ ['name'=>..,'price'=>..], ... ] and [ ['price'=>..], ... ] formats
                        if (is_array($topping) && isset($topping['price'])) {
                            $toppingsTotal += (float) $topping['price'];
                        } elseif (is_numeric($topping)) {
                            // if numeric values are sent directly
                            $toppingsTotal += (float) $topping;
                        }
                    }
                }

                $unitPrice = (float) $product->price + (float) $toppingsTotal;
                $itemSubtotal = $unitPrice * (int) $quantity;
                $totalAmount += $itemSubtotal;
                
                $items[] = [
                    'product' => $product,
                    'quantity' => $quantity,
                    'unit_price' => $unitPrice,
                    'selected_toppings' => $toppings,
                ];
            }

            // Create transaction
            $transaction = Transaction::create([
                'user_id' => Auth::id(),
                'transaction_code' => 'TRX-' . date('Ymd') . '-' . str_pad(Transaction::whereDate('created_at', today())->count() + 1, 4, '0', STR_PAD_LEFT),
                'customer_name' => $request->customer_name,
                'customer_email' => $request->customer_email,
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
                    'unit_price' => $item['unit_price'],
                    'selected_toppings' => $item['selected_toppings'],
                    // subtotal will be auto-calculated in TransactionDetail::saving()
                ]);
            }

            DB::commit();
            
            return redirect()->route('admin.transactions.show', $transaction)
                ->with('success', 'Transaksi berhasil dibuat.');
                
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Transaction $transaction)
    {
        $transaction->load(['user', 'transactionDetails.product']);
        return view('admin.transactions.show', compact('transaction'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Transaction $transaction)
    {
        if (in_array($transaction->status, ['completed', 'cancelled'])) {
            return redirect()->back()
                ->with('error', 'Transaksi yang sudah selesai atau dibatalkan tidak dapat diedit.');
        }
        
        $transaction->load('transactionDetails.product');
        $products = Product::where('is_active', true)->get();
        
        return view('admin.transactions.edit', compact('transaction', 'products'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Transaction $transaction)
    {
        if (in_array($transaction->status, ['completed', 'cancelled'])) {
            return redirect()->back()
                ->with('error', 'Transaksi yang sudah selesai atau dibatalkan tidak dapat diubah.');
        }

        $request->validate([
            'customer_name' => 'required|string|max:255',
            'customer_phone' => 'required|string|max:20',
            'customer_address' => 'required|string',
            'payment_method' => 'required|in:transfer',
            'status' => 'required|in:pending,processing,ready,completed,cancelled',
            'notes' => 'nullable|string',
        ]);

        $transaction->update([
            'customer_name' => $request->customer_name,
            'customer_phone' => $request->customer_phone,
            'customer_address' => $request->customer_address,
            'payment_method' => $request->payment_method,
            'status' => $request->status,
            'notes' => $request->notes,
        ]);

        return redirect()->route('admin.transactions.show', $transaction)
            ->with('success', 'Transaksi berhasil diperbarui.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Transaction $transaction)
    {
        if ($transaction->status == 'completed') {
            return redirect()->back()
                ->with('error', 'Transaksi yang sudah selesai tidak dapat dihapus.');
        }
        
        $transaction->delete();
        
        return redirect()->route('admin.transactions.index')
            ->with('success', 'Transaksi berhasil dihapus.');
    }

    /**
     * Update transaction status
     */
    public function updateStatus(Request $request, Transaction $transaction)
    {
        $request->validate([
            'status' => 'required|in:pending,processing,ready,completed,cancelled'
        ]);

        $transaction->update([
            'status' => $request->status
        ]);

        return redirect()->back()
            ->with('success', 'Status transaksi berhasil diperbarui.');
    }
}
