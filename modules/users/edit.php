<?php
require_once __DIR__ . '/../../config/app.php';
require_once __DIR__ . '/../../config/database.php';
requireAdmin();

$id = intval($_GET['id'] ?? 0);
if (!$id) redirect('modules/users/list.php');

$conn = getConnection();
$stmt = $conn->prepare("SELECT * FROM usuarios WHERE id_usuario = ?");
$stmt->bind_param('i', $id); $stmt->execute();
$usuario = $stmt->get_result()->fetch_assoc(); $stmt->close();
if (!$usuario) { $conn->close(); redirect('modules/users/list.php'); }

$pageTitle  = 'Editar Usuario';
$activePage = 'usuarios';
$errors     = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre   = sanitize($_POST['nombre']   ?? '');
    $correo   = sanitize($_POST['correo']   ?? '');
    $rol      = in_array($_POST['rol'] ?? '', ['admin','encargado']) ? $_POST['rol'] : 'encargado';
    $password = $_POST['password'] ?? '';
    $confirm  = $_POST['password_confirm'] ?? '';

    if (empty($nombre))   $errors[] = 'El nombre es obligatorio.';
    if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) $errors[] = 'Correo inválido.';
    if (!empty($password)) {
        if (strlen($password) < 6)   $errors[] = 'La contraseña debe tener al menos 6 caracteres.';
        if ($password !== $confirm)  $errors[] = 'Las contraseñas no coinciden.';
    }

    if (empty($errors)) {
        if (!empty($password)) {
            $hash = password_hash($password, PASSWORD_BCRYPT);
            $stmt = $conn->prepare("UPDATE usuarios SET nombre=?, correo=?, rol=?, password=? WHERE id_usuario=?");
            $stmt->bind_param('ssssi', $nombre, $correo, $rol, $hash, $id);
        } else {
            $stmt = $conn->prepare("UPDATE usuarios SET nombre=?, correo=?, rol=? WHERE id_usuario=?");
            $stmt->bind_param('sssi', $nombre, $correo, $rol, $id);
        }
        if ($stmt->execute()) {
            $stmt->close(); $conn->close();
            redirect('modules/users/list.php?updated=1');
        } else {
            $errors[] = 'Error: ' . $conn->error;
            $stmt->close();
        }
    }
}
$conn->close();

include __DIR__ . '/../../includes/header.php';
include __DIR__ . '/../../includes/navbar.php';
?>

<div class="main-content" style="max-width:560px">
  <nav style="font-size:.85rem;color:var(--text-muted);margin-bottom:1.25rem">
    <a href="<?= APP_URL ?>/modules/users/list.php" style="color:var(--accent);text-decoration:none">Usuarios</a>
    &rsaquo; Editar
  </nav>

  <div class="table-card" style="padding:2rem">
    <h2 style="font-size:1.3rem;font-weight:700;margin-bottom:1.5rem">
      <i class="fas fa-user-edit" style="color:#7c3aed"></i> Editar Usuario
    </h2>

    <?php foreach ($errors as $e): ?>
      <div class="alert alert-danger"><i class="fas fa-exclamation-circle"></i> <?= $e ?></div>
    <?php endforeach; ?>

    <form method="POST">
      <label class="form-label">Nombre <span style="color:var(--danger)">*</span></label>
      <div class="input-group">
        <i class="fas fa-user input-icon"></i>
        <input type="text" name="nombre" class="form-control"
          value="<?= htmlspecialchars($_POST['nombre'] ?? $usuario['nombre']) ?>" required>
      </div>

      <label class="form-label" style="margin-top:.75rem">Correo <span style="color:var(--danger)">*</span></label>
      <div class="input-group">
        <i class="fas fa-envelope input-icon"></i>
        <input type="email" name="correo" class="form-control"
          value="<?= htmlspecialchars($_POST['correo'] ?? $usuario['correo']) ?>" required>
      </div>

      <label class="form-label" style="margin-top:.75rem">Rol</label>
      <div class="input-group">
        <i class="fas fa-shield-alt input-icon"></i>
        <select name="rol" class="form-control" style="padding-left:2.25rem">
          <option value="encargado" <?= $usuario['rol']==='encargado'?'selected':'' ?>>Encargado</option>
          <option value="admin"     <?= $usuario['rol']==='admin'    ?'selected':'' ?>>Administrador</option>
        </select>
      </div>

      <div style="background:var(--bg-light);border-radius:var(--radius);padding:1rem;margin-top:1rem;margin-bottom:.5rem">
        <p style="font-size:.8rem;color:var(--text-muted);margin-bottom:.75rem">
          <i class="fas fa-info-circle"></i> Deja en blanco para mantener la contraseña actual.
        </p>
        <label class="form-label">Nueva Contraseña</label>
        <div class="input-group">
          <i class="fas fa-lock input-icon"></i>
          <input type="password" name="password" class="form-control" placeholder="Nueva contraseña (opcional)">
        </div>
        <label class="form-label" style="margin-top:.75rem">Confirmar Contraseña</label>
        <div class="input-group">
          <i class="fas fa-lock input-icon"></i>
          <input type="password" name="password_confirm" class="form-control">
        </div>
      </div>

      <div style="display:flex;gap:.75rem;margin-top:1.75rem;justify-content:flex-end">
        <a href="<?= APP_URL ?>/modules/users/list.php" class="btn" style="background:var(--bg-light);border:1.5px solid var(--border);color:var(--text-dark);border-radius:var(--radius);font-weight:600;padding:.65rem 1.25rem;text-decoration:none">
          Cancelar
        </a>
        <button type="submit" class="btn btn-primary" style="width:auto;padding:.65rem 2rem">
          <i class="fas fa-save"></i> Guardar Cambios
        </button>
      </div>
    </form>
  </div>
</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
