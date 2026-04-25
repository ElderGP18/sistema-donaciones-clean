<?php
require_once __DIR__ . '/../../config/app.php';
require_once __DIR__ . '/../../config/database.php';
requireLogin();

$pageTitle  = 'Registrar Egreso';
$activePage = 'reportes';
$errors = [];

$conn = getConnection();
$campanas = [];
$res = $conn->query("SELECT id_campana, nombre FROM campanas WHERE estado != 'finalizada' ORDER BY nombre");
if ($res) while ($r = $res->fetch_assoc()) $campanas[] = $r;

$campanaId = intval($_GET['campana'] ?? 0);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_campana = intval($_POST['id_campana'] ?? 0);
    $concepto   = sanitize($_POST['concepto']   ?? '');
    $monto      = floatval($_POST['monto']       ?? 0);
    $fecha      = $_POST['fecha'] ?? date('Y-m-d');

    if (!$id_campana)  $errors[] = 'Selecciona una campaña.';
    if (empty($concepto)) $errors[] = 'El concepto es obligatorio.';
    if ($monto <= 0)   $errors[] = 'El monto debe ser mayor a 0.';

    if (empty($errors)) {
        $s = $conn->prepare("INSERT INTO egresos (fecha, concepto, monto, id_campana, id_usuario) VALUES (?,?,?,?,?)");
        $s->bind_param('ssdii', $fecha, $concepto, $monto, $id_campana, $_SESSION['user_id']);
        if ($s->execute()) {
            $s->close(); $conn->close();
            redirect("modules/reports/index.php?campana=$id_campana&egreso=1");
        } else {
            $errors[] = 'Error: ' . $conn->error;
            $s->close();
        }
    }
}
$conn->close();

include __DIR__ . '/../../includes/header.php';
include __DIR__ . '/../../includes/navbar.php';
?>

<div class="main-content" style="max-width:600px">
  <nav style="font-size:.85rem;color:var(--text-muted);margin-bottom:1.25rem">
    <a href="<?= APP_URL ?>/modules/reports/index.php" style="color:var(--accent);text-decoration:none">Reportes</a>
    &rsaquo; Registrar Egreso
  </nav>

  <div class="table-card" style="padding:2rem">
    <h2 style="font-size:1.3rem;font-weight:700;margin-bottom:1.5rem">
      <i class="fas fa-minus-circle" style="color:var(--danger)"></i> Registrar Egreso
    </h2>

    <?php foreach ($errors as $e): ?>
      <div class="alert alert-danger"><i class="fas fa-exclamation-circle"></i> <?= $e ?></div>
    <?php endforeach; ?>

    <form method="POST" action="">
      <label class="form-label">Campaña <span style="color:var(--danger)">*</span></label>
      <div class="input-group" style="margin-bottom:1.1rem">
        <i class="fas fa-bullhorn input-icon"></i>
        <select name="id_campana" class="form-control" style="padding-left:2.25rem" required>
          <option value="">Seleccionar campaña ▾</option>
          <?php foreach ($campanas as $c): ?>
            <option value="<?= $c['id_campana'] ?>" <?= ($campanaId == $c['id_campana'] || ($_POST['id_campana'] ?? 0) == $c['id_campana']) ? 'selected' : '' ?>>
              <?= htmlspecialchars($c['nombre']) ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>

      <label class="form-label">Concepto <span style="color:var(--danger)">*</span></label>
      <div class="input-group" style="margin-bottom:1.1rem">
        <i class="fas fa-file-alt input-icon"></i>
        <input type="text" name="concepto" class="form-control"
          placeholder="Ej. Compra de materiales"
          value="<?= htmlspecialchars($_POST['concepto'] ?? '') ?>" required>
      </div>

      <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem">
        <div>
          <label class="form-label">Monto (Q) <span style="color:var(--danger)">*</span></label>
          <div class="input-group">
            <i class="fas fa-dollar-sign input-icon"></i>
            <input type="number" name="monto" step="0.01" min="0.01" class="form-control"
              placeholder="0.00" data-currency value="<?= htmlspecialchars($_POST['monto'] ?? '') ?>" required>
          </div>
        </div>
        <div>
          <label class="form-label">Fecha <span style="color:var(--danger)">*</span></label>
          <div class="input-group">
            <i class="fas fa-calendar input-icon"></i>
            <input type="date" name="fecha" class="form-control"
              value="<?= htmlspecialchars($_POST['fecha'] ?? date('Y-m-d')) ?>" required>
          </div>
        </div>
      </div>

      <div style="display:flex;gap:.75rem;margin-top:1.75rem;justify-content:flex-end">
        <a href="<?= APP_URL ?>/modules/reports/index.php" class="btn" style="background:var(--bg-light);border:1.5px solid var(--border);color:var(--text-dark);border-radius:var(--radius);font-weight:600;padding:.65rem 1.25rem;text-decoration:none">
          Cancelar
        </a>
        <button type="submit" class="btn btn-primary" style="width:auto;padding:.65rem 2rem">
          <i class="fas fa-save"></i> Guardar Egreso
        </button>
      </div>
    </form>
  </div>
</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
