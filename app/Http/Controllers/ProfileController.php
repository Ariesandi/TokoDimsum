<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use App\Models\User;
use App\Models\Transaction;

class ProfileController extends Controller
{
    /**
     * Display user profile
     */
    public function index()
    {
        /** @var User $user */
        $user = Auth::user();
        if (!$user instanceof User) {
            abort(401);
        }
        $orderStats = $this->getOrderStats($user->id);
        
        return view('profile.index', compact('user', 'orderStats'));
    }

    /**
     * Show profile edit form
     */
    public function edit()
    {
        /** @var User $user */
        $user = Auth::user();
        if (!$user instanceof User) {
            abort(401);
        }
        
        return view('profile.edit', compact('user'));
    }

    /**
     * Update user profile
     */
    public function update(Request $request)
    {
        /** @var User $user */
        $user = Auth::user();
        if (!$user instanceof User) {
            abort(401);
        }
        
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
            'avatar' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        // Handle avatar upload
        if ($request->hasFile('avatar')) {
            // Delete old avatar if exists
            if ($user->avatar && Storage::disk('public')->exists($user->avatar)) {
                Storage::disk('public')->delete($user->avatar);
            }
            
            $avatarPath = $request->file('avatar')->store('avatars', 'public');
            $user->avatar = $avatarPath;
        }

        // Update user data
        $user->update([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'address' => $request->address,
            'avatar' => $user->avatar,
        ]);

        return redirect()->route('profile.index')
                        ->with('success', 'Profil berhasil diperbarui.');
    }

    /**
     * Show change password form
     */
    public function showChangePasswordForm()
    {
        return view('profile.change-password');
    }

    /**
     * Update user password
     */
    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required',
            'password' => 'required|string|min:8|confirmed',
        ]);

        /** @var User $user */
        $user = Auth::user();
        if (!$user instanceof User) {
            abort(401);
        }

        // Check if current password is correct
        if (!Hash::check($request->current_password, $user->password)) {
            return back()->withErrors([
                'current_password' => 'Password saat ini tidak sesuai.'
            ]);
        }

        // Update password
        $user->update([
            'password' => Hash::make($request->password)
        ]);

        return redirect()->route('profile.index')
                        ->with('success', 'Password berhasil diubah.');
    }

    /**
     * Show user addresses
     */
    public function addresses()
    {
        /** @var User $user */
        $user = Auth::user();
        if (!$user instanceof User) {
            abort(401);
        }
        // For now, we'll use the single address field
        // In the future, this could be expanded to multiple addresses
        
        return view('profile.addresses', compact('user'));
    }

    /**
     * Update user address
     */
    public function updateAddress(Request $request)
    {
        $request->validate([
            'address' => 'required|string|max:500',
            'phone' => 'required|string|max:20',
        ]);

        /** @var User $user */
        $user = Auth::user();
        if (!$user instanceof User) {
            abort(401);
        }
        $user->update([
            'address' => $request->address,
            'phone' => $request->phone,
        ]);

        return redirect()->route('profile.addresses')
                        ->with('success', 'Alamat berhasil diperbarui.');
    }

    /**
     * Show user order history
     */
    public function orders(Request $request)
    {
        $query = Transaction::where('user_id', Auth::id())
                           ->with(['transactionDetails.product'])
                           ->orderBy('created_at', 'desc');

        // Filter by status if provided
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

        $orders = $query->paginate(10);

        $userId = Auth::id();
        if ($userId === null) {
            abort(401);
        }
        $orderStats = $this->getOrderStats($userId);

        return view('profile.orders', compact('orders', 'orderStats'));
    }

    /**
     * Show user favorites (wishlist)
     */
    public function favorites()
    {
        // For now, we'll show featured products as favorites
        // In the future, this could be expanded to user-specific favorites
        $favorites = \App\Models\Product::where('is_active', true)
                                       ->where('is_featured', true)
                                       ->paginate(12);
        
        return view('profile.favorites', compact('favorites'));
    }

    /**
     * Delete user account
     */
    public function destroy(Request $request)
    {
        $request->validate([
            'password' => 'required',
        ]);

        /** @var User $user */
        $user = Auth::user();
        if (!$user instanceof User) {
            abort(401);
        }

        // Check if password is correct
        if (!Hash::check($request->password, $user->password)) {
            return back()->withErrors([
                'password' => 'Password tidak sesuai.'
            ]);
        }

        // Check if user has pending orders
        $pendingOrders = Transaction::where('user_id', $user->id)
                                   ->whereIn('status', ['pending', 'confirmed', 'processing', 'shipped'])
                                   ->count();

        if ($pendingOrders > 0) {
            return back()->withErrors([
                'account' => 'Tidak dapat menghapus akun karena masih ada pesanan yang sedang diproses.'
            ]);
        }

        // Delete avatar if exists
        if ($user->avatar && Storage::disk('public')->exists($user->avatar)) {
            Storage::disk('public')->delete($user->avatar);
        }

        // Logout and delete user
        Auth::logout();
        $user->delete();

        return redirect()->route('home')
                        ->with('success', 'Akun berhasil dihapus.');
    }

    /**
     * Get user order statistics
     */
    private function getOrderStats(int $userId): array
    {
        $stats = [
            'total_orders' => Transaction::where('user_id', $userId)->count(),
            'completed_orders' => Transaction::where('user_id', $userId)->where('status', 'delivered')->count(),
            'pending_orders' => Transaction::where('user_id', $userId)->whereIn('status', ['pending', 'confirmed', 'processing', 'shipped'])->count(),
            'cancelled_orders' => Transaction::where('user_id', $userId)->where('status', 'cancelled')->count(),
            'total_spent' => Transaction::where('user_id', $userId)->where('status', 'delivered')->sum('total_amount'),
        ];

        // Calculate completion rate
        $stats['completion_rate'] = $stats['total_orders'] > 0 
            ? round(($stats['completed_orders'] / $stats['total_orders']) * 100, 1)
            : 0;

        return $stats;
    }
}