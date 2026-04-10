<!DOCTYPE html>
<html lang="es" class="auth-root auth-root--registro">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Registro de asesor — Capacitación</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="<?php echo htmlspecialchars(BASE_URL . '/assets/css/auth_login.css'); ?>">
</head>
<body class="auth-page auth-page--registro">
  <div
    class="auth-bg auth-bg--registro"
    style="background-image: url('<?php echo htmlspecialchars(BASE_URL . '/img/Frame1tys.png'); ?>');"
    role="presentation"
    aria-hidden="true"
  ></div>
  <div class="auth-shell">
    <div class="auth-panel">
      <div class="auth-card">
        <h1>Registro de asesor</h1>
        <p class="auth-lead">Complete el formulario para crear su cuenta y acceder a las capacitaciones asignadas.</p>
        <?php if (!empty($error)): ?>
          <p class="alert-error"><?php echo htmlspecialchars($error); ?></p>
        <?php endif; ?>
        <?php if (!empty($mensaje)): ?>
          <p class="alert-ok"><?php echo htmlspecialchars($mensaje); ?></p>
        <?php endif; ?>

        <form method="post" action="<?php echo htmlspecialchars(BASE_URL . '/index.php?c=auth&a=registro'); ?>">
          <div class="auth-field">
            <label for="cedula">Cédula</label>
            <input type="text" id="cedula" name="cedula" required inputmode="numeric" maxlength="10" value="<?php echo htmlspecialchars((string) ($form['cedula'] ?? '')); ?>">
          </div>
          <div class="auth-field">
            <label for="nombre">Nombre completo</label>
            <input type="text" id="nombre" name="nombre" required maxlength="100" value="<?php echo htmlspecialchars((string) ($form['nombre'] ?? '')); ?>">
          </div>
          <div class="auth-field">
            <label for="email">Correo electrónico</label>
            <input type="email" id="email" name="email" required maxlength="100" value="<?php echo htmlspecialchars((string) ($form['email'] ?? '')); ?>" autocomplete="email">
          </div>
          <div class="auth-field">
            <label for="usuario">Usuario</label>
            <input type="text" id="usuario" name="usuario" required maxlength="50" value="<?php echo htmlspecialchars((string) ($form['usuario'] ?? '')); ?>" autocomplete="username">
          </div>
          <div class="auth-field">
            <label for="clave">Contraseña</label>
            <input type="password" id="clave" name="clave" required autocomplete="new-password">
          </div>
          <div class="auth-field">
            <label for="clave2">Confirmar contraseña</label>
            <input type="password" id="clave2" name="clave2" required autocomplete="new-password">
          </div>
          <button type="submit" class="auth-btn-primary">Crear cuenta</button>
        </form>

        <p class="muted" style="margin-top: 1.25rem; text-align: center;">
          ¿Ya tienes cuenta?
          <a class="auth-link-back" href="<?php echo htmlspecialchars(BASE_URL . '/index.php?c=auth&a=login'); ?>">Volver al inicio de sesión</a>
        </p>
      </div>
    </div>
  </div>
</body>
</html>
