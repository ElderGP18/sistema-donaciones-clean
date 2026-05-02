<?php
require_once __DIR__ . '/../../config/app.php';
require_once __DIR__ . '/../../config/database.php';

$id = intval($_GET['id'] ?? 0);
if (!$id) redirect('modules/campaigns/list.php');

$conn = getConnection();
$stmt = $conn->prepare("SELECT * FROM campanas WHERE id_campana = ?");
$stmt->bind_param('i', $id);
$stmt->execute();
$campana = $stmt->get_result()->fetch_assoc();
$stmt->close();
if (!$campana) { $conn->close(); redirect('modules/campaigns/list.php'); }

/* Totales */
$r = $conn->query("SELECT COALESCE(SUM(monto),0) AS s, COUNT(*) AS c FROM donaciones WHERE id_campana=$id");
$row = $r->fetch_assoc(); $totalDonado = $row['s']; $numDonaciones = $row['c'];

$r = $conn->query("SELECT COALESCE(SUM(monto),0) AS s FROM egresos WHERE id_campana=$id");
$totalEgresos = $r->fetch_assoc()['s'];
$saldo = $totalDonado - $totalEgresos;
$pct   = $campana['meta'] > 0 ? min(100, round($totalDonado / $campana['meta'] * 100)) : 0;

/* Últimas donaciones de esta campaña */
$donaciones = [];
$res = $conn->query("SELECT d.fecha, d.monto, dn.nombre FROM donaciones d JOIN donantes dn ON dn.id_donante=d.id_donante WHERE d.id_campana=$id ORDER BY d.fecha DESC LIMIT 10");
if ($res) while ($row = $res->fetch_assoc()) $donaciones[] = $row;

$conn->close();

$pageTitle  = htmlspecialchars($campana['nombre']);
$activePage = 'campanas';
$created    = isset($_GET['created']);
$updated    = isset($_GET['updated']);

include __DIR__ . '/../../includes/header.php';
include __DIR__ . '/../../includes/navbar.php';
?>

<div class="main-content">

  <?php if ($created): ?>
    <div class="alert alert-success" data-auto-hide>
      <i class="fas fa-check-circle"></i> ¡Campaña creada exitosamente!
    </div>
  <?php endif; ?>
  <?php if ($updated): ?>
    <div class="alert alert-success" data-auto-hide>
      <i class="fas fa-check-circle"></i> Campaña actualizada correctamente.
    </div>
  <?php endif; ?>

  <nav style="font-size:.85rem;color:var(--text-muted);margin-bottom:1.25rem">
    <a href="<?= APP_URL ?>/modules/campaigns/list.php" style="color:var(--accent);text-decoration:none">Campañas</a>
    &rsaquo; <?= htmlspecialchars($campana['nombre']) ?>
  </nav>

  <!-- Header de campaña -->
  <div class="table-card" style="margin-bottom:1.5rem;overflow:hidden">
    <div style="height:200px;background:linear-gradient(135deg,var(--primary),#1e40af);display:flex;align-items:center;justify-content:center">
      <i class="fas fa-hand-holding-heart" style="font-size:5rem;color:rgba(255,255,255,.3)"></i>
    </div>
    <div style="padding:1.5rem">
      <div style="display:flex;align-items:flex-start;justify-content:space-between;flex-wrap:wrap;gap:1rem">
        <div>
          <span class="badge <?= $campana['estado'] === 'activa' ? 'badge-success' : 'badge-gray' ?>" style="margin-bottom:.5rem">
            <?= ucfirst($campana['estado']) ?>
          </span>
          <h1 style="font-size:1.6rem;font-weight:800"><?= htmlspecialchars($campana['nombre']) ?></h1>
          <p style="color:var(--text-muted);margin-top:.4rem"><?= htmlspecialchars($campana['descripcion'] ?? '') ?></p>
        </div>
        <?php if (isLoggedIn() && $_SESSION['user_rol'] === 'admin'): ?>
          <a href="<?= APP_URL ?>/modules/campaigns/edit.php?id=<?= $campana['id_campana'] ?>" class="btn btn-primary btn-sm">
            <i class="fas fa-edit"></i> Editar
          </a>
        <?php endif; ?>
      </div>

      <!-- Progress -->
      <div style="margin-top:1.5rem">
        <div style="display:flex;justify-content:space-between;margin-bottom:.5rem;font-size:.9rem">
          <strong>Q <?= number_format($totalDonado,2) ?> recaudados</strong>
          <span style="color:var(--text-muted)">Meta: Q <?= number_format($campana['meta'],2) ?></span>
        </div>
        <div class="progress-bar-track" style="height:12px;border-radius:999px">
          <div class="progress-bar-fill" data-width="<?= $pct ?>" style="width:0;height:100%;border-radius:999px"></div>
        </div>
        <div style="display:flex;justify-content:space-between;margin-top:.4rem;font-size:.8rem;color:var(--text-muted)">
          <span><?= $pct ?>% completado</span>
          <span><?= $numDonaciones ?> donantes</span>
        </div>
      </div>

      <!-- KPIs mini -->
      <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:1rem;margin-top:1.5rem">
        <div style="text-align:center;padding:.75rem;background:var(--bg-light);border-radius:var(--radius)">
          <div style="font-size:1.3rem;font-weight:800;color:var(--success)">Q <?= number_format($totalDonado,2) ?></div>
          <div style="font-size:.75rem;color:var(--text-muted)">Total Recaudado</div>
        </div>
        <div style="text-align:center;padding:.75rem;background:var(--bg-light);border-radius:var(--radius)">
          <div style="font-size:1.3rem;font-weight:800;color:var(--danger)">Q <?= number_format($totalEgresos,2) ?></div>
          <div style="font-size:.75rem;color:var(--text-muted)">Total Egresos</div>
        </div>
        <div style="text-align:center;padding:.75rem;background:var(--bg-light);border-radius:var(--radius)">
          <div style="font-size:1.3rem;font-weight:800;color:var(--accent)">Q <?= number_format($saldo,2) ?></div>
          <div style="font-size:.75rem;color:var(--text-muted)">Saldo Disponible</div>
        </div>
      </div>

      <!-- Acción donar -->
      <?php if ($campana['estado'] === 'activa'): ?>
        <div style="margin-top:1.5rem">
          <?php if (isLoggedIn()): ?>
            <a href="<?= APP_URL ?>/modules/donations/create.php?campana=<?= $campana['id_campana'] ?>" class="btn btn-primary">
              <i class="fas fa-hand-holding-heart"></i> Registrar Donación a esta Campaña
            </a>
          <?php else: ?>
            <a href="<?= APP_URL ?>/login.php" class="btn btn-primary">
              <i class="fas fa-sign-in-alt"></i> Iniciar sesión para donar
            </a>
          <?php endif; ?>
        </div>
      <?php endif; ?>
    </div>
  </div>

  <!-- Últimas donaciones -->
  <?php if (!empty($donaciones)): ?>
    <div class="table-card">
      <div class="table-header">
        <h3><i class="fas fa-list" style="color:var(--accent)"></i> Donaciones Recientes</h3>
      </div>
      <div class="table-responsive">
        <table>
          <thead><tr><th>Fecha</th><th>Donante</th><th>Monto</th></tr></thead>
          <tbody>
            <?php foreach ($donaciones as $d): ?>
              <tr>
                <td><?= date('d/m/Y', strtotime($d['fecha'])) ?></td>
                <td><?= htmlspecialchars($d['nombre']) ?></td>
                <td><strong>Q <?= number_format($d['monto'],2) ?></strong></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
  <?php endif; ?>

</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
