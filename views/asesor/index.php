<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Mis capacitaciones</title>
  <link rel="stylesheet" href="<?php echo htmlspecialchars(BASE_URL . '/assets/css/asesor_index.css'); ?>">
</head>
<body>
  <?php $navActive = 'asesor_index'; require BASE_PATH . '/views/auth/header.php'; ?>
  <main>
    <h1>Mis capacitaciones</h1>
    <?php if (!empty($mensaje)): ?>
      <p class="flash-ok"><?php echo htmlspecialchars($mensaje); ?></p>
    <?php endif; ?>
    <?php if (!empty($error)): ?>
      <p class="flash-err"><?php echo htmlspecialchars($error); ?></p>
    <?php endif; ?>
    <?php if (count($items) === 0): ?>
      <p>No tiene cursos asignados todavía.</p>
    <?php else: ?>
      <div class="cards">
        <?php foreach ($items as $it): ?>
          <div class="card">
            <h2><?php echo htmlspecialchars($it['nombre_curso'] ?? ''); ?></h2>
            <div class="meta">
              Progreso: <?php echo (int) ($it['progreso_porcentaje'] ?? 0); ?>%
            </div>
            <div class="estado"><?php echo htmlspecialchars($it['estado_capacitacion'] ?? ''); ?></div>
            <a class="cta" href="<?php echo htmlspecialchars(BASE_URL . '/index.php?c=asesor&a=curso&id=' . (int) $it['id_asignacion']); ?>">Entrar al curso</a>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>

    <hr>

    <h1>Cursos disponibles</h1>
    <?php if (empty($cursos) || count($cursos) === 0): ?>
      <p>No hay cursos activos disponibles.</p>
    <?php else: ?>
      <div class="cards">
        <?php
        $asignados = [];
        foreach ($items as $it) {
            $asignados[(int) ($it['id_curso'] ?? 0)] = (int) ($it['id_asignacion'] ?? 0);
        }
        ?>
        <?php foreach ($cursos as $c): ?>
          <?php
          $idCurso = (int) ($c['id_cursos'] ?? 0);
          $yaIdAsignacion = $asignados[$idCurso] ?? 0;
          ?>
          <div class="card">
            <h2><?php echo htmlspecialchars((string) ($c['nombre_curso'] ?? '')); ?></h2>
            <div class="meta">
              <?php if (!empty($c['nombre_coordinador'])): ?>
                Coordinador: <?php echo htmlspecialchars((string) $c['nombre_coordinador']); ?>
              <?php endif; ?>
            </div>
            <?php if (!empty($c['descripcion'])): ?>
              <p><?php echo nl2br(htmlspecialchars((string) $c['descripcion'])); ?></p>
            <?php endif; ?>

            <?php if ($yaIdAsignacion > 0): ?>
              <a class="cta" href="<?php echo htmlspecialchars(BASE_URL . '/index.php?c=asesor&a=curso&id=' . $yaIdAsignacion); ?>">Continuar</a>
            <?php else: ?>
              <form method="post" action="<?php echo htmlspecialchars(BASE_URL . '/index.php?c=asesor&a=inscribirse'); ?>">
                <input type="hidden" name="id_curso" value="<?php echo $idCurso; ?>">
                <button class="cta" type="submit">Inscribirme</button>
              </form>
            <?php endif; ?>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </main>
</body>
</html>
