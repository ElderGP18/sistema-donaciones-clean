<?php
require_once __DIR__ . '/../../config/app.php';
require_once __DIR__ . '/../../config/database.php';
requireLogin();

$pageTitle  = 'Reporte de Campaña';
$activePage = 'reportes';

$conn = getConnection();

/* Campañas para el selector */
$campanas = [];
$res = $conn->query("SELECT id_campana, nombre FROM campanas ORDER BY nombre");
if ($res) while ($r = $res->fetch_assoc()) $campanas[] = $r;

$campanaId = intval($_GET['campana'] ?? 0);
$campana = null; $donaciones = []; $egresos = [];
$totalDonado = $totalEgresos = $saldo = $numDonantes = 0;
$donacionesMes = [];

if ($campanaId) {
    $s = $conn->prepare("SELECT * FROM campanas WHERE id_campana = ?");
    $s->bind_param('i', $campanaId); $s->execute();
    $campana = $s->get_result()->fetch_assoc(); $s->close();

    if ($campana) {
        $st = $conn->prepare("SELECT COALESCE(SUM(monto),0) s, COUNT(*) c FROM donaciones WHERE id_campana = ?");
        $st->bind_param('i', $campanaId); $st->execute();
        $row = $st->get_result()->fetch_assoc(); $totalDonado = $row['s']; $numDonantes = $row['c']; $st->close();

        $st = $conn->prepare("SELECT COALESCE(SUM(monto),0) s FROM egresos WHERE id_campana = ?");
        $st->bind_param('i', $campanaId); $st->execute();
        $totalEgresos = $st->get_result()->fetch_assoc()['s']; $st->close();
        $saldo = $totalDonado - $totalEgresos;

        /* Últimas donaciones */
        $st = $conn->prepare("SELECT d.fecha, d.monto, dn.nombre FROM donaciones d JOIN donantes dn ON dn.id_donante=d.id_donante WHERE d.id_campana=? ORDER BY d.fecha DESC LIMIT 10");
        $st->bind_param('i', $campanaId); $st->execute();
        $res2 = $st->get_result();
        while ($r2 = $res2->fetch_assoc()) $donaciones[] = $r2; $st->close();

        /* Últimos egresos */
        $st = $conn->prepare("SELECT fecha, concepto, monto FROM egresos WHERE id_campana=? ORDER BY fecha DESC LIMIT 10");
        $st->bind_param('i', $campanaId); $st->execute();
        $res2 = $st->get_result();
        while ($r2 = $res2->fetch_assoc()) $egresos[] = $r2; $st->close();

        /* Donaciones por mes (últimos 6 meses) */
        $st = $conn->prepare("SELECT DATE_FORMAT(fecha,'%b') mes_label, DATE_FORMAT(fecha,'%Y-%m') mes_key, SUM(monto) total FROM donaciones WHERE id_campana=? GROUP BY mes_key, mes_label ORDER BY mes_key DESC LIMIT 6");
        $st->bind_param('i', $campanaId); $st->execute();
        $res2 = $st->get_result();
        $tmp = [];
        while ($r2 = $res2->fetch_assoc()) $tmp[] = $r2;
        $donacionesMes = array_reverse($tmp); $st->close();
    }
}
$conn->close();
$pct = ($campana && $campana['meta'] > 0) ? min(100, round($totalDonado / $campana['meta'] * 100)) : 0;
$maxMes = !empty($donacionesMes) ? max(array_column($donacionesMes, 'total')) : 1;

include __DIR__ . '/../../includes/header.php';
include __DIR__ . '/../../includes/navbar.php';
?>

<div class="main-content">

  <!-- Selector de campaña -->
  <div class="table-card" style="padding:1.25rem;margin-bottom:1.5rem">
    <form method="GET" style="display:flex;align-items:center;gap:1rem;flex-wrap:wrap">
      <label style="font-weight:600;font-size:.9rem">Campaña:</label>
      <div class="input-group" style="flex:1;min-width:220px;margin:0">
        <i class="fas fa-bullhorn input-icon"></i>
        <select name="campana" class="form-control" style="padding-left:2.25rem" onchange="this.form.submit()">
          <option value="">Seleccionar campaña ▾</option>
          <?php foreach ($campanas as $c): ?>
            <option value="<?= $c['id_campana'] ?>" <?= $campanaId == $c['id_campana'] ? 'selected' : '' ?>>
              <?= htmlspecialchars($c['nombre']) ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>
      <?php if ($campana): ?>
        <button type="button" onclick="window.print()" class="btn btn-primary btn-sm">
          <i class="fas fa-file-pdf"></i> Exportar PDF
        </button>
      <?php endif; ?>
    </form>
  </div>

  <?php if (!$campana): ?>
    <div style="text-align:center;padding:4rem;color:var(--text-muted)">
      <i class="fas fa-chart-bar" style="font-size:3.5rem;opacity:.3;display:block;margin-bottom:1rem"></i>
      <p>Selecciona una campaña para ver su reporte.</p>
    </div>
  <?php else: ?>

    <!-- Header del reporte -->
    <div class="table-card" style="padding:1.5rem;margin-bottom:1.5rem">
      <div style="display:flex;align-items:flex-start;justify-content:space-between;flex-wrap:wrap;gap:1rem;margin-bottom:1.25rem">
        <div>
          <h1 style="font-size:1.4rem;font-weight:800">Reporte de Campaña</h1>
          <p style="color:var(--text-muted)"><?= htmlspecialchars($campana['nombre']) ?></p>
        </div>
        <span class="badge <?= $campana['estado'] === 'activa' ? 'badge-success' : 'badge-gray' ?>" style="font-size:.8rem;padding:.3rem .8rem">
          <?= ucfirst($campana['estado']) ?>
        </span>
      </div>

      <!-- KPIs del reporte -->
      <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:1rem;margin-bottom:1.25rem">
        <div style="text-align:center;padding:.9rem;background:#f0fdf4;border-radius:var(--radius);border:1px solid #bbf7d0">
          <div style="font-size:1.4rem;font-weight:800;color:var(--success)">Q <?= number_format($totalDonado,2) ?></div>
          <div style="font-size:.75rem;color:#166534">Total Recaudado</div>
        </div>
        <div style="text-align:center;padding:.9rem;background:#fff7ed;border-radius:var(--radius);border:1px solid #fed7aa">
          <div style="font-size:1.4rem;font-weight:800;color:#ea580c">Q <?= number_format($totalEgresos,2) ?></div>
          <div style="font-size:.75rem;color:#9a3412">Total Egresos</div>
        </div>
        <div style="text-align:center;padding:.9rem;background:#eff6ff;border-radius:var(--radius);border:1px solid #bfdbfe">
          <div style="font-size:1.4rem;font-weight:800;color:var(--accent)">Q <?= number_format($saldo,2) ?></div>
          <div style="font-size:.75rem;color:#1e40af">Saldo Disponible</div>
        </div>
        <div style="text-align:center;padding:.9rem;background:#faf5ff;border-radius:var(--radius);border:1px solid #e9d5ff">
          <div style="font-size:1.4rem;font-weight:800;color:#7c3aed"><?= $pct ?>%</div>
          <div style="font-size:.75rem;color:#6b21a8">Progreso de Meta</div>
        </div>
      </div>

      <!-- Barra de progreso -->
      <div>
        <div style="display:flex;justify-content:space-between;font-size:.85rem;margin-bottom:.4rem">
          <span>Progreso hacia la meta</span>
          <span style="color:var(--text-muted)">Meta: Q <?= number_format($campana['meta'],2) ?></span>
        </div>
        <div class="progress-bar-track" style="height:12px">
          <div class="progress-bar-fill" data-width="<?= $pct ?>" style="width:0;height:100%"></div>
        </div>
      </div>
    </div>

    <!-- Gráfico de barras (CSS) -->
    <?php if (!empty($donacionesMes)): ?>
      <div class="table-card" style="padding:1.5rem;margin-bottom:1.5rem">
        <h3 style="font-weight:700;margin-bottom:1.25rem">
          <i class="fas fa-chart-bar" style="color:var(--accent)"></i> Donaciones por Mes
        </h3>
        <div style="display:flex;align-items:flex-end;gap:.75rem;height:160px;padding-bottom:.5rem;border-bottom:1px solid var(--border)">
          <?php foreach ($donacionesMes as $mes):
            $barH = $maxMes > 0 ? round($mes['total'] / $maxMes * 140) : 0;
          ?>
            <div style="flex:1;display:flex;flex-direction:column;align-items:center;gap:.25rem">
              <span style="font-size:.7rem;color:var(--text-muted)">Q<?= number_format($mes['total'],0) ?></span>
              <div style="width:100%;height:<?= $barH ?>px;background:var(--accent);border-radius:4px 4px 0 0;transition:height .6s ease"></div>
            </div>
          <?php endforeach; ?>
        </div>
        <div style="display:flex;gap:.75rem;padding-top:.4rem">
          <?php foreach ($donacionesMes as $mes): ?>
            <div style="flex:1;text-align:center;font-size:.75rem;color:var(--text-muted)"><?= $mes['mes_label'] ?></div>
          <?php endforeach; ?>
        </div>
      </div>
    <?php endif; ?>

    <!-- Tablas lado a lado -->
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:1.5rem">

      <!-- Últimas donaciones -->
      <div class="table-card">
        <div class="table-header">
          <h3>Últimas Donaciones</h3>
          <span style="font-size:.8rem;color:var(--text-muted)"><?= $numDonantes ?> total</span>
        </div>
        <?php if (empty($donaciones)): ?>
          <p style="padding:1rem;text-align:center;color:var(--text-muted);font-size:.875rem">Sin donaciones</p>
        <?php else: ?>
          <div class="table-responsive">
            <table>
              <thead><tr><th>Fecha</th><th>Donante</th><th>Monto</th></tr></thead>
              <tbody>
                <?php foreach ($donaciones as $d): ?>
                  <tr>
                    <td style="font-size:.8rem"><?= date('d/m/Y', strtotime($d['fecha'])) ?></td>
                    <td style="font-size:.85rem"><?= htmlspecialchars($d['nombre']) ?></td>
                    <td><strong style="color:var(--success)">Q <?= number_format($d['monto'],2) ?></strong></td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        <?php endif; ?>
      </div>

      <!-- Egresos -->
      <div class="table-card">
        <div class="table-header">
          <h3>Egresos Registrados</h3>
          <?php if (isLoggedIn()): ?>
            <a href="<?= APP_URL ?>/modules/expenses/create.php?campana=<?= $campanaId ?>"
              style="font-size:.8rem;color:var(--accent);text-decoration:none">+ Agregar</a>
          <?php endif; ?>
        </div>
        <?php if (empty($egresos)): ?>
          <p style="padding:1rem;text-align:center;color:var(--text-muted);font-size:.875rem">Sin egresos</p>
        <?php else: ?>
          <div class="table-responsive">
            <table>
              <thead><tr><th>Fecha</th><th>Concepto</th><th>Monto</th></tr></thead>
              <tbody>
                <?php foreach ($egresos as $e): ?>
                  <tr>
                    <td style="font-size:.8rem"><?= date('d/m/Y', strtotime($e['fecha'])) ?></td>
                    <td style="font-size:.85rem"><?= htmlspecialchars($e['concepto']) ?></td>
                    <td><strong style="color:var(--danger)">Q <?= number_format($e['monto'],2) ?></strong></td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        <?php endif; ?>
      </div>

    </div>
  <?php endif; ?>
</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
