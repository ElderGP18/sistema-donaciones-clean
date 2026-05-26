<?php
require_once __DIR__ . '/../../config/app.php';
require_once __DIR__ . '/../../config/database.php';
requireAdmin();

$id = intval($_GET['id'] ?? 0);
if (!$id) redirect('modules/donors/list.php');

$conn = getConnection();
$s1 = $conn->prepare("DELETE FROM donaciones WHERE id_donante = ?");
$s1->bind_param('i', $id); $s1->execute(); $s1->close();
$s2 = $conn->prepare("DELETE FROM donantes WHERE id_donante = ?");
$s2->bind_param('i', $id); $s2->execute(); $s2->close();
$conn->close();

redirect('modules/donors/list.php?deleted=1');
