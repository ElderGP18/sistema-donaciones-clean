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

/* Buscar o crear registro de donante para el usuario logueado */
$miDonanteId = 0;
$stmt = $conn->prepare("SELECT id_donante FROM donantes WHERE correo = ?");
$stmt->bind_param('s', $_SESSION['user_correo']);
$stmt->execute();
$stmt->store_result();
if ($stmt->num_rows > 0) {
    $stmt->bind_result($miDonanteId);
    $stmt->fetch();
} else {
    $stmt->close();
    $ins = $conn->prepare("INSERT INTO donantes (nombre, correo) VALUES (?, ?)");
    $ins->bind_param('ss', $_SESSION['user_nombre'], $_SESSION['user_correo']);
    $ins->execute();
    $miDonanteId = $conn->insert_id;
    $ins->close();
}
if ($stmt->num_rows > 0) $stmt->close();

/* Buscar o crear donante Anónimo */
$anonimoId = 0;
$stmt2 = $conn->prepare("SELECT id_donante FROM donantes WHERE nombre = 'Anónimo' AND correo IS NULL LIMIT 1");
$stmt2->execute();
$stmt2->store_result();
if ($stmt2->num_rows > 0) {
    $stmt2->bind_result($anonimoId);
    $stmt2->fetch();
} else {
    $stmt2->close();
    $ins2 = $conn->prepare("INSERT INTO donantes (nombre) VALUES ('Anónimo')");
    $ins2->execute();
    $anonimoId = $conn->insert_id;
    $ins2->close();
}
if ($stmt2->num_rows > 0) $stmt2->close();

$campanaSeleccionada = null;
$campanaId = intval($_GET['campana'] ?? $_POST['id_campana'] ?? 0);
if ($campanaId) {
    foreach ($campanas as $c) {
        if ($c['id_campana'] == $campanaId) { $campanaSeleccionada = $c; break; }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_campana  = intval($_POST['id_campana'] ?? 0);
    $monto       = floatval($_POST['monto']    ?? 0);
    $fecha       = $_POST['fecha']             ?? date('Y-m-d');
    $donante_opt = $_POST['donante_opt']       ?? 'yo';

    if (!$id_campana) $errors[] = 'Selecciona una campaña.';
    if ($monto <= 0)  $errors[] = 'El monto debe ser mayor a 0.';
    if (empty($fecha)) $errors[] = 'La fecha es obligatoria.';

    $id_donante = $donante_opt === 'anonimo' ? $anonimoId : $miDonanteId;
    if (!$id_donante) $errors[] = 'Error al identificar el donante.';

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

    <form method="POST" action="">
      <!-- Campaña y Monto -->
      <div class="table-card" style="padding:1.75rem;margin-bottom:1rem">
        <h3 style="font-size:1rem;font-weight:700;margin-bottom:1.25rem;color:var(--text-muted);text-transform:uppercase;letter-spacing:.05em">
          Campaña y Monto
        </h3>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem">
          <div style="grid-column:1/-1">
            <label class="form-label">Campaña Destino <span style="color:var(--danger)">*</span></label>
            <div class="input-group">
              <i class="fas fa-bullhorn input-icon"></i>
              <select name="id_campana" class="form-control" style="padding-left:2.25rem" required onchange="this.form.submit()">
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
                placeholder="0.00" value="<?= htmlspecialchars($_POST['monto'] ?? '') ?>" required>
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

      <!-- Donante -->
      <div class="table-card" style="padding:1.75rem">
        <h3 style="font-size:1rem;font-weight:700;margin-bottom:1.25rem;color:var(--text-muted);text-transform:uppercase;letter-spacing:.05em">
          Donante
        </h3>

        <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem">

          <label style="cursor:pointer;border:2px solid var(--border);border-radius:var(--radius);padding:1rem;display:flex;align-items:center;gap:.75rem;transition:.2s"
            id="lbl-yo" onclick="selectDonante('yo')">
            <input type="radio" name="donante_opt" value="yo"
              <?= ($_POST['donante_opt'] ?? 'yo') === 'yo' ? 'checked' : '' ?> style="display:none">
            <span style="width:2.5rem;height:2.5rem;border-radius:50%;background:var(--accent);display:flex;align-items:center;justify-content:center;flex-shrink:0">
              <i class="fas fa-user" style="color:#fff;font-size:1rem"></i>
            </span>
            <div>
              <div style="font-weight:700;font-size:.9rem"><?= htmlspecialchars($_SESSION['user_nombre']) ?></div>
              <div style="font-size:.75rem;color:var(--text-muted)">Mi cuenta</div>
            </div>
          </label>

          <label style="cursor:pointer;border:2px solid var(--border);border-radius:var(--radius);padding:1rem;display:flex;align-items:center;gap:.75rem;transition:.2s"
            id="lbl-anonimo" onclick="selectDonante('anonimo')">
            <input type="radio" name="donante_opt" value="anonimo"
              <?= ($_POST['donante_opt'] ?? '') === 'anonimo' ? 'checked' : '' ?> style="display:none">
            <span style="width:2.5rem;height:2.5rem;border-radius:50%;background:#64748b;display:flex;align-items:center;justify-content:center;flex-shrink:0">
              <i class="fas fa-user-secret" style="color:#fff;font-size:1rem"></i>
            </span>
            <div>
              <div style="font-weight:700;font-size:.9rem">Anónimo</div>
              <div style="font-size:.75rem;color:var(--text-muted)">Ocultar identidad</div>
            </div>
          </label>

        </div>

        <div style="display:flex;gap:.75rem;margin-top:1.75rem;justify-content:flex-end">
          <a href="<?= APP_URL ?>/modules/donations/list.php" class="btn"
            style="background:var(--bg-light);border:1.5px solid var(--border);color:var(--text-dark);border-radius:var(--radius);font-weight:600;padding:.65rem 1.25rem;text-decoration:none">
            Cancelar
          </a>
          <button type="submit" class="btn btn-primary" style="width:auto;padding:.65rem 2rem">
            <i class="fas fa-save"></i> Registrar Donación
          </button>
        </div>
      </div>
    </form>

    <!-- Panel lateral -->
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
            Recaudado: <strong>Q <?= number_format($campanaSeleccionada['recaudado'],2) ?></strong> (<?= $pct ?>%)
          </div>
          <div class="progress-bar-track">
            <div class="progress-bar-fill" data-width="<?= $pct ?>" style="width:0"></div>
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
const sel = '<?= ($_POST['donante_opt'] ?? 'yo') ?>';
highlightDonante(sel);

function selectDonante(val) {
    document.querySelector('input[name="donante_opt"][value="' + val + '"]').checked = true;
    highlightDonante(val);
}
function highlightDonante(val) {
    const accent = getComputedStyle(document.documentElement).getPropertyValue('--accent').trim();
    document.getElementById('lbl-yo').style.borderColor      = val === 'yo'      ? accent       : 'var(--border)';
    document.getElementById('lbl-anonimo').style.borderColor = val === 'anonimo' ? '#64748b'    : 'var(--border)';
}
</script>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
