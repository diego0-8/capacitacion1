<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Acceso denegado</title>
  <link rel="stylesheet" href="<?php echo htmlspecialchars(BASE_URL . '/assets/css/auth_forbidden.css'); ?>">
</head>
<body>
  <div class="wrap">
    <h1>Acceso denegado</h1>
    <p>No tiene permisos para ver esta sección.</p>
    <p><a href="<?php echo htmlspecialchars(BASE_URL . '/index.php?c=inicio&a=index'); ?>">Volver al inicio</a></p>
  </div>
</body>
</html>
