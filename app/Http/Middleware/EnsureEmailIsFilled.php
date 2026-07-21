<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsureEmailIsFilled
{
    /**
     * Handle an incoming request.
     * Strictly forces ANY authenticated user (pemohon, admin, superadmin, tkksd)
     * who has not filled `notification_email` to the profile edit page.
     */
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();

        if (!$user) {
            return $next($request);
        }

        // Allow access to profile routes, logout, api, templates
        if (
            $request->routeIs('profile.*') ||
            $request->routeIs('logout') ||
            $request->routeIs('api.*') ||
            $request->is('templates*')
        ) {
            return $next($request);
        }

        // Strictly check if notification_email is filled and valid
        if (empty($user->notification_email) || !filter_var($user->notification_email, FILTER_VALIDATE_EMAIL)) {
            return redirect()->route('profile.edit')
                ->with('warning', 'Anda wajib mengisikan Alamat Email Notifikasi terlebih dahulu sebelum dapat mengakses fitur SIKERJA. (Tenang, Username / NIP SSO Anda tetap aman dan tidak terubah).');
        }

        return $next($request);
    }
}
