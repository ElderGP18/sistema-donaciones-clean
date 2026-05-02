<?php
require_once __DIR__ . '/../../config/app.php';
require_once __DIR__ . '/../../config/database.php';
requireAdmin();

$pageTitle  = 'Nuevo Usuario';
$activePage = 'usuarios';
$errors     = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre   = sanitize($_POST['nombre']   ?? '');
    $correo   = sanitize($_POST['correo']   ?? '');
    $password = $_POST['password']           ?? '';
    $confirm  = $_POST['password_confirm']   ?? '';
    $rol      = in_array($_POST['rol'] ?? '', ['admin','encargado']) ? $_POST['rol'] : 'encargado';

    if (empty($nombre))   $errors[] = 'El nombre es obligatorio.';
    if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) $errors[] = 'Correo inválido.';
    if (strlen($password) < 6) $errors[] = 'La contraseña debe tener al menos 6 caracteres.';
    if ($password !== $confirm)  $errors[] = 'Las contraseñas no coinciden.';

    if (empty($errors)) {
        $conn = getConnection();
        $hash = password_hash($password, PASSWORD_BCRYPT);
        $stmt = $conn->prepare("INSERT INTO usuarios (nombre, correo, password, rol) VALUES (?,?,?,?)");
        $stmt->bind_param('ssss', $nombre, $correo, $hash, $rol);
        if ($stmt->execute()) {
            $stmt->close(); $conn->close();
            redirect('modules/users/list.php?created=1');
        } else {
            $errors[] = 'Error: ' . $conn->error;
            $stmt->close(); $conn->close();
        }
    }
}

include __DIR__ . '/../../includes/header.php';
include __DIR__ . '/../../includes/navbar.php';
?>

<div class="main-content" style="max-width:560px">
  <nav style="font-size:.85rem;color:var(--text-muted);margin-bottom:1.25rem">
    <a href="<?= APP_URL ?>/modules/users/list.php" style="color:var(--accent);text-decoration:none">Usuarios</a>
    &rsaquo; Nuevo Usuario
  </nav>

  <div class="table-card" style="padding:2rem">
    <h2 style="font-size:1.3rem;font-weight:700;margin-bottom:1.5rem">
      <i class="fas fa-user-plus" style="color:var(--accent)"></i> Crear Nuevo Usuario
    </h2>

    <?php foreach ($errors as $e): ?>
      <div class="alert alert-danger"><i class="fas fa-exclamation-circle"></i> <?= $e ?></div>
    <?php endforeach; ?>

    <form method="POST">
      <label class="form-label">Nombre Completo <span style="color:var(--danger)">*</span></label>
      <div class="input-group">
        <i class="fas fa-user input-icon"></i>
        <input type="text" name="nombre" class="form-control"
          value="<?= htmlspecialchars($_POST['nombre'] ?? '') ?>" required>
      </div>

      <label class="form-label" style="margin-top:.75rem">Correo Electrónico <span style="color:var(--danger)">*</span></label>
      <div class="input-group">
        <i class="fas fa-envelope input-icon"></i>
        <input type="email" name="correo" class="form-control"
          value="<?= htmlspecialchars($_POST['correo'] ?? '') ?>" required>
      </div>

      <label class="form-label" style="margin-top:.75rem">Rol <span style="color:var(--danger)">*</span></label>
      <div class="input-group">
        <i class="fas fa-shield-alt input-icon"></i>
        <select name="rol" class="form-control" style="padding-left:2.25rem">
          <option value="encargado" <?= ($_POST['rol'] ?? '') === 'encargado' ? 'selected' : '' ?>>Encargado</option>
          <option value="admin"     <?= ($_POST['rol'] ?? '') === 'admin'     ? 'selected' : '' ?>>Administrador</option>
        </select>
      </div>

      <label class="form-label" style="margin-top:.75rem">Contraseña <span style="color:var(--danger)">*</span></label>
      <div class="input-group">
        <i class="fas fa-lock input-icon"></i>
        <input type="password" name="password" class="form-control" placeholder="Mínimo 6 caracteres" required>
      </div>

      <label class="form-label" style="margin-top:.75rem">Confirmar Contraseña <span style="color:var(--danger)">*</span></label>
      <div class="input-group">
        <i class="fas fa-lock input-icon"></i>
        <input type="password" name="password_confirm" class="form-control" required>
      </div>

      <div style="display:flex;gap:.75rem;margin-top:1.75rem;justify-content:flex-end">
        <a href="<?= APP_URL ?>/modules/users/list.php" class="btn" style="background:var(--bg-light);border:1.5px solid var(--border);color:var(--text-dark);border-radius:var(--radius);font-weight:600;padding:.65rem 1.25rem;text-decoration:none">
          Cancelar
        </a>
        <button type="submit" class="btn btn-primary" style="width:auto;padding:.65rem 2rem">
          <i class="fas fa-save"></i> Crear Usuario
        </button>
      </div>
    </form>
  </div>
</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
