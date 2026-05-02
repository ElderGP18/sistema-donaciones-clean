<?php
require_once __DIR__ . '/../../config/app.php';
require_once __DIR__ . '/../../config/database.php';
requireAdmin();

$id = intval($_GET['id'] ?? 0);
if (!$id) redirect('modules/campaigns/list.php');

$conn = getConnection();
$stmt = $conn->prepare("SELECT * FROM campanas WHERE id_campana = ?");
$stmt->bind_param('i', $id); $stmt->execute();
$campana = $stmt->get_result()->fetch_assoc(); $stmt->close();
if (!$campana) { $conn->close(); redirect('modules/campaigns/list.php'); }

$pageTitle  = 'Editar Campaña';
$activePage = 'campanas';
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre      = sanitize($_POST['nombre']      ?? '');
    $descripcion = sanitize($_POST['descripcion'] ?? '');
    $meta        = floatval($_POST['meta']        ?? 0);
    $fecha_inicio = $_POST['fecha_inicio'] ?? '';
    $fecha_fin    = $_POST['fecha_fin']    ?? '';
    $estado       = in_array($_POST['estado'] ?? '', ['activa','pausada','finalizada']) ? $_POST['estado'] : 'activa';

    if (empty($nombre))       $errors[] = 'El nombre es obligatorio.';
    if ($meta <= 0)           $errors[] = 'La meta debe ser mayor a 0.';
    if (empty($fecha_inicio)) $errors[] = 'La fecha de inicio es obligatoria.';

    if (empty($errors)) {
        $fecha_fin_val = !empty($fecha_fin) ? $fecha_fin : null;
        $stmt = $conn->prepare("UPDATE campanas SET nombre=?, descripcion=?, meta=?, estado=?, fecha_inicio=?, fecha_fin=? WHERE id_campana=?");
        $stmt->bind_param('ssdsssi', $nombre, $descripcion, $meta, $estado, $fecha_inicio, $fecha_fin_val, $id);
        if ($stmt->execute()) {
            $stmt->close(); $conn->close();
            redirect("modules/campaigns/view.php?id=$id&updated=1");
        } else {
            $errors[] = 'Error al actualizar: ' . $conn->error;
            $stmt->close();
        }
    }
}
$conn->close();

include __DIR__ . '/../../includes/header.php';
include __DIR__ . '/../../includes/navbar.php';
?>

<div class="main-content" style="max-width:700px">
  <nav style="font-size:.85rem;color:var(--text-muted);margin-bottom:1.25rem">
    <a href="<?= APP_URL ?>/modules/campaigns/list.php" style="color:var(--accent);text-decoration:none">Campañas</a>
    &rsaquo; <a href="<?= APP_URL ?>/modules/campaigns/view.php?id=<?= $id ?>" style="color:var(--accent);text-decoration:none"><?= htmlspecialchars($campana['nombre']) ?></a>
    &rsaquo; Editar
  </nav>

  <div class="table-card" style="padding:2rem">
    <h2 style="font-size:1.3rem;font-weight:700;margin-bottom:1.5rem">
      <i class="fas fa-edit" style="color:var(--accent)"></i> Editar Campaña
    </h2>

    <?php foreach ($errors as $e): ?>
      <div class="alert alert-danger"><i class="fas fa-exclamation-circle"></i> <?= $e ?></div>
    <?php endforeach; ?>

    <form method="POST">
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:1.1rem">

        <div style="grid-column:1/-1">
          <label class="form-label">Nombre de la Campaña <span style="color:var(--danger)">*</span></label>
          <div class="input-group">
            <i class="fas fa-tag input-icon"></i>
            <input type="text" name="nombre" class="form-control"
              value="<?= htmlspecialchars($_POST['nombre'] ?? $campana['nombre']) ?>" required>
          </div>
        </div>

        <div>
          <label class="form-label">Meta de Recaudación (Q) <span style="color:var(--danger)">*</span></label>
          <div class="input-group">
            <i class="fas fa-dollar-sign input-icon"></i>
            <input type="number" name="meta" class="form-control" step="0.01" min="1"
              value="<?= htmlspecialchars($_POST['meta'] ?? $campana['meta']) ?>" required>
          </div>
        </div>

        <div>
          <label class="form-label">Estado</label>
          <div class="input-group">
            <i class="fas fa-toggle-on input-icon"></i>
            <select name="estado" class="form-control" style="padding-left:2.25rem">
              <?php foreach (['activa','pausada','finalizada'] as $opt): ?>
                <option value="<?= $opt ?>" <?= ($campana['estado'] === $opt) ? 'selected' : '' ?>>
                  <?= ucfirst($opt) ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>
        </div>

        <div>
          <label class="form-label">Fecha de Inicio <span style="color:var(--danger)">*</span></label>
          <div class="input-group">
            <i class="fas fa-calendar input-icon"></i>
            <input type="date" name="fecha_inicio" class="form-control"
              value="<?= htmlspecialchars($_POST['fecha_inicio'] ?? $campana['fecha_inicio']) ?>" required>
          </div>
        </div>

        <div>
          <label class="form-label">Fecha de Finalización</label>
          <div class="input-group">
            <i class="fas fa-calendar-check input-icon"></i>
            <input type="date" name="fecha_fin" class="form-control"
              value="<?= htmlspecialchars($_POST['fecha_fin'] ?? $campana['fecha_fin']) ?>">
          </div>
        </div>

        <div style="grid-column:1/-1">
          <label class="form-label">Descripción</label>
          <textarea name="descripcion" class="form-control" rows="4"
            style="padding:.65rem .875rem;resize:vertical"><?= htmlspecialchars($_POST['descripcion'] ?? $campana['descripcion']) ?></textarea>
        </div>

      </div>

      <div style="display:flex;gap:.75rem;margin-top:1.75rem;justify-content:flex-end">
        <a href="<?= APP_URL ?>/modules/campaigns/view.php?id=<?= $id ?>" class="btn" style="background:var(--bg-light);border:1.5px solid var(--border);color:var(--text-dark);border-radius:var(--radius);font-weight:600;padding:.65rem 1.25rem;text-decoration:none">
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
