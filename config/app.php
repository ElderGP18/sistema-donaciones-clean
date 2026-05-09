<?php
define('APP_NAME', 'DonaTu');
// LOCAL: http://localhost/donatu
// HOSTINGER: https://tudominio.com  (sin slash al final)
define('APP_URL', 'http://localhost/donatu');
define('APP_VERSION', '1.0.0');

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
