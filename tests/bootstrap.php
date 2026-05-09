<?php
declare(strict_types=1);

// Suppress headers in CLI (PHP ignores them anyway, but avoids warnings)
$_SERVER['SERVER_NAME']  = 'localhost';
$_SERVER['REQUEST_URI']  = '/';
$_SERVER['HTTP_HOST']    = 'localhost';
$_SERVER['REQUEST_METHOD'] = 'GET';

// Load app helpers (sanitize, isLoggedIn, redirect, etc.)
require_once dirname(__DIR__) . '/config/app.php';
