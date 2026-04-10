<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Detalle asesor — <?php echo htmlspecialchars((string) ($curso['nombre_curso'] ?? '')); ?></title>
  <link rel="stylesheet" href="<?php echo htmlspecialchars(BASE_URL . '/assets/css/coordinador_index.css'); ?>">
  <style>
    .card{background:#f4f6fb;border:1px solid rgba(255,255,255,.08);border-radius:14px;padding:14px}
    .grid{display:grid;grid-template-columns:1fr;gap:12px}
    @media(min-width:900px){.grid{grid-template-columns:360px 1fr}}
    .muted{opacity:.8}
    .pill{display:inline-block;padding:2px 8px;border-radius:999px;background:rgba(255,255,255,.08)}
    .tl{display:flex;flex-direction:column;gap:10px}
    .evt{display:flex;gap:12px;align-items:flex-start}
    .ts{min-width:160px}
    .evtBody{flex:1}
    .evtTitle{font-weight:600}
  </style>
</head>
<body>
  <?php $navActive = 'coord_detalle'; require BASE_PATH . '/views/auth/header.php'; ?>
  <main>
    <h1>Detalle del asesor</h1>
    <p class="muted">
      Curso: <strong><?php echo htmlspecialchars((string) ($curso['nombre_curso'] ?? '')); ?></strong>
    </p>

    <div class="grid">
      <div class="card">
        <div><strong><?php echo htmlspecialchars((string) ($asesor['nombre'] ?? '')); ?></strong></div>
        <div class="muted">CC <?php echo htmlspecialchars((string) ($asesor['cedula'] ?? '')); ?></div>
        <div style="margin-top:10px">
          <span class="pill"><?php echo htmlspecialchars((string) ($asesor['estado_capacitacion'] ?? '')); ?></span>
          <span class="pill">Progreso: <?php echo (int) ($asesor['progreso_porcentaje'] ?? 0); ?>%</span>
        </div>
        <div class="muted" style="margin-top:10px">
          Asignación: <?php echo htmlspecialchars((string) ($asesor['fecha_asignacion'] ?? '')); ?><br>
          Nota: <?php echo htmlspecialchars((string) ($asesor['calificacion_obtenida'] ?? '')); ?><br>
          Completado: <?php echo htmlspecialchars((string) ($asesor['fecha_completado'] ?? '')); ?>
        </div>
        <div style="margin-top:12px">
          <a href="<?php echo htmlspecialchars(BASE_URL . '/index.php?c=coordinador&a=reporte&id=' . (int) ($curso['id_cursos'] ?? 0)); ?>">Volver al reporte</a>
        </div>
      </div>

      <div class="card">
        <h2 style="margin-top:0">Trazabilidad (timeline)</h2>
        <?php if (empty($timeline)): ?>
          <p class="muted">No hay actividad registrada todavía.</p>
        <?php else: ?>
          <div class="tl">
            <?php foreach ($timeline as $e): ?>
              <?php
              $tipo = (string) ($e['tipo'] ?? '');
              $ts = (string) ($e['ts'] ?? '');
              ?>
              <div class="evt">
                <div class="ts muted"><?php echo htmlspecialchars($ts); ?></div>
                <div class="evtBody">
                  <?php if ($tipo === 'leccion'): ?>
                    <div class="evtTitle">Lección completada</div>
                    <div><?php echo htmlspecialchars((string) ($e['titulo'] ?? '')); ?></div>
                    <div class="muted">Módulo: <?php echo (int) ($e['id_modulo'] ?? 0); ?></div>
                  <?php elseif ($tipo === 'quiz_modulo'): ?>
                    <div class="evtTitle">Quiz de módulo</div>
                    <div class="muted">
                      Módulo <?php echo (int) ($e['id_modulo'] ?? 0); ?> ·
                      <?php echo (int) ($e['correctas'] ?? 0); ?>/<?php echo (int) ($e['total'] ?? 0); ?> ·
                      <?php echo !empty($e['aprobado']) ? 'Aprobado' : 'Reprobado'; ?>
                    </div>
                  <?php elseif ($tipo === 'evaluacion_final'): ?>
                    <div class="evtTitle">Evaluación final</div>
                    <div class="muted">
                      Resultado: <?php echo htmlspecialchars((string) ($e['resultado'] ?? '')); ?> ·
                      Puntaje: <?php echo htmlspecialchars((string) ($e['puntaje'] ?? '')); ?>/10
                    </div>
                  <?php else: ?>
                    <div class="evtTitle">Evento</div>
                    <div class="muted"><?php echo htmlspecialchars($tipo); ?></div>
                  <?php endif; ?>
                </div>
              </div>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
      </div>
    </div>
  </main>
</body>
</html>

