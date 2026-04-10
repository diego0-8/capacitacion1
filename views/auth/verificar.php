<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Verificar cuenta — Capacitación</title>
  <link rel="stylesheet" href="<?php echo htmlspecialchars(BASE_URL . '/assets/css/auth_login.css'); ?>">
</head>
<body>
  <div class="login-wrap">
    <div class="login-card">
      <h1>Verificar cuenta</h1>
      <p>Ingrese el código de 6 dígitos enviado a su correo. Caduca en 15 minutos.</p>

      <?php if (!empty($error)): ?>
        <p class="alert-error"><?php echo htmlspecialchars($error); ?></p>
      <?php endif; ?>
      <?php if (!empty($mensaje)): ?>
        <p class="alert-ok"><?php echo htmlspecialchars($mensaje); ?></p>
      <?php endif; ?>

      <form method="post" action="<?php echo htmlspecialchars(BASE_URL . '/index.php?c=auth&a=verificar'); ?>">
        <label for="pin">Código</label>
        <input type="text" id="pin" name="pin" required inputmode="numeric" maxlength="6" autocomplete="one-time-code">
        <button type="submit">Verificar</button>
      </form>

      <p class="muted" style="margin-top: 14px;">
        ¿No llegó el correo?
        <a href="<?php echo htmlspecialchars(BASE_URL . '/index.php?c=auth&a=reenviar_pin'); ?>">Reenviar código</a>
      </p>
      <p class="muted" style="margin-top: 10px;">
        <a href="<?php echo htmlspecialchars(BASE_URL . '/index.php?c=auth&a=login'); ?>">Volver al login</a>
      </p>
    </div>
  </div>
</body>
</html>

