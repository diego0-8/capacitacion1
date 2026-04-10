<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Preguntas — <?php echo htmlspecialchars($curso['nombre_curso'] ?? ''); ?></title>
  <link rel="stylesheet" href="<?php echo htmlspecialchars(BASE_URL . '/assets/css/coordinador_preguntas.css'); ?>">
</head>
<body>
  <?php $navActive = 'coord_preguntas'; require BASE_PATH . '/views/auth/header.php'; ?>
  <main>
    <?php
    // #region agent log
    @file_put_contents(
        BASE_PATH . DIRECTORY_SEPARATOR . 'debug-4338d8.log',
        json_encode(
            [
                'sessionId' => '4338d8',
                'runId' => 'run1',
                'hypothesisId' => 'H1',
                'location' => 'views/coordinador/preguntas.php:subnav',
                'message' => 'preguntas subnav rendered',
                'data' => [
                    'idCurso' => (int) ($curso['id_cursos'] ?? 0),
                    'hasCoordContextToolbar' => true,
                    'css' => 'assets/css/coordinador_preguntas.css',
                    'request' => (string) ($_SERVER['REQUEST_URI'] ?? ''),
                ],
                'timestamp' => (int) round(microtime(true) * 1000),
            ],
            JSON_UNESCAPED_UNICODE
        ) . PHP_EOL,
        FILE_APPEND
    );
    // #endregion
    ?>
    <div class="coord-context-toolbar" role="navigation" aria-label="Contexto de evaluación">
      <span>Preguntas de evaluación</span>
      <a href="<?php echo htmlspecialchars(BASE_URL . '/index.php?c=coordinador&a=curso&id=' . (int) ($curso['id_cursos'] ?? 0)); ?>">Volver al curso</a>
    </div>
    <h1><?php echo htmlspecialchars($curso['nombre_curso'] ?? ''); ?></h1>
    <?php if (!empty($mensaje)): ?>
      <p class="flash-ok"><?php echo htmlspecialchars($mensaje); ?></p>
    <?php endif; ?>
    <?php if (!empty($error)): ?>
      <p class="flash-err"><?php echo htmlspecialchars($error); ?></p>
    <?php endif; ?>

    <form class="nueva" method="post" action="<?php echo htmlspecialchars(BASE_URL . '/index.php?c=coordinador&a=pregunta_guardar'); ?>">
      <input type="hidden" name="id_curso" value="<?php echo (int) ($curso['id_cursos'] ?? 0); ?>">
      <h2>Nueva pregunta</h2>
      <label for="enunciado">Enunciado</label>
      <textarea id="enunciado" name="enunciado" required></textarea>
      <label for="opcion_a">Opción A</label>
      <input type="text" id="opcion_a" name="opcion_a" required>
      <label for="opcion_b">Opción B</label>
      <input type="text" id="opcion_b" name="opcion_b" required>
      <label for="opcion_c">Opción C</label>
      <input type="text" id="opcion_c" name="opcion_c" required>
      <label for="opcion_d">Opción D</label>
      <input type="text" id="opcion_d" name="opcion_d" required>
      <label for="respuesta_correcta">Respuesta correcta</label>
      <select id="respuesta_correcta" name="respuesta_correcta">
        <option value="a">A</option>
        <option value="b">B</option>
        <option value="c">C</option>
        <option value="d">D</option>
      </select>
      <button type="submit">Agregar</button>
    </form>

    <?php foreach ($preguntas as $p): ?>
      <div class="pregunta">
        <p><?php echo nl2br(htmlspecialchars($p['enunciado'])); ?></p>
        <ul>
          <li>A) <?php echo htmlspecialchars($p['opcion_a']); ?></li>
          <li>B) <?php echo htmlspecialchars($p['opcion_b']); ?></li>
          <li>C) <?php echo htmlspecialchars($p['opcion_c']); ?></li>
          <li>D) <?php echo htmlspecialchars($p['opcion_d']); ?></li>
        </ul>
        <small>Correcta: <strong><?php echo strtoupper(htmlspecialchars($p['respuesta_correcta'])); ?></strong></small>
        <div>
          <a href="<?php echo htmlspecialchars(BASE_URL . '/index.php?c=coordinador&a=pregunta_eliminar&id_pregunta=' . (int) $p['id_pregunta'] . '&id_curso=' . (int) ($curso['id_cursos'] ?? 0)); ?>" onclick="return confirm('¿Eliminar pregunta?');">Eliminar</a>
        </div>
      </div>
    <?php endforeach; ?>
  </main>
</body>
</html>
