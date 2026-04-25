<?php
require_once __DIR__ . '/../../config/app.php';
require_once __DIR__ . '/../../config/database.php';
requireLogin();

$pageTitle  = 'Registrar Donación';
$activePage = 'donaciones';
$errors = [];

$conn = getConnection();

/* Campañas activas */
$campanas = [];
$res = $conn->query("SELECT id_campana, nombre, meta, COALESCE((SELECT SUM(monto) FROM donaciones WHERE id_campana=c.id_campana),0) AS recaudado FROM campanas c WHERE estado='activa' ORDER BY nombre");
if ($res) while ($r = $res->fetch_assoc()) $campanas[] = $r;

/* Donantes existentes */
$donantes = [];
$res = $conn->query("SELECT id_donante, nombre, correo FROM donantes ORDER BY nombre");
if ($res) while ($r = $res->fetch_assoc()) $donantes[] = $r;

$campanaSeleccionada = null;
$campanaId = intval($_GET['campana'] ?? $_POST['id_campana'] ?? 0);
if ($campanaId) {
    foreach ($campanas as $c) { if ($c['id_campana'] == $campanaId) { $campanaSeleccionada = $c; break; } }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_campana  = intval($_POST['id_campana']  ?? 0);
    $monto       = floatval($_POST['monto']      ?? 0);
    $fecha       = $_POST['fecha']               ?? date('Y-m-d');
    $donante_opt = $_POST['donante_opt']         ?? 'existente';

    if (!$id_campana)   $errors[] = 'Selecciona una campaña.';
    if ($monto <= 0)    $errors[] = 'El monto debe ser mayor a 0.';
    if (empty($fecha))  $errors[] = 'La fecha es obligatoria.';

    $id_donante = 0;
    if ($donante_opt === 'nuevo') {
        $dnombre = sanitize($_POST['d_nombre'] ?? '');
        $dcorreo = sanitize($_POST['d_correo'] ?? '');
        $dtel    = sanitize($_POST['d_telefono'] ?? '');
        if (empty($dnombre)) $errors[] = 'El nombre del donante es obligatorio.';
        if (empty($errors)) {
            $s = $conn->prepare("INSERT INTO donantes (nombre, correo, telefono) VALUES (?,?,?)");
            $s->bind_param('sss', $dnombre, $dcorreo, $dtel);
            $s->execute(); $id_donante = $conn->insert_id; $s->close();
        }
    } else {
        $id_donante = intval($_POST['id_donante'] ?? 0);
        if (!$id_donante) $errors[] = 'Selecciona un donante.';
    }

    if (empty($errors)) {
        $s = $conn->prepare("INSERT INTO donaciones (fecha, monto, id_campana, id_donante, id_usuario) VALUES (?,?,?,?,?)");
        $s->bind_param('sdiii', $fecha, $monto, $id_campana, $id_donante, $_SESSION['user_id']);
        if ($s->execute()) {
            $s->close(); $conn->close();
            redirect("modules/donations/list.php?created=1");
        } else {
            $errors[] = 'Error al guardar: ' . $conn->error;
            $s->close();
        }
    }
}

include __DIR__ . '/../../includes/header.php';
include __DIR__ . '/../../includes/navbar.php';
?>

<div class="main-content">
  <nav style="font-size:.85rem;color:var(--text-muted);margin-bottom:1.25rem">
    <a href="<?= APP_URL ?>/dashboard.php" style="color:var(--accent);text-decoration:none">Inicio</a>
    &rsaquo; <a href="<?= APP_URL ?>/modules/donations/list.php" style="color:var(--accent);text-decoration:none">Donaciones</a>
    &rsaquo; Nueva Donación
  </nav>

  <h1 style="font-size:1.4rem;font-weight:800;margin-bottom:1.5rem">
    <i class="fas fa-hand-holding-heart" style="color:var(--accent)"></i> Registrar Donación
  </h1>

  <?php foreach ($errors as $e): ?>
    <div class="alert alert-danger"><i class="fas fa-exclamation-circle"></i> <?= $e ?></div>
  <?php endforeach; ?>

  <div style="display:grid;grid-template-columns:1fr 340px;gap:1.5rem;align-items:start">

    <!-- Formulario -->
    <form method="POST" action="">
      <div class="table-card" style="padding:1.75rem;margin-bottom:1rem">
        <h3 style="font-size:1rem;font-weight:700;margin-bottom:1.25rem;color:var(--text-muted);text-transform:uppercase;letter-spacing:.05em">
          Campaña y Monto
        </h3>

        <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem">
          <div style="grid-column:1/-1">
            <label class="form-label">Campaña Destino <span style="color:var(--danger)">*</span></label>
            <div class="input-group">
              <i class="fas fa-bullhorn input-icon"></i>
              <select name="id_campana" class="form-control" style="padding-left:2.25rem" required
                onchange="this.form.submit()">
                <option value="">Seleccionar campaña ▾</option>
                <?php foreach ($campanas as $c): ?>
                  <option value="<?= $c['id_campana'] ?>" <?= $campanaId == $c['id_campana'] ? 'selected' : '' ?>>
                    <?= htmlspecialchars($c['nombre']) ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>
          </div>

          <div>
            <label class="form-label">Monto (Q) <span style="color:var(--danger)">*</span></label>
            <div class="input-group">
              <i class="fas fa-dollar-sign input-icon"></i>
              <input type="number" name="monto" step="0.01" min="1" class="form-control"
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
      </div>

      <div class="table-card" style="padding:1.75rem">
        <h3 style="font-size:1rem;font-weight:700;margin-bottom:1.25rem;color:var(--text-muted);text-transform:uppercase;letter-spacing:.05em">
          Datos del Donante
        </h3>

        <div style="display:flex;gap:.75rem;margin-bottom:1.25rem">
          <label style="cursor:pointer;display:flex;align-items:center;gap:.4rem;font-size:.9rem">
            <input type="radio" name="donante_opt" value="existente"
              <?= ($_POST['donante_opt'] ?? 'existente') === 'existente' ? 'checked' : '' ?>
              onchange="toggleDonante(this.value)">
            Donante existente
          </label>
          <label style="cursor:pointer;display:flex;align-items:center;gap:.4rem;font-size:.9rem">
            <input type="radio" name="donante_opt" value="nuevo"
              <?= ($_POST['donante_opt'] ?? '') === 'nuevo' ? 'checked' : '' ?>
              onchange="toggleDonante(this.value)">
            Nuevo donante
          </label>
        </div>

        <div id="donante-existente" <?= ($_POST['donante_opt'] ?? '') === 'nuevo' ? 'style="display:none"' : '' ?>>
          <label class="form-label">Seleccionar Donante <span style="color:var(--danger)">*</span></label>
          <div class="input-group">
            <i class="fas fa-user input-icon"></i>
            <select name="id_donante" class="form-control" style="padding-left:2.25rem">
              <option value="">Seleccionar donante ▾</option>
              <?php foreach ($donantes as $d): ?>
                <option value="<?= $d['id_donante'] ?>" <?= ($_POST['id_donante'] ?? 0) == $d['id_donante'] ? 'selected' : '' ?>>
                  <?= htmlspecialchars($d['nombre']) ?><?= $d['correo'] ? ' — ' . $d['correo'] : '' ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>
        </div>

        <div id="donante-nuevo" <?= ($_POST['donante_opt'] ?? 'existente') !== 'nuevo' ? 'style="display:none"' : '' ?>>
          <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem">
            <div style="grid-column:1/-1">
              <label class="form-label">Nombre Completo <span style="color:var(--danger)">*</span></label>
              <div class="input-group">
                <i class="fas fa-user input-icon"></i>
                <input type="text" name="d_nombre" class="form-control" placeholder="Nombre del donante"
                  value="<?= htmlspecialchars($_POST['d_nombre'] ?? '') ?>">
              </div>
            </div>
            <div>
              <label class="form-label">Correo Electrónico</label>
              <div class="input-group">
                <i class="fas fa-envelope input-icon"></i>
                <input type="email" name="d_correo" class="form-control" placeholder="correo@ejemplo.com"
                  value="<?= htmlspecialchars($_POST['d_correo'] ?? '') ?>">
              </div>
            </div>
            <div>
              <label class="form-label">Teléfono</label>
              <div class="input-group">
                <i class="fas fa-phone input-icon"></i>
                <input type="text" name="d_telefono" class="form-control" placeholder="+502 0000-0000"
                  value="<?= htmlspecialchars($_POST['d_telefono'] ?? '') ?>">
              </div>
            </div>
          </div>
        </div>

        <div style="display:flex;gap:.75rem;margin-top:1.75rem;justify-content:flex-end">
          <a href="<?= APP_URL ?>/modules/donations/list.php" class="btn" style="background:var(--bg-light);border:1.5px solid var(--border);color:var(--text-dark);border-radius:var(--radius);font-weight:600;padding:.65rem 1.25rem;text-decoration:none">
            Cancelar
          </a>
          <button type="submit" class="btn btn-primary" style="width:auto;padding:.65rem 2rem">
            <i class="fas fa-save"></i> Registrar Donación
          </button>
        </div>
      </div>
    </form>

    <!-- Panel lateral: campaña seleccionada -->
    <div>
      <?php if ($campanaSeleccionada):
        $pct = $campanaSeleccionada['meta'] > 0
          ? min(100, round($campanaSeleccionada['recaudado'] / $campanaSeleccionada['meta'] * 100)) : 0;
      ?>
        <div class="table-card" style="padding:1.25rem">
          <h4 style="font-size:.85rem;color:var(--text-muted);text-transform:uppercase;letter-spacing:.05em;margin-bottom:1rem">Campaña Seleccionada</h4>
          <div style="height:100px;background:linear-gradient(135deg,var(--primary),#1e40af);border-radius:var(--radius);display:flex;align-items:center;justify-content:center;margin-bottom:1rem">
            <i class="fas fa-hand-holding-heart" style="font-size:2.5rem;color:rgba(255,255,255,.4)"></i>
          </div>
          <h3 style="font-weight:700;margin-bottom:.5rem"><?= htmlspecialchars($campanaSeleccionada['nombre']) ?></h3>
          <span class="badge badge-success" style="margin-bottom:.75rem">Activa</span>
          <div style="font-size:.85rem;color:var(--text-muted);margin-bottom:.5rem">
            Meta: <strong>Q <?= number_format($campanaSeleccionada['meta'],2) ?></strong>
          </div>
          <div style="font-size:.85rem;color:var(--text-muted);margin-bottom:.75rem">
            Recaudado: <strong>Q <?= number_format($campanaSeleccionada['recaudado'],2) ?></strong>
            (<?= $pct ?>%)
          </div>
          <div class="progress-bar-track">
            <div class="progress-bar-fill" data-width="<?= $pct ?>" style="width:0"></div>
          </div>
          <div style="margin-top:1rem;padding:.75rem;background:#eff6ff;border-radius:var(--radius);font-size:.8rem;color:var(--accent)">
            <i class="fas fa-info-circle"></i>
            La donación será verificada y registrada en el sistema.
          </div>
        </div>
      <?php else: ?>
        <div class="table-card" style="padding:1.5rem;text-align:center;color:var(--text-muted)">
          <i class="fas fa-bullhorn" style="font-size:2.5rem;opacity:.3;display:block;margin-bottom:.75rem"></i>
          <p style="font-size:.875rem">Selecciona una campaña para ver su información</p>
        </div>
      <?php endif; ?>
    </div>

  </div>
</div>

<script>
function toggleDonante(val) {
  document.getElementById('donante-existente').style.display = val === 'existente' ? '' : 'none';
  document.getElementById('donante-nuevo').style.display     = val === 'nuevo'      ? '' : 'none';
}
</script>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
