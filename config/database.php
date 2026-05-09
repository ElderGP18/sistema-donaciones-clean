<?php
// LOCAL: http://localhost/donatu  → DB_USER='root', DB_PASS='', DB_NAME='donatu_db'
// HOSTINGER (activo):
define('DB_HOST', 'localhost');
define('DB_USER', 'u949489569_Donacion2026');
define('DB_PASS', 'SisDonacion2026');
define('DB_NAME', 'u949489569_Donacion2026');
define('DB_CHARSET', 'utf8mb4');

function getConnection(): mysqli {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    if ($conn->connect_error) {
        die(json_encode(['error' => 'Error de conexión: ' . $conn->connect_error]));
    }
    $conn->set_charset(DB_CHARSET);
    return $conn;
}
