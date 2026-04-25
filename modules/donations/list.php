<?php
require_once __DIR__ . '/../../config/app.php';
require_once __DIR__ . '/../../config/database.php';
requireLogin();

$pageTitle  = 'Donaciones';
$activePage = 'donaciones';

$conn = getConnection();
$donaciones = [];
$res = $conn->query("
    SELECT d.id_donacion, d.fecha, d.monto, dn.nombre AS donante, c.nombre AS campana, c.id_campana
    FROM donaciones d
    JOIN donantes dn ON dn.id_donante = d.id_donante
    JOIN campanas c  ON c.id_campana  = d.id_campana
    ORDER BY d.fecha DESC, d.created_at DESC
");
if ($res) while ($r = $res->fetch_assoc()) $donaciones[] = $r;
$conn->close();

$created = isset($_GET['created']);
include __DIR__ . '/../../includes/header.php';
include __DIR__ . '/../../includes/navbar.php';
?>

<div class="main-content">
  <?php if ($created): ?>
    <div class="alert alert-success" data-auto-hide>
      <i class="fas fa-check-circle"></i> ¡Donación registrada exitosamente!
    </div>
  <?php endif; ?>

  <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:1.5rem;flex-wrap:wrap;gap:.75rem">
    <div>
      <h1 class="section-title">Donaciones</h1>
      <p class="section-sub">Registro de todas las donaciones recibidas</p>
    </div>
    <a href="<?= APP_URL ?>/modules/donations/create.php" class="btn btn-primary">
      <i class="fas fa-plus"></i> Registrar Donación
    </a>
  </div>

  <div class="table-card">
    <?php if (empty($donaciones)): ?>
      <p style="padding:2rem;text-align:center;color:var(--text-muted)">No hay donaciones registradas.</p>
    <?php else: ?>
      <div class="table-responsive">
        <table>
          <thead>
            <tr><th>#</th><th>Fecha</th><th>Donante</th><th>Campaña</th><th>Monto</th><th>Acciones</th></tr>
          </thead>
          <tbody>
            <?php foreach ($donaciones as $i => $d): ?>
              <tr>
                <td style="color:var(--text-muted)"><?= $i + 1 ?></td>
                <td><?= date('d/m/Y', strtotime($d['fecha'])) ?></td>
                <td><?= htmlspecialchars($d['donante']) ?></td>
                <td>
                  <a href="<?= APP_URL ?>/modules/campaigns/view.php?id=<?= $d['id_campana'] ?>"
                    style="color:var(--accent);text-decoration:none">
                    <?= htmlspecialchars($d['campana']) ?>
                  </a>
                </td>
                <td><strong>Q <?= number_format($d['monto'],2) ?></strong></td>
                <td>
                  <?php if ($_SESSION['user_rol'] === 'admin'): ?>
                    <a href="<?= APP_URL ?>/modules/donations/delete.php?id=<?= $d['id_donacion'] ?>"
                      data-confirm="¿Eliminar esta donación?"
                      style="color:var(--danger);font-size:.8rem;text-decoration:none">
                      <i class="fas fa-trash"></i> Eliminar
                    </a>
                  <?php endif; ?>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php endif; ?>
  </div>
</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
