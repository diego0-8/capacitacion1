<?php
/** @var array<string, mixed> $asignacion */
/** @var array<string, mixed>|null $curso */
/** @var array<int, array<string, mixed>>|null $cursoEvalItems */
/** @var array<int, array<string, mixed>>|null $preguntas */
/** @var string|null $mensaje */
/** @var string|null $error */
$asignacion = is_array($asignacion ?? null) ? $asignacion : [];
$curso = is_array($curso ?? null) ? $curso : [];
$cursoEvalItems = is_array($cursoEvalItems ?? null) ? $cursoEvalItems : [];
$preguntas = is_array($preguntas ?? null) ? $preguntas : [];
$idAsignacion = (int) ($asignacion['id_asignacion'] ?? 0);
$nombreCurso = (string) ($curso['nombre_curso'] ?? $asignacion['nombre_curso'] ?? '');
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Evaluación — <?php echo htmlspecialchars($nombreCurso); ?></title>
  <link rel="stylesheet" href="<?php echo htmlspecialchars(BASE_URL . '/assets/css/asesor_evaluacion.css'); ?>">
</head>
<body>
  <nav class="topnav">
    <span>Evaluación</span>
    <?php if ($idAsignacion > 0): ?>
      <a href="<?php echo htmlspecialchars(BASE_URL . '/index.php?c=asesor&a=curso&id=' . $idAsignacion); ?>">Volver</a>
    <?php else: ?>
      <a href="<?php echo htmlspecialchars(BASE_URL . '/index.php?c=asesor&a=index'); ?>">Mis cursos</a>
    <?php endif; ?>
    <a href="<?php echo htmlspecialchars(BASE_URL . '/index.php?c=auth&a=logout'); ?>">Salir</a>
  </nav>

  <main>
    <?php if (!empty($mensaje)): ?>
      <p class="flash-ok"><?php echo htmlspecialchars((string) $mensaje); ?></p>
    <?php endif; ?>
    <?php if (!empty($error)): ?>
      <p class="flash-err"><?php echo htmlspecialchars((string) $error); ?></p>
    <?php endif; ?>

    <h1><?php echo htmlspecialchars($nombreCurso); ?></h1>
    <p class="intro">Responda todas las preguntas. Nota mínima para aprobar: <strong>70%</strong> de respuestas correctas. Si acierta todas, su nota será <strong>10/10</strong>.</p>

    <?php if ($idAsignacion <= 0): ?>
      <p class="flash-err">No se pudo cargar la capacitación. <a href="<?php echo htmlspecialchars(BASE_URL . '/index.php?c=asesor&a=index'); ?>">Volver a mis cursos</a>.</p>
    <?php elseif ($cursoEvalItems === [] && $preguntas === []): ?>
      <p class="flash-err">No hay preguntas disponibles para esta evaluación.</p>
    <?php else: ?>
    <form method="post" action="<?php echo htmlspecialchars(BASE_URL . '/index.php?c=asesor&a=evaluacion_enviar'); ?>">
      <input type="hidden" name="id_asignacion" value="<?php echo $idAsignacion; ?>">

      <?php if ($cursoEvalItems !== []): ?>
        <?php foreach ($cursoEvalItems as $it): ?>
          <?php
          if (!is_array($it)) {
              continue;
          }
          $p = is_array($it['pregunta'] ?? null) ? $it['pregunta'] : [];
          $ops = is_array($it['opciones'] ?? null) ? $it['opciones'] : [];
          $idP = (int) ($p['id_pregunta_curso'] ?? 0);
          $name = 'p_' . $idP;
          $enun = (string) ($p['enunciado'] ?? '');
          $tipo = (string) ($p['tipo'] ?? '');
          $enunImg = (string) ($p['enunciado_imagen_path'] ?? '');
          ?>
          <div class="pregunta">
            <p class="enunciado"><?php echo nl2br(htmlspecialchars($enun)); ?></p>
            <?php if ($enunImg !== ''): ?>
              <div class="enunciado-img-wrap">
                <img class="enunciado-img" src="<?php echo htmlspecialchars(BASE_URL . '/' . str_replace('\\', '/', $enunImg)); ?>" alt="">
              </div>
            <?php endif; ?>
            <div class="opciones<?php echo $tipo === 'imagen_par' ? ' opciones--imagen-par' : ''; ?>">
              <?php foreach ($ops as $o): ?>
                <?php
                $idO = (int) ($o['id_opcion'] ?? 0);
                $img = (string) ($o['imagen_path'] ?? '');
                $txt = (string) ($o['texto'] ?? '');
                ?>
                <label class="opcion-item">
                  <input class="opcion-input" type="radio" name="<?php echo htmlspecialchars($name); ?>" value="<?php echo (int) $idO; ?>" required>
                  <span class="opcion-body">
                    <?php if ($img !== ''): ?>
                      <img class="opcion-img" src="<?php echo htmlspecialchars(BASE_URL . '/' . str_replace('\\', '/', $img)); ?>" alt="">
                    <?php endif; ?>
                    <?php if ($txt !== ''): ?>
                      <span class="opcion-texto"><?php echo htmlspecialchars($txt); ?></span>
                    <?php endif; ?>
                  </span>
                </label>
              <?php endforeach; ?>
            </div>
          </div>
        <?php endforeach; ?>
      <?php else: ?>
        <?php foreach ($preguntas as $p): ?>
          <?php
          $idPregunta = (int) ($p['id_pregunta'] ?? 0);
          $name = 'p_' . $idPregunta;
          $opciones = PreguntaEvaluacion::opcionesParaVista($p);
          ?>
          <div class="pregunta">
            <p class="enunciado"><?php echo nl2br(htmlspecialchars((string) ($p['enunciado'] ?? ''))); ?></p>
            <div class="opciones">
              <?php foreach ($opciones as $o): ?>
                <?php
                $letra = (string) ($o['clave'] ?? '');
                $txt = (string) ($o['texto'] ?? '');
                $img = (string) ($o['imagen_path'] ?? '');
                ?>
                <label class="opcion-item">
                  <input class="opcion-input" type="radio" name="<?php echo htmlspecialchars($name); ?>" value="<?php echo htmlspecialchars($letra); ?>" required>
                  <span class="opcion-body">
                    <span class="opcion-letra"><?php echo strtoupper(htmlspecialchars($letra)); ?>)</span>
                    <?php if ($img !== ''): ?>
                      <img class="opcion-img" src="<?php echo htmlspecialchars(BASE_URL . '/' . str_replace('\\', '/', $img)); ?>" alt="">
                    <?php endif; ?>
                    <?php if ($txt !== ''): ?>
                      <span class="opcion-texto"><?php echo htmlspecialchars($txt); ?></span>
                    <?php endif; ?>
                  </span>
                </label>
              <?php endforeach; ?>
            </div>
          </div>
        <?php endforeach; ?>
      <?php endif; ?>

      <button class="enviar" type="submit">Enviar respuestas</button>
    </form>
    <?php endif; ?>
  </main>
</body>
</html>
