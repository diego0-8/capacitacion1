<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Asesores atrasados</title>
  <link rel="stylesheet" href="<?php echo htmlspecialchars(BASE_URL . '/assets/css/admin_atrasados.css'); ?>">
</head>
<body>
  <?php $navActive = 'admin_atrasados'; require BASE_PATH . '/views/auth/header.php'; ?>
  <main>
    <h1>Asesores con capacitación pendiente</h1>
    <?php if (count($filas) === 0): ?>
      <p>No hay registros pendientes en la vista.</p>
    <?php else: ?>
      <ul>
        <?php foreach ($filas as $f): ?>
          <li>
            <strong><?php echo htmlspecialchars($f['asesor'] ?? ''); ?></strong>
            <span><?php echo htmlspecialchars($f['nombre_curso'] ?? ''); ?></span>
            <small>desde <?php echo htmlspecialchars((string) ($f['fecha_asignacion'] ?? '')); ?></small>
          </li>
        <?php endforeach; ?>
      </ul>
    <?php endif; ?>
  </main>
</body>
</html>
