<?php
require_once __DIR__ . '/../../config/app.php';
require_once __DIR__ . '/../../config/database.php';
requireAdmin();

$pageTitle  = 'Nueva Campaña';
$activePage = 'campanas';
$errors = []; $success = false;

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
        $conn = getConnection();
        $stmt = $conn->prepare("INSERT INTO campanas (nombre, descripcion, meta, estado, fecha_inicio, fecha_fin, id_usuario) VALUES (?,?,?,?,?,?,?)");
        $fecha_fin_val = !empty($fecha_fin) ? $fecha_fin : null;
        $stmt->bind_param('ssdsssi', $nombre, $descripcion, $meta, $estado, $fecha_inicio, $fecha_fin_val, $_SESSION['user_id']);
        if ($stmt->execute()) {
            $id = $conn->insert_id;
            $stmt->close(); $conn->close();
            redirect("modules/campaigns/view.php?id=$id&created=1");
        } else {
            $errors[] = 'Error al guardar la campaña: ' . $conn->error;
            $stmt->close(); $conn->close();
        }
    }
}

include __DIR__ . '/../../includes/header.php';
include __DIR__ . '/../../includes/navbar.php';
?>

<div class="main-content" style="max-width:700px">

  <!-- Breadcrumb -->
  <nav style="font-size:.85rem;color:var(--text-muted);margin-bottom:1.25rem">
    <a href="<?= APP_URL ?>/modules/campaigns/list.php" style="color:var(--accent);text-decoration:none">Campañas</a>
    &rsaquo; Nuevo Registro
  </nav>

  <div class="table-card" style="padding:2rem">
    <h2 style="font-size:1.3rem;font-weight:700;margin-bottom:1.5rem">
      <i class="fas fa-bullhorn" style="color:var(--accent)"></i> Registrar Nueva Campaña
    </h2>

    <?php foreach ($errors as $e): ?>
      <div class="alert alert-danger"><i class="fas fa-exclamation-circle"></i> <?= $e ?></div>
    <?php endforeach; ?>

    <!-- Stepper visual (informativo) -->
    <div style="display:flex;gap:0;margin-bottom:2rem;border-radius:var(--radius);overflow:hidden;border:1px solid var(--border)">
      <?php $steps = ['Datos Básicos','Objetivos','Documentos','Revisión']; ?>
      <?php foreach ($steps as $i => $s): ?>
        <div style="flex:1;padding:.6rem;text-align:center;font-size:.75rem;font-weight:600;
          background:<?= $i===0 ? 'var(--accent)' : 'var(--bg-light)' ?>;
          color:<?= $i===0 ? '#fff' : 'var(--text-muted)' ?>;
          border-right:<?= $i<count($steps)-1 ? '1px solid var(--border)' : 'none' ?>">
          <span style="display:inline-block;width:18px;height:18px;border-radius:50%;
            background:<?= $i===0 ? 'rgba(255,255,255,.3)' : 'var(--border)' ?>;
            line-height:18px;font-size:.7rem;margin-right:.3rem"><?= $i+1 ?></span>
          <?= $s ?>
        </div>
      <?php endforeach; ?>
    </div>

    <form method="POST" action="">
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:1.1rem">

        <div style="grid-column:1/-1">
          <label class="form-label">Nombre de la Campaña <span style="color:var(--danger)">*</span></label>
          <div class="input-group">
            <i class="fas fa-tag input-icon"></i>
            <input type="text" name="nombre" class="form-control"
              placeholder="Ej. Agua Potable para San Marcos"
              value="<?= htmlspecialchars($_POST['nombre'] ?? '') ?>" required>
          </div>
        </div>

        <div>
          <label class="form-label">Meta de Recaudación (Q) <span style="color:var(--danger)">*</span></label>
          <div class="input-group">
            <i class="fas fa-dollar-sign input-icon"></i>
            <input type="number" name="meta" class="form-control" step="0.01" min="1"
              placeholder="0.00" data-currency
              value="<?= htmlspecialchars($_POST['meta'] ?? '') ?>" required>
          </div>
        </div>

        <div>
          <label class="form-label">Estado</label>
          <div class="input-group">
            <i class="fas fa-toggle-on input-icon"></i>
            <select name="estado" class="form-control" style="padding-left:2.25rem">
              <option value="activa"     <?= ($_POST['estado'] ?? '') === 'activa'     ? 'selected' : '' ?>>Activa</option>
              <option value="pausada"    <?= ($_POST['estado'] ?? '') === 'pausada'    ? 'selected' : '' ?>>Pausada</option>
              <option value="finalizada" <?= ($_POST['estado'] ?? '') === 'finalizada' ? 'selected' : '' ?>>Finalizada</option>
            </select>
          </div>
        </div>

        <div>
          <label class="form-label">Fecha de Inicio <span style="color:var(--danger)">*</span></label>
          <div class="input-group">
            <i class="fas fa-calendar input-icon"></i>
            <input type="date" name="fecha_inicio" class="form-control"
              value="<?= htmlspecialchars($_POST['fecha_inicio'] ?? date('Y-m-d')) ?>" required>
          </div>
        </div>

        <div>
          <label class="form-label">Fecha de Finalización</label>
          <div class="input-group">
            <i class="fas fa-calendar-check input-icon"></i>
            <input type="date" name="fecha_fin" class="form-control"
              value="<?= htmlspecialchars($_POST['fecha_fin'] ?? '') ?>">
          </div>
        </div>

        <div style="grid-column:1/-1">
          <label class="form-label">Descripción de la Campaña</label>
          <textarea name="descripcion" class="form-control" rows="4"
            style="padding:.65rem .875rem;resize:vertical"
            placeholder="Describe el problema a resolver, el impacto esperado y los beneficiarios directos..."><?= htmlspecialchars($_POST['descripcion'] ?? '') ?></textarea>
        </div>

      </div>

      <div style="display:flex;gap:.75rem;margin-top:1.75rem;justify-content:flex-end">
        <a href="<?= APP_URL ?>/modules/campaigns/list.php" class="btn" style="background:var(--bg-light);border:1.5px solid var(--border);color:var(--text-dark);border-radius:var(--radius);font-weight:600;padding:.65rem 1.25rem;text-decoration:none">
          Cancelar
        </a>
        <button type="submit" class="btn btn-primary" style="width:auto;padding:.65rem 2rem">
          <i class="fas fa-save"></i> Guardar y Continuar →
        </button>
      </div>
    </form>
  </div>
</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
