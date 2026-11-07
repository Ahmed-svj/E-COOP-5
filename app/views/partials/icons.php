<?php
/** Inline stroke icons (1.75px, currentColor) — see design system: Iconography. */
function icon(string $name, string $class = ''): string
{
    static $p = [
        'home'    => '<path d="M3 10.5 12 3l9 7.5V20a1 1 0 0 1-1 1h-5v-6h-6v6H4a1 1 0 0 1-1-1z"/>',
        'grid'    => '<rect x="4" y="4" width="6" height="6" rx="1.5"/><rect x="14" y="4" width="6" height="6" rx="1.5"/><rect x="4" y="14" width="6" height="6" rx="1.5"/><rect x="14" y="14" width="6" height="6" rx="1.5"/>',
        'brief'   => '<rect x="3" y="7" width="18" height="13" rx="2"/><path d="M8 7V5a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2M3 12h18"/>',
        'check'   => '<rect x="4" y="4" width="16" height="17" rx="2"/><path d="M8 2v4M16 2v4M4 10h16M9 15l2 2 4-4"/>',
        'doc'     => '<path d="M14 3H7a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V8z"/><path d="M14 3v5h5M9 13h6M9 17h4"/>',
        'star'    => '<path d="m12 3 2.7 5.6 6.1.9-4.4 4.3 1 6.1L12 17l-5.4 2.9 1-6.1L3.2 9.5l6.1-.9z"/>',
        'chat'    => '<path d="M21 12a8 8 0 0 1-11.8 7L4 20l1.1-4.6A8 8 0 1 1 21 12z"/>',
        'shield'  => '<path d="M12 3 5 6v6c0 4.4 3 7.8 7 9 4-1.2 7-4.6 7-9V6z"/><path d="m9 12 2 2 4-4"/>',
        'bell'    => '<path d="M6 16V11a6 6 0 1 1 12 0v5l1.5 2h-15z"/><path d="M10 20a2 2 0 0 0 4 0"/>',
        'search'  => '<circle cx="11" cy="11" r="7"/><path d="m20 20-3.5-3.5"/>',
        'sliders' => '<path d="M4 6h10M18 6h2M4 12h4M12 12h8M4 18h12"/><circle cx="16" cy="6" r="2"/><circle cx="10" cy="12" r="2"/><circle cx="18" cy="18" r="2"/>',
        'pin'     => '<path d="M12 21s-7-6.2-7-11.5a7 7 0 0 1 14 0C19 14.8 12 21 12 21z"/><circle cx="12" cy="9.5" r="2.5"/>',
        'chev'    => '<path d="m9 6 6 6-6 6"/>',
        'down'    => '<path d="m6 9 6 6 6-6"/>',
        'bolt'    => '<path d="M13 2 4 14h7l-1 8 9-12h-7z"/>',
        'key'     => '<circle cx="8" cy="15" r="4"/><path d="m11 12 9-9M17 6l3 3M15 8l2 2"/>',
        'power'   => '<path d="M12 3v9"/><path d="M6.3 6.6a8 8 0 1 0 11.4 0"/>',
        'upload'  => '<path d="M12 16V4M7 9l5-5 5 5"/><path d="M4 16v3a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-3"/>',
        'download'=> '<path d="M12 4v12M7 11l5 5 5-5"/><path d="M4 16v3a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-3"/>',
        'users'   => '<circle cx="9" cy="8" r="3.5"/><path d="M2.5 20a6.5 6.5 0 0 1 13 0"/><path d="M16 4.5a3.5 3.5 0 0 1 0 7M18 14a6.5 6.5 0 0 1 3.5 6"/>',
        'user'    => '<circle cx="12" cy="8" r="4"/><path d="M4 21a8 8 0 0 1 16 0"/>',
        'lock'    => '<rect x="5" y="11" width="14" height="10" rx="2"/><path d="M8 11V7a4 4 0 0 1 8 0v4"/>',
        'info'    => '<circle cx="12" cy="12" r="9"/><path d="M12 11v5M12 8h0"/>',
        'plus'    => '<path d="M12 5v14M5 12h14"/>',
        'x'       => '<path d="M6 6l12 12M18 6 6 18"/>',
        'ok'      => '<path d="m5 12 5 5 9-10"/>',
        'clock'   => '<circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/>',
        'chart'   => '<path d="M4 20V10M10 20V4M16 20v-7M22 20H2"/>',
        'building'=> '<rect x="4" y="3" width="16" height="18" rx="2"/><path d="M9 7h2M13 7h2M9 11h2M13 11h2M9 15h2M13 15h2M10 21v-3h4v3"/>',
        'list'    => '<path d="M9 6h11M9 12h11M9 18h11M4 6h0M4 12h0M4 18h0"/>',
        'mail'    => '<rect x="3" y="5" width="18" height="14" rx="2"/><path d="m3 7 9 6 9-6"/>',
        'settings'=> '<circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.7 1.7 0 0 0 .3 1.8l.1.1a2 2 0 1 1-2.8 2.8l-.1-.1a1.7 1.7 0 0 0-1.8-.3 1.7 1.7 0 0 0-1 1.5V21a2 2 0 1 1-4 0v-.1a1.7 1.7 0 0 0-1.1-1.5 1.7 1.7 0 0 0-1.8.3l-.1.1a2 2 0 1 1-2.8-2.8l.1-.1a1.7 1.7 0 0 0 .3-1.8 1.7 1.7 0 0 0-1.5-1H3a2 2 0 1 1 0-4h.1a1.7 1.7 0 0 0 1.5-1.1 1.7 1.7 0 0 0-.3-1.8l-.1-.1a2 2 0 1 1 2.8-2.8l.1.1a1.7 1.7 0 0 0 1.8.3H9a1.7 1.7 0 0 0 1-1.5V3a2 2 0 1 1 4 0v.1a1.7 1.7 0 0 0 1 1.5 1.7 1.7 0 0 0 1.8-.3l.1-.1a2 2 0 1 1 2.8 2.8l-.1.1a1.7 1.7 0 0 0-.3 1.8V9a1.7 1.7 0 0 0 1.5 1H21a2 2 0 1 1 0 4h-.1a1.7 1.7 0 0 0-1.5 1z"/>',
        'logout'  => '<path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4M16 17l5-5-5-5M21 12H9"/>',
        'globe'   => '<circle cx="12" cy="12" r="9"/><path d="M3 12h18M12 3a14 14 0 0 1 0 18M12 3a14 14 0 0 0 0 18"/>',
        'link'    => '<path d="M10 14a5 5 0 0 0 7 0l3-3a5 5 0 0 0-7-7l-1 1"/><path d="M14 10a5 5 0 0 0-7 0l-3 3a5 5 0 0 0 7 7l1-1"/>',
        'send'    => '<path d="M22 2 11 13M22 2l-7 20-4-9-9-4z"/>',
        'printer' => '<path d="M6 9V2h12v7"/><rect x="2" y="9" width="20" height="9" rx="2"/><path d="M6 14h12v8H6z"/>',
        'assign'  => '<circle cx="9" cy="8" r="4"/><path d="M2 21a7 7 0 0 1 14 0M19 8v6M16 11h6"/>',
        'camera'  => '<path d="M4 8h3l2-3h6l2 3h3a1 1 0 0 1 1 1v10a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1V9a1 1 0 0 1 1-1z"/><circle cx="12" cy="13.5" r="3.5"/>',
        'moon'    => '<path d="M20 14.5A8 8 0 0 1 9.5 4a8 8 0 1 0 10.5 10.5z"/>',
        'sun'     => '<circle cx="12" cy="12" r="4"/><path d="M12 2v2M12 20v2M4.9 4.9l1.4 1.4M17.7 17.7l1.4 1.4M2 12h2M20 12h2M4.9 19.1l1.4-1.4M17.7 6.3l1.4-1.4"/>',
        'cap'     => '<path d="M22 10 12 5 2 10l10 5 10-5z"/><path d="M6 12v5c3 2 9 2 12 0v-5"/>',
    ];
    $cls = 'co-ico' . ($class ? ' ' . $class : '');
    return '<svg class="' . $cls . '" viewBox="0 0 24 24" aria-hidden="true" focusable="false">' . ($p[$name] ?? $p['info']) . '</svg>';
}

/** Navigation per role: [path, label key, icon]. */
function nav_items(string $role): array
{
    return match ($role) {
        'Student' => [
            ['/student/dashboard', 'nav.home', 'home'],
            ['/student/opportunities', 'nav.opportunities', 'brief'],
            ['/student/applications', 'nav.applications', 'list'],
            ['/student/attendance', 'nav.attendance', 'check'],
            ['/student/report', 'nav.report', 'doc'],
            ['/student/evaluations', 'nav.evaluations', 'star'],
            ['/messages', 'nav.messages', 'chat'],
        ],
        'Company' => [
            ['/company/dashboard', 'nav.home', 'home'],
            ['/company/opportunities', 'nav.opportunities', 'brief'],
            ['/company/applicants', 'nav.applicants', 'users'],
            ['/company/daily-code', 'nav.daily_code', 'key'],
            ['/company/trainees', 'nav.trainees', 'star'],
            ['/company/profile', 'nav.company_profile', 'building'],
            ['/messages', 'nav.messages', 'chat'],
        ],
        'Supervisor' => [
            ['/supervisor/dashboard', 'nav.home', 'home'],
            ['/supervisor/students', 'nav.students', 'users'],
            ['/supervisor/attendance', 'nav.attendance', 'check'],
            ['/supervisor/reports', 'nav.reports', 'doc'],
            ['/supervisor/export', 'nav.export', 'download'],
            ['/messages', 'nav.messages', 'chat'],
        ],
        'Admin' => [
            ['/admin/dashboard', 'nav.home', 'home'],
            ['/admin/users', 'nav.users', 'users'],
            ['/admin/companies', 'nav.companies', 'building'],
            ['/admin/opportunities', 'nav.opportunities', 'brief'],
            ['/admin/placements', 'nav.placements', 'cap'],
            ['/admin/assign', 'nav.assign', 'assign'],
            ['/admin/analytics', 'nav.analytics', 'chart'],
            ['/admin/report', 'nav.system_report', 'doc'],
            ['/admin/audit-logs', 'nav.audit', 'shield'],
            ['/admin/contact-messages', 'nav.contact_inbox', 'mail'],
        ],
        default => [],
    };
}
