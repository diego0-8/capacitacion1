<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Reportes</title>
  <link rel="stylesheet" href="<?php echo htmlspecialchars(BASE_URL . '/assets/css/coordinador_index.css'); ?>">
</head>
<body>
  <?php $navActive = 'coord_reportes'; require BASE_PATH . '/views/auth/header.php'; ?>
  <main>
    <h1>Reportes</h1>
    <?php if (!empty($mensaje)): ?>
      <p class="flash-ok"><?php echo htmlspecialchars($mensaje); ?></p>
    <?php endif; ?>
    <?php if (!empty($error)): ?>
      <p class="flash-err"><?php echo htmlspecialchars($error); ?></p>
    <?php endif; ?>

    <?php if (empty($cursos)): ?>
      <p class="muted">No hay cursos asignados.</p>
    <?php else: ?>
      <ul class="list">
        <?php foreach ($cursos as $curso): ?>
          <li class="curso-card">
            <div class="curso-row">
              <div>
                <strong><?php echo htmlspecialchars((string) ($curso['nombre_curso'] ?? '')); ?></strong>
                <div class="muted small">ID <?php echo (int) ($curso['id_cursos'] ?? 0); ?></div>
              </div>
              <div style="display:flex;gap:.5rem;flex-wrap:wrap;justify-content:flex-end">
                <a class="btn-asesores" style="text-decoration:none;display:inline-flex;align-items:center" href="<?php echo htmlspecialchars(BASE_URL . '/index.php?c=coordinador&a=reporte&id=' . (int) ($curso['id_cursos'] ?? 0)); ?>">Ver reporte</a>
                <a class="btn-asesores" style="text-decoration:none;display:inline-flex;align-items:center" href="<?php echo htmlspecialchars(BASE_URL . '/index.php?c=coordinador&a=curso&id=' . (int) ($curso['id_cursos'] ?? 0)); ?>">Abrir curso</a>
              </div>
            </div>
          </li>
        <?php endforeach; ?>
      </ul>
    <?php endif; ?>
  </main>
</body>
</html>

