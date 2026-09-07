<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Product;
use App\Models\Category;
use App\Models\Transaction;
use App\Models\TransactionDetail;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    /**
     * Display admin dashboard
     */
    public function index(Request $request)
    {
        // Get date range for filtering (default to current month)
        $startDate = $request->get('start_date', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->get('end_date', Carbon::now()->endOfMonth()->format('Y-m-d'));
        
        // Basic statistics
        $stats = $this->getBasicStats();
        
        // Revenue statistics for the selected period
        $revenueStats = $this->getRevenueStats($startDate, $endDate);
        
        // Recent orders
        $recentOrders = $this->getRecentOrders();
        
        // Top selling products
        $topProducts = $this->getTopSellingProducts($startDate, $endDate);
        
        // Order status distribution
        $orderStatusStats = $this->getOrderStatusStats();
        
        // Monthly revenue chart data
        $monthlyRevenue = $this->getMonthlyRevenueData();
        
        // Low stock products
        $lowStockProducts = $this->getLowStockProducts();
        
        return view('admin.dashboard', compact(
            'stats',
            'revenueStats', 
            'recentOrders',
            'topProducts',
            'orderStatusStats',
            'monthlyRevenue',
            'lowStockProducts',
            'startDate',
            'endDate'
        ));
    }
    
    /**
     * Get basic statistics
     */
    private function getBasicStats()
    {
        return [
            'total_users' => User::where('role', 'customer')->count(),
            'total_products' => Product::count(),
            'total_categories' => Category::count(),
            'total_orders' => Transaction::count(),
            'active_products' => Product::where('is_active', true)->count(),
            'featured_products' => Product::where('is_featured', true)->count(),
            'pending_orders' => Transaction::where('status', 'pending')->count(),
            'completed_orders' => Transaction::where('status', 'delivered')->count(),
        ];
    }
    
    /**
     * Get revenue statistics for date range
     */
    private function getRevenueStats($startDate, $endDate)
    {
        $currentPeriod = Transaction::whereBetween('created_at', [$startDate, $endDate])
                                  ->where('status', 'delivered')
                                  ->sum('total_amount');
        
        // Calculate previous period for comparison
        $periodDays = Carbon::parse($startDate)->diffInDays(Carbon::parse($endDate)) + 1;
        $previousStart = Carbon::parse($startDate)->subDays($periodDays)->format('Y-m-d');
        $previousEnd = Carbon::parse($startDate)->subDay()->format('Y-m-d');
        
        $previousPeriod = Transaction::whereBetween('created_at', [$previousStart, $previousEnd])
                                   ->where('status', 'delivered')
                                   ->sum('total_amount');
        
        // Calculate growth percentage
        $growth = 0;
        if ($previousPeriod > 0) {
            $growth = (($currentPeriod - $previousPeriod) / $previousPeriod) * 100;
        } elseif ($currentPeriod > 0) {
            $growth = 100;
        }
        
        return [
            'current_revenue' => $currentPeriod,
            'previous_revenue' => $previousPeriod,
            'growth_percentage' => round($growth, 1),
            'total_orders' => Transaction::whereBetween('created_at', [$startDate, $endDate])->count(),
            'average_order_value' => Transaction::whereBetween('created_at', [$startDate, $endDate])
                                              ->where('status', 'delivered')
                                              ->avg('total_amount') ?? 0,
        ];
    }
    
    /**
     * Get recent orders
     */
    private function getRecentOrders($limit = 10)
    {
        return Transaction::with(['user', 'transactionDetails.product'])
                         ->orderBy('created_at', 'desc')
                         ->limit($limit)
                         ->get();
    }
    
    /**
     * Get top selling products
     */
    private function getTopSellingProducts($startDate, $endDate, $limit = 10)
    {
        return TransactionDetail::select('product_id', DB::raw('SUM(quantity) as total_sold'), DB::raw('SUM(subtotal) as total_revenue'))
                               ->with('product')
                               ->whereHas('transaction', function($query) use ($startDate, $endDate) {
                                   $query->whereBetween('created_at', [$startDate, $endDate])
                                         ->where('status', 'delivered');
                               })
                               ->groupBy('product_id')
                               ->orderBy('total_sold', 'desc')
                               ->limit($limit)
                               ->get();
    }
    
    /**
     * Get order status distribution
     */
    private function getOrderStatusStats()
    {
        return Transaction::select('status', DB::raw('count(*) as count'))
                         ->groupBy('status')
                         ->pluck('count', 'status')
                         ->toArray();
    }
    
    /**
     * Get monthly revenue data for chart
     */
    private function getMonthlyRevenueData($months = 12)
    {
        $data = [];
        
        for ($i = $months - 1; $i >= 0; $i--) {
            $date = Carbon::now()->subMonths($i);
            $monthStart = $date->copy()->startOfMonth();
            $monthEnd = $date->copy()->endOfMonth();
            
            $revenue = Transaction::whereBetween('created_at', [$monthStart, $monthEnd])
                                 ->where('status', 'delivered')
                                 ->sum('total_amount');
            
            $data[] = [
                'month' => $date->format('M Y'),
                'revenue' => $revenue,
                'orders' => Transaction::whereBetween('created_at', [$monthStart, $monthEnd])->count()
            ];
        }
        
        return $data;
    }
    
    /**
     * Get low stock products
     */
    private function getLowStockProducts($threshold = 10)
    {
        return Product::where('is_active', true)
                     ->where('stock', '<=', $threshold)
                     ->orderBy('stock', 'asc')
                     ->limit(10)
                     ->get();
    }
    
    /**
     * Get sales analytics data
     */
    public function analytics(Request $request)
    {
        $period = $request->get('period', 'month'); // day, week, month, year
        
        switch ($period) {
            case 'day':
                $data = $this->getDailySalesData();
                break;
            case 'week':
                $data = $this->getWeeklySalesData();
                break;
            case 'year':
                $data = $this->getYearlySalesData();
                break;
            default:
                $data = $this->getMonthlyRevenueData();
        }
        
        return response()->json($data);
    }
    
    /**
     * Get daily sales data for current month
     */
    private function getDailySalesData()
    {
        $data = [];
        $startOfMonth = Carbon::now()->startOfMonth();
        $endOfMonth = Carbon::now()->endOfMonth();
        
        for ($date = $startOfMonth->copy(); $date->lte($endOfMonth); $date->addDay()) {
            $dayStart = $date->copy()->startOfDay();
            $dayEnd = $date->copy()->endOfDay();
            
            $revenue = Transaction::whereBetween('created_at', [$dayStart, $dayEnd])
                                 ->where('status', 'delivered')
                                 ->sum('total_amount');
            
            $data[] = [
                'date' => $date->format('d M'),
                'revenue' => $revenue,
                'orders' => Transaction::whereBetween('created_at', [$dayStart, $dayEnd])->count()
            ];
        }
        
        return $data;
    }
    
    /**
     * Get weekly sales data for current year
     */
    private function getWeeklySalesData()
    {
        $data = [];
        $startOfYear = Carbon::now()->startOfYear();
        $currentWeek = Carbon::now();
        
        for ($week = $startOfYear->copy(); $week->lte($currentWeek); $week->addWeek()) {
            $weekStart = $week->copy()->startOfWeek();
            $weekEnd = $week->copy()->endOfWeek();
            
            $revenue = Transaction::whereBetween('created_at', [$weekStart, $weekEnd])
                                 ->where('status', 'delivered')
                                 ->sum('total_amount');
            
            $data[] = [
                'week' => 'Week ' . $week->weekOfYear,
                'revenue' => $revenue,
                'orders' => Transaction::whereBetween('created_at', [$weekStart, $weekEnd])->count()
            ];
        }
        
        return $data;
    }
    
    /**
     * Get yearly sales data
     */
    private function getYearlySalesData($years = 5)
    {
        $data = [];
        
        for ($i = $years - 1; $i >= 0; $i--) {
            $year = Carbon::now()->subYears($i)->year;
            $yearStart = Carbon::createFromDate($year, 1, 1)->startOfYear();
            $yearEnd = Carbon::createFromDate($year, 12, 31)->endOfYear();
            
            $revenue = Transaction::whereBetween('created_at', [$yearStart, $yearEnd])
                                 ->where('status', 'delivered')
                                 ->sum('total_amount');
            
            $data[] = [
                'year' => $year,
                'revenue' => $revenue,
                'orders' => Transaction::whereBetween('created_at', [$yearStart, $yearEnd])->count()
            ];
        }
        
        return $data;
    }
}