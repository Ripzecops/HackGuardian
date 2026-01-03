<?php
// Database credentials (update if needed)
if (!defined('DB_HOST')) {
    define('DB_HOST', 'localhost');
}
if (!defined('DB_NAME')) {
    define('DB_NAME', 'hackguardian');
}
if (!defined('DB_USER')) {
    define('DB_USER', 'root');
}
if (!defined('DB_PASS')) {
    define('DB_PASS', '');
}

function db_connect()
{
    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    if (!$conn) {
        die('Database connection error: ' . mysqli_connect_error());
    }

    mysqli_set_charset($conn, 'utf8mb4');
    return $conn;
}
