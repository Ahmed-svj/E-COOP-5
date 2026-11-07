<?php
return [
    'host' => getenv('DB_HOST') ?: '127.0.0.1',
    'port' => getenv('DB_PORT') ?: '3306',
    'name' => getenv('DB_NAME') ?: 'coop_db',
    'user' => getenv('DB_USER') ?: 'coop_app',
    'pass' => getenv('DB_PASS') ?: '',
];
