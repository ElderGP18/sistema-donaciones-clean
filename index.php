<?php
require_once __DIR__ . '/config/app.php';
require_once __DIR__ . '/config/database.php';

$pageTitle  = 'Inicio';
$activePage = 'inicio';

/* Stats y campañas destacadas */
$conn = getConnection();

$stats = ['campanas' => 0, 'recaudado' => 0, 'donantes' => 0];

$r = $conn->query("SELECT COUNT(*) AS total FROM campanas WHERE estado = 'activa'");
if ($r) $stats['campanas'] = $r->fetch_assoc()['total'];

$r = $conn->query("SELECT COALESCE(SUM(monto),0) AS total FROM donaciones");
if ($r) $stats['recaudado'] = $r->fetch_assoc()['total'];

$r = $conn->query("SELECT COUNT(*) AS total FROM donantes");
if ($r) $stats['donantes'] = $r->fetch_assoc()['total'];

/* Campañas destacadas (activas) */
$campanas = [];
$res = $conn->query("
    SELECT c.*,
           COALESCE((SELECT SUM(d.monto) FROM donaciones d WHERE d.id_campana = c.id_campana),0) AS recaudado
    FROM campanas c
    WHERE c.estado = 'activa'
    ORDER BY recaudado DESC
    LIMIT 6
");
if ($res) {
    while ($row = $res->fetch_assoc()) $campanas[] = $row;
}
$conn->close();

include __DIR__ . '/includes/header.php';
include __DIR__ . '/includes/navbar.php';
?>

<!-- ── HERO ── -->
<section class="hero">
  <div class="hero-inner">
    <div class="hero-content">
      <h1>Plataforma de Donaciones y<br>Rendición de Cuentas</h1>
      <p>Transparencia total. Contribuye a campañas verificadas y sigue el impacto de tu donación.</p>
      <div class="hero-btns">
        <a href="<?= APP_URL ?>/modules/campaigns/list.php" class="btn btn-white">
          <i class="fas fa-list"></i> Ver Campañas Activas
        </a>
        <?php if (isLoggedIn()): ?>
          <a href="<?= APP_URL ?>/modules/donations/create.php" class="btn btn-outline-white">
            <i class="fas fa-hand-holding-heart"></i> Registrar Donación
          </a>
        <?php else: ?>
          <a href="<?= APP_URL ?>/login.php" class="btn btn-outline-white">
            <i class="fas fa-sign-in-alt"></i> Iniciar Sesión
          </a>
        <?php endif; ?>
      </div>
    </div>
    <div class="hero-illustration">
      <i class="fas fa-hand-holding-heart"></i>
    </div>
  </div>
</section>

<!-- ── STATS BAR ── -->
<section class="stats-bar">
  <div class="stats-inner">
    <div class="stat-item">
      <div class="stat-value"><?= number_format($stats['campanas']) ?></div>
      <div class="stat-label">Campañas Activas</div>
    </div>
    <div class="stat-item">
      <div class="stat-value">Q <?= number_format($stats['recaudado'], 0, '.', ',') ?></div>
      <div class="stat-label">Recaudado</div>
    </div>
    <div class="stat-item">
      <div class="stat-value"><?= number_format($stats['donantes']) ?></div>
      <div class="stat-label">Donantes</div>
    </div>
    <div class="stat-item">
      <div class="stat-value">100%</div>
      <div class="stat-label">Transparencia</div>
    </div>
  </div>
</section>

<!-- ── CAMPAÑAS DESTACADAS ── -->
<div class="main-content">
  <h2 class="section-title">Campañas Destacadas</h2>
  <p class="section-sub">Conoce los proyectos con mayor impacto y transparencia</p>

  <?php if (empty($campanas)): ?>
    <div style="text-align:center;padding:3rem;color:var(--text-muted);">
      <i class="fas fa-inbox" style="font-size:3rem;margin-bottom:1rem;display:block;opacity:.4"></i>
      <p>Aún no hay campañas registradas.</p>
      <?php if (isLoggedIn() && $_SESSION['user_rol'] === 'admin'): ?>
        <a href="<?= APP_URL ?>/modules/campaigns/create.php" class="btn btn-primary" style="margin-top:1rem;display:inline-flex">
          <i class="fas fa-plus"></i> Crear primera campaña
        </a>
      <?php endif; ?>
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
            <div class="card-meta">
              <span>Q <?= number_format($c['recaudado'],2) ?> / Q <?= number_format($c['meta'],2) ?></span>
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

<?php include __DIR__ . '/includes/footer.php'; ?>
