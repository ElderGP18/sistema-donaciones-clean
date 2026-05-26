<?php
require_once __DIR__ . '/config/app.php';
require_once __DIR__ . '/config/database.php';

if (isLoggedIn()) {
    redirect('dashboard.php');
}

$error      = '';
$registered = isset($_GET['registered']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $correo   = sanitize($_POST['correo']   ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($correo) || empty($password)) {
        $error = 'Por favor ingresa tu correo y contraseña.';
    } else {
        $conn = getConnection();
        $stmt = $conn->prepare("SELECT id_usuario, nombre, correo, password, rol FROM usuarios WHERE correo = ? AND activo = 1");
        if (!$stmt) {
            $error = 'Error del servidor. Intenta de nuevo.';
            $conn->close();
        } else {
            $stmt->bind_param('s', $correo);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($row = $result->fetch_assoc()) {
                if (password_verify($password, $row['password'])) {
                    $_SESSION['user_id']     = $row['id_usuario'];
                    $_SESSION['user_nombre'] = $row['nombre'];
                    $_SESSION['user_correo'] = $row['correo'];
                    $_SESSION['user_rol']    = $row['rol'];
                    redirect('dashboard.php');
                } else {
                    $error = 'Correo o contraseña incorrectos.';
                }
            } else {
                $error = 'Correo o contraseña incorrectos.';
            }
            $stmt->close();
            $conn->close();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Iniciar Sesión | DonaTu</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link rel="stylesheet" href="<?= APP_URL ?>/assets/css/style.css">
</head>
<body>

<div class="login-wrapper">

  <!-- ── Panel izquierdo ── -->
  <div class="login-left">
    <div class="login-brand">
      <span class="heart">&#9829;</span> DonacionesGT
    </div>
    <h2 class="login-tagline">Transparencia y<br>Rendición de Cuentas</h2>
    <p class="login-sub">Únete a miles de donantes que confían en nuestra plataforma verificada y segura.</p>

    <ul class="login-features">
      <li>
        <span class="icon"><i class="fas fa-check"></i></span>
        Campañas 100% verificadas
      </li>
      <li>
        <span class="icon"><i class="fas fa-chart-bar"></i></span>
        Reportes en tiempo real
      </li>
      <li>
        <span class="icon"><i class="fas fa-lock"></i></span>
        Donaciones seguras y transparentes
      </li>
      <li>
        <span class="icon"><i class="fas fa-globe-americas"></i></span>
        Impacto en toda Guatemala
      </li>
    </ul>

    <span class="login-footer-text">&copy; 2025 DonacionesGT · Transparencia y Rendición de Cuentas</span>
  </div>

  <!-- ── Panel derecho ── -->
  <div class="login-right">
    <div class="login-card">

      <div class="login-tabs">
        <button class="login-tab active">Iniciar Sesión</button>
        <button class="login-tab" onclick="window.location='<?= APP_URL ?>/register.php'">Registrarse</button>
      </div>

      <h2>Bienvenido de vuelta</h2>
      <p class="sub">Ingresa tus credenciales para continuar</p>

      <?php if ($registered): ?>
        <div class="alert alert-success" data-auto-hide>
          <i class="fas fa-check-circle"></i> ¡Cuenta creada! Ingresa con tus credenciales.
        </div>
      <?php endif; ?>
      <?php if ($error): ?>
        <div class="alert alert-danger" data-auto-hide>
          <i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($error) ?>
        </div>
      <?php endif; ?>

      <form method="POST" action="">
        <label class="form-label" for="correo">Correo Electrónico <span>*</span></label>
        <div class="input-group">
          <i class="fas fa-envelope input-icon"></i>
          <input
            type="email" id="correo" name="correo"
            class="form-control"
            placeholder="correo@ejemplo.com"
            value="<?= htmlspecialchars($_POST['correo'] ?? '') ?>"
            required autocomplete="email">
        </div>

        <label class="form-label" for="password">Contraseña <span>*</span></label>
        <div class="input-group">
          <i class="fas fa-lock input-icon"></i>
          <input
            type="password" id="password" name="password"
            class="form-control"
            placeholder="••••••••"
            required autocomplete="current-password">
        </div>

        <div class="form-check">
          <label>
            <input type="checkbox" name="recordar"> Recordar sesión
          </label>
          <a href="#">¿Olvidaste tu contraseña?</a>
        </div>

        <button type="submit" class="btn btn-primary">
          <i class="fas fa-sign-in-alt"></i> Ingresar a mi cuenta
        </button>
      </form>

      <p class="register-link">
        ¿No tienes cuenta? <a href="<?= APP_URL ?>/register.php">Regístrate gratis →</a>
      </p>

      <div class="security-badge">
        <i class="fas fa-shield-alt"></i>
        Conexión segura · Datos protegidos · Verificado por DonaTu
      </div>

    </div>
  </div>

</div>

<script src="<?= APP_URL ?>/assets/js/main.js"></script>
</body>
</html>
