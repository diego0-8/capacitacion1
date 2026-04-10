<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Asignaciones</title>
  <link rel="stylesheet" href="<?php echo htmlspecialchars(BASE_URL . '/assets/css/admin_asignaciones.css'); ?>">
</head>
<body>
  <?php $navActive = 'admin_asignaciones'; require BASE_PATH . '/views/auth/header.php'; ?>
  <main>
    <h1>Asignaciones</h1>
    <?php if (!empty($mensaje)): ?>
      <p class="flash-ok"><?php echo htmlspecialchars($mensaje); ?></p>
    <?php endif; ?>
    <?php if (!empty($error)): ?>
      <p class="flash-err"><?php echo htmlspecialchars($error); ?></p>
    <?php endif; ?>
    <div class="grid">
      <div class="panel">
        <h2>Nueva asignación</h2>
        <form method="post" action="<?php echo htmlspecialchars(BASE_URL . '/index.php?c=admin&a=asignacion_guardar'); ?>">
          <label for="cedula_asesor">Asesor</label>
          <select id="cedula_asesor" name="cedula_asesor" required>
            <option value="">Seleccione…</option>
            <?php foreach ($asesores as $a): ?>
              <option value="<?php echo htmlspecialchars($a['cedula']); ?>">
                <?php echo htmlspecialchars($a['nombre'] . ' — ' . $a['cedula']); ?>
              </option>
            <?php endforeach; ?>
          </select>
          <label for="id_curso">Curso</label>
          <select id="id_curso" name="id_curso" required>
            <option value="">Seleccione…</option>
            <?php foreach ($cursos as $c): ?>
              <option value="<?php echo (int) $c['id_cursos']; ?>">
                <?php echo htmlspecialchars($c['nombre_curso']); ?>
              </option>
            <?php endforeach; ?>
          </select>
          <button type="submit">Asignar</button>
        </form>
      </div>
      <div class="panel">
        <h2>Registro</h2>
        <table>
          <thead>
            <tr>
              <th>Asesor</th>
              <th>Curso</th>
              <th>Estado</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach (
              $asignaciones as $r
            ): ?>
              <tr>
                <td><?php echo htmlspecialchars($r['nombre_asesor'] ?? ''); ?></td>
                <td><?php echo htmlspecialchars($r['nombre_curso'] ?? ''); ?></td>
                <td><?php echo htmlspecialchars($r['estado_capacitacion'] ?? ''); ?></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
  </main>
</body>
</html>
