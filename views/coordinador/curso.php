<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?php echo htmlspecialchars($curso['nombre_curso'] ?? ''); ?></title>
  <link rel="stylesheet" href="<?php echo htmlspecialchars(BASE_URL . '/assets/css/coordinador_curso.css'); ?>">
</head>
<body>
  <?php $navActive = 'coord_curso'; require BASE_PATH . '/views/auth/header.php'; ?>
  <main>
    <?php
    // #region agent log
    @file_put_contents(
        BASE_PATH . DIRECTORY_SEPARATOR . 'debug-4338d8.log',
        json_encode(
            [
                'sessionId' => '4338d8',
                'runId' => 'post-fix',
                'hypothesisId' => 'H2',
                'location' => 'views/coordinador/curso.php:subnav',
                'message' => 'curso subnav rendered',
                'data' => [
                    'idCurso' => (int) ($curso['id_cursos'] ?? 0),
                    'hasSecondaryTopnav' => false,
                    'usesCoordContextToolbar' => true,
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
    <div class="coord-context-toolbar" role="navigation" aria-label="Acciones del curso">
      <span><?php echo htmlspecialchars($curso['nombre_curso'] ?? ''); ?></span>
      <button type="button" class="btn-asesores" data-open-asesores="<?php echo (int) $curso['id_cursos']; ?>">Asesores</button>
      <a href="<?php echo htmlspecialchars(BASE_URL . '/index.php?c=coordinador&a=preguntas&id=' . (int) $curso['id_cursos']); ?>">Evaluación</a>
      <a href="<?php echo htmlspecialchars(BASE_URL . '/index.php?c=coordinador&a=reporte&id=' . (int) $curso['id_cursos']); ?>">Reporte</a>
    </div>
    <?php if (!empty($mensaje)): ?>
      <p class="flash-ok"><?php echo htmlspecialchars($mensaje); ?></p>
    <?php endif; ?>
    <?php if (!empty($error)): ?>
      <p class="flash-err"><?php echo htmlspecialchars($error); ?></p>
    <?php endif; ?>

    <div class="layout-2col">
      <section class="col-left">
        <div class="panel">
          <h2>Módulos y cursos</h2>
          <form method="post" action="<?php echo htmlspecialchars(BASE_URL . '/index.php?c=coordinador&a=modulo_crear'); ?>">
            <input type="hidden" name="id_curso" value="<?php echo (int) $curso['id_cursos']; ?>">
            <label for="titulo_modulo">Título del módulo</label>
            <input type="text" id="titulo_modulo" name="titulo_modulo" maxlength="150" required placeholder="Ej.: 1. Fundamentos y Seguridad en el Endpoint">
            <button type="submit">Crear módulo</button>
          </form>
        </div>

        <?php if (empty($modulos)): ?>
          <div class="panel"><p>Aún no hay módulos creados.</p></div>
        <?php else: ?>
          <?php foreach ($modulos as $modIdx => $m): ?>
            <?php
            $idModulo = (int) ($m['id_modulo'] ?? 0);
            $lecs = $leccionesPorModulo[$idModulo] ?? [];
            $q = $quizPorModulo[$idModulo] ?? ['config' => ['preguntas_requeridas' => 1, 'activo' => 0], 'slots' => []];
            $cfg = $q['config'];
            $slots = $q['slots'];
            $req = (int) ($cfg['preguntas_requeridas'] ?? 1);
            $act = (int) ($cfg['activo'] ?? 0) === 1;
            $nLecciones = count($lecs);
            $idModAbierto = (int) ($idModuloAbierto ?? 0);
            $modExpandido = $idModAbierto === $idModulo;
            ?>
            <details class="panel modulo-card modulo-details" id="coord-mod-<?php echo $idModulo; ?>" <?php echo $modExpandido ? 'open' : ''; ?>>
              <summary class="modulo-accordion-summary">
                <span class="modulo-accordion-title">
                  <span class="modulo-accordion-chev" aria-hidden="true"></span>
                  <h3>Módulo <?php echo (int) ($m['modulo'] ?? 0); ?> — <?php echo htmlspecialchars((string) ($m['titulo'] ?? '')); ?></h3>
                </span>
                <span class="modulo-accordion-meta muted small"><?php echo $nLecciones; ?> curso<?php echo $nLecciones === 1 ? '' : 's'; ?></span>
              </summary>

              <div class="modulo-accordion-body">
                <div class="modulo-toolbar">
                  <div class="modulo-actions">
                    <button type="button" class="btn-secondary" data-open-quiz="<?php echo $idModulo; ?>">Colocar quiz</button>
                    <a
                      class="btn-danger"
                      href="<?php echo htmlspecialchars(BASE_URL . '/index.php?c=coordinador&a=modulo_eliminar&id_modulo=' . $idModulo . '&id_curso=' . (int) $curso['id_cursos']); ?>"
                      onclick="return confirm('¿Eliminar este módulo y todos sus cursos?');"
                    >Eliminar</a>
                  </div>
                </div>

                <h4>Crear curso / clase</h4>
                <form method="post" action="<?php echo htmlspecialchars(BASE_URL . '/index.php?c=coordinador&a=leccion_crear'); ?>">
                  <input type="hidden" name="id_curso" value="<?php echo (int) $curso['id_cursos']; ?>">
                  <input type="hidden" name="id_modulo" value="<?php echo $idModulo; ?>">
                  <label>Título</label>
                  <input type="text" name="titulo_leccion" maxlength="150" required placeholder="Ej.: 1.1 Gestión de Identidad y Autenticación Robusta">
                  <button type="submit">Crear</button>
                </form>

                <h4>Lista de cursos</h4>
                <?php if (empty($lecs)): ?>
                  <p class="muted">Aún no hay cursos en este módulo.</p>
                <?php else: ?>
                  <?php $idLeccRes = (int) ($idLeccionResaltada ?? 0); ?>
                  <ul class="curso-list">
                    <?php foreach ($lecs as $L): ?>
                      <?php
                      $idLeccion = (int) ($L['id_leccion'] ?? 0);
                      $img = (string) ($L['imagen_path'] ?? '');
                      $vid = (string) ($L['video_path'] ?? '');
                      $imgTexto = (string) ($L['imagen_texto'] ?? '');
                      ?>
                      <li class="curso-item<?php echo $idLeccion === $idLeccRes ? ' curso-item--active' : ''; ?>" id="coord-curso-<?php echo $idLeccion; ?>">
                        <div class="curso-title">
                          <strong><?php echo htmlspecialchars((string) ($L['titulo_leccion'] ?? '')); ?></strong>
                          <small class="muted">Orden <?php echo (int) ($L['orden'] ?? 0); ?></small>
                        </div>
                        <div class="curso-actions">
                          <button
                            type="button"
                            class="btn-secondary"
                            data-configurar-curso="1"
                            data-id-curso="<?php echo (int) $curso['id_cursos']; ?>"
                            data-id-modulo="<?php echo $idModulo; ?>"
                            data-id-leccion="<?php echo $idLeccion; ?>"
                            data-titulo="<?php echo htmlspecialchars((string) ($L['titulo_leccion'] ?? ''), ENT_QUOTES); ?>"
                            data-contenido="<?php echo htmlspecialchars((string) ($L['contenido'] ?? ''), ENT_QUOTES); ?>"
                            data-imagen="<?php echo htmlspecialchars($img, ENT_QUOTES); ?>"
                            data-imagen-texto="<?php echo htmlspecialchars($imgTexto, ENT_QUOTES); ?>"
                            data-video="<?php echo htmlspecialchars($vid, ENT_QUOTES); ?>"
                          >Configurar curso</button>
                          <a
                            class="link-danger"
                            href="<?php echo htmlspecialchars(BASE_URL . '/index.php?c=coordinador&a=leccion_eliminar&id_leccion=' . $idLeccion . '&id_curso=' . (int) $curso['id_cursos']); ?>"
                            onclick="return confirm('¿Eliminar este curso?');"
                          >Eliminar</a>
                        </div>
                      </li>
                    <?php endforeach; ?>
                  </ul>
                <?php endif; ?>
              </div>
            </details>

            <div class="modal" id="quiz-modal-<?php echo $idModulo; ?>" aria-hidden="true">
              <div class="modal-backdrop" data-close-quiz="<?php echo $idModulo; ?>"></div>
              <div class="modal-card" role="dialog" aria-modal="true">
                <div class="modal-head">
                  <strong>Evaluación del módulo</strong>
                  <button type="button" class="btn-x" data-close-quiz="<?php echo $idModulo; ?>">×</button>
                </div>
                <div class="modal-body">
                  <form method="post" action="<?php echo htmlspecialchars(BASE_URL . '/index.php?c=coordinador&a=modulo_quiz_guardar'); ?>" enctype="multipart/form-data">
                    <input type="hidden" name="id_curso" value="<?php echo (int) $curso['id_cursos']; ?>">
                    <input type="hidden" name="id_modulo" value="<?php echo $idModulo; ?>">

                    <label>Activar evaluación</label>
                    <input type="checkbox" name="quiz_activo" value="1" <?php echo $act ? 'checked' : ''; ?>>

                    <label>Preguntas requeridas</label>
                    <select name="preguntas_requeridas">
                      <?php foreach ([1, 2, 3] as $n): ?>
                        <option value="<?php echo $n; ?>" <?php echo $req === $n ? 'selected' : ''; ?>><?php echo $n; ?></option>
                      <?php endforeach; ?>
                    </select>

                    <?php for ($i = 1; $i <= 3; $i++): ?>
                      <?php
                      $slot = $slots[$i] ?? null;
                      $p = $slot['pregunta'] ?? null;
                      $ops = $slot['opciones'] ?? [];
                      $tipo = (string) ($p['tipo'] ?? 'vf');
                      $enun = (string) ($p['enunciado'] ?? '');
                      $byClave = [];
                      foreach ($ops as $o) {
                          $byClave[(string) $o['clave']] = $o;
                      }
                      ?>
                      <fieldset data-q-fieldset="<?php echo $i; ?>">
                        <legend>Pregunta <?php echo $i; ?></legend>
                        <label>Tipo</label>
                        <select name="q_tipo[<?php echo $i; ?>]" data-q-tipo="<?php echo $i; ?>">
                          <option value="">(sin usar)</option>
                          <option value="imagen_par" <?php echo $tipo === 'imagen_par' ? 'selected' : ''; ?>>Imagen (correcto/incorrecto)</option>
                          <option value="vf" <?php echo $tipo === 'vf' ? 'selected' : ''; ?>>Verdadero / Falso</option>
                          <option value="multi" <?php echo $tipo === 'multi' ? 'selected' : ''; ?>>Selección múltiple (A–D)</option>
                        </select>

                        <label>Enunciado</label>
                        <textarea name="q_enunciado[<?php echo $i; ?>]" rows="2" placeholder="Ej.: Seleccione la acción correcta" data-q-enunciado="<?php echo $i; ?>"><?php echo htmlspecialchars($enun); ?></textarea>

                        <div class="quiz-tipo">
                          <div data-q-block="<?php echo $i; ?>" data-q-kind="vf">
                            <strong>V/F</strong>
                            <select name="q_vf_correcta[<?php echo $i; ?>]">
                              <option value="true">Verdadero</option>
                              <option value="false">Falso</option>
                            </select>
                          </div>
                          <div data-q-block="<?php echo $i; ?>" data-q-kind="multi">
                            <strong>Multi (A–D)</strong>
                            <div>
                              <input type="text" name="q_multi_a[<?php echo $i; ?>]" placeholder="Opción A" value="<?php echo htmlspecialchars((string) (($byClave['a']['texto'] ?? '') ?: '')); ?>">
                              <input type="text" name="q_multi_b[<?php echo $i; ?>]" placeholder="Opción B" value="<?php echo htmlspecialchars((string) (($byClave['b']['texto'] ?? '') ?: '')); ?>">
                              <input type="text" name="q_multi_c[<?php echo $i; ?>]" placeholder="Opción C" value="<?php echo htmlspecialchars((string) (($byClave['c']['texto'] ?? '') ?: '')); ?>">
                              <input type="text" name="q_multi_d[<?php echo $i; ?>]" placeholder="Opción D" value="<?php echo htmlspecialchars((string) (($byClave['d']['texto'] ?? '') ?: '')); ?>">
                            </div>
                            <label>Correcta</label>
                            <select name="q_multi_correcta[<?php echo $i; ?>]">
                              <?php foreach (['a','b','c','d'] as $k): ?>
                                <option value="<?php echo $k; ?>"><?php echo strtoupper($k); ?></option>
                              <?php endforeach; ?>
                            </select>
                          </div>
                          <div data-q-block="<?php echo $i; ?>" data-q-kind="imagen_par">
                            <strong>Imagen par</strong>
                            <div>
                              <label>Imagen correcta</label>
                              <input type="file" name="q_img_ok_<?php echo $i; ?>" accept=".jpg,.jpeg,.png,image/jpeg,image/png">
                              <label>Imagen incorrecta</label>
                              <input type="file" name="q_img_bad_<?php echo $i; ?>" accept=".jpg,.jpeg,.png,image/jpeg,image/png">
                            </div>
                          </div>
                        </div>
                      </fieldset>
                    <?php endfor; ?>

                    <button type="submit">Guardar evaluación</button>
                  </form>
                </div>
              </div>
            </div>
          <?php endforeach; ?>
        <?php endif; ?>
      </section>

      <section class="col-right">
        <div class="panel">
          <h2>Configurar curso</h2>
          <p class="muted" id="cfg-hint">Selecciona un curso en la columna izquierda y presiona “Configurar curso”.</p>

          <form id="cfg-form" class="hidden" method="post" action="<?php echo htmlspecialchars(BASE_URL . '/index.php?c=coordinador&a=leccion_actualizar'); ?>" enctype="multipart/form-data">
            <input type="hidden" name="id_curso" value="<?php echo (int) $curso['id_cursos']; ?>">
            <input type="hidden" name="id_modulo" id="cfg-id-modulo" value="">
            <input type="hidden" name="id_leccion" id="cfg-id-leccion" value="">

            <label>Título</label>
            <input type="text" name="titulo_leccion" id="cfg-titulo" maxlength="150" required>

            <label>¿De qué se trata este curso? (obligatorio)</label>
            <textarea name="contenido" id="cfg-contenido" rows="8" required placeholder="Describe el contenido del curso…"></textarea>

            <label>Imagen (opcional)</label>
            <input type="file" name="imagen" accept=".jpg,.jpeg,.png,image/jpeg,image/png">
            <div class="preview" id="cfg-prev-img"></div>

            <div id="cfg-imagen-texto-wrap" class="hidden">
              <label>Texto para mostrar al dar clic en la imagen (opcional)</label>
              <textarea name="imagen_texto" id="cfg-imagen-texto" rows="4" placeholder="Ej.: Explicación / concepto clave / mensaje de ciberseguridad…"></textarea>
            </div>

            <label>Video MP4 (opcional)</label>
            <input type="file" name="video" accept=".mp4,video/mp4">
            <div class="preview" id="cfg-prev-vid"></div>

            <button type="submit">Guardar configuración</button>
          </form>
        </div>
      </section>
    </div>
  </main>

  <div class="modal" id="asesores-modal" aria-hidden="true">
    <div class="modal-backdrop" data-close-asesores="1"></div>
    <div class="modal-card" role="dialog" aria-modal="true">
      <div class="modal-head">
        <strong>Asesores</strong>
        <button type="button" class="btn-x" data-close-asesores="1">×</button>
      </div>
      <div class="modal-body" id="asesores-modal-body">
        <p class="muted">Cargando…</p>
      </div>
    </div>
  </div>

  <script>
  (function () {
    function byId(id) { return document.getElementById(id); }
    var asesoresUrlBase = <?php echo json_encode(BASE_URL . '/index.php?c=coordinador&a=asesores&id_curso='); ?>;
    var asesoresModal = byId('asesores-modal');
    var asesoresBody = byId('asesores-modal-body');
    if (asesoresModal && asesoresBody) {
      function openAsesoresModal() {
        asesoresModal.setAttribute('aria-hidden', 'false');
        asesoresModal.classList.add('open');
      }
      function closeAsesoresModal() {
        asesoresModal.setAttribute('aria-hidden', 'true');
        asesoresModal.classList.remove('open');
      }
      document.addEventListener('click', function (e) {
        var b = e.target.closest('[data-open-asesores]');
        if (b) {
          var idCurso = b.getAttribute('data-open-asesores');
          asesoresBody.innerHTML = '<p class="muted">Cargando…</p>';
          openAsesoresModal();
          fetch(asesoresUrlBase + encodeURIComponent(idCurso), { credentials: 'same-origin' })
            .then(function (r) {
              // #region agent log
              fetch('http://127.0.0.1:7783/ingest/030f6a8d-8a77-4dd0-9f5e-4b5381ffb3e1', { method: 'POST', headers: { 'Content-Type': 'application/json', 'X-Debug-Session-Id': '335d18' }, body: JSON.stringify({ sessionId: '335d18', runId: 'post-fix', hypothesisId: 'H7', location: 'views/coordinador/curso.php:fetch', message: 'asesores fetch response', data: { status: r.status, ok: r.ok, idCurso: idCurso }, timestamp: Date.now() }) }).catch(function () {});
              // #endregion
              if (!r.ok) {
                return Promise.resolve('<p class="muted">No se pudo cargar (HTTP ' + r.status + ').</p>');
              }
              return r.text();
            })
            .then(function (html) {
              // #region agent log
              fetch('http://127.0.0.1:7783/ingest/030f6a8d-8a77-4dd0-9f5e-4b5381ffb3e1', { method: 'POST', headers: { 'Content-Type': 'application/json', 'X-Debug-Session-Id': '335d18' }, body: JSON.stringify({ sessionId: '335d18', runId: 'post-fix', hypothesisId: 'H7', location: 'views/coordinador/curso.php:html', message: 'asesores body length', data: { len: html ? html.length : 0, head: html ? html.slice(0, 80) : '' }, timestamp: Date.now() }) }).catch(function () {});
              // #endregion
              asesoresBody.innerHTML = html;
            })
            .catch(function () { asesoresBody.innerHTML = '<p class="muted">No se pudo cargar.</p>'; });
          return;
        }
        if (e.target.closest('[data-close-asesores]')) {
          closeAsesoresModal();
        }
      });
      document.addEventListener('keydown', function (e) {
        if (e.key !== 'Escape') return;
        if (asesoresModal.classList.contains('open')) {
          e.preventDefault();
          closeAsesoresModal();
        }
      });
    }
  })();
  </script>

  <script>
  (function () {
    function byId(id) { return document.getElementById(id); }

    var hint = byId('cfg-hint');
    var form = byId('cfg-form');
    var idModulo = byId('cfg-id-modulo');
    var idLeccion = byId('cfg-id-leccion');
    var titulo = byId('cfg-titulo');
    var contenido = byId('cfg-contenido');
    var prevImg = byId('cfg-prev-img');
    var prevVid = byId('cfg-prev-vid');
    var imgTxtWrap = byId('cfg-imagen-texto-wrap');
    var imgTxt = byId('cfg-imagen-texto');

    var modalOpen = null;
    function focusFirstField(modal) {
      if (!modal) return;
      var first = modal.querySelector('input:not([type="hidden"]):not([disabled]), select:not([disabled]), textarea:not([disabled]), button:not([disabled])');
      if (first && typeof first.focus === 'function') {
        first.focus();
      }
    }

    function closeModal(modal) {
      if (!modal) return;
      modal.setAttribute('aria-hidden', 'true');
      modal.classList.remove('open');
      if (modalOpen === modal) {
        modalOpen = null;
      }
    }

    function openModal(modal) {
      if (!modal) return;
      modal.setAttribute('aria-hidden', 'false');
      modal.classList.add('open');
      modalOpen = modal;
      // foco después de pintar
      setTimeout(function () { focusFirstField(modal); }, 0);
    }

    function showConfig(btn) {
      idModulo.value = btn.getAttribute('data-id-modulo') || '';
      idLeccion.value = btn.getAttribute('data-id-leccion') || '';
      titulo.value = btn.getAttribute('data-titulo') || '';
      contenido.value = btn.getAttribute('data-contenido') || '';

      var img = btn.getAttribute('data-imagen') || '';
      var vid = btn.getAttribute('data-video') || '';
      var imgTexto = btn.getAttribute('data-imagen-texto') || '';
      prevImg.innerHTML = img ? ('<small class="muted">Imagen actual:</small><br><a target="_blank" rel="noopener" href="<?php echo htmlspecialchars(BASE_URL . '/'); ?>' + img + '">Abrir imagen</a>') : '';
      prevVid.innerHTML = vid ? ('<small class="muted">Video actual:</small><br><a target="_blank" rel="noopener" href="<?php echo htmlspecialchars(BASE_URL . '/'); ?>' + vid + '">Abrir video</a>') : '';

      imgTxt.value = imgTexto || '';
      if (img || imgTexto) {
        imgTxtWrap.classList.remove('hidden');
      } else {
        imgTxtWrap.classList.add('hidden');
      }

      hint.classList.add('hidden');
      form.classList.remove('hidden');
      form.scrollIntoView({behavior: 'smooth', block: 'start'});

      var idC = btn.getAttribute('data-id-curso') || '';
      var idM = btn.getAttribute('data-id-modulo') || '';
      var idL = btn.getAttribute('data-id-leccion') || '';
      if (idC && idM && idL && window.history && window.history.replaceState) {
        var params = new URLSearchParams(window.location.search);
        params.set('c', 'coordinador');
        params.set('a', 'curso');
        params.set('id', idC);
        params.set('id_modulo', idM);
        params.set('id_leccion', idL);
        window.history.replaceState({}, '', window.location.pathname + '?' + params.toString());
      }
    }

    document.addEventListener('click', function (e) {
      var cfgBtn = e.target.closest('[data-configurar-curso]');
      if (cfgBtn) {
        e.preventDefault();
        showConfig(cfgBtn);
        return;
      }

      var openQuiz = e.target.closest('[data-open-quiz]');
      if (openQuiz) {
        var id = openQuiz.getAttribute('data-open-quiz');
        var modal = byId('quiz-modal-' + id);
        openModal(modal);
        return;
      }

      var closeQuiz = e.target.closest('[data-close-quiz]');
      if (closeQuiz) {
        var id2 = closeQuiz.getAttribute('data-close-quiz');
        var modal2 = byId('quiz-modal-' + id2);
        closeModal(modal2);
      }
    });

    // Mostrar textarea de texto cuando se elige una imagen nueva
    document.addEventListener('change', function (e) {
      if (!e.target || e.target.name !== 'imagen') return;
      if (e.target.files && e.target.files.length > 0) {
        imgTxtWrap.classList.remove('hidden');
      }
    });

    // Cerrar modal con ESC
    document.addEventListener('keydown', function (e) {
      if (e.key !== 'Escape') return;
      if (modalOpen) {
        e.preventDefault();
        closeModal(modalOpen);
      }
    });

    function setEnabledIn(el, enabled) {
      if (!el) return;
      var fields = el.querySelectorAll('input, select, textarea, button');
      for (var i = 0; i < fields.length; i++) {
        var f = fields[i];
        if (f && f.getAttribute('type') === 'hidden') continue;
        f.disabled = !enabled;
      }
    }

    function refreshQuizModal(modal) {
      if (!modal) return;
      var formQuiz = modal.querySelector('form');
      if (!formQuiz) return;

      var selReq = formQuiz.querySelector('select[name="preguntas_requeridas"]');
      var req = selReq ? parseInt(selReq.value || '1', 10) : 1;
      if (!(req >= 1 && req <= 3)) req = 1;

      for (var q = 1; q <= 3; q++) {
        var fs = formQuiz.querySelector('fieldset[data-q-fieldset="' + q + '"]');
        if (!fs) continue;

        var activeSlot = q <= req;
        fs.classList.toggle('hidden', !activeSlot);
        setEnabledIn(fs, activeSlot);

        if (!activeSlot) continue;

        // Enunciado siempre visible/habilitado
        var en = fs.querySelector('textarea[data-q-enunciado="' + q + '"]');
        if (en) en.disabled = false;

        var tipoSel = fs.querySelector('select[data-q-tipo="' + q + '"]');
        if (tipoSel) tipoSel.disabled = false;

        var tipo = tipoSel ? (tipoSel.value || '') : '';
        var blocks = fs.querySelectorAll('[data-q-block="' + q + '"]');
        for (var i = 0; i < blocks.length; i++) {
          var b = blocks[i];
          var kind = b.getAttribute('data-q-kind') || '';
          var show = (tipo !== '' && kind === tipo);
          b.classList.toggle('hidden', !show);
          setEnabledIn(b, show);
        }
      }
    }

    // Delegación: cambios dentro de cualquier modal de quiz
    document.addEventListener('change', function (e) {
      var t = e.target;
      if (!t) return;
      // Cambia preguntas requeridas
      if (t.matches && t.matches('select[name="preguntas_requeridas"]')) {
        var m = t.closest('.modal');
        if (m) refreshQuizModal(m);
      }
      // Cambia tipo
      if (t.matches && t.matches('select[data-q-tipo]')) {
        var m2 = t.closest('.modal');
        if (m2) refreshQuizModal(m2);
      }
    });

    // Al abrir modal, refrescar visibilidad según selección actual
    (function () {
      var prevOpenModal = openModal;
      openModal = function (modal) {
        prevOpenModal(modal);
        refreshQuizModal(modal);
      };
    })();
  })();
  </script>
  <script>
  document.addEventListener('DOMContentLoaded', function () {
    var lid = <?php echo (int) ($idLeccionResaltada ?? 0); ?>;
    var mid = <?php echo (int) ($idModuloAbierto ?? 0); ?>;
    requestAnimationFrame(function () {
      if (lid) {
        var row = document.getElementById('coord-curso-' + lid);
        if (row) {
          row.scrollIntoView({ block: 'nearest', behavior: 'instant' });
          return;
        }
      }
      if (mid) {
        var det = document.getElementById('coord-mod-' + mid);
        if (det) {
          det.scrollIntoView({ block: 'nearest', behavior: 'instant' });
        }
      }
    });
  });
  </script>
</body>
</html>
