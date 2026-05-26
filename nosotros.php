<?php
require_once __DIR__ . '/config/app.php';
require_once __DIR__ . '/config/database.php';

$pageTitle  = 'Nosotros';
$activePage = 'nosotros';

include __DIR__ . '/includes/header.php';
include __DIR__ . '/includes/navbar.php';
?>

<div class="main-content">

  <!-- Hero -->
  <div style="text-align:center;padding:3rem 1rem 2rem">
    <span style="display:inline-flex;align-items:center;justify-content:center;width:5rem;height:5rem;border-radius:50%;background:linear-gradient(135deg,var(--primary),var(--accent));margin-bottom:1.25rem">
      <i class="fas fa-hand-holding-heart" style="font-size:2.2rem;color:#fff"></i>
    </span>
    <h1 style="font-size:2rem;font-weight:800;margin-bottom:.75rem">Sobre DonaTu</h1>
    <p style="color:var(--text-muted);max-width:560px;margin:0 auto;font-size:1.05rem;line-height:1.7">
      Plataforma de gestión de donaciones y rendición de cuentas para Guatemala,
      desarrollada con transparencia total.
    </p>
  </div>

  <!-- Misión / Visión -->
  <div style="display:grid;grid-template-columns:1fr 1fr;gap:1.5rem;margin-bottom:1.5rem">
    <div class="table-card" style="padding:1.75rem">
      <div style="display:flex;align-items:center;gap:.75rem;margin-bottom:1rem">
        <span style="width:2.5rem;height:2.5rem;border-radius:50%;background:#eff6ff;display:flex;align-items:center;justify-content:center;flex-shrink:0">
          <i class="fas fa-bullseye" style="color:var(--accent)"></i>
        </span>
        <h2 style="font-size:1.1rem;font-weight:700">Misión</h2>
      </div>
      <p style="color:var(--text-muted);line-height:1.7;font-size:.95rem">
        Facilitar la gestión transparente de donaciones y egresos, brindando a
        donantes y administradores una herramienta confiable que garantice la
        rendición de cuentas en cada campaña solidaria.
      </p>
    </div>
    <div class="table-card" style="padding:1.75rem">
      <div style="display:flex;align-items:center;gap:.75rem;margin-bottom:1rem">
        <span style="width:2.5rem;height:2.5rem;border-radius:50%;background:#f0fdf4;display:flex;align-items:center;justify-content:center;flex-shrink:0">
          <i class="fas fa-eye" style="color:var(--success)"></i>
        </span>
        <h2 style="font-size:1.1rem;font-weight:700">Visión</h2>
      </div>
      <p style="color:var(--text-muted);line-height:1.7;font-size:.95rem">
        Ser la plataforma de referencia en Guatemala para la gestión ética y
        transparente de donaciones, impulsando el desarrollo social mediante
        tecnología accesible y reportes en tiempo real.
      </p>
    </div>
  </div>

  <!-- Valores -->
  <div class="table-card" style="padding:1.75rem;margin-bottom:1.5rem">
    <h2 style="font-size:1.1rem;font-weight:700;margin-bottom:1.5rem;text-align:center">Nuestros Valores</h2>
    <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:1.25rem">
      <?php
      $valores = [
        ['fas fa-shield-alt','#eff6ff','var(--accent)','Transparencia','Cada donación y egreso queda registrado y es auditable.'],
        ['fas fa-lock','#f0fdf4','var(--success)','Seguridad','Datos protegidos con autenticación y control de acceso.'],
        ['fas fa-chart-bar','#faf5ff','#7c3aed','Rendición','Reportes claros para que los donantes vean el impacto.'],
        ['fas fa-heart','#fff7ed','#ea580c','Compromiso','Apoyamos causas reales con tecnología al servicio del bien.'],
      ];
      foreach ($valores as $v): ?>
        <div style="text-align:center;padding:1.25rem;background:<?= $v[1] ?>;border-radius:var(--radius)">
          <i class="<?= $v[0] ?>" style="font-size:1.75rem;color:<?= $v[2] ?>;margin-bottom:.75rem;display:block"></i>
          <div style="font-weight:700;margin-bottom:.4rem"><?= $v[3] ?></div>
          <p style="font-size:.8rem;color:var(--text-muted);line-height:1.5"><?= $v[4] ?></p>
        </div>
      <?php endforeach; ?>
    </div>
  </div>

  <!-- Cómo funciona -->
  <div class="table-card" style="padding:1.75rem;margin-bottom:1.5rem">
    <h2 style="font-size:1.1rem;font-weight:700;margin-bottom:1.5rem;text-align:center">¿Cómo Funciona?</h2>
    <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:1rem">
      <?php
      $pasos = [
        ['1','fas fa-user-plus','Regístrate','Crea tu cuenta gratuita en minutos y accede al sistema.'],
        ['2','fas fa-bullhorn','Explora','Descubre campañas activas y elige dónde quieres contribuir.'],
        ['3','fas fa-hand-holding-heart','Dona','Registra tu donación de forma segura y transparente.'],
        ['4','fas fa-file-alt','Seguimiento','Revisa reportes y verifica el destino de cada quetzal.'],
      ];
      foreach ($pasos as $p): ?>
        <div style="text-align:center;padding:1.25rem;border:1.5px solid var(--border);border-radius:var(--radius);position:relative">
          <div style="position:absolute;top:-1rem;left:50%;transform:translateX(-50%);width:2rem;height:2rem;border-radius:50%;background:var(--accent);color:#fff;font-weight:800;font-size:.85rem;display:flex;align-items:center;justify-content:center">
            <?= $p[0] ?>
          </div>
          <i class="<?= $p[1] ?>" style="font-size:1.75rem;color:var(--accent);margin:1rem 0 .75rem;display:block"></i>
          <div style="font-weight:700;margin-bottom:.4rem"><?= $p[2] ?></div>
          <p style="font-size:.8rem;color:var(--text-muted);line-height:1.5"><?= $p[3] ?></p>
        </div>
      <?php endforeach; ?>
    </div>
  </div>

  <!-- Equipo de Desarrollo -->
  <div class="table-card" style="padding:1.75rem;margin-bottom:1.5rem">
    <h2 style="font-size:1.1rem;font-weight:700;margin-bottom:1.5rem;text-align:center">Equipo de Desarrollo</h2>
    <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:1.25rem;margin-bottom:1.5rem">
      <?php
      $equipo = [
        ['Elder Estuardo García Pacheco',    '0900 24 9106'],
        ['Mario Estuardo López Rodas',        null],
        ['Allan Wilfredo Estrada Recinos',    null],
      ];
      $colores = [
        'linear-gradient(135deg,var(--primary),var(--accent))',
        'linear-gradient(135deg,#059669,#10b981)',
        'linear-gradient(135deg,#7c3aed,#a78bfa)',
      ];
      foreach ($equipo as $i => $m): ?>
        <div style="text-align:center;padding:1.5rem 1rem;border:1.5px solid var(--border);border-radius:var(--radius)">
          <div style="width:4rem;height:4rem;border-radius:50%;background:<?= $colores[$i] ?>;display:flex;align-items:center;justify-content:center;margin:0 auto .9rem">
            <i class="fas fa-user-graduate" style="font-size:1.6rem;color:#fff"></i>
          </div>
          <div style="font-weight:700;font-size:.95rem;margin-bottom:.3rem"><?= $m[0] ?></div>
          <?php if ($m[1]): ?>
            <div style="font-size:.8rem;color:var(--text-muted)">Carné: <?= $m[1] ?></div>
          <?php endif; ?>
        </div>
      <?php endforeach; ?>
    </div>
    <p style="text-align:center;color:var(--text-muted);font-size:.9rem">
      Ingeniería de Software &mdash; Universidad Mariano Gálvez de Guatemala
    </p>
    <div style="display:flex;justify-content:center;gap:.75rem;margin-top:1.25rem;flex-wrap:wrap">
      <span style="background:var(--bg-light);border:1px solid var(--border);border-radius:999px;padding:.3rem .9rem;font-size:.8rem">
        <i class="fab fa-php" style="color:#777bb4"></i> PHP 8.3
      </span>
      <span style="background:var(--bg-light);border:1px solid var(--border);border-radius:999px;padding:.3rem .9rem;font-size:.8rem">
        <i class="fas fa-database" style="color:#4479a1"></i> MySQL 8
      </span>
      <span style="background:var(--bg-light);border:1px solid var(--border);border-radius:999px;padding:.3rem .9rem;font-size:.8rem">
        <i class="fab fa-bootstrap" style="color:#7952b3"></i> Bootstrap
      </span>
      <span style="background:var(--bg-light);border:1px solid var(--border);border-radius:999px;padding:.3rem .9rem;font-size:.8rem">
        <i class="fab fa-github"></i> GitHub CI/CD
      </span>
    </div>
  </div>

  <!-- CTA -->
  <div style="text-align:center;padding:2rem">
    <?php if (isLoggedIn()): ?>
      <a href="<?= APP_URL ?>/modules/campaigns/list.php" class="btn btn-primary" style="display:inline-flex;padding:.75rem 2rem;font-size:1rem">
        <i class="fas fa-list"></i> Ver Campañas
      </a>
    <?php else: ?>
      <a href="<?= APP_URL ?>/register.php" class="btn btn-primary" style="display:inline-flex;padding:.75rem 2rem;font-size:1rem">
        <i class="fas fa-user-plus"></i> Crear cuenta gratis
      </a>
    <?php endif; ?>
  </div>

</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
