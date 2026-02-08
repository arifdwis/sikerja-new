<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Novay\SSO\Services\Broker;
use Carbon\Carbon;

class SSOController extends Controller
{
    protected $redirectTo = '/dashboard';

    public function login()
    {
        $queries = http_build_query([
            'name' => config('sso.broker_name'),
            'secret' => config('sso.broker_secret'),
            'redirect_uri' => urlencode(url('/oauth/sso/callback')),
            'response_type' => 'code',
        ]);

        return redirect(config('sso.server_url') . '/oauth/sso/authorize?' . $queries);
    }

    public function callback(Request $request)
    {
        Log::info('SSO Callback received', ['params' => $request->all()]);

        try {
            $broker = new Broker();

            if ($request->filled('code') && $request->filled('uid') && $request->filled('pwd')) {
                $username = base64_decode($request->uid);
                $password = base64_decode($request->pwd);

                Log::info('SSO: Attempting login with credentials', ['username' => $username]);

                try {
                    $loginResult = $broker->login($username, $password);
                    Log::info('SSO: Broker login result', ['result' => $loginResult]);

                    if ($loginResult) {
                        $request->session()->regenerate();
                        $this->syncUserFromSSO($broker);

                        Log::info('SSO: Login successful with credentials', ['user' => Auth::id()]);
                        return redirect()->to($this->redirectTo);
                    }
                } catch (\Exception $e) {
                    Log::error('SSO: Broker login error', ['error' => $e->getMessage()]);
                }

                Log::info('SSO: Trying token method as fallback');
                try {
                    if ($broker->token($request->code)) {
                        $request->session()->regenerate();
                        $this->syncUserFromSSO($broker);

                        Log::info('SSO: Token login successful as fallback', ['user' => Auth::id()]);
                        return redirect()->to($this->redirectTo);
                    }
                } catch (\Exception $e) {
                    Log::error('SSO: Token fallback error', ['error' => $e->getMessage()]);
                }

                Log::info('SSO: Attempting direct user creation from SSO response');

                $codeParts = explode('.', $request->code);
                if (count($codeParts) === 3) {
                    $payload = json_decode(base64_decode($codeParts[1]), true);
                    Log::info('SSO: JWT Payload', ['payload' => $payload]);

                    if (isset($payload['sub'])) {
                        $user = $this->createOrUpdateUserFromJWT($payload, $username);
                        if ($user) {
                            Auth::login($user, true);
                            $request->session()->regenerate();
                            Log::info('SSO: Direct user creation successful', ['user_id' => $user->id]);
                            return redirect()->to($this->redirectTo);
                        }
                    }
                }
            }

            if ($request->filled('code') && !$request->filled('uid') && !$request->filled('pwd')) {
                Log::info('SSO: Attempting token login', ['code' => substr($request->code, 0, 50) . '...']);

                if ($broker->token($request->code)) {
                    $request->session()->regenerate();
                    $this->syncUserFromSSO($broker);

                    Log::info('SSO: Token login successful', ['user' => Auth::id()]);
                    return redirect()->to($this->redirectTo);
                } else {
                    Log::warning('SSO: Token login failed');
                }
            }

            Log::warning('SSO: No valid login method found');
            return redirect()->route('login')->with('error', 'Tidak dapat terhubung dengan SSO. Silakan coba lagi.');

        } catch (\Exception $e) {
            Log::error('SSO Callback Error: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return redirect()->route('login')->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    protected function createOrUpdateUserFromJWT(array $payload, string $email)
    {
        try {
            $user = User::where('uid', $payload['sub'])
                ->orWhere('email', $email)
                ->first();

            $userData = [
                'uid' => $payload['sub'],
                'email' => $email,
                'name' => explode('@', $email)[0],
                'last_login' => Carbon::now(),
                'last_ip_address' => request()->ip(),
            ];

            if ($user) {
                $user->update($userData);
                Log::info('SSO: User updated from JWT', ['user_id' => $user->id]);
            } else {
                $user = User::create($userData);
                Log::info('SSO: User created from JWT', ['user_id' => $user->id]);

                if (method_exists($user, 'assignRole')) {
                    try {
                        $user->assignRole('pemohon');
                    } catch (\Exception $e) {
                        Log::warning('Failed to assign role: ' . $e->getMessage());
                    }
                }
            }

            return $user;
        } catch (\Exception $e) {
            Log::error('SSO: createOrUpdateUserFromJWT error', ['error' => $e->getMessage()]);
            return null;
        }
    }

    protected function syncUserFromSSO(Broker $broker)
    {
        try {
            $response = $broker->getUserInfo();
            Log::info('SSO: getUserInfo response', ['response' => $response]);

            if (!isset($response['data'])) {
                Log::warning('SSO: No data in getUserInfo response');
                return;
            }

            $sso = $response['data'];

            $user = User::where('uid', $sso['id'])
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

            if ($user) {
                $user->update($userData);
                Log::info('SSO: User updated', ['user_id' => $user->id]);
            } else {
                $user = User::create($userData);
                Log::info('SSO: User created', ['user_id' => $user->id]);

                if (method_exists($user, 'assignRole')) {
                    try {
                        $user->assignRole('pemohon');
                    } catch (\Exception $e) {
                        Log::warning('Failed to assign role: ' . $e->getMessage());
                    }
                }
            }

            Auth::login($user, true);
            Log::info('SSO: Auth::login called', ['auth_check' => Auth::check(), 'user_id' => Auth::id()]);

        } catch (\Exception $e) {
            Log::error('SSO syncUserFromSSO Error: ' . $e->getMessage());
        }
    }

    public function logout(Request $request)
    {
        $broker = new Broker();
        $broker->logout();

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}
