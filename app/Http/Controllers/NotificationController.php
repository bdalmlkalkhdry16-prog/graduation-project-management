<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    /**
     * عرض قائمة الإشعارات
     */
    public function index(Request $request)
    {
        $query = auth()->user()->notifications();

        // فلترة حسب نوع الإشعار
        if ($request->has('type') && $request->type) {
            $query->where('type', $request->type);
        }

        // فلترة حسب حالة القراءة
        if ($request->has('is_read') && $request->is_read !== '') {
            $query->where('is_read', $request->is_read);
        }

        $notifications = $query->latest()->paginate(20);

        return view('notifications.index', compact('notifications'));
    }

    /**
     * عرض تفاصيل إشعار
     */
    public function show(Notification $notification)
    {
        // التحقق من أن الإشعار يخص المستخدم الحالي
        if ($notification->user_id !== auth()->id()) {
            abort(403);
        }

        // تحديد الإشعار كمقروء
        if (!$notification->is_read) {
            $notification->markAsRead();
        }

        return view('notifications.show', compact('notification'));
    }

    /**
     * تحديد إشعار كمقروء
     */
    public function markAsRead(Notification $notification)
    {
        if ($notification->user_id !== auth()->id()) {
            abort(403);
        }

        $notification->markAsRead();

        return response()->json(['success' => true]);
    }

    /**
     * تحديد جميع الإشعارات كمقروءة
     */
    public function markAllAsRead()
    {
        auth()->user()->notifications()->unread()->update([
            'is_read' => true,
            'read_at' => now(),
        ]);

        return back()->with('success', 'تم تحديد جميع الإشعارات كمقروءة');
    }

    /**
     * حذف إشعار
     */
    public function destroy(Notification $notification)
    {
        if ($notification->user_id !== auth()->id()) {
            abort(403);
        }

        $notification->delete();

        return back()->with('success', 'تم حذف الإشعار بنجاح');
    }

    /**
     * الحصول على عدد الإشعارات غير المقروءة (API)
     */
    public function unreadCount()
    {
        $count = auth()->user()->notifications()->unread()->count();

        return response()->json([
            'count' => $count,
            'has_unread' => $count > 0,
        ]);
    }
}
