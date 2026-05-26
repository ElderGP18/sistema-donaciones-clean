<?php
require_once __DIR__ . '/../../config/app.php';
require_once __DIR__ . '/../../config/database.php';
requireLogin();

$pageTitle  = 'Reporte de Campaña';
$activePage = 'reportes';

$conn = getConnection();

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

        $st = $conn->prepare("SELECT d.fecha, d.monto, dn.nombre FROM donaciones d JOIN donantes dn ON dn.id_donante=d.id_donante WHERE d.id_campana=? ORDER BY d.fecha DESC LIMIT 10");
        $st->bind_param('i', $campanaId); $st->execute();
        $res2 = $st->get_result();
        while ($r2 = $res2->fetch_assoc()) $donaciones[] = $r2; $st->close();

        $st = $conn->prepare("SELECT fecha, concepto, monto FROM egresos WHERE id_campana=? ORDER BY fecha DESC LIMIT 10");
        $st->bind_param('i', $campanaId); $st->execute();
        $res2 = $st->get_result();
        while ($r2 = $res2->fetch_assoc()) $egresos[] = $r2; $st->close();

        $st = $conn->prepare("SELECT DATE_FORMAT(fecha,'%b') mes_label, DATE_FORMAT(fecha,'%Y-%m') mes_key, SUM(monto) total FROM donaciones WHERE id_campana=? GROUP BY mes_key, mes_label ORDER BY mes_key DESC LIMIT 6");
        $st->bind_param('i', $campanaId); $st->execute();
        $res2 = $st->get_result();
        $tmp = [];
        while ($r2 = $res2->fetch_assoc()) $tmp[] = $r2;
        $donacionesMes = array_reverse($tmp); $st->close();
    }
}
$conn->close();
$pct    = ($campana && $campana['meta'] > 0) ? min(100, round($totalDonado / $campana['meta'] * 100)) : 0;
$maxMes = !empty($donacionesMes) ? max(array_column($donacionesMes, 'total')) : 1;

include __DIR__ . '/../../includes/header.php';
include __DIR__ . '/../../includes/navbar.php';
?>

<div class="main-content">

  <!-- Selector -->
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
        <button type="button" onclick="exportarPDF()" class="btn btn-primary btn-sm" id="btn-pdf">
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

    <!-- Vista web normal -->
    <div id="reporte-web">

      <div class="table-card" style="padding:1.5rem;margin-bottom:1.5rem">
        <div style="display:flex;align-items:flex-start;justify-content:space-between;flex-wrap:wrap;gap:1rem;margin-bottom:1.25rem">
          <div>
            <h1 style="font-size:1.4rem;font-weight:800">Reporte de Campaña</h1>
            <p style="color:var(--text-muted)"><?= htmlspecialchars($campana['nombre']) ?></p>
          </div>
          <span class="badge <?= $campana['estado'] === 'activa' ? 'badge-success' : 'badge-gray' ?>"><?= ucfirst($campana['estado']) ?></span>
        </div>
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
            <div style="font-size:.75rem;color:#6b21a8">Progreso Meta</div>
          </div>
        </div>
        <div style="display:flex;justify-content:space-between;font-size:.85rem;margin-bottom:.4rem">
          <span>Progreso hacia la meta</span>
          <span style="color:var(--text-muted)">Meta: Q <?= number_format($campana['meta'],2) ?></span>
        </div>
        <div class="progress-bar-track" style="height:12px">
          <div class="progress-bar-fill" data-width="<?= $pct ?>" style="width:0;height:100%"></div>
        </div>
      </div>

      <?php if (!empty($donacionesMes)): ?>
        <div class="table-card" style="padding:1.5rem;margin-bottom:1.5rem">
          <h3 style="font-weight:700;margin-bottom:1.25rem"><i class="fas fa-chart-bar" style="color:var(--accent)"></i> Donaciones por Mes</h3>
          <div style="display:flex;align-items:flex-end;gap:.75rem;height:160px;padding-bottom:.5rem;border-bottom:1px solid var(--border)">
            <?php foreach ($donacionesMes as $mes):
              $barH = $maxMes > 0 ? round($mes['total'] / $maxMes * 140) : 0; ?>
              <div style="flex:1;display:flex;flex-direction:column;align-items:center;gap:.25rem">
                <span style="font-size:.7rem;color:var(--text-muted)">Q<?= number_format($mes['total'],0) ?></span>
                <div style="width:100%;height:<?= $barH ?>px;background:var(--accent);border-radius:4px 4px 0 0"></div>
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

      <div style="display:grid;grid-template-columns:1fr 1fr;gap:1.5rem">
        <div class="table-card">
          <div class="table-header"><h3>Últimas Donaciones</h3><span style="font-size:.8rem;color:var(--text-muted)"><?= $numDonantes ?> total</span></div>
          <?php if (empty($donaciones)): ?>
            <p style="padding:1rem;text-align:center;color:var(--text-muted);font-size:.875rem">Sin donaciones</p>
          <?php else: ?>
            <div class="table-responsive"><table>
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
            </table></div>
          <?php endif; ?>
        </div>
        <div class="table-card">
          <div class="table-header">
            <h3>Egresos Registrados</h3>
            <?php if (isLoggedIn()): ?>
              <a href="<?= APP_URL ?>/modules/expenses/create.php?campana=<?= $campanaId ?>" style="font-size:.8rem;color:var(--accent);text-decoration:none">+ Agregar</a>
            <?php endif; ?>
          </div>
          <?php if (empty($egresos)): ?>
            <p style="padding:1rem;text-align:center;color:var(--text-muted);font-size:.875rem">Sin egresos</p>
          <?php else: ?>
            <div class="table-responsive"><table>
              <thead><tr><th>Fecha</th><th>Concepto</th><th>Monto</th></tr></thead>
              <tbody>
                <?php foreach ($egresos as $e): ?>
                  <tr>
                    <td style="font-size:.8rem"><?= date('d/m/Y', strtotime($e['fecha'])) ?></td>
                    <td style="font-size:.85rem"><?= htmlspecialchars($e['concepto']) ?></td>
                    <td><strong style="color:#dc2626">Q <?= number_format($e['monto'],2) ?></strong></td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table></div>
          <?php endif; ?>
        </div>
      </div>
    </div><!-- /reporte-web -->

    <!-- ═══════════════════════════════════════════════════════
         PLANTILLA PDF — oculta en pantalla, usada por html2pdf
         ═══════════════════════════════════════════════════════ -->
    <div id="pdf-area" style="display:none">
      <div style="font-family:Arial,sans-serif;color:#1e293b;padding:0;max-width:780px;margin:0 auto">

        <!-- Encabezado -->
        <div style="background:linear-gradient(135deg,#1e3a5f,#2563eb);color:#fff;padding:28px 32px;border-radius:8px 8px 0 0;margin-bottom:0">
          <div style="display:flex;justify-content:space-between;align-items:flex-start">
            <div>
              <div style="font-size:22px;font-weight:900;letter-spacing:-.5px">&#9829; DonaTu</div>
              <div style="font-size:11px;opacity:.8;margin-top:2px">Plataforma de Gestión de Donaciones</div>
            </div>
            <div style="text-align:right">
              <div style="font-size:16px;font-weight:700">REPORTE DE CAMPAÑA</div>
              <div style="font-size:10px;opacity:.8;margin-top:3px">Generado el <?= date('d/m/Y \a\l\a\s H:i') ?></div>
            </div>
          </div>
        </div>

        <!-- Info campaña -->
        <div style="background:#f8fafc;border:1px solid #e2e8f0;border-top:none;padding:18px 32px;margin-bottom:20px;border-radius:0 0 8px 8px">
          <div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:8px">
            <div>
              <div style="font-size:18px;font-weight:800"><?= htmlspecialchars($campana['nombre']) ?></div>
              <?php if ($campana['descripcion']): ?>
                <div style="font-size:11px;color:#64748b;margin-top:4px"><?= htmlspecialchars($campana['descripcion']) ?></div>
              <?php endif; ?>
              <div style="font-size:11px;color:#64748b;margin-top:4px">
                Inicio: <?= date('d/m/Y', strtotime($campana['fecha_inicio'])) ?>
                <?= $campana['fecha_fin'] ? ' &nbsp;|&nbsp; Fin: ' . date('d/m/Y', strtotime($campana['fecha_fin'])) : '' ?>
              </div>
            </div>
            <div style="background:<?= $campana['estado'] === 'activa' ? '#dcfce7' : '#f1f5f9' ?>;color:<?= $campana['estado'] === 'activa' ? '#166534' : '#475569' ?>;padding:4px 14px;border-radius:999px;font-size:11px;font-weight:700;text-transform:uppercase">
              <?= ucfirst($campana['estado']) ?>
            </div>
          </div>
        </div>

        <!-- KPIs -->
        <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:12px;margin-bottom:20px">
          <div style="background:#f0fdf4;border:1px solid #bbf7d0;border-radius:8px;padding:14px;text-align:center">
            <div style="font-size:18px;font-weight:800;color:#16a34a">Q <?= number_format($totalDonado,2) ?></div>
            <div style="font-size:10px;color:#166534;margin-top:3px;font-weight:600">TOTAL RECAUDADO</div>
          </div>
          <div style="background:#fff7ed;border:1px solid #fed7aa;border-radius:8px;padding:14px;text-align:center">
            <div style="font-size:18px;font-weight:800;color:#ea580c">Q <?= number_format($totalEgresos,2) ?></div>
            <div style="font-size:10px;color:#9a3412;margin-top:3px;font-weight:600">TOTAL EGRESOS</div>
          </div>
          <div style="background:#eff6ff;border:1px solid #bfdbfe;border-radius:8px;padding:14px;text-align:center">
            <div style="font-size:18px;font-weight:800;color:#2563eb">Q <?= number_format($saldo,2) ?></div>
            <div style="font-size:10px;color:#1e40af;margin-top:3px;font-weight:600">SALDO DISPONIBLE</div>
          </div>
          <div style="background:#faf5ff;border:1px solid #e9d5ff;border-radius:8px;padding:14px;text-align:center">
            <div style="font-size:18px;font-weight:800;color:#7c3aed"><?= $pct ?>%</div>
            <div style="font-size:10px;color:#6b21a8;margin-top:3px;font-weight:600">PROGRESO META</div>
          </div>
        </div>

        <!-- Barra progreso -->
        <div style="background:#fff;border:1px solid #e2e8f0;border-radius:8px;padding:16px 20px;margin-bottom:20px">
          <div style="display:flex;justify-content:space-between;font-size:11px;color:#64748b;margin-bottom:8px">
            <span><strong style="color:#1e293b">Q <?= number_format($totalDonado,2) ?></strong> recaudados</span>
            <span>Meta: <strong style="color:#1e293b">Q <?= number_format($campana['meta'],2) ?></strong></span>
          </div>
          <div style="background:#e2e8f0;border-radius:999px;height:14px;overflow:hidden">
            <div style="background:linear-gradient(90deg,#2563eb,#1e40af);width:<?= $pct ?>%;height:100%;border-radius:999px"></div>
          </div>
          <div style="font-size:10px;color:#64748b;margin-top:5px;text-align:right"><?= $pct ?>% completado · <?= $numDonantes ?> donaciones registradas</div>
        </div>

        <?php if (!empty($donacionesMes)): ?>
        <!-- Tabla donaciones por mes -->
        <div style="background:#fff;border:1px solid #e2e8f0;border-radius:8px;padding:16px 20px;margin-bottom:20px">
          <div style="font-size:13px;font-weight:700;color:#1e293b;margin-bottom:12px;border-bottom:2px solid #2563eb;padding-bottom:6px">
            DONACIONES POR MES
          </div>
          <table style="width:100%;border-collapse:collapse;font-size:11px">
            <thead>
              <tr style="background:#f8fafc">
                <th style="text-align:left;padding:7px 10px;border-bottom:1px solid #e2e8f0;color:#64748b">MES</th>
                <th style="text-align:right;padding:7px 10px;border-bottom:1px solid #e2e8f0;color:#64748b">TOTAL DONADO</th>
                <th style="text-align:left;padding:7px 10px;border-bottom:1px solid #e2e8f0;color:#64748b">GRÁFICO</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($donacionesMes as $i => $mes):
                $barW = $maxMes > 0 ? round($mes['total'] / $maxMes * 180) : 0;
              ?>
                <tr style="background:<?= $i % 2 === 0 ? '#fff' : '#f8fafc' ?>">
                  <td style="padding:7px 10px;border-bottom:1px solid #f1f5f9;font-weight:600"><?= $mes['mes_label'] ?></td>
                  <td style="padding:7px 10px;border-bottom:1px solid #f1f5f9;text-align:right;color:#16a34a;font-weight:700">Q <?= number_format($mes['total'],2) ?></td>
                  <td style="padding:7px 10px;border-bottom:1px solid #f1f5f9">
                    <div style="background:#2563eb;height:10px;width:<?= $barW ?>px;border-radius:3px"></div>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
        <?php endif; ?>

        <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:20px">

          <!-- Donaciones -->
          <div style="background:#fff;border:1px solid #e2e8f0;border-radius:8px;padding:16px 20px">
            <div style="font-size:13px;font-weight:700;color:#1e293b;margin-bottom:12px;border-bottom:2px solid #16a34a;padding-bottom:6px">
              DONACIONES <span style="font-size:10px;font-weight:400;color:#64748b">(últimas <?= count($donaciones) ?>)</span>
            </div>
            <?php if (empty($donaciones)): ?>
              <p style="font-size:11px;color:#94a3b8;text-align:center;padding:12px 0">Sin donaciones registradas</p>
            <?php else: ?>
              <table style="width:100%;border-collapse:collapse;font-size:10px">
                <thead>
                  <tr style="background:#f0fdf4">
                    <th style="text-align:left;padding:5px 6px;color:#166534">FECHA</th>
                    <th style="text-align:left;padding:5px 6px;color:#166534">DONANTE</th>
                    <th style="text-align:right;padding:5px 6px;color:#166534">MONTO</th>
                  </tr>
                </thead>
                <tbody>
                  <?php foreach ($donaciones as $i => $d): ?>
                    <tr style="background:<?= $i % 2 === 0 ? '#fff' : '#f8fafc' ?>">
                      <td style="padding:5px 6px;border-bottom:1px solid #f1f5f9"><?= date('d/m/Y', strtotime($d['fecha'])) ?></td>
                      <td style="padding:5px 6px;border-bottom:1px solid #f1f5f9"><?= htmlspecialchars($d['nombre']) ?></td>
                      <td style="padding:5px 6px;border-bottom:1px solid #f1f5f9;text-align:right;font-weight:700;color:#16a34a">Q <?= number_format($d['monto'],2) ?></td>
                    </tr>
                  <?php endforeach; ?>
                </tbody>
              </table>
            <?php endif; ?>
          </div>

          <!-- Egresos -->
          <div style="background:#fff;border:1px solid #e2e8f0;border-radius:8px;padding:16px 20px">
            <div style="font-size:13px;font-weight:700;color:#1e293b;margin-bottom:12px;border-bottom:2px solid #dc2626;padding-bottom:6px">
              EGRESOS <span style="font-size:10px;font-weight:400;color:#64748b">(últimos <?= count($egresos) ?>)</span>
            </div>
            <?php if (empty($egresos)): ?>
              <p style="font-size:11px;color:#94a3b8;text-align:center;padding:12px 0">Sin egresos registrados</p>
            <?php else: ?>
              <table style="width:100%;border-collapse:collapse;font-size:10px">
                <thead>
                  <tr style="background:#fff7ed">
                    <th style="text-align:left;padding:5px 6px;color:#9a3412">FECHA</th>
                    <th style="text-align:left;padding:5px 6px;color:#9a3412">CONCEPTO</th>
                    <th style="text-align:right;padding:5px 6px;color:#9a3412">MONTO</th>
                  </tr>
                </thead>
                <tbody>
                  <?php foreach ($egresos as $i => $e): ?>
                    <tr style="background:<?= $i % 2 === 0 ? '#fff' : '#f8fafc' ?>">
                      <td style="padding:5px 6px;border-bottom:1px solid #f1f5f9"><?= date('d/m/Y', strtotime($e['fecha'])) ?></td>
                      <td style="padding:5px 6px;border-bottom:1px solid #f1f5f9"><?= htmlspecialchars($e['concepto']) ?></td>
                      <td style="padding:5px 6px;border-bottom:1px solid #f1f5f9;text-align:right;font-weight:700;color:#dc2626">Q <?= number_format($e['monto'],2) ?></td>
                    </tr>
                  <?php endforeach; ?>
                </tbody>
              </table>
            <?php endif; ?>
          </div>

        </div>

        <!-- Pie de página -->
        <div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:8px;padding:12px 20px;display:flex;justify-content:space-between;align-items:center;font-size:10px;color:#94a3b8">
          <span>&#9829; DonaTu — Plataforma de Gestión de Donaciones</span>
          <span>Universidad Mariano Gálvez de Guatemala · Elder García Pacheco</span>
          <span><?= date('d/m/Y H:i') ?></span>
        </div>

      </div>
    </div><!-- /pdf-area -->

  <?php endif; ?>
</div>

<!-- html2pdf.js -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
<script>
function exportarPDF() {
  const btn = document.getElementById('btn-pdf');
  btn.disabled = true;
  btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Generando...';

  const el = document.getElementById('pdf-area');
  el.style.display = 'block';

  const nombre = '<?= addslashes(htmlspecialchars($campana['nombre'] ?? 'reporte')) ?>';

  html2pdf().set({
    margin:       [10, 10, 10, 10],
    filename:     'reporte-' + nombre.replace(/[^a-z0-9]/gi, '_').toLowerCase() + '-<?= date('Ymd') ?>.pdf',
    image:        { type: 'jpeg', quality: 0.98 },
    html2canvas:  { scale: 2, useCORS: true, logging: false },
    jsPDF:        { unit: 'mm', format: 'a4', orientation: 'portrait' },
    pagebreak:    { mode: ['avoid-all', 'css'] }
  }).from(el).save().then(() => {
    el.style.display = 'none';
    btn.disabled = false;
    btn.innerHTML = '<i class="fas fa-file-pdf"></i> Exportar PDF';
  });
}
</script>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
