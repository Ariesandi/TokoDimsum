<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Transaction;
use App\Models\TransactionDetail;
use App\Models\User;
use App\Models\Product;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class OrderController extends Controller
{
    /**
     * Display a listing of orders
     */
    public function index(Request $request)
    {
        $query = Transaction::with(['user', 'transactionDetails.product'])
                           ->orderBy('created_at', 'desc');
        
        // Search functionality
        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('id', 'like', '%' . $search . '%')
                  ->orWhere('customer_name', 'like', '%' . $search . '%')
                  ->orWhere('customer_email', 'like', '%' . $search . '%')
                  ->orWhere('customer_phone', 'like', '%' . $search . '%')
                  ->orWhereHas('user', function($q2) use ($search) {
                      $q2->where('name', 'like', '%' . $search . '%')
                         ->orWhere('email', 'like', '%' . $search . '%');
                  });
            });
        }
        
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
        
        // Filter by amount range
        if ($request->has('amount_min') && $request->amount_min != '') {
            $query->where('total_amount', '>=', $request->amount_min);
        }
        
        if ($request->has('amount_max') && $request->amount_max != '') {
            $query->where('total_amount', '<=', $request->amount_max);
        }
        
        $orders = $query->paginate(15);
        
        // Statistics
        $stats = $this->getOrderStats();
        
        return view('admin.orders.index', compact('orders', 'stats'));
    }

    /**
     * Display the specified order
     */
    public function show(Transaction $order)
    {
        $order->load(['user', 'transactionDetails.product']);
        
        // Get order timeline/history
        $timeline = $this->getOrderTimeline($order);
        
        return view('admin.orders.show', compact('order', 'timeline'));
    }

    /**
     * Update order status
     */
    public function updateStatus(Request $request, Transaction $order)
    {
        $request->validate([
            // Tambahkan 'ready' dan 'completed' agar sesuai dengan tombol di view
            'status' => 'required|in:pending,confirmed,processing,ready,completed,shipped,delivered,cancelled',
            'notes' => 'nullable|string|max:500'
        ]);

        $oldStatus = $order->status;
        $newStatus = $request->status;
        
        // Validate status transition
        if (!$this->isValidStatusTransition($oldStatus, $newStatus)) {
            // Kembalikan JSON untuk request AJAX
            if ($request->ajax() || $request->wantsJson() || $request->isJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Perubahan status tidak valid.'
                ], 422);
            }
            return redirect()->back()
                           ->with('error', 'Perubahan status tidak valid.');
        }

        // Handle stock restoration for cancelled orders
        if ($newStatus === 'cancelled' && $oldStatus !== 'cancelled') {
            foreach ($order->transactionDetails as $detail) {
                $detail->product->increment('stock', $detail->quantity);
            }
        }
        
        // Handle stock deduction for confirmed orders
        if ($newStatus === 'confirmed' && $oldStatus === 'pending') {
            foreach ($order->transactionDetails as $detail) {
                if ($detail->product->stock < $detail->quantity) {
                    if ($request->ajax() || $request->wantsJson() || $request->isJson()) {
                        return response()->json([
                            'success' => false,
                            'message' => "Stok produk {$detail->product->name} tidak mencukupi."
                        ], 422);
                    }
                    return redirect()->back()
                                   ->with('error', "Stok produk {$detail->product->name} tidak mencukupi.");
                }
            }
        }

        // Update order
        $order->update([
            'status' => $newStatus,
            // use real newline when appending notes if provided
            'notes' => $request->notes ? ($order->notes ? $order->notes . "\n\n" : '') . $request->notes : $order->notes,
            'updated_at' => now()
        ]);

        // Respon tergantung tipe request
        if ($request->ajax() || $request->wantsJson() || $request->isJson()) {
            return response()->json([
                'success' => true,
                'status' => $newStatus
            ]);
        }

        return redirect()->back()
                        ->with('success', 'Status pesanan berhasil diperbarui.');
    }

    /**
     * Add notes to order
     */
    public function addNotes(Request $request, Transaction $order)
    {
        // The form uses field name "note"
        $request->validate([
            'note' => 'required|string|max:500'
        ]);

        $currentNotes = $order->notes ?? '';
        $newNotes = ($currentNotes ? $currentNotes . "\n\n" : '') . '[' . now()->format('Y-m-d H:i') . '] ' . $request->note;

        $order->update(['notes' => $newNotes]);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Catatan berhasil ditambahkan.',
                'notes' => $order->notes,
            ]);
        }

        return redirect()->back()->with('success', 'Catatan berhasil ditambahkan.');
     }

    /**
     * Print order invoice
     */
    public function printInvoice(Transaction $order)
    {
        $order->load(['user', 'transactionDetails.product']);
        
        return view('admin.orders.invoice', compact('order'));
    }

    /**
     * Export orders to CSV
     */
    public function export(Request $request)
    {
        $query = Transaction::with(['user', 'transactionDetails.product']);
        
        // Apply same filters as index
        if ($request->has('status') && $request->status != '') {
            $query->where('status', $request->status);
        }
        
        if ($request->has('date_from') && $request->date_from != '') {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        
        if ($request->has('date_to') && $request->date_to != '') {
            $query->whereDate('created_at', '<=', $request->date_to);
        }
        
        $orders = $query->orderBy('created_at', 'desc')->get();
        
        $filename = 'orders_' . now()->format('Y-m-d_H-i-s') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];
        
        $callback = function() use ($orders) {
            $file = fopen('php://output', 'w');
            
            // CSV headers
            fputcsv($file, [
                'ID Pesanan',
                'Nama Pelanggan',
                'Email Pelanggan',
                'No. Telepon',
                'Status',
                'Total',
                'Tanggal Pesanan',
                'Item',
                'Catatan'
            ]);
            
            foreach ($orders as $order) {
                $items = $order->transactionDetails->map(function($detail) {
                    return $detail->product->name . ' (x' . $detail->quantity . ')';
                })->implode(', ');
                
                fputcsv($file, [
                    $order->id,
                    $order->customer_name,
                    $order->customer_email,
                    $order->customer_phone,
                    $this->translateStatus($order->status),
                    $order->total_amount,
                    $order->created_at->format('Y-m-d H:i:s'),
                    $items,
                    $order->notes
                ]);
            }
            
            fclose($file);
        };
        
        return response()->stream($callback, 200, $headers);
    }

    /**
     * Bulk actions for orders
     */
    public function bulkAction(Request $request)
    {
        $request->validate([
            'action' => 'required|in:confirm,process,ship,deliver,cancel',
            'order_ids' => 'required|array',
            'order_ids.*' => 'exists:transactions,id'
        ]);

        $orderIds = $request->order_ids;
        $action = $request->action;
        $count = 0;
        $errors = [];

        foreach ($orderIds as $orderId) {
            $order = Transaction::find($orderId);
            
            $newStatus = $this->getStatusFromAction($action);
            
            if ($this->isValidStatusTransition($order->status, $newStatus)) {
                $order->update(['status' => $newStatus]);
                $count++;
            } else {
                $errors[] = "Order #{$orderId} tidak dapat diubah dari {$order->status} ke {$newStatus}";
            }
        }

        $message = "{$count} pesanan berhasil diperbarui.";
        if (!empty($errors)) {
            $message .= ' Beberapa pesanan tidak dapat diperbarui: ' . implode(', ', $errors);
        }

        return redirect()->route('admin.orders.index')
                        ->with('success', $message);
    }

    /**
     * Get order statistics
     */
    private function getOrderStats()
    {
        $today = Carbon::today();
        $thisMonth = Carbon::now()->startOfMonth();
        
        return [
            'total' => Transaction::count(),
            'pending' => Transaction::where('status', 'pending')->count(),
            'confirmed' => Transaction::where('status', 'confirmed')->count(),
            'processing' => Transaction::where('status', 'processing')->count(),
            'shipped' => Transaction::where('status', 'shipped')->count(),
            'delivered' => Transaction::where('status', 'delivered')->count(),
            'cancelled' => Transaction::where('status', 'cancelled')->count(),
            'today_orders' => Transaction::whereDate('created_at', $today)->count(),
            'today_revenue' => Transaction::whereDate('created_at', $today)->where('status', 'delivered')->sum('total_amount'),
            'month_orders' => Transaction::where('created_at', '>=', $thisMonth)->count(),
            'month_revenue' => Transaction::where('created_at', '>=', $thisMonth)->where('status', 'delivered')->sum('total_amount'),
            'average_order_value' => Transaction::where('status', 'delivered')->avg('total_amount') ?? 0,
        ];
    }

    /**
     * Get order timeline/history
     */
    private function getOrderTimeline($order)
    {
        $timeline = [];
        
        // Order created
        $timeline[] = [
            'status' => 'created',
            'title' => 'Pesanan Dibuat',
            'description' => 'Pesanan dibuat oleh pelanggan',
            'timestamp' => $order->created_at,
            'icon' => 'plus-circle',
            'color' => 'blue'
        ];
        
        // Status changes (this would be better with a separate order_history table)
        if ($order->status !== 'pending') {
            $statusMap = [
                'confirmed' => ['Pesanan Dikonfirmasi', 'Pesanan telah dikonfirmasi dan akan diproses', 'check-circle', 'green'],
                'processing' => ['Sedang Diproses', 'Pesanan sedang disiapkan', 'clock', 'yellow'],
                'shipped' => ['Dalam Pengiriman', 'Pesanan sedang dalam perjalanan', 'truck', 'blue'],
                'delivered' => ['Pesanan Selesai', 'Pesanan telah sampai di tujuan', 'check-circle', 'green'],
                'cancelled' => ['Pesanan Dibatalkan', 'Pesanan telah dibatalkan', 'x-circle', 'red']
            ];
            
            if (isset($statusMap[$order->status])) {
                $timeline[] = [
                    'status' => $order->status,
                    'title' => $statusMap[$order->status][0],
                    'description' => $statusMap[$order->status][1],
                    'timestamp' => $order->updated_at,
                    'icon' => $statusMap[$order->status][2],
                    'color' => $statusMap[$order->status][3]
                ];
            }
        }
        
        return $timeline;
    }

    /**
     * Check if status transition is valid
     */
    private function isValidStatusTransition($from, $to)
    {
        $validTransitions = [
            // Jalur status untuk workflow dengan shipping
            'pending' => ['confirmed', 'cancelled'],
            'confirmed' => ['processing', 'cancelled'],
            // Dukung dua alur: ke shipped/delivered ATAU ke ready/completed
            'processing' => ['shipped', 'ready', 'cancelled'],
            'shipped' => ['delivered'],
            'delivered' => [], // Final state
            // Jalur status untuk workflow pickup/takeaway
            'ready' => ['completed'],
            'completed' => [], // Final state
            'cancelled' => [] // Final state
        ];
        
        return in_array($to, $validTransitions[$from] ?? []);
    }

    /**
     * Get status from bulk action
     */
    private function getStatusFromAction($action)
    {
        $actionMap = [
            'confirm' => 'confirmed',
            'process' => 'processing',
            'ship' => 'shipped',
            'deliver' => 'delivered',
            'cancel' => 'cancelled'
        ];
        
        return $actionMap[$action] ?? null;
    }

    public function approvePayment(Request $request, Transaction $order)
    {
        if (!$order->payment_proof) {
            return back()->with('error', 'Tidak ada bukti pembayaran untuk diverifikasi.');
        }

        if ($order->payment_status === 'verified') {
            return back()->with('info', 'Pembayaran sudah diverifikasi sebelumnya.');
        }

        $order->update([
            'payment_status' => 'verified',
            'status' => $order->status === 'pending' ? 'confirmed' : $order->status,
            'payment_verified_at' => now(),
            'tracking_number' => $order->tracking_number ?: ('RESI-' . strtoupper(Str::random(10)))
        ]);

        return back()->with('success', 'Pembayaran telah diverifikasi dan pesanan dikonfirmasi.');
    }

    public function rejectPayment(Request $request, Transaction $order)
    {
        $request->validate([
            'reason' => 'required|string|max:500'
        ]);

        if ($order->payment_status === 'verified') {
            return back()->with('error', 'Pembayaran sudah diverifikasi, tidak dapat ditolak.');
        }

        $order->update([
            'payment_status' => 'rejected',
            'notes' => ($order->notes ? $order->notes."\n\n" : '') . '[Admin] Pembayaran ditolak: ' . $request->reason
        ]);

        return back()->with('success', 'Bukti pembayaran ditolak.');
    }

    private function translateStatus($status)
    {
        $map = [
            'pending' => 'Menunggu',
            'confirmed' => 'Dikonfirmasi',
            'processing' => 'Diproses',
            'ready' => 'Siap',
            'completed' => 'Selesai',
            'shipped' => 'Dikirim',
            'delivered' => 'Terkirim',
            'cancelled' => 'Dibatalkan',
        ];
        return $map[$status] ?? ucfirst($status);
    }
}