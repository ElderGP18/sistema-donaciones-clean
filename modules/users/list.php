<?php
require_once __DIR__ . '/../../config/app.php';
require_once __DIR__ . '/../../config/database.php';
requireAdmin();

$pageTitle  = 'Usuarios';
$activePage = 'usuarios';

$conn     = getConnection();
$usuarios = [];
$res      = $conn->query("SELECT id_usuario, nombre, correo, rol, activo, created_at FROM usuarios ORDER BY created_at DESC");
if ($res) while ($r = $res->fetch_assoc()) $usuarios[] = $r;
$conn->close();

$created = isset($_GET['created']);
$updated = isset($_GET['updated']);
include __DIR__ . '/../../includes/header.php';
include __DIR__ . '/../../includes/navbar.php';
?>

<div class="main-content">
  <?php if ($created): ?>
    <div class="alert alert-success" data-auto-hide><i class="fas fa-check-circle"></i> Usuario creado exitosamente.</div>
  <?php endif; ?>
  <?php if ($updated): ?>
    <div class="alert alert-success" data-auto-hide><i class="fas fa-check-circle"></i> Usuario actualizado.</div>
  <?php endif; ?>

  <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:1.5rem;flex-wrap:wrap;gap:.75rem">
    <div>
      <h1 class="section-title">Gestión de Usuarios</h1>
      <p class="section-sub">Administra las cuentas con acceso al sistema</p>
    </div>
    <a href="<?= APP_URL ?>/modules/users/create.php" class="btn btn-primary">
      <i class="fas fa-user-plus"></i> Nuevo Usuario
    </a>
  </div>

  <div class="table-card">
    <div class="table-responsive">
      <table>
        <thead>
          <tr><th>#</th><th>Nombre</th><th>Correo</th><th>Rol</th><th>Estado</th><th>Creado</th><th>Acciones</th></tr>
        </thead>
        <tbody>
          <?php foreach ($usuarios as $i => $u): ?>
            <tr>
              <td style="color:var(--text-muted);font-size:.8rem"><?= $i + 1 ?></td>
              <td>
                <div style="display:flex;align-items:center;gap:.6rem">
                  <div style="width:32px;height:32px;border-radius:50%;background:<?= $u['rol']==='admin'?'linear-gradient(135deg,#dc2626,#9f1239)':'linear-gradient(135deg,var(--accent),#2563eb)' ?>;display:flex;align-items:center;justify-content:center;color:#fff;font-weight:700;font-size:.8rem;flex-shrink:0">
                    <?= strtoupper(substr($u['nombre'],0,1)) ?>
                  </div>
                  <strong><?= htmlspecialchars($u['nombre']) ?></strong>
                </div>
              </td>
              <td><?= htmlspecialchars($u['correo']) ?></td>
              <td>
                <span class="badge <?= $u['rol']==='admin'?'badge-warning':'badge-info' ?>">
                  <?= ucfirst($u['rol']) ?>
                </span>
              </td>
              <td>
                <span class="badge <?= $u['activo']?'badge-success':'badge-gray' ?>">
                  <?= $u['activo'] ? 'Activo' : 'Inactivo' ?>
                </span>
              </td>
              <td style="font-size:.8rem;color:var(--text-muted)"><?= date('d/m/Y', strtotime($u['created_at'])) ?></td>
              <td>
                <div style="display:flex;gap:.4rem">
                  <a href="<?= APP_URL ?>/modules/users/edit.php?id=<?= $u['id_usuario'] ?>"
                    style="color:#7c3aed;font-size:.8rem;text-decoration:none;padding:.3rem .6rem;background:#faf5ff;border-radius:4px">
                    <i class="fas fa-edit"></i>
                  </a>
                  <?php if ($u['id_usuario'] != $_SESSION['user_id']): ?>
                    <a href="<?= APP_URL ?>/modules/users/toggle.php?id=<?= $u['id_usuario'] ?>"
                      data-confirm="<?= $u['activo'] ? '¿Desactivar este usuario?' : '¿Activar este usuario?' ?>"
                      style="color:<?= $u['activo']?'var(--danger)':'var(--success)' ?>;font-size:.8rem;text-decoration:none;padding:.3rem .6rem;background:<?= $u['activo']?'#fee2e2':'#d1fae5' ?>;border-radius:4px">
                      <i class="fas fa-<?= $u['activo']?'ban':'check' ?>"></i>
                    </a>
                  <?php endif; ?>
                </div>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
