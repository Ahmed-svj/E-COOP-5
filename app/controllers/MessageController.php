<?php
namespace Controllers;

use Auth;
use Controller;
use Database;

class MessageController extends Controller
{
    public function index(): void
    {
        $conversations = $this->conversations();
        $contacts = $this->contacts();
        $this->view('shared/messages', ['conversations' => $conversations, 'contacts' => $contacts, 'active' => null, 'messages' => []]);
    }

    public function show(int $id): void
    {
        $this->member($id);
        Database::query('UPDATE conversation_members SET LastReadAt = NOW() WHERE ConversationID = ? AND UserID = ?', [$id, Auth::id()]);
        $messages = $this->messages($id);
        $active = $this->conversations($id)[0] ?? null;
        $this->view('shared/messages', [
            'conversations' => $this->conversations(),
            'contacts'      => $this->contacts(),
            'active'        => $active,
            'messages'      => $messages,
        ]);
    }

    public function send(int $id): void
    {
        $this->member($id);
        $body = trim((string) ($_POST['body'] ?? ''));
        if ($body !== '' && mb_strlen($body) <= 4000) {
            Database::query('INSERT INTO messages (ConversationID, SenderUserID, Body) VALUES (?, ?, ?)', [$id, Auth::id(), $body]);
            Database::query('UPDATE conversation_members SET LastReadAt = NOW() WHERE ConversationID = ? AND UserID = ?', [$id, Auth::id()]);
            foreach (Database::all('SELECT UserID FROM conversation_members WHERE ConversationID = ? AND UserID <> ?', [$id, Auth::id()]) as $m) {
                Database::query('INSERT INTO notifications (UserID, Message, Link) VALUES (?, ?, ?)',
                    [$m['UserID'], __('n.message', ['name' => Auth::user()['FullName']]), "/messages/$id"]);
            }
        }
        if (!empty($_SERVER['HTTP_X_REQUESTED_WITH'])) {
            $this->json(['ok' => true]);
        }
        $this->redirect("/messages/$id");
    }

    public function poll(int $id): void
    {
        $this->member($id);
        $after = (int) ($_GET['after'] ?? 0);
        $rows = Database::all(
            'SELECT m.MessageID, m.Body, m.SentAt, m.SenderUserID, u.FullName FROM messages m JOIN users u ON u.UserID = m.SenderUserID
              WHERE m.ConversationID = ? AND m.MessageID > ? ORDER BY m.MessageID',
            [$id, $after]
        );
        if ($rows) {
            Database::query('UPDATE conversation_members SET LastReadAt = NOW() WHERE ConversationID = ? AND UserID = ?', [$id, Auth::id()]);
        }
        $me = Auth::id();
        $this->json(array_map(fn ($r) => [
            'id' => (int) $r['MessageID'], 'body' => $r['Body'], 'mine' => (int) $r['SenderUserID'] === $me,
            'name' => $r['FullName'], 'time' => date('H:i', strtotime($r['SentAt'])),
        ], $rows));
    }

    /** Start (or reopen) a direct conversation with someone the user is allowed to contact. */
    public function start(): void
    {
        $other = (int) ($_POST['user_id'] ?? 0);
        if (!in_array($other, array_column($this->contacts(), 'UserID'), false)) {
            abort(403);
        }
        $existing = Database::value(
            "SELECT c.ConversationID FROM conversations c
               JOIN conversation_members a ON a.ConversationID = c.ConversationID AND a.UserID = ?
               JOIN conversation_members b ON b.ConversationID = c.ConversationID AND b.UserID = ?
              WHERE c.Type = 'Direct' LIMIT 1",
            [Auth::id(), $other]
        );
        if (!$existing) {
            $existing = Database::transaction(function () use ($other) {
                Database::query("INSERT INTO conversations (Type) VALUES ('Direct')");
                $cid = (int) Database::connect()->lastInsertId();
                Database::query('INSERT INTO conversation_members (ConversationID, UserID) VALUES (?, ?), (?, ?)', [$cid, Auth::id(), $cid, $other]);
                return $cid;
            });
        }
        $this->redirect("/messages/$existing");
    }

    private function member(int $id): void
    {
        if (!Database::value('SELECT 1 FROM conversation_members WHERE ConversationID = ? AND UserID = ?', [$id, Auth::id()])) {
            abort(404);
        }
    }

    private function messages(int $id): array
    {
        return Database::all(
            'SELECT m.*, u.FullName FROM messages m JOIN users u ON u.UserID = m.SenderUserID WHERE m.ConversationID = ? ORDER BY m.MessageID LIMIT 500',
            [$id]
        );
    }

    private function conversations(?int $only = null): array
    {
        $sql = "SELECT c.ConversationID, c.Type, c.Title,
                       (SELECT GROUP_CONCAT(u.FullName SEPARATOR ', ') FROM conversation_members cm2 JOIN users u ON u.UserID = cm2.UserID
                         WHERE cm2.ConversationID = c.ConversationID AND cm2.UserID <> me.UserID) AS Others,
                       (SELECT GROUP_CONCAT(u.Role SEPARATOR ', ') FROM conversation_members cm2 JOIN users u ON u.UserID = cm2.UserID
                         WHERE cm2.ConversationID = c.ConversationID AND cm2.UserID <> me.UserID) AS OtherRoles,
                       (SELECT Body FROM messages m WHERE m.ConversationID = c.ConversationID ORDER BY m.MessageID DESC LIMIT 1) AS LastBody,
                       (SELECT MAX(SentAt) FROM messages m WHERE m.ConversationID = c.ConversationID) AS LastAt,
                       (SELECT COUNT(*) FROM messages m WHERE m.ConversationID = c.ConversationID AND m.SenderUserID <> me.UserID
                          AND (me.LastReadAt IS NULL OR m.SentAt > me.LastReadAt)) AS Unread
                  FROM conversations c
                  JOIN conversation_members me ON me.ConversationID = c.ConversationID AND me.UserID = ?";
        $params = [Auth::id()];
        if ($only) {
            $sql .= ' WHERE c.ConversationID = ?';
            $params[] = $only;
        }
        return Database::all($sql . ' ORDER BY LastAt IS NULL, LastAt DESC', $params);
    }

    /** People this user may message: linked through a placement, or admins. */
    private function contacts(): array
    {
        $me = Auth::id();
        $p = Auth::profile();
        $admins = "SELECT u.UserID, u.FullName, u.Role FROM users u WHERE u.Role = 'Admin' AND u.AccountStatus = 'Active' AND u.UserID <> ?";
        return match (Auth::role()) {
            'Student' => Database::all(
                "$admins UNION
                 SELECT u.UserID, u.FullName, u.Role FROM academic_supervisors sp JOIN users u ON u.UserID = sp.UserID WHERE sp.SupervisorID = ?
                 UNION
                 SELECT u.UserID, c.CompanyName, u.Role FROM training_placements p JOIN coop_opportunities o ON o.OpportunityID = p.OpportunityID
                   JOIN companies c ON c.CompanyID = o.CompanyID JOIN users u ON u.UserID = c.UserID WHERE p.PlacementID = ?",
                [$me, $p['SupervisorID'] ?? 0, $p['PlacementID'] ?? 0]
            ),
            'Company' => Database::all(
                "$admins UNION
                 SELECT u.UserID, u.FullName, u.Role FROM students s JOIN users u ON u.UserID = s.UserID
                   JOIN training_placements p ON p.PlacementID = s.PlacementID JOIN coop_opportunities o ON o.OpportunityID = p.OpportunityID
                  WHERE o.CompanyID = ?",
                [$me, $p['CompanyID']]
            ),
            'Supervisor' => Database::all(
                "$admins UNION SELECT u.UserID, u.FullName, u.Role FROM students s JOIN users u ON u.UserID = s.UserID WHERE s.SupervisorID = ?",
                [$me, $p['SupervisorID']]
            ),
            'Admin' => Database::all("SELECT UserID, FullName, Role FROM users WHERE UserID <> ? AND AccountStatus = 'Active' ORDER BY Role, FullName", [$me]),
        };
    }
}
