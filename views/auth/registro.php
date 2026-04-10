<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Registro de asesor — Capacitación</title>
  <link rel="stylesheet" href="<?php echo htmlspecialchars(BASE_URL . '/assets/css/auth_login.css'); ?>">
</head>
<body>
  <div class="login-wrap">
    <div class="login-card">
      <h1>Registro (asesores)</h1>
      <p>Cree su cuenta para acceder a sus capacitaciones.</p>
      <?php if (!empty($error)): ?>
        <p class="alert-error"><?php echo htmlspecialchars($error); ?></p>
      <?php endif; ?>
      <?php if (!empty($mensaje)): ?>
        <p class="alert-ok"><?php echo htmlspecialchars($mensaje); ?></p>
      <?php endif; ?>

      <form method="post" action="<?php echo htmlspecialchars(BASE_URL . '/index.php?c=auth&a=registro'); ?>">
        <label for="cedula">Cédula</label>
        <input type="text" id="cedula" name="cedula" required inputmode="numeric" maxlength="10" value="<?php echo htmlspecialchars((string) ($form['cedula'] ?? '')); ?>">

        <label for="nombre">Nombre completo</label>
        <input type="text" id="nombre" name="nombre" required maxlength="100" value="<?php echo htmlspecialchars((string) ($form['nombre'] ?? '')); ?>">

        <label for="email">Email</label>
        <input type="email" id="email" name="email" required maxlength="100" value="<?php echo htmlspecialchars((string) ($form['email'] ?? '')); ?>" autocomplete="email">

        <label for="usuario">Usuario</label>
        <input type="text" id="usuario" name="usuario" required maxlength="50" value="<?php echo htmlspecialchars((string) ($form['usuario'] ?? '')); ?>" autocomplete="username">

        <label for="clave">Contraseña</label>
        <input type="password" id="clave" name="clave" required autocomplete="new-password">

        <label for="clave2">Confirmación de contraseña</label>
        <input type="password" id="clave2" name="clave2" required autocomplete="new-password">

        <button type="submit">Crear cuenta</button>
      </form>

      <p class="muted" style="margin-top: 14px;">
        ¿Ya tienes cuenta?
        <a href="<?php echo htmlspecialchars(BASE_URL . '/index.php?c=auth&a=login'); ?>">Volver al login</a>
      </p>
    </div>
  </div>
</body>
</html>

