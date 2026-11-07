<?php
return [
    'name'                 => getenv('APP_NAME') ?: 'COOP',
    'url'                  => rtrim(getenv('APP_URL') ?: '', '/'),
    'debug'                => (getenv('APP_DEBUG') ?: 'false') === 'true',
    'timezone'             => 'Asia/Riyadh',
    'session_timeout'      => 30 * 60,          // 30 minutes of inactivity
    'login_max_attempts'   => 5,
    'login_lockout_min'    => 15,
    'code_max_attempts'    => 5,
    'code_lockout_min'     => 10,
    'student_email_domain' => getenv('STUDENT_EMAIL_DOMAIN') ?: 'jic.edu.sa,ecoop.test',
    'upload_max_bytes'     => 10 * 1024 * 1024, // 10 MB
    'reset_valid_min'      => 30,
    'required_days'        => (int) (getenv('REQUIRED_TRAINING_DAYS') ?: 40),
];
