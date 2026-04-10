<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Administración</title>
  <link rel="stylesheet" href="<?php echo htmlspecialchars(BASE_URL . '/assets/css/admin_index.css'); ?>">
</head>
<body>
  <?php $navActive = 'admin_index'; require BASE_PATH . '/views/auth/header.php'; ?>
  <main>
    <h1>Panel administrativo</h1>
    <?php if (!empty($mensaje)): ?>
      <p class="flash-ok"><?php echo htmlspecialchars($mensaje); ?></p>
    <?php endif; ?>
    <div class="cards">
      <div class="card">
        <div>Cursos</div>
        <div class="num"><?php echo (int) $totalCursos; ?></div>
      </div>
      <div class="card">
        <div>Asignaciones</div>
        <div class="num"><?php echo (int) $totalAsignaciones; ?></div>
      </div>
    </div>
  </main>
</body>
</html>
