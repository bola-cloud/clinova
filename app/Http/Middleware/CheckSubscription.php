<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckSubscription
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && $user->isDoctor()) {
            if (!$user->subscription_active || ($user->subscription_expires_at && $user->subscription_expires_at->isPast())) {
                return redirect()->route('subscription.inactive');
            }
        }

        return $next($request);
    }
}
