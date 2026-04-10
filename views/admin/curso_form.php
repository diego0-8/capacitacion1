<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?php echo !empty($curso) ? 'Editar curso' : 'Nuevo curso'; ?></title>
  <link rel="stylesheet" href="<?php echo htmlspecialchars(BASE_URL . '/assets/css/admin_curso_form.css'); ?>">
</head>
<body>
  <?php $navActive = 'admin_cursos'; require BASE_PATH . '/views/auth/header.php'; ?>
  <main>
    <?php if (!empty($error)): ?>
      <p class="flash-err"><?php echo htmlspecialchars($error); ?></p>
    <?php endif; ?>
    <form method="post" action="<?php echo htmlspecialchars(BASE_URL . '/index.php?c=admin&a=curso_guardar'); ?>">
      <?php if (!empty($curso)): ?>
        <input type="hidden" name="id_cursos" value="<?php echo (int) $curso['id_cursos']; ?>">
      <?php endif; ?>
      <label for="nombre_curso">Nombre del curso</label>
      <input type="text" id="nombre_curso" name="nombre_curso" required value="<?php echo htmlspecialchars($curso['nombre_curso'] ?? ''); ?>">

      <label for="descripcion">Descripción</label>
      <textarea id="descripcion" name="descripcion"><?php echo htmlspecialchars($curso['descripcion'] ?? ''); ?></textarea>

      <label for="estado">Estado</label>
      <select id="estado" name="estado">
        <option value="activo" <?php echo (!empty($curso) && ($curso['estado'] ?? '') === 'activo') ? 'selected' : ''; ?>>activo</option>
        <option value="inactivo" <?php echo (!empty($curso) && ($curso['estado'] ?? '') === 'inactivo') ? 'selected' : ''; ?>>inactivo</option>
      </select>

      <label for="cedula_coordinador">Coordinador asignado</label>
      <select id="cedula_coordinador" name="cedula_coordinador">
        <option value="">— Sin asignar —</option>
        <?php foreach ($coordinadores as $coord): ?>
          <option value="<?php echo htmlspecialchars($coord['cedula']); ?>"
            <?php echo (!empty($curso) && ($curso['cedula_coordinador'] ?? '') === $coord['cedula']) ? 'selected' : ''; ?>>
            <?php echo htmlspecialchars($coord['nombre'] . ' (' . $coord['cedula'] . ')'); ?>
          </option>
        <?php endforeach; ?>
      </select>
      <p class="form-hint">
        Solo usuarios con rol <strong>coordinador</strong> activos aparecen aquí. Se guarda su <strong>cédula</strong> en el curso;
        otros coordinadores no verán ni podrán editar este curso. Si elige “Sin asignar”, nadie con rol coordinador lo gestiona hasta que lo asigne de nuevo.
      </p>

      <button type="submit">Guardar</button>
    </form>
  </main>
</body>
</html>
