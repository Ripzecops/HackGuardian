<?php
// Database configuration
define('DB_HOST', 'localhost');
define('DB_NAME', 'hackguardian');
define('DB_USER', 'root');
define('DB_PASS', '');

// Site configuration
define('SITE_NAME', 'HackGuardian');
define('BASE_URL', 'http://localhost/hackguardian/public/');

// User roles
$roles = [
    'user' => 'Normal User',
    'hacker' => 'Ethical Hacker',
    'pentester' => 'Penetration Tester',
    'engineer' => 'Security Engineer',
    'admin' => 'Administrator'
];
?>