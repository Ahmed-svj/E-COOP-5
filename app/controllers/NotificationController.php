<?php
namespace Controllers;

use Auth;
use Controller;
use Database;

class NotificationController extends Controller
{
    public function index(): void
    {
        [$offset, $per, $page] = paginate(20);
        $items = Database::all("SELECT * FROM notifications WHERE UserID = ? ORDER BY SentDate DESC, NotificationID DESC LIMIT $per OFFSET $offset", [Auth::id()]);
        $total = (int) Database::value('SELECT COUNT(*) FROM notifications WHERE UserID = ?', [Auth::id()]);
        $this->view('shared/notifications', compact('items', 'total', 'page', 'per'));
    }

    public function open(int $id): void
    {
        $n = Database::one('SELECT * FROM notifications WHERE NotificationID = ? AND UserID = ?', [$id, Auth::id()]);
        if (!$n) {
            abort(404);
        }
        Database::query('UPDATE notifications SET IsRead = 1 WHERE NotificationID = ?', [$id]);
        $link = $n['Link'] && str_starts_with($n['Link'], '/') && !str_starts_with($n['Link'], '//') ? $n['Link'] : '/notifications';
        $this->redirect($link);
    }

    public function readAll(): void
    {
        Database::query('UPDATE notifications SET IsRead = 1 WHERE UserID = ?', [Auth::id()]);
        $this->back();
    }

    public function unread(): void
    {
        $this->json(['count' => \Notification::unreadCount(Auth::id())]);
    }
}
