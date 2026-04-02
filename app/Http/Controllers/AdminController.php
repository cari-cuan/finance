<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AdminController extends Controller
{
    public function dashboard()
    {
        $stats = [
            'total_users' => User::count(),
            'active_users' => User::where('subscription_ends_at', '>', now())->count(),
            'total_revenue' => Order::where('status', 'success')->sum('total_amount'),
            'pending_orders' => Order::where('status', 'pending')->count(),
        ];

        return Inertia::render('Admin/Dashboard', [
            'stats' => $stats
        ]);
    }

    public function users()
    {
        $users = User::orderByDesc('created_at')->paginate(20);
        
        return Inertia::render('Admin/Users', [
            'users' => $users
        ]);
    }

    public function toggleUserStatus(User $user)
    {
        $user->status = $user->status === 'active' ? 'suspended' : 'active';
        $user->save();

        return back();
    }
}
