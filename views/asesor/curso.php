<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?php echo htmlspecialchars($curso['nombre_curso'] ?? ''); ?></title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="<?php echo htmlspecialchars(BASE_URL . '/assets/css/asesor_curso.css'); ?>">
</head>
<body>
  <?php
  /**
   * @var array<string, mixed> $curso
   * @var array<string, mixed> $asignacion
   * @var array<int, array<string, mixed>> $modulos
   * @var array<int, array<int, array<string, mixed>>> $leccionesPorModulo
   * @var array<string, mixed> $cursoEvalEstado
   */
  $curso = is_array($curso ?? null) ? $curso : [];
  $asignacion = is_array($asignacion ?? null) ? $asignacion : [];
  $cursoEvalEstado = is_array($cursoEvalEstado ?? null) ? $cursoEvalEstado : ['available' => false, 'reason' => 'missing'];
  ?>
  <nav class="topnav">
    <span><?php echo htmlspecialchars($curso['nombre_curso'] ?? ''); ?></span>
    <a href="<?php echo htmlspecialchars(BASE_URL . '/index.php?c=asesor&a=index'); ?>">Mis cursos</a>
    <a href="<?php echo htmlspecialchars(BASE_URL . '/index.php?c=auth&a=logout'); ?>">Salir</a>
  </nav>
  <main>
    <?php if (!empty($mensaje)): ?>
      <p class="flash-ok"><?php echo htmlspecialchars($mensaje); ?></p>
    <?php endif; ?>
    <?php if (!empty($error)): ?>
      <p class="flash-err"><?php echo htmlspecialchars($error); ?></p>
    <?php endif; ?>
    <div class="container-fluid px-0">
      <div class="row g-3">
        <aside class="col-12 col-lg-4">
          <div class="card shadow-sm mb-3">
            <div class="card-body">
              <div class="d-flex align-items-start justify-content-between gap-2">
                <div>
                  <div class="text-muted small">Curso</div>
                  <div class="fw-semibold"><?php echo htmlspecialchars($curso['nombre_curso'] ?? ''); ?></div>
                </div>
                <span class="badge text-bg-secondary"><?php echo htmlspecialchars((string) ($asignacion['estado_capacitacion'] ?? '')); ?></span>
              </div>
              <?php if (!empty($insigniaCursoCompletado) && is_array($insigniaCursoCompletado)): ?>
                <div class="mt-2">
                  <span class="badge text-bg-success">Insignia otorgada</span>
                  <div class="small text-muted mt-1">
                    <?php echo htmlspecialchars((string) ($insigniaCursoCompletado['tipo'] ?? 'curso_completado')); ?>
                    <?php if (!empty($insigniaCursoCompletado['otorgada_en'])): ?>
                      · <?php echo htmlspecialchars((string) $insigniaCursoCompletado['otorgada_en']); ?>
                    <?php endif; ?>
                  </div>
                </div>
              <?php endif; ?>
              <div class="mt-2 text-muted small">
                <?php echo nl2br(htmlspecialchars((string) ($curso['descripcion'] ?? ''))); ?>
              </div>
              <div class="mt-3">
                <div class="progress" role="progressbar" aria-label="Progreso del curso" aria-valuenow="<?php echo (int) ($asignacion['progreso_porcentaje'] ?? 0); ?>" aria-valuemin="0" aria-valuemax="100">
                  <div class="progress-bar" style="width: <?php echo min(100, (int) ($asignacion['progreso_porcentaje'] ?? 0)); ?>%"></div>
                </div>
                <div class="small text-muted mt-1">Progreso general: <?php echo (int) ($asignacion['progreso_porcentaje'] ?? 0); ?>%</div>
              </div>
            </div>
          </div>

          <?php if (empty($modulos)): ?>
            <div class="card shadow-sm">
              <div class="card-body">Aún no hay módulos publicados.</div>
            </div>
          <?php else: ?>
            <div class="accordion" id="accModulos">
              <?php foreach ($modulos as $m): ?>
                <?php
                $idModulo = (int) ($m['id_modulo'] ?? 0);
                $lecs = $leccionesPorModulo[$idModulo] ?? [];
                $st = $moduloEstado[$idModulo] ?? ['progreso' => 0, 'quiz_activo' => false, 'quiz_aprobado' => false, 'can_quiz' => false, 'total' => 0, 'completadas' => 0];
                $collapseId = 'mod_' . $idModulo;
                $modAbierto = (int) ($idModuloAcordeonAbierto ?? 0) === $idModulo;
                ?>
                <div class="accordion-item">
                  <h2 class="accordion-header" id="h_<?php echo $collapseId; ?>">
                    <button class="accordion-button <?php echo $modAbierto ? '' : 'collapsed'; ?>" type="button" data-bs-toggle="collapse" data-bs-target="#c_<?php echo $collapseId; ?>" aria-expanded="<?php echo $modAbierto ? 'true' : 'false'; ?>" aria-controls="c_<?php echo $collapseId; ?>">
                      <div class="w-100">
                        <div class="d-flex justify-content-between align-items-center gap-2">
                          <span class="fw-semibold"><?php echo htmlspecialchars((string) ($m['titulo'] ?? '')); ?></span>
                          <span class="small text-muted"><?php echo (int) ($st['progreso'] ?? 0); ?>%</span>
                        </div>
                        <div class="progress mt-2" style="height: 6px;">
                          <div class="progress-bar" style="width: <?php echo min(100, (int) ($st['progreso'] ?? 0)); ?>%"></div>
                        </div>
                        <div class="small text-muted mt-1">
                          <?php echo (int) ($st['completadas'] ?? 0); ?> / <?php echo (int) ($st['total'] ?? 0); ?> clases
                          <?php if (!empty($st['quiz_activo'])): ?>
                            · Quiz <?php echo !empty($st['quiz_aprobado']) ? 'aprobado' : 'pendiente'; ?>
                          <?php endif; ?>
                        </div>
                      </div>
                    </button>
                  </h2>
                  <div id="c_<?php echo $collapseId; ?>" class="accordion-collapse collapse <?php echo $modAbierto ? 'show' : ''; ?>" aria-labelledby="h_<?php echo $collapseId; ?>" data-bs-parent="#accModulos">
                    <div class="accordion-body">
                      <?php if (empty($lecs)): ?>
                        <div class="text-muted">No hay clases en este módulo.</div>
                      <?php else: ?>
                        <div class="list-group">
                          <?php foreach ($lecs as $L): ?>
                            <?php
                            $idLeccion = (int) ($L['id_leccion'] ?? 0);
                            $done = isset($leccionesCompletadasSet[$idLeccion]);
                            $isSel = (int) ($idLeccionSeleccionada ?? 0) === $idLeccion;
                            ?>
                            <a
                              id="asesor-leccion-<?php echo $idLeccion; ?>"
                              class="list-group-item list-group-item-action d-flex justify-content-between align-items-center <?php echo $isSel ? 'active' : ''; ?>"
                              href="<?php echo htmlspecialchars(BASE_URL . '/index.php?c=asesor&a=curso&id=' . (int) $asignacion['id_asignacion'] . '&id_leccion=' . $idLeccion); ?>"
                            >
                              <span><?php echo htmlspecialchars((string) ($L['titulo_leccion'] ?? '')); ?></span>
                              <?php if ($done): ?>
                                <span class="badge text-bg-success">✓</span>
                              <?php else: ?>
                                <span class="badge text-bg-secondary">Pendiente</span>
                              <?php endif; ?>
                            </a>
                          <?php endforeach; ?>
                        </div>
                      <?php endif; ?>

                      <?php if (!empty($st['quiz_activo'])): ?>
                        <div class="mt-3 d-grid">
                          <button
                            type="button"
                            class="btn <?php echo !empty($st['can_quiz']) ? 'btn-primary' : 'btn-secondary'; ?>"
                            data-open-quiz="<?php echo $idModulo; ?>"
                            <?php echo !empty($st['can_quiz']) ? '' : 'disabled'; ?>
                          >
                            Presentar quiz del módulo
                          </button>
                          <?php if (empty($st['can_quiz'])): ?>
                            <div class="small text-muted mt-1">Completa todas las clases para habilitar el quiz.</div>
                          <?php endif; ?>
                        </div>
                      <?php endif; ?>
                    </div>
                  </div>
                </div>
              <?php endforeach; ?>
            </div>
          <?php endif; ?>
        </aside>

        <section class="col-12 col-lg-8">
          <div class="card shadow-sm">
            <div class="card-body">
              <?php if (empty($leccionSeleccionada)): ?>
                <div class="text-muted">Selecciona una clase en la columna izquierda.</div>
              <?php else: ?>
                <h2 class="h5 mb-3"><?php echo htmlspecialchars((string) ($leccionSeleccionada['titulo_leccion'] ?? '')); ?></h2>
                <?php
                $img = (string) ($leccionSeleccionada['imagen_path'] ?? '');
                $imgTxt = (string) ($leccionSeleccionada['imagen_texto'] ?? '');
                $vid = (string) ($leccionSeleccionada['video_path'] ?? '');
                $pdf = (string) ($leccionSeleccionada['pdf_path'] ?? '');
                $ruta = (string) ($leccionSeleccionada['ruta_video'] ?? '');
                $idLeccionSel = (int) ($leccionSeleccionada['id_leccion'] ?? 0);
                $yaCompleta = $idLeccionSel > 0 && !empty($leccionesCompletadasSet) && isset($leccionesCompletadasSet[$idLeccionSel]);
                $tieneImg = $img !== '';
                $tieneVid = $vid !== '';
                $mostrarBotonMarcar = !$tieneImg && !$tieneVid;
                ?>
                <?php if (!empty($leccionSeleccionada['contenido'])): ?>
                  <div class="mb-3">
                    <?php echo nl2br(htmlspecialchars((string) $leccionSeleccionada['contenido'])); ?>
                  </div>
                <?php endif; ?>
                <?php if ($img !== '' && $vid !== ''): ?>
                  <div class="row g-3 mb-3">
                    <div class="col-12 col-md-6">
                      <div class="flip-img" role="button" tabindex="0" aria-label="Ver imagen interactiva" data-flip-track="1">
                        <div class="flip-inner">
                          <div class="flip-front">
                            <img class="img-fluid rounded w-100" src="<?php echo htmlspecialchars(BASE_URL . '/' . str_replace('\\', '/', $img)); ?>" alt="">
                          </div>
                          <div class="flip-back rounded">
                            <?php echo nl2br(htmlspecialchars($imgTxt !== '' ? $imgTxt : 'Sin texto configurado para esta imagen.')); ?>
                          </div>
                        </div>
                      </div>
                      <div class="small text-muted mt-2">Haz clic en la imagen para girarla.</div>
                    </div>
                    <div class="col-12 col-md-6">
                      <video class="w-100 rounded" src="<?php echo htmlspecialchars(BASE_URL . '/' . str_replace('\\', '/', $vid)); ?>" controls data-video-track="1"></video>
                    </div>
                  </div>
                <?php else: ?>
                  <?php if ($img !== ''): ?>
                    <div class="mb-3">
                      <div class="flip-img" role="button" tabindex="0" aria-label="Ver imagen interactiva" data-flip-track="1">
                        <div class="flip-inner">
                          <div class="flip-front">
                            <img class="img-fluid rounded w-100" src="<?php echo htmlspecialchars(BASE_URL . '/' . str_replace('\\', '/', $img)); ?>" alt="">
                          </div>
                          <div class="flip-back rounded">
                            <?php echo nl2br(htmlspecialchars($imgTxt !== '' ? $imgTxt : 'Sin texto configurado para esta imagen.')); ?>
                          </div>
                        </div>
                      </div>
                      <div class="small text-muted mt-2">Haz clic en la imagen para girarla.</div>
                    </div>
                  <?php endif; ?>
                  <?php if ($vid !== ''): ?>
                    <div class="mb-3">
                      <video class="w-100 rounded" src="<?php echo htmlspecialchars(BASE_URL . '/' . str_replace('\\', '/', $vid)); ?>" controls data-video-track="1"></video>
                    </div>
                  <?php endif; ?>
                <?php endif; ?>
                <?php if (($img === '') && ($vid === '') && ($ruta !== '')): ?>
                  <div class="mb-3">
                    <a class="btn btn-outline-secondary" target="_blank" rel="noopener" href="<?php echo htmlspecialchars(BASE_URL . '/' . str_replace('\\', '/', $ruta)); ?>">Descargar / abrir material</a>
                  </div>
                <?php endif; ?>
                <?php if ($pdf !== ''): ?>
                  <div class="mb-3">
                    <a class="btn btn-outline-primary" target="_blank" rel="noopener" href="<?php echo htmlspecialchars(BASE_URL . '/' . str_replace('\\', '/', $pdf)); ?>">Abrir PDF de apoyo</a>
                  </div>
                <?php endif; ?>

                <form id="form-completar" method="post" action="<?php echo htmlspecialchars(BASE_URL . '/index.php?c=asesor&a=leccion_completar'); ?>">
                  <input type="hidden" name="id_asignacion" value="<?php echo (int) $asignacion['id_asignacion']; ?>">
                  <input type="hidden" name="id_leccion" value="<?php echo $idLeccionSel; ?>">
                  <?php if ($yaCompleta): ?>
                    <div class="mt-2"><span class="badge text-bg-success">Completada ✓</span></div>
                  <?php elseif ($mostrarBotonMarcar): ?>
                    <button type="submit" class="btn btn-success">Marcar como vista</button>
                  <?php else: ?>
                    <div class="alert alert-light border mb-0 small text-muted">
                      <?php if ($tieneImg && $tieneVid): ?>
                        Gira la imagen (clic) y termina de ver el video para marcar la clase como vista.
                      <?php elseif ($tieneImg): ?>
                        Haz clic en la imagen para girarla y marcar la clase como vista.
                      <?php else: ?>
                        Termina de ver el video para marcar la clase como vista.
                      <?php endif; ?>
                    </div>
                  <?php endif; ?>
                </form>
              <?php endif; ?>
            </div>
          </div>

          <?php if (($asignacion['estado_capacitacion'] ?? '') === 'evaluacion_pendiente'): ?>
            <div class="mt-3">
              <?php
              $cursoEvalEstado = is_array($cursoEvalEstado ?? null) ? $cursoEvalEstado : ['available' => false, 'reason' => 'missing'];
              $cursoEvalAvailable = !empty($cursoEvalEstado['available']);
              $cursoEvalReason = (string) ($cursoEvalEstado['reason'] ?? 'missing');
              ?>
              <?php if ($cursoEvalAvailable): ?>
                <a class="btn btn-outline-primary" href="<?php echo htmlspecialchars(BASE_URL . '/index.php?c=asesor&a=evaluacion&id=' . (int) $asignacion['id_asignacion']); ?>">Realizar evaluación final del curso</a>
              <?php else: ?>
                <button class="btn btn-outline-secondary" type="button" disabled>Realizar evaluación final del curso</button>
                <div class="small text-muted mt-2">
                  <?php if ($cursoEvalReason === 'new_inactive'): ?>
                    El coordinador ya cargó la evaluación final, pero aún no la ha habilitado.
                  <?php elseif ($cursoEvalReason === 'new_incomplete'): ?>
                    El coordinador aún no completa la evaluación final del curso.
                  <?php elseif ($cursoEvalReason === 'manual_no_asignado'): ?>
                    El coordinador aún no le ha asignado una evaluación.
                  <?php elseif ($cursoEvalReason === 'manual_incomplete'): ?>
                    La evaluación asignada aún no tiene todas las preguntas listas.
                  <?php elseif ($cursoEvalReason === 'aleatorio_sin_preguntas'): ?>
                    El coordinador aún no ha creado suficientes preguntas para la evaluación.
                  <?php else: ?>
                    El coordinador aún no ha cargado preguntas para este curso.
                  <?php endif; ?>
                </div>
              <?php endif; ?>
            </div>
          <?php endif; ?>
        </section>
      </div>
    </div>

    <?php if (!empty($quizDataPorModulo)): ?>
      <?php foreach ($quizDataPorModulo as $idModulo => $qd): ?>
        <?php
        $items = $qd['items'] ?? [];
        $st = $moduloEstado[(int) $idModulo] ?? ['can_quiz' => false];
        ?>
        <div class="modal fade" id="quizModal_<?php echo (int) $idModulo; ?>" tabindex="-1" aria-hidden="true">
          <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content">
              <div class="modal-header">
                <h5 class="modal-title">Evaluación del módulo</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
              </div>
              <div class="modal-body">
                <?php if (empty($st['can_quiz'])): ?>
                  <div class="alert alert-secondary">Completa todas las clases del módulo para habilitar la evaluación.</div>
                <?php else: ?>
                  <p class="text-muted">Debe responder correctamente todas las preguntas para aprobar el módulo.</p>
                  <form method="post" action="<?php echo htmlspecialchars(BASE_URL . '/index.php?c=asesor&a=modulo_quiz_enviar'); ?>">
                    <input type="hidden" name="id_asignacion" value="<?php echo (int) $asignacion['id_asignacion']; ?>">
                    <input type="hidden" name="id_modulo" value="<?php echo (int) $idModulo; ?>">
                    <?php foreach ($items as $it): ?>
                      <?php $p = $it['pregunta']; $ops = $it['opciones']; ?>
                      <div class="mb-4">
                        <div class="fw-semibold mb-2"><?php echo nl2br(htmlspecialchars((string) ($p['enunciado'] ?? ''))); ?></div>
                        <?php foreach ($ops as $o): ?>
                          <?php
                          $idP = (int) ($p['id_pregunta_modulo'] ?? 0);
                          $name = 'p_' . $idP;
                          $idO = (int) ($o['id_opcion'] ?? 0);
                          $img = (string) ($o['imagen_path'] ?? '');
                          $txt = (string) ($o['texto'] ?? '');
                          ?>
                          <label class="d-block border rounded p-2 mb-2">
                            <input class="form-check-input me-2" type="radio" name="<?php echo htmlspecialchars($name); ?>" value="<?php echo $idO; ?>" required>
                            <?php if ($img !== ''): ?>
                              <img src="<?php echo htmlspecialchars(BASE_URL . '/' . $img); ?>" alt="" style="max-width: 320px; display: block;" class="mt-2 rounded">
                            <?php else: ?>
                              <span><?php echo htmlspecialchars($txt); ?></span>
                            <?php endif; ?>
                          </label>
                        <?php endforeach; ?>
                      </div>
                    <?php endforeach; ?>
                    <button class="btn btn-primary" type="submit">Enviar</button>
                  </form>
                <?php endif; ?>
              </div>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    <?php endif; ?>

  </main>
  <?php
  $jsNeedFlip = false;
  $jsNeedVideoEnd = false;
  $jsYaCompleta = false;
  if (!empty($leccionSeleccionada)) {
      $jsNeedFlip = ((string) ($leccionSeleccionada['imagen_path'] ?? '')) !== '';
      $jsNeedVideoEnd = ((string) ($leccionSeleccionada['video_path'] ?? '')) !== '';
      $idLecJs = (int) ($leccionSeleccionada['id_leccion'] ?? 0);
      $jsYaCompleta = $idLecJs > 0 && !empty($leccionesCompletadasSet) && isset($leccionesCompletadasSet[$idLecJs]);
  }
  ?>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script>
  (function () {
    document.addEventListener('click', function (e) {
      var btn = e.target.closest('[data-open-quiz]');
      if (!btn) return;
      var id = btn.getAttribute('data-open-quiz');
      if (!id) return;
      var el = document.getElementById('quizModal_' + id);
      if (!el) return;
      var modal = bootstrap.Modal.getOrCreateInstance(el);
      modal.show();
    });

    function toggleFlip(el) {
      el.classList.toggle('flipped');
    }

    document.addEventListener('click', function (e) {
      var flip = e.target.closest('.flip-img');
      if (!flip) return;
      toggleFlip(flip);
    });

    document.addEventListener('keydown', function (e) {
      if (e.key !== 'Enter' && e.key !== ' ') return;
      var flip = e.target.closest('.flip-img');
      if (!flip) return;
      e.preventDefault();
      toggleFlip(flip);
    });

    // Auto-completar según material: solo imagen (voltear), solo video (ended), imagen+video (ambos)
    var videoVisto = false;
    var imagenVolteada = false;
    var yaCompleta = <?php echo $jsYaCompleta ? 'true' : 'false'; ?>;
    var needFlip = <?php echo $jsNeedFlip ? 'true' : 'false'; ?>;
    var needVideoEnd = <?php echo $jsNeedVideoEnd ? 'true' : 'false'; ?>;
    var autoInteractivo = needFlip || needVideoEnd;
    var enviando = false;

    function intentarCompletar() {
      if (!autoInteractivo || yaCompleta || enviando) return;
      if (needFlip && !imagenVolteada) return;
      if (needVideoEnd && !videoVisto) return;
      var form = document.getElementById('form-completar');
      if (!form) return;
      enviando = true;
      fetch(form.action, { method: 'POST', body: new FormData(form), credentials: 'same-origin' })
        .then(function () { window.location.reload(); })
        .catch(function () { enviando = false; });
    }

    var v = document.querySelector('video[data-video-track="1"]');
    if (v) {
      v.addEventListener('ended', function () {
        videoVisto = true;
        intentarCompletar();
      });
    }
    var flipTrack = document.querySelector('.flip-img[data-flip-track="1"]');
    if (flipTrack) {
      flipTrack.addEventListener('click', function () {
        imagenVolteada = true;
        intentarCompletar();
      });
      flipTrack.addEventListener('keydown', function (e) {
        if (e.key === 'Enter' || e.key === ' ') {
          imagenVolteada = true;
          intentarCompletar();
        }
      });
    }
  })();
  </script>
  <script>
  document.addEventListener('DOMContentLoaded', function () {
    var mid = <?php echo (int) ($idModuloAcordeonAbierto ?? 0); ?>;
    var lid = <?php echo (int) ($idLeccionSeleccionada ?? 0); ?>;
    requestAnimationFrame(function () {
      if (lid) {
        var a = document.getElementById('asesor-leccion-' + lid);
        if (a) {
          a.scrollIntoView({ block: 'nearest', behavior: 'instant' });
          return;
        }
      }
      if (mid) {
        var h = document.getElementById('h_mod_' + mid);
        if (h) {
          h.scrollIntoView({ block: 'nearest', behavior: 'instant' });
        }
      }
    });
  });
  </script>
</body>
</html>
