<?php
require_once __DIR__ . '/../../config/app.php';
require_once __DIR__ . '/../../config/database.php';
requireAdmin();

$id = intval($_GET['id'] ?? 0);
if (!$id || $id === $_SESSION['user_id']) redirect('modules/users/list.php');

$conn = getConnection();
$conn->query("UPDATE usuarios SET activo = NOT activo WHERE id_usuario = $id");
$conn->close();
redirect('modules/users/list.php?updated=1');
