<?php
require_once __DIR__ . '/../../config/app.php';
require_once __DIR__ . '/../../config/database.php';
requireLogin();

$pageTitle  = 'Nuevo Donante';
$activePage = 'donantes';
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre   = sanitize($_POST['nombre']   ?? '');
    $correo   = sanitize($_POST['correo']   ?? '');
    $telefono = sanitize($_POST['telefono'] ?? '');

    if (empty($nombre)) $errors[] = 'El nombre es obligatorio.';
    if (!empty($correo) && !filter_var($correo, FILTER_VALIDATE_EMAIL))
        $errors[] = 'El correo no tiene un formato válido.';

    if (empty($errors)) {
        $conn = getConnection();
        $stmt = $conn->prepare("INSERT INTO donantes (nombre, correo, telefono) VALUES (?,?,?)");
        $stmt->bind_param('sss', $nombre, $correo, $telefono);
        if ($stmt->execute()) {
            $stmt->close(); $conn->close();
            redirect('modules/donors/list.php?created=1');
        } else {
            $errors[] = 'Error al guardar: ' . $conn->error;
            $stmt->close(); $conn->close();
        }
    }
}

include __DIR__ . '/../../includes/header.php';
include __DIR__ . '/../../includes/navbar.php';
?>

<div class="main-content" style="max-width:600px">
  <nav style="font-size:.85rem;color:var(--text-muted);margin-bottom:1.25rem">
    <a href="<?= APP_URL ?>/modules/donors/list.php" style="color:var(--accent);text-decoration:none">Donantes</a>
    &rsaquo; Nuevo Donante
  </nav>

  <div class="table-card" style="padding:2rem">
    <h2 style="font-size:1.3rem;font-weight:700;margin-bottom:1.5rem">
      <i class="fas fa-user-plus" style="color:var(--accent)"></i> Registrar Nuevo Donante
    </h2>

    <?php foreach ($errors as $e): ?>
      <div class="alert alert-danger"><i class="fas fa-exclamation-circle"></i> <?= $e ?></div>
    <?php endforeach; ?>

    <form method="POST">
      <label class="form-label">Nombre Completo <span style="color:var(--danger)">*</span></label>
      <div class="input-group">
        <i class="fas fa-user input-icon"></i>
        <input type="text" name="nombre" class="form-control"
          placeholder="Nombre completo del donante"
          value="<?= htmlspecialchars($_POST['nombre'] ?? '') ?>" required>
      </div>

      <label class="form-label" style="margin-top:.75rem">Correo Electrónico</label>
      <div class="input-group">
        <i class="fas fa-envelope input-icon"></i>
        <input type="email" name="correo" class="form-control"
          placeholder="correo@ejemplo.com"
          value="<?= htmlspecialchars($_POST['correo'] ?? '') ?>">
      </div>

      <label class="form-label" style="margin-top:.75rem">Teléfono</label>
      <div class="input-group">
        <i class="fas fa-phone input-icon"></i>
        <input type="text" name="telefono" class="form-control"
          placeholder="+502 0000-0000"
          value="<?= htmlspecialchars($_POST['telefono'] ?? '') ?>">
      </div>

      <div style="display:flex;gap:.75rem;margin-top:1.75rem;justify-content:flex-end">
        <a href="<?= APP_URL ?>/modules/donors/list.php" class="btn" style="background:var(--bg-light);border:1.5px solid var(--border);color:var(--text-dark);border-radius:var(--radius);font-weight:600;padding:.65rem 1.25rem;text-decoration:none">
          Cancelar
        </a>
        <button type="submit" class="btn btn-primary" style="width:auto;padding:.65rem 2rem">
          <i class="fas fa-save"></i> Guardar Donante
        </button>
      </div>
    </form>
  </div>
</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
