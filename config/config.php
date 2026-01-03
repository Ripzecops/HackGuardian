<?php
// Global configuration and helper functions
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!defined('APP_NAME')) {
    define('APP_NAME', 'HackGuardian');
}
if (!defined('ROOT_PATH')) {
    define('ROOT_PATH', dirname(__DIR__));
}
if (!defined('BASE_URL')) {
    $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $doc_root = isset($_SERVER['DOCUMENT_ROOT']) ? str_replace('\\', '/', rtrim($_SERVER['DOCUMENT_ROOT'], '/')) : '';
    $root_path = str_replace('\\', '/', rtrim(ROOT_PATH, '/'));
    $base_path = '';

    if ($doc_root !== '' && strpos($root_path, $doc_root) === 0) {
        $base_path = substr($root_path, strlen($doc_root));
    }

    $base_path = '/' . trim($base_path, '/');
    if ($base_path === '/') {
        $base_path = '';
    }

    define('BASE_URL', $scheme . '://' . $host . $base_path . '/');
}

require_once ROOT_PATH . '/config/database.php';

$ALLOWED_ROLES = ['user', 'hacker', 'pentester', 'qa', 'admin'];

function e($value)
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function is_valid_role($role)
{
    $allowed = ['user', 'hacker', 'pentester', 'qa', 'admin'];
    return in_array($role, $allowed, true);
}

function redirect($path)
{
    $url = BASE_URL . ltrim($path, '/');
    header('Location: ' . $url);
    exit;
}

function set_flash($message, $type = 'info')
{
    $_SESSION['flash_message'] = [
        'message' => $message,
        'type' => $type
    ];
}

function get_flash()
{
    if (!isset($_SESSION['flash_message'])) {
        return null;
    }
    $flash = $_SESSION['flash_message'];
    unset($_SESSION['flash_message']);
    return $flash;
}

function require_login()
{
    if (empty($_SESSION['user_id'])) {
        set_flash('Please login to continue.', 'error');
        redirect('index.php');
    }
}

function require_role($role)
{
    require_login();
    $current_role = $_SESSION['user_role'] ?? '';
    if ($current_role !== $role) {
        set_flash('Access denied', 'error');
        redirect('index.php');
    }
}

function sanitize_filename($name)
{
    $name = basename($name);
    $name = preg_replace('/[^A-Za-z0-9._-]/', '_', $name);
    $name = preg_replace('/_+/', '_', $name);
    $name = trim($name, '._-');

    if ($name === '') {
        $name = 'file.pdf';
    }

    return $name;
}

function generate_unique_filename($original_name)
{
    $clean_name = sanitize_filename($original_name);

    try {
        $random = bin2hex(random_bytes(4));
    } catch (Exception $e) {
        $random = (string) mt_rand(1000, 9999);
    }

    return time() . '_' . $random . '_' . $clean_name;
}

function validate_pdf_upload($file, &$error)
{
    if (!isset($file) || !isset($file['error'])) {
        $error = 'No file uploaded.';
        return false;
    }

    if ($file['error'] !== UPLOAD_ERR_OK) {
        $error = 'Upload error. Please try again.';
        return false;
    }

    if ($file['size'] > 10 * 1024 * 1024) {
        $error = 'File too large. Max 10MB.';
        return false;
    }

    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if ($ext !== 'pdf') {
        $error = 'Only PDF files are allowed.';
        return false;
    }

    $mime = '';
    if (function_exists('finfo_open')) {
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);
    }

    if ($mime !== '' && $mime !== 'application/pdf' && $mime !== 'application/x-pdf') {
        $error = 'Invalid PDF file.';
        return false;
    }

    return true;
}

function ensure_dir($dir)
{
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
    }
}

function store_pdf_upload($file, $relative_dir, &$error)
{
    if (!validate_pdf_upload($file, $error)) {
        return '';
    }

    $relative_dir = trim($relative_dir, '/');
    $absolute_dir = ROOT_PATH . '/' . $relative_dir;
    ensure_dir($absolute_dir);

    $filename = generate_unique_filename($file['name']);
    $absolute_path = $absolute_dir . '/' . $filename;

    if (!move_uploaded_file($file['tmp_name'], $absolute_path)) {
        $error = 'Failed to save uploaded file.';
        return '';
    }

    return $relative_dir . '/' . $filename;
}

function bounty_for_severity($severity)
{
    $map = [
        'low' => 100,
        'medium' => 500,
        'high' => 5000,
        'critical' => 25000
    ];

    return $map[$severity] ?? 0;
}
