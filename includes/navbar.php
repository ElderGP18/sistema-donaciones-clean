<nav class="navbar">
  <div class="navbar-inner">
    <a href="<?= APP_URL ?>/index.php" class="navbar-brand">
      <span class="heart">&#9829;</span> DonaTu
    </a>

    <ul class="navbar-nav">
      <li><a href="<?= APP_URL ?>/index.php"       class="<?= $activePage === 'inicio'     ? 'active' : '' ?>">Inicio</a></li>
      <li><a href="<?= APP_URL ?>/modules/campaigns/list.php" class="<?= $activePage === 'campanas'   ? 'active' : '' ?>">Campañas</a></li>
      <?php if (isLoggedIn()): ?>
      <li><a href="<?= APP_URL ?>/modules/donations/list.php" class="<?= $activePage === 'donaciones' ? 'active' : '' ?>">Donaciones</a></li>
      <li><a href="<?= APP_URL ?>/modules/donors/list.php"    class="<?= $activePage === 'donantes'   ? 'active' : '' ?>">Donantes</a></li>
      <li><a href="<?= APP_URL ?>/modules/reports/index.php"  class="<?= $activePage === 'reportes'   ? 'active' : '' ?>">Reportes</a></li>
      <?php if ($_SESSION['user_rol'] === 'admin'): ?>
      <li><a href="<?= APP_URL ?>/modules/users/list.php"     class="<?= $activePage === 'usuarios'   ? 'active' : '' ?>">Usuarios</a></li>
      <?php endif; ?>
      <?php endif; ?>
      <li><a href="#">Nosotros</a></li>
    </ul>

    <div class="navbar-user">
      <?php if (isLoggedIn()): ?>
        <span class="user-name"><i class="fas fa-user-circle"></i> <?= htmlspecialchars($_SESSION['user_nombre']) ?></span>
        <a href="<?= APP_URL ?>/logout.php" class="btn btn-sm btn-outline-white" style="border:1px solid rgba(255,255,255,.4);color:#fff;padding:.35rem .8rem;border-radius:6px;text-decoration:none;font-size:.85rem;">
          <i class="fas fa-sign-out-alt"></i> Salir
        </a>
      <?php else: ?>
        <a href="<?= APP_URL ?>/login.php" class="btn btn-sm btn-accent">Iniciar Sesión</a>
      <?php endif; ?>
    </div>
  </div>
</nav>
