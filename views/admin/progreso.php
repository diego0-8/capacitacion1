<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Progreso asesores</title>
  <link rel="stylesheet" href="<?php echo htmlspecialchars(BASE_URL . '/assets/css/admin_progreso.css'); ?>">
</head>
<body>
  <?php $navActive = 'admin_progreso'; require BASE_PATH . '/views/auth/header.php'; ?>
  <main>
    <h1>Progreso por asesor</h1>
    <div class="table-wrap">
      <table>
        <thead>
          <tr>
            <th>Cédula</th>
            <th>Asesor</th>
            <th>Curso</th>
            <th>Progreso</th>
            <th>Estado</th>
            <th>Nota</th>
            <th>Asignación</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($filas as $f): ?>
            <tr>
              <td><?php echo htmlspecialchars($f['cedula'] ?? ''); ?></td>
              <td><?php echo htmlspecialchars($f['asesor'] ?? ''); ?></td>
              <td><?php echo htmlspecialchars($f['nombre_curso'] ?? ''); ?></td>
              <td><?php echo (int) ($f['progreso_porcentaje'] ?? 0); ?>%</td>
              <td><span class="badge"><?php echo htmlspecialchars($f['estado_capacitacion'] ?? ''); ?></span></td>
              <td><?php echo htmlspecialchars((string) ($f['calificacion_obtenida'] ?? '')); ?></td>
              <td><?php echo htmlspecialchars((string) ($f['fecha_asignacion'] ?? '')); ?></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </main>
</body>
</html>
