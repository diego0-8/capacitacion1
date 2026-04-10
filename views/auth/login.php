<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Ingresar — Capacitación</title>
  <link rel="stylesheet" href="<?php echo htmlspecialchars(BASE_URL . '/assets/css/auth_login.css'); ?>">
</head>
<body>
  <div class="login-wrap">
    <div class="login-card">
      <h1>Capacitación</h1>
      <p>Inicie sesión con su usuario institucional.</p>
      <?php if (!empty($error)): ?>
        <p class="alert-error"><?php echo htmlspecialchars($error); ?></p>
      <?php endif; ?>
      <?php if (!empty($mensaje)): ?>
        <p class="alert-ok"><?php echo htmlspecialchars($mensaje); ?></p>
        <script>
          alert(<?php echo json_encode((string) $mensaje, JSON_UNESCAPED_UNICODE); ?>);
        </script>
      <?php endif; ?>
      <form method="post" action="<?php echo htmlspecialchars(BASE_URL . '/index.php?c=auth&a=login'); ?>">
        <label for="usuario">Usuario</label>
        <input type="text" id="usuario" name="usuario" required autocomplete="username">

        <label for="clave">Contraseña</label>
        <input type="password" id="clave" name="clave" required autocomplete="current-password">

        <button type="submit">Entrar</button>
      </form>
      <p class="muted" style="margin-top: 14px;">
        ¿Eres asesor y no tienes cuenta?
        <a href="<?php echo htmlspecialchars(BASE_URL . '/index.php?c=auth&a=registro'); ?>">Registrarte</a>
      </p>
    </div>
  </div>
</body>
</html>
