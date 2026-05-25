<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use App\Models\Pemohon;
use App\Models\Corporate;
use App\Models\Kota;
use App\Models\Provinsi;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;
use Inertia\Response;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): Response
    {
        $user = $request->user();
        $pemohon = null;
        $corporate = null;
        $provinsis = [];
        $kotas = [];
        $opds = [];

        // Load pemohon and corporate data if user is pemohon role
        if ($user->hasRole('pemohon')) {
            $pemohon = Pemohon::where('id_operator', $user->id)->first();
            $corporate = Corporate::where('id_operator', $user->id)->first();

            // Load provinsi and kota for dropdown
            $provinsis = Provinsi::orderBy('name')->get(['id', 'name']);
            if ($corporate && $corporate->kota_id) {
                $kota = Kota::find($corporate->kota_id);
                if ($kota) {
                    $kotas = Kota::where('province_id', $kota->province_id)->orderBy('name')->get(['id', 'name']);
                }
            }

            // Daftar OPD untuk dropdown satuan kerja (Req 1.1, 11.2)
            $opds = \App\Models\Opd::active()->orderBy('nama')->get(['id', 'nama', 'singkatan']);
        }

        return Inertia::render('Backend/Profile/Edit', [
            'mustVerifyEmail' => $request->user() instanceof MustVerifyEmail,
            'status' => session('status'),
            'pemohon' => $pemohon,
            'corporate' => $corporate,
            'provinsis' => $provinsis,
            'kotas' => $kotas,
            'opds' => $opds,
            // user.id_opd untuk auto-select dropdown
            'userIdOpd' => $user->id_opd,
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $request->user()->fill($request->validated());

        if ($request->user()->isDirty('email')) {
            $request->user()->email_verified_at = null;
        }

        $request->user()->save();

        return Redirect::route('profile.edit');
    }

    /**
     * Update biodata pemohon.
     * Field id_opd akan disinkronkan ke users.id_opd dan pemohon.unit_kerja.
     */
    public function updateBiodata(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'nickname' => 'required|string|max:100',
            'nik' => 'required|string|max:20',
            'gender' => 'required|in:L,P',
            'phone' => 'required|string|max:20',
            'email' => 'required|email|max:255',
            'kota_id' => 'nullable|integer',
            'address' => 'nullable|string',
            // id_opd jadi sumber utama untuk satuan kerja Pemkot
            // unit_kerja masih dipakai untuk pemohon non-Pemkot (manual input)
            'id_opd' => 'nullable|exists:opd,id',
            'unit_kerja' => 'required|string|max:255',
            'nip' => 'nullable|string|max:30',
            'jabatan' => 'required|string|max:100',
        ]);

        $user = $request->user();

        // Sinkronkan id_opd ke tabel users (Req 1.2 — auto-link)
        if (array_key_exists('id_opd', $validated)) {
            $user->update(['id_opd' => $validated['id_opd']]);
        }

        // unit_kerja di tabel pemohon tetap diisi:
        // - Jika id_opd dipilih, pakai nama OPD
        // - Jika tidak (mitra luar), pakai input manual
        if (!empty($validated['id_opd'])) {
            $opd = \App\Models\Opd::find($validated['id_opd']);
            if ($opd) {
                $validated['unit_kerja'] = $opd->nama;
            }
        }

        // Hapus id_opd dari array agar tidak masuk ke pemohon (kolom tidak ada di tabel pemohon)
        $pemohonData = $validated;
        unset($pemohonData['id_opd']);

        Pemohon::updateOrCreate(
            ['id_operator' => $user->id],
            $pemohonData
        );

        return Redirect::route('profile.edit')->with('success', 'Data biodata berhasil disimpan.');
    }

    /**
     * Update corporate/satuan kerja data for pemohon.
     */
    public function updateCorporate(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'id_opd' => 'nullable|exists:opd,id',
            'name' => 'required_without:id_opd|nullable|string|max:255',
            'kota_id' => 'nullable|integer',
            'postal_code' => 'required|string|max:10',
            'address' => 'required|string',
            'phone' => 'required|string|max:20',
            'email' => 'required|email|max:255',
            'website' => 'nullable|string|max:255',
        ]);

        $user = $request->user();

        // Jika user pilih dari dropdown OPD, sinkronkan name + users.id_opd
        if (!empty($validated['id_opd'])) {
            $opd = \App\Models\Opd::find($validated['id_opd']);
            if ($opd) {
                $validated['name'] = $opd->nama;
                $user->update(['id_opd' => $opd->id]);
            }
        } else {
            // Jika ketik manual, lepas link id_opd di tabel users
            $user->update(['id_opd' => null]);
        }

        // Hapus id_opd dari array agar tidak masuk ke corporate (kolom tidak ada)
        $corporateData = $validated;
        unset($corporateData['id_opd']);

        // Get kota name if kota_id provided
        if (!empty($corporateData['kota_id'])) {
            $kota = Kota::find($corporateData['kota_id']);
            $corporateData['kota'] = $kota ? $kota->name : null;
        }

        // Find or create corporate record
        Corporate::updateOrCreate(
            ['id_operator' => $user->id],
            array_merge($corporateData, ['status' => 1])
        );

        return Redirect::route('profile.edit')->with('success', 'Data satuan kerja berhasil disimpan.');
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validate([
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}
