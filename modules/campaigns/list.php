<?php
require_once __DIR__ . '/../../config/app.php';
require_once __DIR__ . '/../../config/database.php';

$pageTitle  = 'Campañas';
$activePage = 'campanas';

$conn = getConnection();
$campanas = [];
$res = $conn->query("
    SELECT c.*,
           COALESCE((SELECT SUM(monto) FROM donaciones WHERE id_campana=c.id_campana),0) AS recaudado
    FROM campanas c
    ORDER BY c.created_at DESC
");
if ($res) while ($row = $res->fetch_assoc()) $campanas[] = $row;
$conn->close();

include __DIR__ . '/../../includes/header.php';
include __DIR__ . '/../../includes/navbar.php';
?>

<div class="main-content">
  <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:1.5rem;flex-wrap:wrap;gap:.75rem">
    <div>
      <h1 class="section-title">Campañas de Donación</h1>
      <p class="section-sub">Gestión de todas las campañas activas y finalizadas</p>
    </div>
    <?php if (isLoggedIn() && $_SESSION['user_rol'] === 'admin'): ?>
      <a href="<?= APP_URL ?>/modules/campaigns/create.php" class="btn btn-primary">
        <i class="fas fa-plus"></i> Nueva Campaña
      </a>
    <?php endif; ?>
  </div>

  <?php if (empty($campanas)): ?>
    <div style="text-align:center;padding:4rem;color:var(--text-muted)">
      <i class="fas fa-inbox" style="font-size:3rem;margin-bottom:1rem;display:block;opacity:.4"></i>
      <p>No hay campañas registradas.</p>
    </div>
  <?php else: ?>
    <div class="cards-grid">
      <?php foreach ($campanas as $c):
        $pct = $c['meta'] > 0 ? min(100, round($c['recaudado'] / $c['meta'] * 100)) : 0;
      ?>
        <div class="card">
          <div class="card-img">
            <i class="fas fa-hand-holding-heart"></i>
            <span class="card-badge <?= $c['estado'] ?>"><?= ucfirst($c['estado']) ?></span>
          </div>
          <div class="card-body">
            <h3 class="card-title"><?= htmlspecialchars($c['nombre']) ?></h3>
            <p style="font-size:.8rem;color:var(--text-muted);margin-bottom:.75rem;line-height:1.5">
              <?= htmlspecialchars(substr($c['descripcion'] ?? '', 0, 80)) ?>...
            </p>
            <div class="card-meta">
              <span>Q <?= number_format($c['recaudado'],2) ?> / Q <?= number_format($c['meta'],2) ?></span>
              <span><?= date('d/m/Y', strtotime($c['fecha_inicio'])) ?></span>
            </div>
            <div class="progress-wrap">
              <div class="progress-info">
                <span><?= $pct ?>% completado</span>
              </div>
              <div class="progress-bar-track">
                <div class="progress-bar-fill" data-width="<?= $pct ?>" style="width:0"></div>
              </div>
            </div>
            <div class="card-actions">
              <a href="<?= APP_URL ?>/modules/campaigns/view.php?id=<?= $c['id_campana'] ?>" class="btn-card-outline">Ver Detalles</a>
              <?php if (isLoggedIn()): ?>
                <a href="<?= APP_URL ?>/modules/donations/create.php?campana=<?= $c['id_campana'] ?>" class="btn-card-primary">Donar</a>
              <?php else: ?>
                <a href="<?= APP_URL ?>/login.php" class="btn-card-primary">Donar</a>
              <?php endif; ?>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
