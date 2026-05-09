<?php
require_once __DIR__ . '/../../config/app.php';
require_once __DIR__ . '/../../config/database.php';
requireAdmin();

$id = intval($_GET['id'] ?? 0);
if (!$id) redirect('modules/donations/list.php');

$conn = getConnection();
$stmt = $conn->prepare("DELETE FROM donaciones WHERE id_donacion = ?");
$stmt->bind_param('i', $id);
$stmt->execute();
$stmt->close();
$conn->close();

redirect('modules/donations/list.php?deleted=1');
