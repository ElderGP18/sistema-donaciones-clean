<?php
require_once __DIR__ . '/config/app.php';
require_once __DIR__ . '/config/database.php';
requireLogin();

$pageTitle  = 'Dashboard';
$activePage = 'dashboard';

$conn = getConnection();

/* KPIs */
$kpi = ['campanas' => 0, 'donaciones' => 0, 'recaudado' => 0, 'egresos' => 0, 'saldo' => 0, 'donantes' => 0];

$r = $conn->query("SELECT COUNT(*) AS v FROM campanas WHERE estado='activa'");
if ($r) $kpi['campanas'] = $r->fetch_assoc()['v'];

$r = $conn->query("SELECT COUNT(*) AS v, COALESCE(SUM(monto),0) AS s FROM donaciones");
if ($r) { $row = $r->fetch_assoc(); $kpi['donaciones'] = $row['v']; $kpi['recaudado'] = $row['s']; }

$r = $conn->query("SELECT COALESCE(SUM(monto),0) AS s FROM egresos");
if ($r) $kpi['egresos'] = $r->fetch_assoc()['s'];

$kpi['saldo'] = $kpi['recaudado'] - $kpi['egresos'];

$r = $conn->query("SELECT COUNT(*) AS v FROM donantes");
if ($r) $kpi['donantes'] = $r->fetch_assoc()['v'];

/* Últimas donaciones */
$ultimas = [];
$res = $conn->query("
    SELECT d.fecha, d.monto, dn.nombre AS donante, c.nombre AS campana
    FROM donaciones d
    JOIN donantes dn ON dn.id_donante = d.id_donante
    JOIN campanas c  ON c.id_campana  = d.id_campana
    ORDER BY d.created_at DESC LIMIT 8
");
if ($res) while ($row = $res->fetch_assoc()) $ultimas[] = $row;

/* Campañas activas */
$campanas = [];
$res = $conn->query("
    SELECT c.id_campana, c.nombre, c.meta, c.estado,
           COALESCE((SELECT SUM(monto) FROM donaciones WHERE id_campana=c.id_campana),0) AS recaudado
    FROM campanas c ORDER BY c.created_at DESC LIMIT 5
");
if ($res) while ($row = $res->fetch_assoc()) $campanas[] = $row;

$conn->close();
include __DIR__ . '/includes/header.php';
include __DIR__ . '/includes/navbar.php';
?>

<!-- Dashboard header -->
<div class="dashboard-header">
  <div class="dashboard-header-inner">
    <div>
      <h1>Panel de Administración</h1>
      <p>Bienvenido, <?= htmlspecialchars($_SESSION['user_nombre']) ?> · <?= date('d/m/Y') ?></p>
    </div>
    <div style="display:flex;gap:.75rem;flex-wrap:wrap">
      <?php if ($_SESSION['user_rol'] === 'admin'): ?>
        <a href="<?= APP_URL ?>/modules/campaigns/create.php" class="btn btn-primary btn-sm">
          <i class="fas fa-plus"></i> Nueva Campaña
        </a>
      <?php endif; ?>
      <a href="<?= APP_URL ?>/modules/donations/create.php" class="btn btn-sm" style="background:var(--bg-light);border:1.5px solid var(--border);color:var(--text-dark);border-radius:var(--radius);font-weight:600;padding:.4rem .9rem;text-decoration:none;display:inline-flex;align-items:center;gap:.4rem">
        <i class="fas fa-hand-holding-heart"></i> Registrar Donación
      </a>
    </div>
  </div>
</div>

<div class="main-content">

  <!-- KPIs -->
  <div class="kpi-grid">
    <div class="kpi-card">
      <div class="kpi-icon blue"><i class="fas fa-bullhorn"></i></div>
      <div>
        <div class="kpi-value"><?= $kpi['campanas'] ?></div>
        <div class="kpi-label">Campañas Activas</div>
      </div>
    </div>
    <div class="kpi-card">
      <div class="kpi-icon green"><i class="fas fa-arrow-down"></i></div>
      <div>
        <div class="kpi-value">Q <?= number_format($kpi['recaudado'],2) ?></div>
        <div class="kpi-label">Total Recaudado</div>
      </div>
    </div>
    <div class="kpi-card">
      <div class="kpi-icon orange"><i class="fas fa-arrow-up"></i></div>
      <div>
        <div class="kpi-value">Q <?= number_format($kpi['egresos'],2) ?></div>
        <div class="kpi-label">Total Egresos</div>
      </div>
    </div>
    <div class="kpi-card">
      <div class="kpi-icon purple"><i class="fas fa-wallet"></i></div>
      <div>
        <div class="kpi-value">Q <?= number_format($kpi['saldo'],2) ?></div>
        <div class="kpi-label">Saldo Disponible</div>
      </div>
    </div>
  </div>

  <!-- Grid 2 columnas -->
  <div style="display:grid;grid-template-columns:1fr 1fr;gap:1.5rem;margin-bottom:1.5rem">

    <!-- Últimas donaciones -->
    <div class="table-card" style="grid-column:1 / -1">
      <div class="table-header">
        <h3><i class="fas fa-history" style="color:var(--accent)"></i> Últimas Donaciones</h3>
        <a href="<?= APP_URL ?>/modules/donations/list.php" style="font-size:.85rem;color:var(--accent);text-decoration:none">Ver todas →</a>
      </div>
      <?php if (empty($ultimas)): ?>
        <p style="padding:1.5rem;text-align:center;color:var(--text-muted)">No hay donaciones registradas aún.</p>
      <?php else: ?>
        <div class="table-responsive">
          <table>
            <thead>
              <tr>
                <th>Fecha</th><th>Donante</th><th>Campaña</th><th>Monto</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($ultimas as $d): ?>
                <tr>
                  <td><?= date('d/m/Y', strtotime($d['fecha'])) ?></td>
                  <td><?= htmlspecialchars($d['donante']) ?></td>
                  <td><?= htmlspecialchars($d['campana']) ?></td>
                  <td><strong>Q <?= number_format($d['monto'],2) ?></strong></td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      <?php endif; ?>
    </div>

  </div>

  <!-- Campañas -->
  <div class="table-card">
    <div class="table-header">
      <h3><i class="fas fa-bullhorn" style="color:var(--accent)"></i> Campañas</h3>
      <a href="<?= APP_URL ?>/modules/campaigns/list.php" style="font-size:.85rem;color:var(--accent);text-decoration:none">Ver todas →</a>
    </div>
    <?php if (empty($campanas)): ?>
      <p style="padding:1.5rem;text-align:center;color:var(--text-muted)">
        No hay campañas. <a href="<?= APP_URL ?>/modules/campaigns/create.php">Crea una</a>.
      </p>
    <?php else: ?>
      <div class="table-responsive">
        <table>
          <thead>
            <tr><th>Campaña</th><th>Meta</th><th>Recaudado</th><th>Progreso</th><th>Estado</th><th>Acciones</th></tr>
          </thead>
          <tbody>
            <?php foreach ($campanas as $c):
              $pct = $c['meta'] > 0 ? min(100, round($c['recaudado'] / $c['meta'] * 100)) : 0;
            ?>
              <tr>
                <td><strong><?= htmlspecialchars($c['nombre']) ?></strong></td>
                <td>Q <?= number_format($c['meta'],2) ?></td>
                <td>Q <?= number_format($c['recaudado'],2) ?></td>
                <td style="min-width:120px">
                  <div class="progress-bar-track" style="height:8px">
                    <div class="progress-bar-fill" data-width="<?= $pct ?>" style="width:0"></div>
                  </div>
                  <small style="color:var(--text-muted)"><?= $pct ?>%</small>
                </td>
                <td>
                  <span class="badge <?= $c['estado'] === 'activa' ? 'badge-success' : ($c['estado'] === 'pausada' ? 'badge-warning' : 'badge-gray') ?>">
                    <?= ucfirst($c['estado']) ?>
                  </span>
                </td>
                <td>
                  <a href="<?= APP_URL ?>/modules/campaigns/view.php?id=<?= $c['id_campana'] ?>" style="color:var(--accent);font-size:.8rem;text-decoration:none">Ver</a>
                  <?php if ($_SESSION['user_rol'] === 'admin'): ?>
                    · <a href="<?= APP_URL ?>/modules/campaigns/edit.php?id=<?= $c['id_campana'] ?>" style="color:var(--text-muted);font-size:.8rem;text-decoration:none">Editar</a>
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

<?php include __DIR__ . '/includes/footer.php'; ?>
