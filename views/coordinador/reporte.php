<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Reporte — <?php echo htmlspecialchars((string) ($curso['nombre_curso'] ?? '')); ?></title>
  <link rel="stylesheet" href="<?php echo htmlspecialchars(BASE_URL . '/assets/css/coordinador_reporte.css'); ?>">
</head>
<body>
  <?php
  $navActive = 'coord_reporte';
  require BASE_PATH . '/views/auth/header.php';

  $idCurso = (int) ($curso['id_cursos'] ?? 0);
  $filtroEmpresa = CoordinadorReporte::normalizarFiltroEmpresa($filtroEmpresa ?? ($_GET['empresa'] ?? ''));
  $empresasOpciones = [
      'onix' => 'Onix',
      'nextdata' => 'Nextdata',
      'tys' => 'TyS',
  ];
  $baseExport = BASE_URL . '/index.php?c=coordinador&a=%s&id=' . $idCurso;
  ?>
  <main>
    <div class="coord-context-toolbar" role="navigation" aria-label="Acciones del reporte">
      <span><?php echo htmlspecialchars((string) ($curso['nombre_curso'] ?? '')); ?></span>
      <a id="link-csv" href="<?php echo htmlspecialchars(sprintf($baseExport, 'reporte_csv')); ?>">Descargar CSV</a>
      <a id="link-pdf" href="<?php echo htmlspecialchars(sprintf($baseExport, 'reporte_pdf')); ?>" target="_blank" rel="noopener">Imprimir / PDF</a>
      <a href="<?php echo htmlspecialchars(BASE_URL . '/index.php?c=coordinador&a=curso&id=' . $idCurso); ?>">Volver al curso</a>
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
      <select id="estado" aria-label="Filtrar por estado">
        <option value="">Estado (todos)</option>
        <?php foreach (['pendiente','en_progreso','evaluacion_pendiente','completado'] as $e): ?>
          <option value="<?php echo htmlspecialchars($e); ?>"><?php echo htmlspecialchars($e); ?></option>
        <?php endforeach; ?>
      </select>
      <select id="empresa" aria-label="Filtrar por empresa">
        <option value="">Empresa (todas)</option>
        <?php foreach ($empresasOpciones as $key => $label): ?>
          <option value="<?php echo htmlspecialchars($key); ?>"<?php echo $filtroEmpresa === $key ? ' selected' : ''; ?>>
            <?php echo htmlspecialchars($label); ?>
          </option>
        <?php endforeach; ?>
      </select>
    </div>

    <?php if (empty($filas)): ?>
      <p class="muted">Aún no hay asesores inscritos<?php echo $filtroEmpresa !== '' ? ' para la empresa seleccionada' : ''; ?>.</p>
    <?php else: ?>
      <div class="table-wrap">
        <table id="t">
          <thead>
            <tr>
              <th>Asesor</th>
              <th>Estado</th>
              <th>Empresa</th>
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
              $empKey = (string) ($f['empresa'] ?? 'onix');
              $empLabel = (string) ($f['empresa_label'] ?? CoordinadorReporte::etiquetaEmpresa($empKey));
              ?>
              <tr
                data-ced="<?php echo htmlspecialchars($ced, ENT_QUOTES); ?>"
                data-nom="<?php echo htmlspecialchars($nom, ENT_QUOTES); ?>"
                data-est="<?php echo htmlspecialchars($est, ENT_QUOTES); ?>"
                data-emp="<?php echo htmlspecialchars($empKey, ENT_QUOTES); ?>"
              >
                <td>
                  <strong><?php echo htmlspecialchars($nom); ?></strong><br>
                  <span class="muted">CC <?php echo htmlspecialchars($ced); ?></span>
                </td>
                <td><span class="pill"><?php echo htmlspecialchars($est !== '' ? $est : '—'); ?></span></td>
                <td><span class="pill pill-empresa"><?php echo htmlspecialchars($empLabel); ?></span></td>
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
                  <a href="<?php echo htmlspecialchars(BASE_URL . '/index.php?c=coordinador&a=asesor_detalle&id=' . $idCurso . '&cedula=' . rawurlencode($ced)); ?>">Ver detalle</a>
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
    var empresa = document.getElementById('empresa');
    var linkPdf = document.getElementById('link-pdf');
    var linkCsv = document.getElementById('link-csv');
    var rows = Array.prototype.slice.call(document.querySelectorAll('#t tbody tr'));
    var basePdf = <?php echo json_encode(sprintf($baseExport, 'reporte_pdf'), JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>;
    var baseCsv = <?php echo json_encode(sprintf($baseExport, 'reporte_csv'), JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>;

    function norm(s) { return (s || '').toString().toLowerCase(); }

    function exportUrl(base) {
      var emp = (empresa && empresa.value) || '';
      return emp ? base + '&empresa=' + encodeURIComponent(emp) : base;
    }

    function syncExportLinks() {
      if (linkPdf) linkPdf.href = exportUrl(basePdf);
      if (linkCsv) linkCsv.href = exportUrl(baseCsv);
    }

    function apply() {
      var term = norm(q && q.value);
      var est = (estado && estado.value) || '';
      var emp = (empresa && empresa.value) || '';
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
        if (ok && emp) {
          ok = (r.getAttribute('data-emp') || '') === emp;
        }
        r.style.display = ok ? '' : 'none';
      });
      syncExportLinks();
      if (history.replaceState) {
        try {
          var url = new URL(window.location.href);
          if (emp) {
            url.searchParams.set('empresa', emp);
          } else {
            url.searchParams.delete('empresa');
          }
          history.replaceState({}, '', url.pathname + url.search);
        } catch (e) { /* ignore */ }
      }
    }

    if (q) q.addEventListener('input', apply);
    if (estado) estado.addEventListener('change', apply);
    if (empresa) empresa.addEventListener('change', apply);
    apply();
  })();
  </script>
</body>
</html>

