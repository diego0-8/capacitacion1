<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Evaluación del módulo</title>
  <link rel="stylesheet" href="<?php echo htmlspecialchars(BASE_URL . '/assets/css/asesor_evaluacion.css'); ?>">
</head>
<body>
  <nav class="topnav">
    <span>Evaluación del módulo</span>
    <a href="<?php echo htmlspecialchars(BASE_URL . '/index.php?c=asesor&a=curso&id=' . (int) $asignacion['id_asignacion']); ?>">Volver</a>
    <a href="<?php echo htmlspecialchars(BASE_URL . '/index.php?c=auth&a=logout'); ?>">Salir</a>
  </nav>

  <main>
    <?php if (!empty($mensaje)): ?>
      <p class="flash-ok"><?php echo htmlspecialchars($mensaje); ?></p>
    <?php endif; ?>
    <?php if (!empty($error)): ?>
      <p class="flash-err"><?php echo htmlspecialchars($error); ?></p>
    <?php endif; ?>

    <h1><?php echo htmlspecialchars($curso['nombre_curso'] ?? ''); ?></h1>
    <p>Debe responder correctamente todas las preguntas para aprobar el módulo.</p>

    <form method="post" action="<?php echo htmlspecialchars(BASE_URL . '/index.php?c=asesor&a=modulo_quiz_enviar'); ?>">
      <input type="hidden" name="id_asignacion" value="<?php echo (int) $asignacion['id_asignacion']; ?>">
      <input type="hidden" name="id_modulo" value="<?php echo (int) $idModulo; ?>">

      <?php foreach ($items as $it): ?>
        <?php $p = $it['pregunta']; $ops = $it['opciones']; ?>
        <div class="pregunta">
          <p><?php echo nl2br(htmlspecialchars((string) ($p['enunciado'] ?? ''))); ?></p>
          <div class="opciones">
            <?php foreach ($ops as $o): ?>
              <?php
              $idP = (int) ($p['id_pregunta_modulo'] ?? 0);
              $name = 'p_' . $idP;
              $idO = (int) ($o['id_opcion'] ?? 0);
              $img = (string) ($o['imagen_path'] ?? '');
              $txt = (string) ($o['texto'] ?? '');
              ?>
              <label>
                <input type="radio" name="<?php echo htmlspecialchars($name); ?>" value="<?php echo $idO; ?>" required>
                <?php if ($img !== ''): ?>
                  <img src="<?php echo htmlspecialchars(BASE_URL . '/' . $img); ?>" alt="" style="max-width: 320px; display: block;">
                <?php else: ?>
                  <span><?php echo htmlspecialchars($txt); ?></span>
                <?php endif; ?>
              </label>
            <?php endforeach; ?>
          </div>
        </div>
      <?php endforeach; ?>

      <button class="enviar" type="submit">Enviar</button>
    </form>
  </main>
</body>
</html>

