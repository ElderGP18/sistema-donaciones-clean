<?php
define('APP_NAME', 'DonaTu');
define('APP_VERSION', '1.0.0');

// Detecta la URL base automáticamente: funciona en localhost, IIS y Hostinger
if (isset($_SERVER['HTTP_HOST'])) {
    $scheme  = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $docRoot = rtrim(str_replace('\\', '/', $_SERVER['DOCUMENT_ROOT']), '/');
    $appDir  = rtrim(str_replace('\\', '/', dirname(__DIR__)), '/');
    $subPath = str_replace($docRoot, '', $appDir);
    define('APP_URL', $scheme . '://' . $_SERVER['HTTP_HOST'] . $subPath);
} else {
    // Fallback para CLI / PHPUnit
    define('APP_URL', 'http://localhost/donatu');
}

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function isLoggedIn(): bool {
    return isset($_SESSION['user_id']);
}

function requireLogin(): void {
    if (!isLoggedIn()) {
        header('Location: ' . APP_URL . '/login.php');
        exit;
    }
}

function requireAdmin(): void {
    requireLogin();
    if ($_SESSION['user_rol'] !== 'admin') {
        header('Location: ' . APP_URL . '/dashboard.php');
        exit;
    }
}

function redirect(string $path): void {
    header('Location: ' . APP_URL . '/' . ltrim($path, '/'));
    exit;
}

function sanitize(string $value): string {
    return htmlspecialchars(strip_tags(trim($value)), ENT_QUOTES, 'UTF-8');
}
