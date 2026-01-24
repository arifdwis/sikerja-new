<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\User;
use Novay\SSO\Services\Broker;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class SSOAutoLogin
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Only check on login page if user is guest
        if ($request->segment(1) == 'login' && auth()->guest()) {
            try {
                $broker = new Broker();
                $response = $broker->getUserInfo();

                // If client is logged out in SSO server but still logged in broker.
                if (!isset($response['data']) && !auth()->guest()) {
                    return $this->logout($request);
                }

                // If there is a problem with data in SSO server, we will re-attach client session.
                if (isset($response['error']) && strpos($response['error'], 'There is no saved session data associated with the broker session id') !== false) {
                    return $this->clearSSOCookie($request);
                }

                // If client is logged in SSO server and didn't logged in broker...
                if (isset($response['data']) && auth()->guest()) {
                    // ... we will authenticate our client.
                    $this->handleLogin($response);
                }
            } catch (\Exception $e) {
                // SSO not available, continue to normal login
                \Log::warning('SSO Auto-login failed: ' . $e->getMessage());
            }
        }

        return $next($request);
    }

    /**
     * Handle the SSO login process
     */
    public function handleLogin($response)
    {
        $sso = $response['data'];

        $check = User::where('uid', $sso['id'])
            ->orWhere('email', $sso['email'])
            ->first();

        $userData = [
            'uid' => $sso['id'],
            'name' => $sso['name'],
            'email' => $sso['email'],
            'phone' => $sso['phone'] ?? null,
            'photo' => $sso['photo'] ?? null,
            'last_login' => Carbon::now(),
            'last_ip_address' => request()->ip(),
        ];

        if ($check) {
            $check->update($userData);
            Auth::login($check);
        } else {
            $user = User::create($userData);

            // Assign default role "pemohon"
            if (method_exists($user, 'assignRole')) {
                try {
                    $user->assignRole('pemohon');
                } catch (\Exception $e) {
                    \Log::warning('Failed to assign role: ' . $e->getMessage());
                }
            }

            Auth::login($user);
        }
    }

    /**
     * Clearing SSO cookie so broker will re-attach SSO server session.
     */
    protected function clearSSOCookie(Request $request)
    {
        return redirect($request->fullUrl())->cookie(cookie('sso_token_' . config('sso.broker_name')));
    }

    /**
     * Logging out authenticated user.
     */
    protected function logout(Request $request)
    {
        Auth::logout();
        return redirect($request->fullUrl());
    }
}
