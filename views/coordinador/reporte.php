<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Reporte — <?php echo htmlspecialchars((string) ($curso['nombre_curso'] ?? '')); ?></title>
  <link rel="stylesheet" href="<?php echo htmlspecialchars(BASE_URL . '/assets/css/coordinador_reporte.css'); ?>">
</head>
<body>
  <?php $navActive = 'coord_reporte'; require BASE_PATH . '/views/auth/header.php'; ?>
  <main>
    <div class="coord-context-toolbar" role="navigation" aria-label="Acciones del reporte">
      <span><?php echo htmlspecialchars((string) ($curso['nombre_curso'] ?? '')); ?></span>
      <a href="<?php echo htmlspecialchars(BASE_URL . '/index.php?c=coordinador&a=reporte_csv&id=' . (int) ($curso['id_cursos'] ?? 0)); ?>">Descargar CSV</a>
      <a href="<?php echo htmlspecialchars(BASE_URL . '/index.php?c=coordinador&a=reporte_pdf&id=' . (int) ($curso['id_cursos'] ?? 0)); ?>" target="_blank" rel="noopener">Imprimir / PDF</a>
      <a href="<?php echo htmlspecialchars(BASE_URL . '/index.php?c=coordinador&a=curso&id=' . (int) ($curso['id_cursos'] ?? 0)); ?>">Volver al curso</a>
    </div>
    <h1>Reporte</h1>

    <?php if (!empty($mensaje)): ?>
      <p class="flash-ok"><?php echo htmlspecialchars($mensaje); ?></p>
    <?php endif; ?>
    <?php if (!empty($error)): ?>
      <p class="flash-err"><?php echo htmlspecialchars($error); ?></p>
    <?php endif; ?>

    <div class="toolbar" role="search">
      <input id="q" type="search" placeholder="Buscar por cédula o nombre…" autocomplete="off">
      <select id="estado">
        <option value="">(todos)</option>
        <?php foreach (['pendiente','en_progreso','evaluacion_pendiente','completado'] as $e): ?>
          <option value="<?php echo htmlspecialchars($e); ?>"><?php echo htmlspecialchars($e); ?></option>
        <?php endforeach; ?>
      </select>
    </div>

    <?php if (empty($filas)): ?>
      <p class="muted">Aún no hay asesores inscritos.</p>
    <?php else: ?>
      <div class="table-wrap">
        <table id="t">
          <thead>
            <tr>
              <th>Asesor</th>
              <th>Estado</th>
              <th>Curso</th>
              <th>Módulos</th>
              <th>Quiz</th>
              <th>Evaluación final</th>
              <th>Fechas</th>
              <th></th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($filas as $f): ?>
              <?php
              $ced = (string) ($f['cedula_asesor'] ?? '');
              $nom = trim((string) ($f['nombre_asesor'] ?? '')) ?: '(Sin nombre)';
              $est = (string) ($f['estado_capacitacion'] ?? '');
              ?>
              <tr data-ced="<?php echo htmlspecialchars($ced, ENT_QUOTES); ?>" data-nom="<?php echo htmlspecialchars($nom, ENT_QUOTES); ?>" data-est="<?php echo htmlspecialchars($est, ENT_QUOTES); ?>">
                <td>
                  <strong><?php echo htmlspecialchars($nom); ?></strong><br>
                  <span class="muted">CC <?php echo htmlspecialchars($ced); ?></span>
                </td>
                <td><span class="pill"><?php echo htmlspecialchars($est !== '' ? $est : '—'); ?></span></td>
                <td><?php echo (int) ($f['progreso_porcentaje'] ?? 0); ?>%</td>
                <td><?php echo (int) ($f['modulos_completos'] ?? 0); ?> / <?php echo (int) ($f['modulos_total'] ?? 0); ?></td>
                <td><?php echo (int) ($f['quices_aprobados'] ?? 0); ?> / <?php echo (int) ($f['quices_activos'] ?? 0); ?></td>
                <td>
                  <?php if (!empty($f['evaluacion_resultado'])): ?>
                    <span class="pill"><?php echo htmlspecialchars((string) $f['evaluacion_resultado']); ?></span>
                    <div class="muted"><?php echo htmlspecialchars((string) $f['evaluacion_puntaje']); ?> / 10</div>
                  <?php else: ?>
                    <span class="muted">—</span>
                  <?php endif; ?>
                </td>
                <td class="muted">
                  Asignación: <?php echo htmlspecialchars((string) ($f['fecha_asignacion'] ?? '')); ?><br>
                  Completado: <?php echo htmlspecialchars((string) ($f['fecha_completado'] ?? '')); ?>
                </td>
                <td>
                  <a href="<?php echo htmlspecialchars(BASE_URL . '/index.php?c=coordinador&a=asesor_detalle&id=' . (int) ($curso['id_cursos'] ?? 0) . '&cedula=' . rawurlencode($ced)); ?>">Ver detalle</a>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php endif; ?>
  </main>
  <script>
  (function () {
    var q = document.getElementById('q');
    var estado = document.getElementById('estado');
    var rows = Array.prototype.slice.call(document.querySelectorAll('#t tbody tr'));
    function norm(s){ return (s||'').toString().toLowerCase(); }
    function apply(){
      var term = norm(q && q.value);
      var est = (estado && estado.value) || '';
      rows.forEach(function (r) {
        var ok = true;
        if (term) {
          var ced = norm(r.getAttribute('data-ced'));
          var nom = norm(r.getAttribute('data-nom'));
          ok = ced.indexOf(term) !== -1 || nom.indexOf(term) !== -1;
        }
        if (ok && est) {
          ok = (r.getAttribute('data-est') || '') === est;
        }
        r.style.display = ok ? '' : 'none';
      });
    }
    if (q) q.addEventListener('input', apply);
    if (estado) estado.addEventListener('change', apply);
  })();
  </script>
</body>
</html>

