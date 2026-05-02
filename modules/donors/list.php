<?php
require_once __DIR__ . '/../../config/app.php';
require_once __DIR__ . '/../../config/database.php';
requireLogin();

$pageTitle  = 'Donantes';
$activePage = 'donantes';

$conn = getConnection();
$donantes = [];
$res = $conn->query("
    SELECT d.*,
           COUNT(dn.id_donacion) AS total_donaciones,
           COALESCE(SUM(dn.monto), 0) AS total_donado
    FROM donantes d
    LEFT JOIN donaciones dn ON dn.id_donante = d.id_donante
    GROUP BY d.id_donante
    ORDER BY d.nombre ASC
");
if ($res) while ($r = $res->fetch_assoc()) $donantes[] = $r;
$conn->close();

$created = isset($_GET['created']);
$deleted = isset($_GET['deleted']);
include __DIR__ . '/../../includes/header.php';
include __DIR__ . '/../../includes/navbar.php';
?>

<div class="main-content">

  <?php if ($created): ?>
    <div class="alert alert-success" data-auto-hide>
      <i class="fas fa-check-circle"></i> Donante registrado exitosamente.
    </div>
  <?php endif; ?>
  <?php if ($deleted): ?>
    <div class="alert alert-success" data-auto-hide>
      <i class="fas fa-check-circle"></i> Donante eliminado.
    </div>
  <?php endif; ?>

  <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:1.5rem;flex-wrap:wrap;gap:.75rem">
    <div>
      <h1 class="section-title">Donantes</h1>
      <p class="section-sub">Registro de todas las personas que han apoyado las campañas</p>
    </div>
    <a href="<?= APP_URL ?>/modules/donors/create.php" class="btn btn-primary">
      <i class="fas fa-plus"></i> Nuevo Donante
    </a>
  </div>

  <!-- Buscador -->
  <div class="table-card" style="padding:1rem;margin-bottom:1rem">
    <div class="input-group" style="margin:0">
      <i class="fas fa-search input-icon"></i>
      <input type="text" id="searchInput" class="form-control"
        placeholder="Buscar por nombre o correo..."
        oninput="filterTable(this.value)">
    </div>
  </div>

  <div class="table-card">
    <?php if (empty($donantes)): ?>
      <div style="padding:3rem;text-align:center;color:var(--text-muted)">
        <i class="fas fa-users" style="font-size:3rem;opacity:.3;display:block;margin-bottom:1rem"></i>
        <p>No hay donantes registrados.</p>
        <a href="<?= APP_URL ?>/modules/donors/create.php" class="btn btn-primary" style="margin-top:1rem;display:inline-flex">
          <i class="fas fa-plus"></i> Registrar primer donante
        </a>
      </div>
    <?php else: ?>
      <div class="table-responsive">
        <table id="donantesTable">
          <thead>
            <tr>
              <th>#</th>
              <th>Nombre</th>
              <th>Correo</th>
              <th>Teléfono</th>
              <th>Donaciones</th>
              <th>Total Donado</th>
              <th>Registrado</th>
              <th>Acciones</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($donantes as $i => $d): ?>
              <tr>
                <td style="color:var(--text-muted);font-size:.8rem"><?= $i + 1 ?></td>
                <td>
                  <div style="display:flex;align-items:center;gap:.6rem">
                    <div style="width:34px;height:34px;border-radius:50%;background:linear-gradient(135deg,var(--accent),#7c3aed);display:flex;align-items:center;justify-content:center;color:#fff;font-weight:700;font-size:.85rem;flex-shrink:0">
                      <?= strtoupper(substr($d['nombre'], 0, 1)) ?>
                    </div>
                    <strong><?= htmlspecialchars($d['nombre']) ?></strong>
                  </div>
                </td>
                <td><?= htmlspecialchars($d['correo'] ?? '—') ?></td>
                <td><?= htmlspecialchars($d['telefono'] ?? '—') ?></td>
                <td>
                  <span class="badge badge-info"><?= $d['total_donaciones'] ?></span>
                </td>
                <td><strong style="color:var(--success)">Q <?= number_format($d['total_donado'], 2) ?></strong></td>
                <td style="font-size:.8rem;color:var(--text-muted)"><?= date('d/m/Y', strtotime($d['created_at'])) ?></td>
                <td>
                  <div style="display:flex;gap:.4rem">
                    <a href="<?= APP_URL ?>/modules/donors/view.php?id=<?= $d['id_donante'] ?>"
                      style="color:var(--accent);font-size:.8rem;text-decoration:none;padding:.3rem .6rem;background:#eff6ff;border-radius:4px">
                      <i class="fas fa-eye"></i>
                    </a>
                    <a href="<?= APP_URL ?>/modules/donors/edit.php?id=<?= $d['id_donante'] ?>"
                      style="color:#7c3aed;font-size:.8rem;text-decoration:none;padding:.3rem .6rem;background:#faf5ff;border-radius:4px">
                      <i class="fas fa-edit"></i>
                    </a>
                    <?php if ($_SESSION['user_rol'] === 'admin'): ?>
                      <a href="<?= APP_URL ?>/modules/donors/delete.php?id=<?= $d['id_donante'] ?>"
                        data-confirm="¿Eliminar este donante? Se eliminarán también sus donaciones."
                        style="color:var(--danger);font-size:.8rem;text-decoration:none;padding:.3rem .6rem;background:#fee2e2;border-radius:4px">
                        <i class="fas fa-trash"></i>
                      </a>
                    <?php endif; ?>
                  </div>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php endif; ?>
  </div>
</div>

<script>
function filterTable(q) {
  q = q.toLowerCase();
  document.querySelectorAll('#donantesTable tbody tr').forEach(row => {
    const text = row.innerText.toLowerCase();
    row.style.display = text.includes(q) ? '' : 'none';
  });
}
</script>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
