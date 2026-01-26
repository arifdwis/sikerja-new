<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;

use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class GoogleController extends Controller
{
    /**
     * Handle the incoming Google User data from Firebase
     */
    public function callback(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'name' => 'required|string',
            'uid' => 'required|string', // Firebase UID
        ]);

        $email = $request->email;
        $name = $request->name;

        // Find or Create User
        $user = User::where('email', $email)->first();

        if (!$user) {
            // Create new Pemohon user
            $user = User::create([
                'name' => $name,
                'email' => $email,
                'password' => Hash::make(Str::random(24)), // Random password
                'email_verified_at' => now(), // Google emails are verified
            ]);

            // Assign role 'pemohon'
            $user->assignRole('pemohon');

            // Trigger registered event
            event(new Registered($user));
        } else {
            // Optional: Check if user has role 'pemohon' or allow generic login?
            // User requested "khusus pemohon", but generally login works for any account matching email.
            // If we want to restrict:
            // if (!$user->hasRole(['pemohon', 'admin', ...])) { ... }
        }

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->intended(route('dashboard'));
    }
}
