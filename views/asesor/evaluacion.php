<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Evaluación — <?php echo htmlspecialchars($curso['nombre_curso'] ?? ''); ?></title>
  <link rel="stylesheet" href="<?php echo htmlspecialchars(BASE_URL . '/assets/css/asesor_evaluacion.css'); ?>">
</head>
<body>
  <nav class="topnav">
    <span>Evaluación</span>
    <a href="<?php echo htmlspecialchars(BASE_URL . '/index.php?c=asesor&a=curso&id=' . (int) $asignacion['id_asignacion']); ?>">Volver</a>
    <a href="<?php echo htmlspecialchars(BASE_URL . '/index.php?c=auth&a=logout'); ?>">Salir</a>
  </nav>
  <main>
    <h1><?php echo htmlspecialchars($curso['nombre_curso'] ?? ''); ?></h1>
    <p>Responda todas las preguntas. Nota mínima para aprobar: 70% de respuestas correctas.</p>
    <form method="post" action="<?php echo htmlspecialchars(BASE_URL . '/index.php?c=asesor&a=evaluacion_enviar'); ?>">
      <input type="hidden" name="id_asignacion" value="<?php echo (int) $asignacion['id_asignacion']; ?>">
      <?php foreach ($preguntas as $p): ?>
        <div class="pregunta">
          <p><?php echo nl2br(htmlspecialchars($p['enunciado'])); ?></p>
          <div class="opciones">
            <?php foreach (['a' => 'opcion_a', 'b' => 'opcion_b', 'c' => 'opcion_c', 'd' => 'opcion_d'] as $letra => $campo): ?>
              <label>
                <input type="radio" name="<?php echo 'p_' . (int) $p['id_pregunta']; ?>" value="<?php echo $letra; ?>" required>
                <span><?php echo strtoupper($letra); ?>) <?php echo htmlspecialchars($p[$campo]); ?></span>
              </label>
            <?php endforeach; ?>
          </div>
        </div>
      <?php endforeach; ?>
      <button class="enviar" type="submit">Enviar respuestas</button>
    </form>
  </main>
</body>
</html>
