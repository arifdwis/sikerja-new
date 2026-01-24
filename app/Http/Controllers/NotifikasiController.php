<?php

namespace App\Http\Controllers;

use App\Models\Notifikasi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class NotifikasiController extends Controller
{
    /**
     * Display a listing of notifications.
     */
    public function index()
    {
        $notifikasis = Notifikasi::with(['fromUser', 'permohonan'])
            ->where('id_user', Auth::id())
            ->latest()
            ->paginate(20);

        return Inertia::render('Backend/Notifikasi/Index', [
            'notifikasis' => $notifikasis,
        ]);
    }

    /**
     * Get unread count for navbar
     */
    public function unreadCount()
    {
        $count = Notifikasi::where('id_user', Auth::id())
            ->unread()
            ->count();

        return response()->json(['count' => $count]);
    }

    /**
     * Get recent notifications for dropdown
     */
    public function recent()
    {
        $notifikasis = Notifikasi::with(['fromUser', 'permohonan'])
            ->where('id_user', Auth::id())
            ->latest()
            ->limit(5)
            ->get();

        return response()->json($notifikasis);
    }

    /**
     * Mark a notification as read
     */
    public function markAsRead(string $uuid)
    {
        $notifikasi = Notifikasi::where('id_user', Auth::id())
            ->uuid($uuid)
            ->firstOrFail();

        $notifikasi->markAsRead();

        return response()->json(['success' => true]);
    }

    /**
     * Mark all notifications as read
     */
    public function markAllAsRead()
    {
        Notifikasi::where('id_user', Auth::id())
            ->unread()
            ->update([
                'is_read' => true,
                'read_at' => now(),
            ]);

        return response()->json(['success' => true]);
    }

    /**
     * Delete a notification
     */
    public function destroy(string $uuid)
    {
        $notifikasi = Notifikasi::where('id_user', Auth::id())
            ->uuid($uuid)
            ->firstOrFail();

        $notifikasi->delete();

        return redirect()->back()->with('success', 'Notifikasi dihapus.');
    }
}
