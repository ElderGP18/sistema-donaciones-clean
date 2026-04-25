<?php
// LOCAL: cambiar estos valores por los de Hostinger al desplegar
define('DB_HOST', 'localhost');
define('DB_USER', 'root');         // Hostinger: usuario que creas en hPanel
define('DB_PASS', '');             // Hostinger: contraseña de tu base de datos
define('DB_NAME', 'donatu_db');    // Hostinger: nombre que le pongas a la BD
define('DB_CHARSET', 'utf8mb4');

function getConnection(): mysqli {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    if ($conn->connect_error) {
        die(json_encode(['error' => 'Error de conexión: ' . $conn->connect_error]));
    }
    $conn->set_charset(DB_CHARSET);
    return $conn;
}
