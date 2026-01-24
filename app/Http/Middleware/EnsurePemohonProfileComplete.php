<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\Pemohon;
use App\Models\Corporate;

class EnsurePemohonProfileComplete
{
    /**
     * Handle an incoming request.
     * Redirect pemohon to profile page if biodata/corporate is not complete.
     */
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();

        // Only check for pemohon role
        if (!$user || !$user->hasRole('pemohon')) {
            return $next($request);
        }

        // Allow access to profile pages and logout
        if ($request->routeIs('profile.*') || $request->routeIs('logout') || $request->routeIs('api.*')) {
            return $next($request);
        }

        // Check if pemohon biodata is complete
        $pemohon = Pemohon::where('id_operator', $user->id)->first();

        // Check if corporate data is complete
        $corporate = Corporate::where('id_operator', $user->id)->first();

        if (!$this->isBiodataComplete($pemohon) || !$this->isCorporateComplete($corporate)) {
            return redirect()->route('profile.edit')
                ->with('warning', 'Silakan lengkapi data diri dan data satuan kerja Anda terlebih dahulu sebelum melanjutkan.');
        }

        return $next($request);
    }

    /**
     * Check if pemohon biodata is complete.
     */
    protected function isBiodataComplete(?Pemohon $pemohon): bool
    {
        if (!$pemohon) {
            return false;
        }

        // Required fields for complete biodata
        $requiredFields = [
            'name',
            'nickname',
            'nik',
            'gender',
            'phone',
            'email',
            'unit_kerja',
            'jabatan',
        ];

        foreach ($requiredFields as $field) {
            if (empty($pemohon->{$field})) {
                return false;
            }
        }

        return true;
    }

    /**
     * Check if corporate data is complete.
     */
    protected function isCorporateComplete(?Corporate $corporate): bool
    {
        if (!$corporate) {
            return false;
        }

        // Required fields for complete corporate
        $requiredFields = [
            'name',
            'postal_code',
            'address',
            'phone',
            'email',
        ];

        foreach ($requiredFields as $field) {
            if (empty($corporate->{$field})) {
                return false;
            }
        }

        return true;
    }
}
