<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Category;
use App\Models\Transaction;
use App\Models\TransactionDetail;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class AdminController extends Controller
{
    /**
     * Display admin dashboard
     */
    public function dashboard()
    {
        $today = Carbon::today();
        $thisMonth = Carbon::now()->startOfMonth();
        $lastMonth = Carbon::now()->subMonth()->startOfMonth();
        
        // Basic statistics
        $stats = [
            'total_products' => Product::count(),
            'active_products' => Product::where('is_active', true)->count(),
            'total_categories' => Category::count(),
            'total_customers' => User::where('role', 'customer')->count(),
            
            // Today's stats
            'today_orders' => Transaction::whereDate('created_at', $today)->count(),
            'today_revenue' => Transaction::whereDate('created_at', $today)
                ->where('status', 'completed')
                ->sum('total_amount'),
            
            // This month's stats
            'month_orders' => Transaction::where('created_at', '>=', $thisMonth)->count(),
            'month_revenue' => Transaction::where('created_at', '>=', $thisMonth)
                ->where('status', 'completed')
                ->sum('total_amount'),
            
            // Order status counts
            'pending_orders' => Transaction::where('status', 'pending')->count(),
            'processing_orders' => Transaction::where('status', 'processing')->count(),
            'ready_orders' => Transaction::where('status', 'ready')->count(),
        ];
        
        // Recent orders
        $recentOrders = Transaction::with(['user', 'transactionDetails'])
            ->latest()
            ->take(10)
            ->get();
        
        // Top selling products this month
        $topProducts = TransactionDetail::select('product_id', DB::raw('SUM(quantity) as total_sold'))
            ->whereHas('transaction', function($query) use ($thisMonth) {
                $query->where('created_at', '>=', $thisMonth)
                      ->where('status', 'completed');
            })
            ->with('product')
            ->groupBy('product_id')
            ->orderBy('total_sold', 'desc')
            ->take(5)
            ->get();
        
        // Revenue chart data (last 7 days)
        $revenueChart = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i);
            $revenue = Transaction::whereDate('created_at', $date)
                ->where('status', 'completed')
                ->sum('total_amount');
            $revenueChart[] = [
                'date' => $date->format('M d'),
                'revenue' => $revenue
            ];
        }
        
        return view('admin.dashboard', compact('stats', 'recentOrders', 'topProducts', 'revenueChart'));
    }
    
    /**
     * Display sales report
     */
    public function salesReport(Request $request)
    {
        $startDate = $request->get('start_date', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->get('end_date', Carbon::now()->format('Y-m-d'));
        
        // Sales summary
        $salesSummary = Transaction::whereBetween('created_at', [$startDate, $endDate . ' 23:59:59'])
            ->where('status', 'completed')
            ->selectRaw('
                COUNT(*) as total_orders,
                SUM(total_amount) as total_revenue,
                AVG(total_amount) as average_order_value,
                DATE(created_at) as order_date
            ')
            ->groupBy('order_date')
            ->orderBy('order_date')
            ->get();
        
        // Product sales
        $productSales = TransactionDetail::select(
                'products.name',
                'products.price',
                DB::raw('SUM(transaction_details.quantity) as total_sold'),
                DB::raw('SUM(transaction_details.subtotal) as total_revenue')
            )
            ->join('products', 'transaction_details.product_id', '=', 'products.id')
            ->join('transactions', 'transaction_details.transaction_id', '=', 'transactions.id')
            ->whereBetween('transactions.created_at', [$startDate, $endDate . ' 23:59:59'])
            ->where('transactions.status', 'completed')
            ->groupBy('products.id', 'products.name', 'products.price')
            ->orderBy('total_sold', 'desc')
            ->get();
        
        // Category sales
        $categorySales = TransactionDetail::select(
                'categories.name as category_name',
                DB::raw('SUM(transaction_details.quantity) as total_sold'),
                DB::raw('SUM(transaction_details.subtotal) as total_revenue')
            )
            ->join('products', 'transaction_details.product_id', '=', 'products.id')
            ->join('categories', 'products.category_id', '=', 'categories.id')
            ->join('transactions', 'transaction_details.transaction_id', '=', 'transactions.id')
            ->whereBetween('transactions.created_at', [$startDate, $endDate . ' 23:59:59'])
            ->where('transactions.status', 'completed')
            ->groupBy('categories.id', 'categories.name')
            ->orderBy('total_revenue', 'desc')
            ->get();
        
        return view('admin.reports.sales', compact(
            'salesSummary', 
            'productSales', 
            'categorySales', 
            'startDate', 
            'endDate'
        ));
    }
    
    /**
     * Display inventory report
     */
    public function inventoryReport()
    {
        $products = Product::with('category')
            ->withCount(['transactionDetails as total_sold' => function($query) {
                $query->select(DB::raw('SUM(quantity)'));
            }])
            ->get();
        
        $lowStockProducts = Product::where('stock', '<=', 10)
            ->where('is_active', true)
            ->with('category')
            ->get();
        
        $inactiveProducts = Product::where('is_active', false)
            ->with('category')
            ->get();
        
        return view('admin.reports.inventory', compact(
            'products', 
            'lowStockProducts', 
            'inactiveProducts'
        ));
    }
    
    /**
     * Display customer report
     */
    public function customerReport(Request $request)
    {
        $startDate = $request->get('start_date', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->get('end_date', Carbon::now()->format('Y-m-d'));
    
        // Normalisasi rentang waktu
        $startDateTime = $startDate . ' 00:00:00';
        $endDateTime = $endDate . ' 23:59:59';
        
        // Statistik pelanggan berbasis transaksi (opsional dipakai untuk analisis lebih lanjut)
        $customerStats = Transaction::select(
                'customer_name',
                'customer_email',
                'customer_phone',
                DB::raw('COUNT(*) as total_orders'),
                DB::raw('SUM(total_amount) as total_spent'),
                DB::raw('AVG(total_amount) as average_order_value'),
                DB::raw('MAX(created_at) as last_order_date')
            )
            ->whereBetween('created_at', [$startDateTime, $endDateTime])
            ->where('status', 'completed')
            ->whereNotNull('customer_name')
            ->groupBy('customer_name', 'customer_email', 'customer_phone')
            ->orderBy('total_spent', 'desc')
            ->get();
        
        // Pelanggan baru (berdasarkan transaksi)
        $newCustomers = Transaction::select('customer_name', 'customer_email', 'customer_phone', 'created_at')
            ->whereBetween('created_at', [$startDateTime, $endDateTime])
            ->where('status', 'completed')
            ->whereNotNull('customer_name')
            ->whereRaw('customer_email NOT IN (
                SELECT customer_email 
                FROM transactions 
                WHERE created_at < ? AND customer_email IS NOT NULL)', [$startDateTime])
            ->orderBy('created_at', 'desc')
            ->get();
        
        // Kartu ringkasan
        $totalCustomers = User::where('role', 'customer')->count();
        $activeCustomers = User::where('role', 'customer')
            ->whereHas('transactions', function($query) {
                $query->where('created_at', '>=', Carbon::now()->subDays(30));
            })->count();
        $newCustomersThisMonth = User::where('role', 'customer')
            ->whereMonth('created_at', Carbon::now()->month)
            ->whereYear('created_at', Carbon::now()->year)
            ->count();
        $averageOrderValue = Transaction::where('status', 'completed')
            ->whereBetween('created_at', [$startDateTime, $endDateTime])
            ->avg('total_amount') ?? 0;

        // Subquery untuk metrik per user pada rentang tanggal
        $ordersCountSub = Transaction::selectRaw('COUNT(*)')
            ->whereColumn('user_id', 'users.id')
            ->whereBetween('created_at', [$startDateTime, $endDateTime])
            ->where('status', 'completed');

        $totalSpentSub = Transaction::selectRaw('COALESCE(SUM(total_amount), 0)')
            ->whereColumn('user_id', 'users.id')
            ->whereBetween('created_at', [$startDateTime, $endDateTime])
            ->where('status', 'completed');

        $lastOrderSub = Transaction::selectRaw('MAX(created_at)')
            ->whereColumn('user_id', 'users.id')
            ->whereBetween('created_at', [$startDateTime, $endDateTime])
            ->where('status', 'completed');

        // Semua pelanggan terdaftar dengan metrik yang dibutuhkan tabel "Semua Pelanggan"
        $customers = User::where('role', 'customer')
            ->select('users.*')
            ->selectSub($ordersCountSub, 'orders_count')
            ->selectSub($totalSpentSub, 'total_spent')
            ->selectSub($lastOrderSub, 'last_order_date')
            ->orderBy('name')
            ->get();

        // Top 10 pelanggan berdasarkan total_spent pada rentang tanggal
        $topCustomers = $customers->sortByDesc('total_spent')
            ->take(10)
            ->values()
            ->map(function ($u) {
                $u->average_order = ($u->orders_count ?? 0) > 0 ? round($u->total_spent / $u->orders_count) : 0;
                $u->last_order_date = $u->last_order_date ? Carbon::parse($u->last_order_date) : null;
                return $u;
            });

        // Registrasi pelanggan per bulan pada rentang tanggal (untuk chart)
        $monthlyRaw = User::where('role', 'customer')
            ->whereBetween('created_at', [$startDateTime, $endDateTime])
            ->selectRaw("DATE_FORMAT(created_at, '%Y-%m') as ym, COUNT(*) as count")
            ->groupBy('ym')
            ->orderBy('ym')
            ->get();

        $monthlyRegistrations = collect($monthlyRaw)->map(function ($row) {
            $label = Carbon::createFromFormat('Y-m', $row->ym)->format('M Y');
            return ['month' => $label, 'count' => (int) $row->count];
        });

        // Distribusi tipe pelanggan berdasarkan jumlah pesanan
        $customerTypes = [
            'new' => $customers->filter(fn($c) => ($c->orders_count ?? 0) < 2)->count(),
            'regular' => $customers->filter(fn($c) => ($c->orders_count ?? 0) >= 2 && ($c->orders_count ?? 0) < 5)->count(),
            'loyal' => $customers->filter(fn($c) => ($c->orders_count ?? 0) >= 5 && ($c->orders_count ?? 0) < 10)->count(),
            'vip' => $customers->filter(fn($c) => ($c->orders_count ?? 0) >= 10)->count(),
        ];
        
        return view('admin.reports.customers', compact(
            'customerStats', 
            'newCustomers', 
            'startDate', 
            'endDate',
            'totalCustomers',
            'activeCustomers', 
            'newCustomersThisMonth',
            'averageOrderValue',
            'customers',
            'topCustomers',
            'monthlyRegistrations',
            'customerTypes'
        ));
    }
    
    /**
     * Export sales report to CSV
     */
    public function exportSalesReport(Request $request)
    {
        $startDate = $request->get('start_date', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->get('end_date', Carbon::now()->format('Y-m-d'));
        
        $transactions = Transaction::with(['transactionDetails.product'])
            ->whereBetween('created_at', [$startDate, $endDate . ' 23:59:59'])
            ->where('status', 'completed')
            ->orderBy('created_at')
            ->get();
        
        $filename = 'sales_report_' . $startDate . '_to_' . $endDate . '.csv';
        
        // Gunakan delimiter ";" agar Excel (lokal ID) otomatis memisahkan kolom
        $delimiter = ';';
        
        // Hitung total pendapatan dari transaksi (bukan menjumlah kolom Total per baris detail)
        $grandTotal = $transactions->sum('total_amount');
        
        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];
        
        $callback = function() use ($transactions, $delimiter, $grandTotal) {
            $file = fopen('php://output', 'w');
            
            // Tulis BOM agar Excel membaca UTF-8 dengan benar
            fwrite($file, "\xEF\xBB\xBF");
            // Hint ke Excel untuk memakai delimiter yang dipilih
            fwrite($file, "sep=" . $delimiter . "\n");
            
            // CSV headers (pakai delimiter ";")
            fputcsv($file, [
                'Kode Transaksi',
                'Tanggal',
                'Nama Pelanggan',
                'No. Telepon',
                'Nama Produk',
                'Kuantitas',
                'Harga',
                'Subtotal',
                'Total',
                'Metode Pembayaran',
                'Status'
            ], $delimiter);
            
            foreach ($transactions as $transaction) {
                foreach ($transaction->transactionDetails as $detail) {
                    // Angka diekspor tanpa pemisah ribuan supaya Excel mengenalinya sebagai angka
                    $price = $detail->price ?? $detail->unit_price ?? 0;
                    $subtotal = $detail->subtotal ?? ($price * ($detail->quantity ?? 0));
                    $total = $transaction->total_amount ?? 0;
                    
                    fputcsv($file, [
                        $transaction->transaction_code,
                        $transaction->created_at->format('Y-m-d H:i:s'),
                        $transaction->customer_name ?: 'Guest',
                        $transaction->customer_phone,
                        optional($detail->product)->name,
                        (int) $detail->quantity,
                        $price,
                        $subtotal,
                        $total,
                        $this->translatePaymentMethod($transaction->payment_method),
                        $this->translateStatus($transaction->status)
                    ], $delimiter);
                }
            }
            
            // Baris kosong pemisah
            fputcsv($file, [], $delimiter);
            // Baris total pendapatan (label di kolom 8, angka di kolom 9 agar sejajar dengan kolom "Total")
            fputcsv($file, ['', '', '', '', '', '', '', 'TOTAL PENDAPATAN', $grandTotal, '', ''], $delimiter);
            
            fclose($file);
        };
        
        return response()->stream($callback, 200, $headers);
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

    private function translatePaymentMethod($method)
    {
        $map = [
            'transfer' => 'Transfer Bank',
        ];
        return $map[$method] ?? ucfirst($method);
    }
}