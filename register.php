<?php
require_once __DIR__ . '/config/app.php';
require_once __DIR__ . '/config/database.php';

if (isLoggedIn()) {
    redirect('dashboard.php');
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre   = sanitize($_POST['nombre']   ?? '');
    $correo   = sanitize($_POST['correo']   ?? '');
    $password = $_POST['password']          ?? '';
    $confirm  = $_POST['password_confirm']  ?? '';

    if (empty($nombre))                              $errors[] = 'El nombre es obligatorio.';
    if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) $errors[] = 'Correo electrónico inválido.';
    if (strlen($password) < 6)                       $errors[] = 'La contraseña debe tener al menos 6 caracteres.';
    if ($password !== $confirm)                      $errors[] = 'Las contraseñas no coinciden.';

    if (empty($errors)) {
        $conn = getConnection();
        $stmt = $conn->prepare("SELECT id_usuario FROM usuarios WHERE correo = ?");
        $stmt->bind_param('s', $correo);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $errors[] = 'Este correo ya está registrado.';
            $stmt->close();
            $conn->close();
        } else {
            $stmt->close();
            $hash = password_hash($password, PASSWORD_BCRYPT);
            $stmt = $conn->prepare("INSERT INTO usuarios (nombre, correo, password, rol) VALUES (?, ?, ?, 'encargado')");
            $stmt->bind_param('sss', $nombre, $correo, $hash);
            if ($stmt->execute()) {
                $stmt->close();
                $conn->close();
                redirect('login.php?registered=1');
            } else {
                $errors[] = 'Error al registrar. Intenta de nuevo.';
                $stmt->close();
                $conn->close();
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Registrarse | DonaTu</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link rel="stylesheet" href="<?= APP_URL ?>/assets/css/style.css">
</head>
<body>

<div class="login-wrapper">

  <!-- Panel izquierdo -->
  <div class="login-left">
    <div class="login-brand">
      <span class="heart">&#9829;</span> DonacionesGT
    </div>
    <h2 class="login-tagline">Únete a nuestra<br>Comunidad</h2>
    <p class="login-sub">Crea tu cuenta y comienza a gestionar campañas con total transparencia.</p>

    <ul class="login-features">
      <li>
        <span class="icon"><i class="fas fa-check"></i></span>
        Registro gratuito y seguro
      </li>
      <li>
        <span class="icon"><i class="fas fa-chart-bar"></i></span>
        Acceso a reportes en tiempo real
      </li>
      <li>
        <span class="icon"><i class="fas fa-users"></i></span>
        Gestión de donantes y campañas
      </li>
      <li>
        <span class="icon"><i class="fas fa-globe-americas"></i></span>
        Impacto en toda Guatemala
      </li>
    </ul>

    <span class="login-footer-text">&copy; 2025 DonacionesGT · Transparencia y Rendición de Cuentas</span>
  </div>

  <!-- Panel derecho -->
  <div class="login-right">
    <div class="login-card">

      <div class="login-tabs">
        <button class="login-tab" onclick="window.location='<?= APP_URL ?>/login.php'">Iniciar Sesión</button>
        <button class="login-tab active">Registrarse</button>
      </div>

      <h2>Crear cuenta</h2>
      <p class="sub">Completa los campos para registrarte</p>

      <?php foreach ($errors as $e): ?>
        <div class="alert alert-danger">
          <i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($e) ?>
        </div>
      <?php endforeach; ?>

      <form method="POST" action="">

        <label class="form-label" for="nombre">Nombre Completo <span>*</span></label>
        <div class="input-group">
          <i class="fas fa-user input-icon"></i>
          <input type="text" id="nombre" name="nombre" class="form-control"
            placeholder="Tu nombre completo"
            value="<?= htmlspecialchars($_POST['nombre'] ?? '') ?>" required>
        </div>

        <label class="form-label" for="correo">Correo Electrónico <span>*</span></label>
        <div class="input-group">
          <i class="fas fa-envelope input-icon"></i>
          <input type="email" id="correo" name="correo" class="form-control"
            placeholder="correo@ejemplo.com"
            value="<?= htmlspecialchars($_POST['correo'] ?? '') ?>"
            required autocomplete="email">
        </div>

        <label class="form-label" for="password">Contraseña <span>*</span></label>
        <div class="input-group">
          <i class="fas fa-lock input-icon"></i>
          <input type="password" id="password" name="password" class="form-control"
            placeholder="Mínimo 6 caracteres"
            required autocomplete="new-password">
          <button type="button" class="password-toggle" data-target="password">
            <i class="fas fa-eye"></i>
          </button>
        </div>

        <label class="form-label" for="password_confirm">Confirmar Contraseña <span>*</span></label>
        <div class="input-group">
          <i class="fas fa-lock input-icon"></i>
          <input type="password" id="password_confirm" name="password_confirm" class="form-control"
            placeholder="Repite tu contraseña"
            required autocomplete="new-password">
        </div>

        <button type="submit" class="btn btn-primary" style="margin-top:1.25rem">
          <i class="fas fa-user-plus"></i> Crear mi cuenta
        </button>
      </form>

      <p class="register-link">
        ¿Ya tienes cuenta? <a href="<?= APP_URL ?>/login.php">Inicia sesión →</a>
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
