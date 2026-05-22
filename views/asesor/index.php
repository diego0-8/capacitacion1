<?php
/** @var array<int, array<string,mixed>> $items */
/** @var array<int, array<string,mixed>> $cursosInscripcion */
/** @var array<int, array<string,mixed>> $misCursosPendientes */
/** @var array<int, array<string,mixed>> $certificaciones */
/** @var array{pendientes:int,completados:int,disponibles_inscripcion:int} $stats */
/** @var string $tabInicial */
/** @var array<int, array<string,mixed>> $insigniasPorCurso */
/** @var string $nombreAsesorCompleto */
$stats = $stats ?? ['pendientes' => 0, 'completados' => 0, 'disponibles_inscripcion' => 0];
$tabInicial = $tabInicial ?? 'inicio';
$inscribirUrl = BASE_URL . '/index.php?c=asesor&a=inscribirse';
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Centro de capacitación</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="<?php echo htmlspecialchars(BASE_URL . '/assets/css/asesor_index.css'); ?>">
</head>
<body>
  <?php $navActive = 'asesor_index'; require BASE_PATH . '/views/auth/header.php'; ?>
  <main class="asesor-dashboard">
    <?php if (!empty($mensaje)): ?>
      <p class="flash-ok"><?php echo htmlspecialchars($mensaje); ?></p>
    <?php endif; ?>
    <?php if (!empty($error)): ?>
      <p class="flash-err"><?php echo htmlspecialchars($error); ?></p>
    <?php endif; ?>

    <div class="container-fluid px-0">
      <div class="row g-3">
        <aside class="col-12 col-md-4">
          <div class="card shadow-sm asesor-sidebar-card">
            <div class="card-body p-0">
              <div class="asesor-sidebar-head px-3 py-2 border-bottom">
                <div class="text-muted small">Centro de capacitación</div>
                <div class="fw-semibold">Mis apartados</div>
              </div>
              <nav class="list-group list-group-flush asesor-nav-apartados" id="asesor-nav-apartados" aria-label="Apartados">
                <button type="button" class="list-group-item list-group-item-action asesor-nav-apartado d-flex justify-content-between align-items-center" data-tab="inicio">
                  <span>Inicio</span>
                </button>
                <button type="button" class="list-group-item list-group-item-action asesor-nav-apartado d-flex justify-content-between align-items-center" data-tab="inscripciones">
                  <span>Inscripciones</span>
                  <span class="badge rounded-pill text-bg-primary"><?php echo (int) ($stats['disponibles_inscripcion'] ?? 0); ?></span>
                </button>
                <button type="button" class="list-group-item list-group-item-action asesor-nav-apartado d-flex justify-content-between align-items-center" data-tab="mis-cursos">
                  <span>Mis cursos</span>
                  <span class="badge rounded-pill text-bg-secondary"><?php echo (int) ($stats['pendientes'] ?? 0); ?></span>
                </button>
                <button type="button" class="list-group-item list-group-item-action asesor-nav-apartado d-flex justify-content-between align-items-center" data-tab="certificaciones">
                  <span>Certificaciones</span>
                  <span class="badge rounded-pill text-bg-success"><?php echo (int) ($stats['completados'] ?? 0); ?></span>
                </button>
              </nav>
            </div>
          </div>

          <div class="card shadow-sm mt-3 asesor-lista-card" id="asesor-lista-wrap" hidden>
            <div class="card-body p-0">
              <div class="px-3 py-2 border-bottom small fw-semibold text-muted" id="asesor-lista-titulo">Cursos</div>
              <div class="list-group list-group-flush asesor-lista-cursos" id="asesor-lista-cursos">
                <?php foreach ($cursosInscripcion as $c): ?>
                  <?php $idCurso = (int) ($c['id_cursos'] ?? 0); ?>
                  <button
                    type="button"
                    class="list-group-item list-group-item-action asesor-lista-item"
                    data-tab-list="inscripciones"
                    data-item-id="<?php echo $idCurso; ?>"
                  >
                    <span class="asesor-lista-nombre"><?php echo htmlspecialchars((string) ($c['nombre_curso'] ?? '')); ?></span>
                    <?php if (!empty($c['nombre_coordinador'])): ?>
                      <span class="small text-muted d-block text-truncate"><?php echo htmlspecialchars((string) $c['nombre_coordinador']); ?></span>
                    <?php endif; ?>
                  </button>
                <?php endforeach; ?>

                <?php foreach ($misCursosPendientes as $it): ?>
                  <?php $idAsign = (int) ($it['id_asignacion'] ?? 0); ?>
                  <button
                    type="button"
                    class="list-group-item list-group-item-action asesor-lista-item"
                    data-tab-list="mis-cursos"
                    data-item-id="<?php echo $idAsign; ?>"
                  >
                    <span class="asesor-lista-nombre"><?php echo htmlspecialchars((string) ($it['nombre_curso'] ?? '')); ?></span>
                    <span class="small text-muted d-block">
                      <?php echo (int) ($it['progreso_porcentaje'] ?? 0); ?>% · <?php echo htmlspecialchars((string) ($it['estado_capacitacion'] ?? '')); ?>
                    </span>
                  </button>
                <?php endforeach; ?>

                <?php foreach ($certificaciones as $it): ?>
                  <?php $idAsign = (int) ($it['id_asignacion'] ?? 0); ?>
                  <button
                    type="button"
                    class="list-group-item list-group-item-action asesor-lista-item"
                    data-tab-list="certificaciones"
                    data-item-id="<?php echo $idAsign; ?>"
                  >
                    <span class="asesor-lista-nombre"><?php echo htmlspecialchars((string) ($it['nombre_curso'] ?? '')); ?></span>
                    <span class="badge text-bg-success mt-1">Completado</span>
                  </button>
                <?php endforeach; ?>
              </div>
              <p class="muted small px-3 py-3 mb-0 d-none" id="asesor-lista-empty">No hay cursos en esta sección.</p>
            </div>
          </div>
        </aside>

        <section class="col-12 col-md-8">
          <div class="asesor-panels">
            <!-- Inicio -->
            <div class="asesor-panel card shadow-sm" data-panel="inicio" id="panel-inicio">
              <div class="card-body">
                <p class="text-muted small mb-1">Bienvenido al CRM de capacitación</p>
                <h1 class="h4 mb-2">Hola, <?php echo htmlspecialchars($nombreAsesorCompleto !== '' ? $nombreAsesorCompleto : 'asesor'); ?></h1>
                <p class="text-muted mb-4">
                  Gestione sus inscripciones, avance en los cursos asignados y descargue certificados cuando complete cada capacitación.
                </p>
                <div class="row g-3 mb-4">
                  <div class="col-sm-4">
                    <div class="asesor-stat-card asesor-stat-card--pendiente">
                      <div class="asesor-stat-val"><?php echo (int) ($stats['pendientes'] ?? 0); ?></div>
                      <div class="asesor-stat-label">En progreso</div>
                      <button type="button" class="btn btn-sm btn-outline-primary mt-2" data-goto-tab="mis-cursos">Ir a mis cursos</button>
                    </div>
                  </div>
                  <div class="col-sm-4">
                    <div class="asesor-stat-card asesor-stat-card--cert">
                      <div class="asesor-stat-val"><?php echo (int) ($stats['completados'] ?? 0); ?></div>
                      <div class="asesor-stat-label">Certificados</div>
                      <button type="button" class="btn btn-sm btn-outline-success mt-2" data-goto-tab="certificaciones">Ver certificaciones</button>
                    </div>
                  </div>
                  <div class="col-sm-4">
                    <div class="asesor-stat-card asesor-stat-card--nuevo">
                      <div class="asesor-stat-val"><?php echo (int) ($stats['disponibles_inscripcion'] ?? 0); ?></div>
                      <div class="asesor-stat-label">Disponibles</div>
                      <button type="button" class="btn btn-sm btn-outline-primary mt-2" data-goto-tab="inscripciones">Ver inscripciones</button>
                    </div>
                  </div>
                </div>
                <div class="asesor-inicio-tip border rounded p-3 bg-light">
                  <strong class="d-block mb-1">¿Cómo empezar?</strong>
                  <ul class="small text-muted mb-0 ps-3">
                    <li>Revise <strong>Inscripciones</strong> para matricularse en cursos nuevos.</li>
                    <li>En <strong>Mis cursos</strong> continúe las capacitaciones pendientes.</li>
                    <li>En <strong>Certificaciones</strong> descargue el diploma al completar un curso.</li>
                  </ul>
                </div>
              </div>
            </div>

            <!-- Inscripciones -->
            <div class="asesor-panel card shadow-sm d-none" data-panel="inscripciones" id="panel-inscripciones">
              <div class="card-body">
                <div class="asesor-panel-empty text-muted" id="inscripciones-empty">
                  Seleccione un curso en la lista de la izquierda o no hay cursos disponibles para inscribirse.
                </div>
                <?php foreach ($cursosInscripcion as $c): ?>
                  <?php $idCurso = (int) ($c['id_cursos'] ?? 0); ?>
                  <div class="asesor-detalle d-none" data-detalle-tab="inscripciones" data-detalle-id="<?php echo $idCurso; ?>">
                    <h2 class="h5 mb-2"><?php echo htmlspecialchars((string) ($c['nombre_curso'] ?? '')); ?></h2>
                    <?php if (!empty($c['nombre_coordinador'])): ?>
                      <p class="meta mb-2">Coordinador: <?php echo htmlspecialchars((string) $c['nombre_coordinador']); ?></p>
                    <?php endif; ?>
                    <?php if (!empty($c['descripcion'])): ?>
                      <div class="mb-3"><?php echo nl2br(htmlspecialchars((string) $c['descripcion'])); ?></div>
                    <?php endif; ?>
                    <form method="post" action="<?php echo htmlspecialchars($inscribirUrl); ?>">
                      <input type="hidden" name="id_curso" value="<?php echo $idCurso; ?>">
                      <button class="cta" type="submit">Inscribirme</button>
                    </form>
                  </div>
                <?php endforeach; ?>
              </div>
            </div>

            <!-- Mis cursos -->
            <div class="asesor-panel card shadow-sm d-none" data-panel="mis-cursos" id="panel-mis-cursos">
              <div class="card-body">
                <div class="asesor-panel-empty text-muted" id="mis-cursos-empty">
                  Seleccione un curso en la lista de la izquierda o no tiene cursos pendientes.
                </div>
                <?php foreach ($misCursosPendientes as $it): ?>
                  <?php
                  $idAsign = (int) ($it['id_asignacion'] ?? 0);
                  $idCurso = (int) ($it['id_curso'] ?? 0);
                  $ins = $insigniasPorCurso[$idCurso] ?? null;
                  $pct = (int) ($it['progreso_porcentaje'] ?? 0);
                  ?>
                  <div class="asesor-detalle d-none" data-detalle-tab="mis-cursos" data-detalle-id="<?php echo $idAsign; ?>">
                    <div class="d-flex justify-content-between align-items-start gap-2 mb-2">
                      <h2 class="h5 mb-0"><?php echo htmlspecialchars((string) ($it['nombre_curso'] ?? '')); ?></h2>
                      <span class="estado"><?php echo htmlspecialchars((string) ($it['estado_capacitacion'] ?? '')); ?></span>
                    </div>
                    <div class="progress mb-2" role="progressbar" aria-valuenow="<?php echo $pct; ?>" aria-valuemin="0" aria-valuemax="100">
                      <div class="progress-bar" style="width: <?php echo min(100, $pct); ?>%"></div>
                    </div>
                    <p class="meta mb-2">Progreso: <?php echo $pct; ?>%</p>
                    <?php if (is_array($ins)): ?>
                      <p class="meta mb-2">
                        Insignia: <?php echo htmlspecialchars((string) ($ins['tipo'] ?? 'curso_completado')); ?>
                        <?php if (!empty($ins['otorgada_en'])): ?>
                          (<?php echo htmlspecialchars((string) $ins['otorgada_en']); ?>)
                        <?php endif; ?>
                      </p>
                    <?php endif; ?>
                    <?php if (!empty($it['descripcion'])): ?>
                      <div class="mb-3 text-muted small"><?php echo nl2br(htmlspecialchars((string) $it['descripcion'])); ?></div>
                    <?php endif; ?>
                    <a class="cta" href="<?php echo htmlspecialchars(BASE_URL . '/index.php?c=asesor&a=curso&id=' . $idAsign); ?>">Entrar al curso</a>
                  </div>
                <?php endforeach; ?>
              </div>
            </div>

            <!-- Certificaciones -->
            <div class="asesor-panel card shadow-sm d-none" data-panel="certificaciones" id="panel-certificaciones">
              <div class="card-body">
                <div class="asesor-panel-empty text-muted" id="certificaciones-empty">
                  Seleccione un curso completado en la lista o aún no tiene certificaciones.
                </div>
                <?php foreach ($certificaciones as $it): ?>
                  <?php
                  $idAsign = (int) ($it['id_asignacion'] ?? 0);
                  $idCurso = (int) ($it['id_curso'] ?? 0);
                  $ins = $insigniasPorCurso[$idCurso] ?? null;
                  $certUrl = BASE_URL . '/index.php?c=asesor&a=certificado&id=' . $idAsign;
                  ?>
                  <div class="asesor-detalle d-none" data-detalle-tab="certificaciones" data-detalle-id="<?php echo $idAsign; ?>">
                    <h2 class="h5 mb-2"><?php echo htmlspecialchars((string) ($it['nombre_curso'] ?? '')); ?></h2>
                    <p class="meta mb-2">Progreso: 100% · Completado</p>
                    <?php if (is_array($ins)): ?>
                      <p class="meta mb-2">
                        Insignia: <?php echo htmlspecialchars((string) ($ins['tipo'] ?? 'curso_completado')); ?>
                        <?php if (!empty($ins['otorgada_en'])): ?>
                          (<?php echo htmlspecialchars((string) $ins['otorgada_en']); ?>)
                        <?php endif; ?>
                      </p>
                    <?php endif; ?>
                    <?php if ($idAsign > 0): ?>
                      <button
                        type="button"
                        class="btn-certificado-verde"
                        data-cert-modal
                        data-curso="<?php echo htmlspecialchars((string) ($it['nombre_curso'] ?? '')); ?>"
                        data-cert-url="<?php echo htmlspecialchars($certUrl); ?>"
                      >Descargar certificado</button>
                    <?php endif; ?>
                  </div>
                <?php endforeach; ?>
              </div>
            </div>
          </div>
        </section>
      </div>
    </div>
  </main>

  <dialog id="certificado-modal" class="cert-dialog" aria-labelledby="cert-modal-title">
    <div class="cert-dialog-inner">
      <button type="button" class="cert-dialog-close" data-cert-close aria-label="Cerrar">&times;</button>
      <div class="cert-dialog-badge" aria-hidden="true">
        <svg viewBox="0 0 120 120" xmlns="http://www.w3.org/2000/svg" width="72" height="72">
          <defs>
            <linearGradient id="certGrad" x1="0%" y1="0%" x2="100%" y2="100%">
              <stop offset="0%" style="stop-color:#22c55e"/>
              <stop offset="100%" style="stop-color:#15803d"/>
            </linearGradient>
          </defs>
          <circle cx="60" cy="60" r="52" fill="url(#certGrad)" opacity="0.12"/>
          <circle cx="60" cy="60" r="44" fill="none" stroke="url(#certGrad)" stroke-width="3"/>
          <path d="M60 28 L72 52 L98 56 L78 74 L82 100 L60 86 L38 100 L42 74 L22 56 L48 52 Z" fill="url(#certGrad)" stroke="#14532d" stroke-width="1.2" stroke-linejoin="round"/>
        </svg>
      </div>
      <h2 id="cert-modal-title" class="cert-dialog-title">Curso completado</h2>
      <p class="cert-dialog-curso" id="cert-modal-curso"></p>
      <p class="cert-dialog-label">Asesor</p>
      <p class="cert-dialog-nombre"><?php echo htmlspecialchars($nombreAsesorCompleto); ?></p>
      <div class="cert-dialog-actions">
        <a class="btn-cert-descargar" id="cert-modal-descargar" href="#" target="_blank" rel="noopener">Descargar certificado</a>
      </div>
    </div>
  </dialog>

  <script>
    (function () {
      var tabInicial = <?php echo json_encode($tabInicial); ?>;
      var nav = document.getElementById('asesor-nav-apartados');
      var listaWrap = document.getElementById('asesor-lista-wrap');
      var listaItems = document.querySelectorAll('.asesor-lista-item');
      var listaEmpty = document.getElementById('asesor-lista-empty');
      var listaTitulo = document.getElementById('asesor-lista-titulo');
      var panels = document.querySelectorAll('.asesor-panel');

      var titulosLista = {
        inscripciones: 'Cursos disponibles',
        'mis-cursos': 'Mis cursos pendientes',
        certificaciones: 'Certificaciones'
      };

      function getItemsForTab(tab) {
        return Array.prototype.filter.call(listaItems, function (el) {
          return el.getAttribute('data-tab-list') === tab;
        });
      }

      function showPanel(tab) {
        panels.forEach(function (p) {
          var show = p.getAttribute('data-panel') === tab;
          p.classList.toggle('d-none', !show);
        });
      }

      function setNavActive(tab) {
        if (!nav) return;
        nav.querySelectorAll('.asesor-nav-apartado').forEach(function (btn) {
          var on = btn.getAttribute('data-tab') === tab;
          btn.classList.toggle('active', on);
        });
      }

      function hideAllDetalles(tab) {
        document.querySelectorAll('.asesor-detalle[data-detalle-tab="' + tab + '"]').forEach(function (d) {
          d.classList.add('d-none');
        });
      }

      function showDetalle(tab, id) {
        hideAllDetalles(tab);
        var empty = document.getElementById(
          tab === 'inscripciones' ? 'inscripciones-empty'
            : tab === 'mis-cursos' ? 'mis-cursos-empty'
            : 'certificaciones-empty'
        );
        if (!id) {
          if (empty) empty.classList.remove('d-none');
          return;
        }
        if (empty) empty.classList.add('d-none');
        var det = document.querySelector(
          '.asesor-detalle[data-detalle-tab="' + tab + '"][data-detalle-id="' + id + '"]'
        );
        if (det) det.classList.remove('d-none');
      }

      function setListaActive(item) {
        listaItems.forEach(function (el) {
          el.classList.toggle('active', el === item);
        });
      }

      function activateTab(tab) {
        setNavActive(tab);
        showPanel(tab);

        var showLista = tab !== 'inicio';
        if (listaWrap) {
          listaWrap.hidden = !showLista;
        }
        if (listaTitulo && titulosLista[tab]) {
          listaTitulo.textContent = titulosLista[tab];
        }

        listaItems.forEach(function (el) {
          el.hidden = el.getAttribute('data-tab-list') !== tab;
        });

        var visibles = getItemsForTab(tab);
        if (listaEmpty) {
          listaEmpty.classList.toggle('d-none', visibles.length > 0 || !showLista);
        }

        if (!showLista) return;

        if (visibles.length === 0) {
          setListaActive(null);
          showDetalle(tab, null);
          return;
        }
        var first = visibles[0];
        setListaActive(first);
        showDetalle(tab, first.getAttribute('data-item-id'));
      }

      if (nav) {
        nav.addEventListener('click', function (e) {
          var btn = e.target.closest('.asesor-nav-apartado');
          if (!btn) return;
          activateTab(btn.getAttribute('data-tab'));
        });
      }

      var listaCursos = document.getElementById('asesor-lista-cursos');
      if (listaCursos) listaCursos.addEventListener('click', function (e) {
        var item = e.target.closest('.asesor-lista-item');
        if (!item || item.hidden) return;
        var tab = item.getAttribute('data-tab-list');
        setListaActive(item);
        showDetalle(tab, item.getAttribute('data-item-id'));
      });

      document.querySelectorAll('[data-goto-tab]').forEach(function (btn) {
        btn.addEventListener('click', function () {
          activateTab(btn.getAttribute('data-goto-tab'));
        });
      });

      activateTab(tabInicial);

      var modal = document.getElementById('certificado-modal');
      var cursoEl = document.getElementById('cert-modal-curso');
      var link = document.getElementById('cert-modal-descargar');
      if (modal && cursoEl && link) {
        document.querySelectorAll('[data-cert-modal]').forEach(function (btn) {
          btn.addEventListener('click', function () {
            cursoEl.textContent = btn.getAttribute('data-curso') || '';
            link.href = btn.getAttribute('data-cert-url') || '#';
            modal.showModal();
          });
        });
        modal.querySelectorAll('[data-cert-close]').forEach(function (b) {
          b.addEventListener('click', function () { modal.close(); });
        });
        modal.addEventListener('click', function (e) {
          if (e.target === modal) modal.close();
        });
      }
    })();
  </script>
</body>
</html>
