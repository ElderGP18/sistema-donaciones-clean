<?php
require_once __DIR__ . '/../../config/app.php';
require_once __DIR__ . '/../../config/database.php';
requireAdmin();

$id = intval($_GET['id'] ?? 0);
if (!$id) redirect('modules/donors/list.php');

$conn = getConnection();
$conn->query("DELETE FROM donaciones WHERE id_donante = $id");
$conn->query("DELETE FROM donantes WHERE id_donante = $id");
$conn->close();

redirect('modules/donors/list.php?deleted=1');
