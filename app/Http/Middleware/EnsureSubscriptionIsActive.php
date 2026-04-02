<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsureSubscriptionIsActive
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();

        if (!$user) {
            return redirect()->route('login');
        }

        if ($user->status !== 'active') {
            return redirect()->route('login');
        }

        $endsAt = $user->subscription_ends_at;

        if (!$endsAt || $endsAt->isPast()) {
            return redirect()->route('paywall');
        }

        return $next($request);
    }
}

