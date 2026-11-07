<?php
namespace Controllers;

use Auth;
use Controller;
use Database;
use Session;

class HomeController extends Controller
{
    public function index(): void
    {
        $stats = [
            'companies'     => (int) Database::value("SELECT COUNT(*) FROM companies WHERE ApprovalStatus = 'Approved'"),
            'opportunities' => (int) Database::value("SELECT COUNT(*) FROM v_public_opportunities"),
            'placed'        => (int) Database::value('SELECT COUNT(*) FROM students WHERE PlacementID IS NOT NULL'),
        ];
        $latest = Database::all('SELECT * FROM v_public_opportunities ORDER BY PostedDate DESC, OpportunityID DESC LIMIT 3');
        $this->view('home/index', compact('stats', 'latest'), 'main');
    }

    public function about(): void
    {
        $this->view('home/about', [], 'main');
    }

    public function faq(): void
    {
        $this->view('home/faq', [], 'main');
    }

    public function privacy(): void
    {
        $this->view('home/privacy', [], 'main');
    }

    public function contact(): void
    {
        $this->view('home/contact', [], 'main');
    }

    public function sendContact(): void
    {
        // Honeypot: bots fill hidden fields.
        if (!empty($_POST['website'])) {
            $this->redirect('/contact');
        }
        $d = $this->validate([
            'name'  => 'required|max:150',
            'email' => 'required|email|max:190',
            'topic' => 'required|max:150',
            'body'  => 'required|min:10|max:5000',
        ]);
        \ContactMessage::insert(['SenderName' => $d['name'], 'SenderEmail' => $d['email'], 'Topic' => $d['topic'], 'Body' => $d['body']]);
        \NotificationService::admins(__('n.contact', ['name' => $d['name']]), '/admin/contact-messages');
        flash('success', __('contact.sent'));
        $this->redirect('/contact');
    }

    public function lang(string $code): void
    {
        $code = $code === 'ar' ? 'ar' : 'en';
        Session::set('lang', $code);
        if ($id = Auth::id()) {
            \UserPreference::set($id, 'lang_ar', $code === 'ar');
        }
        $this->back();
    }

    /** Flip dark/light. Saved to the account when signed in, else to this visit. */
    public function theme(): void
    {
        $want = $_POST['theme'] ?? '';
        $next = in_array($want, ['light', 'dark'], true) ? $want : (current_theme() === 'dark' ? 'light' : 'dark');
        if ($id = Auth::id()) {
            \UserPreference::set($id, 'dark_mode', $next === 'dark');
        } else {
            Session::set('theme', $next);
        }
        if (!empty($_SERVER['HTTP_X_REQUESTED_WITH'])) {
            $this->json(['theme' => $next]);
        }
        $this->back();
    }

    public function dashboard(): void
    {
        $this->redirect(match (Auth::role()) {
            'Student'    => '/student/dashboard',
            'Company'    => '/company/dashboard',
            'Supervisor' => '/supervisor/dashboard',
            'Admin'      => '/admin/dashboard',
        });
    }
}
