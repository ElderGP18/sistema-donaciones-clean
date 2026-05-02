<?php
require_once __DIR__ . '/../../config/app.php';
require_once __DIR__ . '/../../config/database.php';
requireLogin();

$id = intval($_GET['id'] ?? 0);
if (!$id) redirect('modules/donors/list.php');

$conn = getConnection();
$stmt = $conn->prepare("SELECT * FROM donantes WHERE id_donante = ?");
$stmt->bind_param('i', $id); $stmt->execute();
$donante = $stmt->get_result()->fetch_assoc(); $stmt->close();
if (!$donante) { $conn->close(); redirect('modules/donors/list.php'); }

$r = $conn->query("SELECT COUNT(*) v, COALESCE(SUM(monto),0) s FROM donaciones WHERE id_donante=$id");
$row = $r->fetch_assoc(); $totalDonaciones = $row['v']; $totalDonado = $row['s'];

$donaciones = [];
$res = $conn->query("
    SELECT d.fecha, d.monto, c.nombre AS campana, c.id_campana
    FROM donaciones d JOIN campanas c ON c.id_campana = d.id_campana
    WHERE d.id_donante = $id ORDER BY d.fecha DESC
");
if ($res) while ($r2 = $res->fetch_assoc()) $donaciones[] = $r2;
$conn->close();

$pageTitle  = htmlspecialchars($donante['nombre']);
$activePage = 'donantes';
$updated    = isset($_GET['updated']);

include __DIR__ . '/../../includes/header.php';
include __DIR__ . '/../../includes/navbar.php';
?>

<div class="main-content">
  <?php if ($updated): ?>
    <div class="alert alert-success" data-auto-hide>
      <i class="fas fa-check-circle"></i> Donante actualizado correctamente.
    </div>
  <?php endif; ?>

  <nav style="font-size:.85rem;color:var(--text-muted);margin-bottom:1.25rem">
    <a href="<?= APP_URL ?>/modules/donors/list.php" style="color:var(--accent);text-decoration:none">Donantes</a>
    &rsaquo; <?= htmlspecialchars($donante['nombre']) ?>
  </nav>

  <div style="display:grid;grid-template-columns:300px 1fr;gap:1.5rem;align-items:start">

    <!-- Perfil -->
    <div class="table-card" style="padding:1.5rem;text-align:center">
      <div style="width:80px;height:80px;border-radius:50%;background:linear-gradient(135deg,var(--accent),#7c3aed);display:flex;align-items:center;justify-content:center;color:#fff;font-weight:800;font-size:2rem;margin:0 auto 1rem">
        <?= strtoupper(substr($donante['nombre'], 0, 1)) ?>
      </div>
      <h2 style="font-weight:800;font-size:1.2rem;margin-bottom:.25rem"><?= htmlspecialchars($donante['nombre']) ?></h2>
      <p style="font-size:.875rem;color:var(--text-muted);margin-bottom:1.25rem">
        Donante desde <?= date('d/m/Y', strtotime($donante['created_at'])) ?>
      </p>

      <?php if ($donante['correo']): ?>
        <div style="display:flex;align-items:center;gap:.5rem;font-size:.875rem;margin-bottom:.5rem;justify-content:center">
          <i class="fas fa-envelope" style="color:var(--text-muted)"></i>
          <?= htmlspecialchars($donante['correo']) ?>
        </div>
      <?php endif; ?>
      <?php if ($donante['telefono']): ?>
        <div style="display:flex;align-items:center;gap:.5rem;font-size:.875rem;margin-bottom:1.25rem;justify-content:center">
          <i class="fas fa-phone" style="color:var(--text-muted)"></i>
          <?= htmlspecialchars($donante['telefono']) ?>
        </div>
      <?php endif; ?>

      <div style="display:grid;grid-template-columns:1fr 1fr;gap:.75rem;margin-bottom:1.25rem">
        <div style="padding:.75rem;background:var(--bg-light);border-radius:var(--radius)">
          <div style="font-size:1.3rem;font-weight:800;color:var(--accent)"><?= $totalDonaciones ?></div>
          <div style="font-size:.7rem;color:var(--text-muted)">Donaciones</div>
        </div>
        <div style="padding:.75rem;background:var(--bg-light);border-radius:var(--radius)">
          <div style="font-size:1rem;font-weight:800;color:var(--success)">Q <?= number_format($totalDonado, 0) ?></div>
          <div style="font-size:.7rem;color:var(--text-muted)">Total</div>
        </div>
      </div>

      <a href="<?= APP_URL ?>/modules/donors/edit.php?id=<?= $id ?>" class="btn btn-primary" style="width:100%;justify-content:center">
        <i class="fas fa-edit"></i> Editar Donante
      </a>
    </div>

    <!-- Historial de donaciones -->
    <div class="table-card">
      <div class="table-header">
        <h3><i class="fas fa-history" style="color:var(--accent)"></i> Historial de Donaciones</h3>
        <span style="font-size:.8rem;color:var(--text-muted)">Total: Q <?= number_format($totalDonado, 2) ?></span>
      </div>
      <?php if (empty($donaciones)): ?>
        <p style="padding:2rem;text-align:center;color:var(--text-muted)">Este donante no tiene donaciones registradas.</p>
      <?php else: ?>
        <div class="table-responsive">
          <table>
            <thead><tr><th>Fecha</th><th>Campaña</th><th>Monto</th></tr></thead>
            <tbody>
              <?php foreach ($donaciones as $d): ?>
                <tr>
                  <td><?= date('d/m/Y', strtotime($d['fecha'])) ?></td>
                  <td>
                    <a href="<?= APP_URL ?>/modules/campaigns/view.php?id=<?= $d['id_campana'] ?>"
                      style="color:var(--accent);text-decoration:none">
                      <?= htmlspecialchars($d['campana']) ?>
                    </a>
                  </td>
                  <td><strong style="color:var(--success)">Q <?= number_format($d['monto'], 2) ?></strong></td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      <?php endif; ?>
    </div>

  </div>
</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
