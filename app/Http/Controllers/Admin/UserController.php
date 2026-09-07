<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use App\Models\User;
use App\Models\Transaction;
use Carbon\Carbon;

class UserController extends Controller
{
    /**
     * Display a listing of users
     */
    public function index(Request $request)
    {
        $query = User::withCount(['transactions']);
        
        // Search functionality
        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%')
                  ->orWhere('email', 'like', '%' . $search . '%')
                  ->orWhere('phone', 'like', '%' . $search . '%');
            });
        }
        
        // Filter by role
        if ($request->has('role') && $request->role != '') {
            $query->where('role', $request->role);
        }
        
        // Filter by registration date
        if ($request->has('date_from') && $request->date_from != '') {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        
        if ($request->has('date_to') && $request->date_to != '') {
            $query->whereDate('created_at', '<=', $request->date_to);
        }
        
        // Sort functionality
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);
        
        $users = $query->paginate(15);
        
        // Statistics
        $stats = $this->getUserStats();
        
        return view('admin.users.index', compact('users', 'stats'));
    }

    /**
     * Show the form for creating a new user
     */
    public function create()
    {
        return view('admin.users.create');
    }

    /**
     * Store a newly created user
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
            'role' => 'required|in:customer,admin',
            'avatar' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        // Handle avatar upload
        $avatarPath = null;
        if ($request->hasFile('avatar')) {
            $avatarPath = $request->file('avatar')->store('avatars', 'public');
        }

        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'phone' => $request->phone,
            'address' => $request->address,
            'role' => $request->role,
            'avatar' => $avatarPath,
            'email_verified_at' => now(), // Auto-verify admin created users
        ]);

        return redirect()->route('admin.users.index')
                        ->with('success', 'Pengguna berhasil ditambahkan.');
    }

    /**
     * Display the specified user
     */
    public function show(User $user)
    {
        // Get user statistics
        $stats = $this->getUserDetailStats($user->id);
        
        // Get recent orders
        $recentOrders = Transaction::where('user_id', $user->id)
                                  ->with(['transactionDetails.product'])
                                  ->orderBy('created_at', 'desc')
                                  ->limit(10)
                                  ->get();
        
        // Get monthly order data for chart
        $monthlyOrders = $this->getMonthlyOrderData($user->id);
        
        return view('admin.users.show', compact('user', 'stats', 'recentOrders', 'monthlyOrders'));
    }

    /**
     * Show the form for editing the specified user
     */
    public function edit(User $user)
    {
        return view('admin.users.edit', compact('user'));
    }

    /**
     * Update the specified user
     */
    public function update(Request $request, User $user)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique('users')->ignore($user->id),
            ],
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
            'role' => 'required|in:customer,admin',
            'avatar' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'password' => 'nullable|string|min:8|confirmed',
        ]);

        // Handle avatar upload
        $avatarPath = $user->avatar;
        if ($request->hasFile('avatar')) {
            // Delete old avatar
            if ($user->avatar && Storage::disk('public')->exists($user->avatar)) {
                Storage::disk('public')->delete($user->avatar);
            }
            $avatarPath = $request->file('avatar')->store('avatars', 'public');
        }

        // Prepare update data
        $updateData = [
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'address' => $request->address,
            'role' => $request->role,
            'avatar' => $avatarPath,
        ];

        // Update password if provided
        if ($request->filled('password')) {
            $updateData['password'] = Hash::make($request->password);
        }

        $user->update($updateData);

        return redirect()->route('admin.users.index')
                        ->with('success', 'Pengguna berhasil diperbarui.');
    }

    /**
     * Remove the specified user
     */
    public function destroy(User $user)
    {
        // Prevent deletion of admin users
        if ($user->role === 'admin') {
            return redirect()->route('admin.users.index')
                           ->with('error', 'Admin tidak dapat dihapus.');
        }

        // Check if user has pending orders
        $pendingOrders = Transaction::where('user_id', $user->id)
                                   ->whereIn('status', ['pending', 'confirmed', 'processing', 'shipped'])
                                   ->count();

        if ($pendingOrders > 0) {
            return redirect()->route('admin.users.index')
                           ->with('error', 'Pengguna tidak dapat dihapus karena masih memiliki pesanan yang sedang diproses.');
        }

        // Delete avatar if exists
        if ($user->avatar && Storage::disk('public')->exists($user->avatar)) {
            Storage::disk('public')->delete($user->avatar);
        }

        $user->delete();

        return redirect()->route('admin.users.index')
                        ->with('success', 'Pengguna berhasil dihapus.');
    }

    /**
     * Toggle user role (customer/admin)
     */
    public function toggleRole(User $user)
    {
        $newRole = $user->role === 'admin' ? 'customer' : 'admin';
        
        $user->update(['role' => $newRole]);

        return redirect()->back()
                        ->with('success', "Role pengguna berhasil diubah menjadi {$newRole}.");
    }

    /**
     * Reset user password
     */
    public function resetPassword(Request $request, User $user)
    {
        $request->validate([
            'password' => 'required|string|min:8|confirmed',
        ]);

        $user->update([
            'password' => Hash::make($request->password)
        ]);

        return redirect()->back()
                        ->with('success', 'Password pengguna berhasil direset.');
    }

    /**
     * Bulk actions for users
     */
    public function bulkAction(Request $request)
    {
        $request->validate([
            'action' => 'required|in:make_admin,make_customer,delete',
            'user_ids' => 'required|array',
            'user_ids.*' => 'exists:users,id'
        ]);

        $userIds = $request->user_ids;
        $action = $request->action;
        $count = 0;
        $errors = [];

        switch ($action) {
            case 'make_admin':
                User::whereIn('id', $userIds)->update(['role' => 'admin']);
                $count = count($userIds);
                $message = "{$count} pengguna berhasil dijadikan admin.";
                break;
                
            case 'make_customer':
                User::whereIn('id', $userIds)->update(['role' => 'customer']);
                $count = count($userIds);
                $message = "{$count} pengguna berhasil dijadikan customer.";
                break;
                
            case 'delete':
                foreach ($userIds as $userId) {
                    $user = User::find($userId);
                    
                    // Skip admin users
                    if ($user->role === 'admin') {
                        $errors[] = "User {$user->name} (admin) tidak dapat dihapus";
                        continue;
                    }
                    
                    // Check pending orders
                    $pendingOrders = Transaction::where('user_id', $userId)
                                               ->whereIn('status', ['pending', 'confirmed', 'processing', 'shipped'])
                                               ->count();
                    
                    if ($pendingOrders > 0) {
                        $errors[] = "User {$user->name} memiliki pesanan aktif";
                        continue;
                    }
                    
                    // Delete avatar
                    if ($user->avatar && Storage::disk('public')->exists($user->avatar)) {
                        Storage::disk('public')->delete($user->avatar);
                    }
                    
                    $user->delete();
                    $count++;
                }
                
                $message = "{$count} pengguna berhasil dihapus.";
                if (!empty($errors)) {
                    $message .= ' Beberapa pengguna tidak dapat dihapus: ' . implode(', ', $errors);
                }
                break;
        }

        return redirect()->route('admin.users.index')
                        ->with('success', $message);
    }

    /**
     * Export users to CSV
     */
    public function export(Request $request)
    {
        $query = User::query();
        
        // Apply same filters as index
        if ($request->has('role') && $request->role != '') {
            $query->where('role', $request->role);
        }
        
        if ($request->has('date_from') && $request->date_from != '') {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        
        if ($request->has('date_to') && $request->date_to != '') {
            $query->whereDate('created_at', '<=', $request->date_to);
        }
        
        $users = $query->orderBy('created_at', 'desc')->get();
        
        $filename = 'users_' . now()->format('Y-m-d_H-i-s') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];
        
        $callback = function() use ($users) {
            $file = fopen('php://output', 'w');
            
            // CSV headers
            fputcsv($file, [
                'ID',
                'Nama',
                'Email',
                'Telepon',
                'Peran',
                'Tanggal Registrasi',
                'Email Terverifikasi',
                'Total Pesanan',
                'Total Belanja'
            ]);
            
            foreach ($users as $user) {
                $totalOrders = Transaction::where('user_id', $user->id)->count();
                $totalSpent = Transaction::where('user_id', $user->id)->where('status', 'delivered')->sum('total_amount');
                
                fputcsv($file, [
                    $user->id,
                    $user->name,
                    $user->email,
                    $user->phone,
                    $this->translateRole($user->role),
                    $user->created_at->format('Y-m-d H:i:s'),
                    $user->email_verified_at ? 'Ya' : 'Tidak',
                    $totalOrders,
                    $totalSpent
                ]);
            }
            
            fclose($file);
        };
        
        return response()->stream($callback, 200, $headers);
    }

    /**
     * Get user statistics
     */
    private function getUserStats()
    {
        $today = Carbon::today();
        $thisMonth = Carbon::now()->startOfMonth();
        
        return [
            'total' => User::count(),
            'customers' => User::where('role', 'customer')->count(),
            'admins' => User::where('role', 'admin')->count(),
            'verified' => User::whereNotNull('email_verified_at')->count(),
            'unverified' => User::whereNull('email_verified_at')->count(),
            'today_registrations' => User::whereDate('created_at', $today)->count(),
            'month_registrations' => User::where('created_at', '>=', $thisMonth)->count(),
            'active_customers' => User::where('role', 'customer')
                                    ->whereHas('transactions', function($query) {
                                        $query->where('created_at', '>=', Carbon::now()->subDays(30));
                                    })->count(),
        ];
    }

    /**
     * Get detailed statistics for a specific user
     */
    private function getUserDetailStats($userId)
    {
        return [
            'total_orders' => Transaction::where('user_id', $userId)->count(),
            'completed_orders' => Transaction::where('user_id', $userId)->where('status', 'delivered')->count(),
            'pending_orders' => Transaction::where('user_id', $userId)->whereIn('status', ['pending', 'confirmed', 'processing', 'shipped'])->count(),
            'cancelled_orders' => Transaction::where('user_id', $userId)->where('status', 'cancelled')->count(),
            'total_spent' => Transaction::where('user_id', $userId)->where('status', 'delivered')->sum('total_amount'),
            'average_order_value' => Transaction::where('user_id', $userId)->where('status', 'delivered')->avg('total_amount') ?? 0,
            'first_order' => Transaction::where('user_id', $userId)->orderBy('created_at', 'asc')->first(),
            'last_order' => Transaction::where('user_id', $userId)->orderBy('created_at', 'desc')->first(),
        ];
    }

    /**
     * Get monthly order data for a user
     */
    private function getMonthlyOrderData($userId, $months = 12)
    {
        $data = [];
        
        for ($i = $months - 1; $i >= 0; $i--) {
            $date = Carbon::now()->subMonths($i);
            $monthStart = $date->copy()->startOfMonth();
            $monthEnd = $date->copy()->endOfMonth();
            
            $orders = Transaction::where('user_id', $userId)
                                ->whereBetween('created_at', [$monthStart, $monthEnd])
                                ->count();
            
            $spent = Transaction::where('user_id', $userId)
                               ->whereBetween('created_at', [$monthStart, $monthEnd])
                               ->where('status', 'delivered')
                               ->sum('total_amount');
            
            $data[] = [
                'month' => $date->format('M Y'),
                'orders' => $orders,
                'spent' => $spent
            ];
        }
        
        return $data;
    }

    private function translateRole($role)
    {
        $map = [
            'admin' => 'Admin',
            'customer' => 'Pelanggan',
        ];
        return $map[$role] ?? ucfirst($role);
    }
}