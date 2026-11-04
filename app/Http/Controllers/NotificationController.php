<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\View\View;

class NotificationController extends Controller
{
    public function index(): View
    {
        $notifications = auth()->user()->notifications()->paginate(15);
        $unreadCount   = auth()->user()->unreadNotifications()->count();

        return view('notifications.index', compact('notifications', 'unreadCount'));
    }

    // BUG 4 (w10): IDOR pada notifikasi!
    // Mengambil notifikasi langsung dari model global DatabaseNotification::findOrFail($id)
    // tanpa memvalidasi apakah notifikasi tersebut benar-benar milik pengguna yang sedang login.
    // Mahasiswa A dapat menandai notifikasi Mahasiswa B sebagai sudah dibaca cukup dengan
    // mengirimkan UUID notifikasi milik Mahasiswa B.
    //
    // Solusi:
    // $notification = auth()->user()->notifications()->findOrFail($id);
    // $notification->markAsRead();
    public function markAsRead(string $id): RedirectResponse
    {
        // BUG 4: Tidak ada pengecekan kepemilikan!
        $notification = DatabaseNotification::findOrFail($id);
        $notification->markAsRead();

        return back()->with('success', 'Notifikasi berhasil ditandai sudah dibaca.');
    }

    public function markAllAsRead(): RedirectResponse
    {
        auth()->user()->unreadNotifications->markAsRead();

        return back()->with('success', 'Semua notifikasi berhasil ditandai sudah dibaca.');
    }
}
