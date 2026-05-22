<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Mis cursos</title>
  <link rel="stylesheet" href="<?php echo htmlspecialchars(BASE_URL . '/assets/css/coordinador_index.css'); ?>">
</head>
<body>
  <?php
  /**
   * @var array<int, array<string, mixed>> $cursos
   * @var array<int, array{modo: string, n_permitidos: int}> $accesoPorCurso
   * @var bool $migracionAccesoOk
   * @var string|null $mensaje
   * @var string|null $error
   */
  $navActive = 'coord_index';
  require BASE_PATH . '/views/auth/header.php';
  $cursos = is_array($cursos ?? null) ? $cursos : [];
  $accesoPorCurso = is_array($accesoPorCurso ?? null) ? $accesoPorCurso : [];
  $migracionAccesoOk = !empty($migracionAccesoOk);
  ?>
  <main>
    <h1>Cursos a su cargo</h1>
    <?php if (!empty($mensaje)): ?>
      <p class="flash-ok"><?php echo htmlspecialchars($mensaje); ?></p>
    <?php endif; ?>
    <?php if (!empty($error)): ?>
      <p class="flash-err"><?php echo htmlspecialchars($error); ?></p>
    <?php endif; ?>
    <?php if (count($cursos) === 0): ?>
      <p>Aún no tiene cursos asignados. El administrador debe designarlo en cada curso.</p>
    <?php else: ?>
      <ul class="list">
        <?php foreach ($cursos as $curso): ?>
          <?php
          $desc = trim((string) ($curso['descripcion'] ?? ''));
          $idCurso = (int) ($curso['id_cursos'] ?? 0);
          $acc = $accesoPorCurso[$idCurso] ?? ['modo' => 'publico', 'n_permitidos' => 0];
          $modoAcceso = (string) ($acc['modo'] ?? 'publico');
          $esRestringido = $modoAcceso === 'restringido';
          ?>
          <li class="curso-card">
            <div class="curso-row">
              <div class="curso-row-main">
                <a class="curso-titulo" href="<?php echo htmlspecialchars(BASE_URL . '/index.php?c=coordinador&a=curso&id=' . $idCurso); ?>">
                  <?php echo htmlspecialchars((string) ($curso['nombre_curso'] ?? '')); ?>
                </a>
                <?php if (!empty($migracionAccesoOk)): ?>
                  <span class="chip-acceso <?php echo $esRestringido ? 'chip-acceso--restr' : 'chip-acceso--pub'; ?>">
                    <?php echo $esRestringido
                      ? 'Restringido (' . (int) ($acc['n_permitidos'] ?? 0) . ')'
                      : 'Público'; ?>
                  </span>
                <?php endif; ?>
              </div>
              <div class="curso-row-actions">
                <?php if (!empty($migracionAccesoOk)): ?>
                  <button type="button" class="btn-asesores btn-acceso" data-open-acceso="<?php echo $idCurso; ?>">Acceso asesores</button>
                <?php endif; ?>
                <button type="button" class="btn-asesores" data-open-asesores="<?php echo $idCurso; ?>">Asesores</button>
              </div>
            </div>
            <div class="curso-desc">
              <span class="curso-desc-label">Descripción del curso</span>
              <?php if ($desc !== ''): ?>
                <div class="curso-desc-body"><?php echo nl2br(htmlspecialchars($desc)); ?></div>
              <?php else: ?>
                <div class="curso-desc-body curso-desc-vacio">Sin descripción. Puede editarla en el panel del administrador al gestionar el curso.</div>
              <?php endif; ?>
            </div>
          </li>
        <?php endforeach; ?>
      </ul>
    <?php endif; ?>
  </main>

  <div class="modal" id="asesores-modal" aria-hidden="true">
    <div class="modal-backdrop" data-close-modal="asesores"></div>
    <div class="modal-card" role="dialog" aria-modal="true" aria-labelledby="asesores-modal-title">
      <div class="modal-head">
        <strong id="asesores-modal-title">Asesores inscritos</strong>
        <button type="button" class="btn-x" data-close-modal="asesores" aria-label="Cerrar">×</button>
      </div>
      <div class="modal-body" id="asesores-modal-body">
        <p class="muted">Cargando…</p>
      </div>
    </div>
  </div>

  <div class="modal" id="acceso-modal" aria-hidden="true">
    <div class="modal-backdrop" data-close-modal="acceso"></div>
    <div class="modal-card" role="dialog" aria-modal="true" aria-labelledby="acceso-modal-title">
      <div class="modal-head">
        <strong id="acceso-modal-title">Acceso de asesores</strong>
        <button type="button" class="btn-x" data-close-modal="acceso" aria-label="Cerrar">×</button>
      </div>
      <div class="modal-body" id="acceso-modal-body">
        <p class="muted">Cargando…</p>
      </div>
    </div>
  </div>

  <script>
  (function () {
    function byId(id) { return document.getElementById(id); }
    var asesoresUrlBase = <?php echo json_encode(BASE_URL . '/index.php?c=coordinador&a=asesores&id_curso='); ?>;
    var accesoUrlBase = <?php echo json_encode(BASE_URL . '/index.php?c=coordinador&a=acceso_asesores_form&id_curso='); ?>;
    var modals = {
      asesores: { el: byId('asesores-modal'), body: byId('asesores-modal-body') },
      acceso: { el: byId('acceso-modal'), body: byId('acceso-modal-body') }
    };
    var openBtn = null;
    var openKey = null;

    function focusFirst(modalEl) {
      if (!modalEl) return;
      var first = modalEl.querySelector('button:not([disabled]), [href], input:not([disabled]), select:not([disabled]), textarea:not([disabled])');
      if (first && typeof first.focus === 'function') first.focus();
    }

    function openModal(key) {
      var m = modals[key];
      if (!m || !m.el) return;
      Object.keys(modals).forEach(function (k) {
        if (modals[k].el) {
          modals[k].el.setAttribute('aria-hidden', 'true');
          modals[k].el.classList.remove('open');
        }
      });
      openKey = key;
      m.el.setAttribute('aria-hidden', 'false');
      m.el.classList.add('open');
      setTimeout(function () { focusFirst(m.el); }, 0);
    }

    function closeModals() {
      Object.keys(modals).forEach(function (k) {
        if (modals[k].el) {
          modals[k].el.setAttribute('aria-hidden', 'true');
          modals[k].el.classList.remove('open');
        }
      });
      if (openBtn && typeof openBtn.focus === 'function') openBtn.focus();
      openKey = null;
    }

    function loadInto(key, url) {
      var m = modals[key];
      if (!m || !m.body) return;
      m.body.innerHTML = '<p class="muted">Cargando…</p>';
      openModal(key);
      fetch(url, { credentials: 'same-origin' })
        .then(function (r) {
          if (!r.ok) {
            return Promise.resolve('<p class="muted">No se pudo cargar (HTTP ' + r.status + ').</p>');
          }
          return r.text();
        })
        .then(function (html) {
          m.body.innerHTML = html;
          if (key === 'acceso') {
            initAccesoAsesoresModal(m.body);
          }
        })
        .catch(function () { m.body.innerHTML = '<p class="muted">No se pudo cargar.</p>'; });
    }

    function syncAccesoListaVisible(root) {
      if (!root) return;
      var wrap = root.querySelector('#acceso-lista-wrap');
      if (!wrap) return;
      var radioRestr = root.querySelector('input[name="acceso_asesores"][value="restringido"]');
      var show = radioRestr && radioRestr.checked;
      wrap.hidden = !show;
      wrap.classList.toggle('acceso-lista-wrap--open', show);
    }

    function initAccesoAsesoresModal(root) {
      syncAccesoListaVisible(root);
      var form = root.querySelector('#acceso-asesores-form');
      if (!form) return;
      form.querySelectorAll('input[name="acceso_asesores"]').forEach(function (radio) {
        radio.addEventListener('change', function () {
          syncAccesoListaVisible(root);
        });
      });
    }

    var accesoBody = modals.acceso.body;
    if (accesoBody) {
      accesoBody.addEventListener('change', function (e) {
        if (e.target && e.target.name === 'acceso_asesores') {
          syncAccesoListaVisible(accesoBody);
        }
      });
      accesoBody.addEventListener('click', function (e) {
        var form = accesoBody.querySelector('#acceso-asesores-form');
        if (!form) return;
        if (e.target.closest('[data-acceso-select-all]')) {
          e.preventDefault();
          form.querySelectorAll('.acceso-check-input').forEach(function (cb) {
            cb.checked = true;
          });
          return;
        }
        if (e.target.closest('[data-acceso-select-none]')) {
          e.preventDefault();
          form.querySelectorAll('.acceso-check-input').forEach(function (cb) {
            cb.checked = false;
          });
        }
      });
    }

    document.addEventListener('click', function (e) {
      var bAses = e.target.closest('[data-open-asesores]');
      if (bAses) {
        openBtn = bAses;
        loadInto('asesores', asesoresUrlBase + encodeURIComponent(bAses.getAttribute('data-open-asesores')));
        return;
      }
      var bAcc = e.target.closest('[data-open-acceso]');
      if (bAcc) {
        openBtn = bAcc;
        loadInto('acceso', accesoUrlBase + encodeURIComponent(bAcc.getAttribute('data-open-acceso')));
        return;
      }
      if (e.target.closest('[data-close-modal]')) {
        closeModals();
      }
    });

    document.addEventListener('keydown', function (e) {
      if (e.key !== 'Escape') return;
      if (openKey && modals[openKey].el && modals[openKey].el.classList.contains('open')) {
        e.preventDefault();
        closeModals();
      }
    });
  })();
  </script>
</body>
</html>
