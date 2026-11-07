<?php
namespace Controllers\Admin;

use Database;

class ContactController extends Base
{
    public function index(): void
    {
        [$offset, $per, $page] = paginate(20);
        $total = (int) Database::value('SELECT COUNT(*) FROM contact_messages');
        $items = Database::all("SELECT * FROM contact_messages ORDER BY ContactID DESC LIMIT $per OFFSET $offset");
        $this->view('admin/contact-messages', compact('items', 'total', 'page', 'per'));
    }
}
