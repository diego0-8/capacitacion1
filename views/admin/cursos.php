<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Cursos</title>
  <link rel="stylesheet" href="<?php echo htmlspecialchars(BASE_URL . '/assets/css/admin_cursos.css'); ?>">
</head>
<body>
  <?php $navActive = 'admin_cursos'; require BASE_PATH . '/views/auth/header.php'; ?>
  <main>
    <h1>Gestión de cursos</h1>
    <?php if (!empty($mensaje)): ?>
      <p class="flash-ok"><?php echo htmlspecialchars($mensaje); ?></p>
    <?php endif; ?>
    <?php if (!empty($error)): ?>
      <p class="flash-err"><?php echo htmlspecialchars($error); ?></p>
    <?php endif; ?>
    <p><a class="btn" href="<?php echo htmlspecialchars(BASE_URL . '/index.php?c=admin&a=curso_form'); ?>">Nuevo curso</a></p>
    <table>
      <thead>
        <tr>
          <th>Nombre</th>
          <th>Estado</th>
          <th>Coordinador</th>
          <th></th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($cursos as $c): ?>
          <tr>
            <td><?php echo htmlspecialchars($c['nombre_curso']); ?></td>
            <td><?php echo htmlspecialchars($c['estado']); ?></td>
            <td><?php echo htmlspecialchars($c['nombre_coordinador'] ?? '—'); ?></td>
            <td>
              <a class="btn" href="<?php echo htmlspecialchars(BASE_URL . '/index.php?c=admin&a=curso_form&id=' . (int) $c['id_cursos']); ?>">Editar</a>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </main>
</body>
</html>
