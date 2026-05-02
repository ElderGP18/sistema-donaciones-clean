<?php
require_once __DIR__ . '/../../config/app.php';
require_once __DIR__ . '/../../config/database.php';
requireAdmin();

$id = intval($_GET['id'] ?? 0);
if (!$id) redirect('modules/donations/list.php');

$conn = getConnection();
$conn->query("DELETE FROM donaciones WHERE id_donacion = $id");
$conn->close();

redirect('modules/donations/list.php?deleted=1');
