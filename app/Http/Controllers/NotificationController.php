<?php

namespace App\Http\Controllers;

use App\Models\PushSubscription;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    /**
     * Display a listing of the notifications.
     */
    public function index()
    {
        $title = 'Riwayat Notifikasi';
        $user = Auth::user();
        
        // Paginate all notifications for the user
        $notifications = $user->notifications()->paginate(15);
        
        return view('pages.notifications.index', compact('title', 'notifications'));
    }

    /**
     * Fetch unread notifications for the dropdown/navbar.
     */
    public function fetchUnread()
    {
        $user = Auth::user();
        $unreadCount = $user->unreadNotifications()->count();
        $latestNotifications = $user->unreadNotifications()
            ->take(5)
            ->get()
            ->map(function ($notification) {
                return [
                    'id' => $notification->id,
                    'title' => $notification->data['title'] ?? 'Notifikasi',
                    'message' => $notification->data['message'] ?? '',
                    'url' => $notification->data['url'] ?? '#',
                    'icon' => $notification->data['icon'] ?? 'bi-bell-fill',
                    'icon_color' => $notification->data['icon_color'] ?? 'secondary',
                    'time_ago' => $notification->created_at->diffForHumans(),
                ];
            });

        return response()->json([
            'success' => true,
            'unread_count' => $unreadCount,
            'count' => $unreadCount,
            'notifications' => $latestNotifications
        ]);
    }

    /**
     * Mark a single notification as read and redirect.
     */
    public function markAsRead($id)
    {
        $notification = Auth::user()->notifications()->findOrFail($id);
        $notification->markAsRead();

        // Target URL to redirect
        $url = isset($notification->data['url']) ? $notification->data['url'] : route('dashboard');

        return redirect($url);
    }

    /**
     * Mark all notifications as read.
     */
    public function markAllAsRead()
    {
        Auth::user()->unreadNotifications->markAsRead();

        return response()->json([
            'success' => true,
            'message' => 'Semua notifikasi ditandai sebagai dibaca.'
        ]);
    }

    /**
     * Simpan/update push subscription dari browser.
     */
    public function subscribePush(Request $request)
    {
        $request->validate([
            'endpoint'         => 'required|url',
            'keys.p256dh'      => 'required|string',
            'keys.auth'        => 'required|string',
        ]);

        PushSubscription::updateOrCreate(
            [
                'user_id'  => Auth::id(),
                'endpoint' => $request->input('endpoint'),
            ],
            [
                'public_key'       => $request->input('keys.p256dh'),
                'auth_token'       => $request->input('keys.auth'),
                'content_encoding' => $request->input('contentEncoding', 'aesgcm'),
            ]
        );

        return response()->json(['success' => true, 'message' => 'Push subscription berhasil disimpan.']);
    }

    /**
     * Hapus push subscription (saat user unsubscribe).
     */
    public function unsubscribePush(Request $request)
    {
        $request->validate([
            'endpoint' => 'required|string',
        ]);

        PushSubscription::where('user_id', Auth::id())
            ->where('endpoint', $request->input('endpoint'))
            ->delete();

        return response()->json(['success' => true, 'message' => 'Push subscription dihapus.']);
    }
}
