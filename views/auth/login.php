<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Ingresar — Capacitación</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="<?php echo htmlspecialchars(BASE_URL . '/assets/css/auth_login.css'); ?>">
</head>
<body class="auth-page">
  <div
    class="auth-bg"
    style="background-image: url('<?php echo htmlspecialchars(BASE_URL . '/img/Frame1tys.png'); ?>');"
    role="presentation"
    aria-hidden="true"
  ></div>
  <div class="auth-shell">
    <div class="auth-panel">
      <div class="auth-card">
        <h1>Iniciar sesión</h1>
        <p class="auth-lead">Acceda con su usuario institucional para continuar con sus capacitaciones.</p>
        <?php if (!empty($error)): ?>
          <p class="alert-error"><?php echo htmlspecialchars($error); ?></p>
        <?php endif; ?>
        <?php if (!empty($mensaje)): ?>
          <p class="alert-ok"><?php echo htmlspecialchars($mensaje); ?></p>
        <?php endif; ?>
        <form method="post" action="<?php echo htmlspecialchars(BASE_URL . '/index.php?c=auth&a=login'); ?>">
          <div class="auth-field">
            <label for="usuario">Usuario</label>
            <input type="text" id="usuario" name="usuario" required autocomplete="username">
          </div>
          <div class="auth-field">
            <label for="clave">Contraseña</label>
            <input type="password" id="clave" name="clave" required autocomplete="current-password">
          </div>
          <button type="submit" class="auth-btn-primary">Entrar</button>
        </form>

        <div class="auth-divider" aria-hidden="true">o</div>

        <div class="auth-register-block">
          <p>¿Eres asesor y aún no tienes cuenta?</p>
          <a
            class="auth-btn-secondary"
            href="<?php echo htmlspecialchars(BASE_URL . '/index.php?c=auth&a=registro'); ?>"
          >Crear cuenta de asesor</a>
        </div>
      </div>
    </div>
  </div>
</body>
</html>
