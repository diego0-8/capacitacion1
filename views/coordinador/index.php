<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Mis cursos</title>
  <link rel="stylesheet" href="<?php echo htmlspecialchars(BASE_URL . '/assets/css/coordinador_index.css'); ?>">
</head>
<body>
  <?php $navActive = 'coord_index'; require BASE_PATH . '/views/auth/header.php'; ?>
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
          <?php $desc = trim((string) ($curso['descripcion'] ?? '')); ?>
          <li class="curso-card">
            <div class="curso-row">
              <a class="curso-titulo" href="<?php echo htmlspecialchars(BASE_URL . '/index.php?c=coordinador&a=curso&id=' . (int) $curso['id_cursos']); ?>">
                <?php echo htmlspecialchars($curso['nombre_curso']); ?>
              </a>
              <button type="button" class="btn-asesores" data-open-asesores="<?php echo (int) $curso['id_cursos']; ?>">Asesores</button>
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
    var modal = byId('asesores-modal');
    var body = byId('asesores-modal-body');
    var openBtn = null;

    function focusFirst(modalEl) {
      if (!modalEl) return;
      var first = modalEl.querySelector('button:not([disabled]), [href], input:not([disabled]), select:not([disabled]), textarea:not([disabled])');
      if (first && typeof first.focus === 'function') first.focus();
    }

    function openModal() {
      modal.setAttribute('aria-hidden', 'false');
      modal.classList.add('open');
      setTimeout(function () { focusFirst(modal); }, 0);
    }

    function closeModal() {
      modal.setAttribute('aria-hidden', 'true');
      modal.classList.remove('open');
    }

    document.addEventListener('click', function (e) {
      var b = e.target.closest('[data-open-asesores]');
      if (b) {
        openBtn = b;
        var idCurso = b.getAttribute('data-open-asesores');
        body.innerHTML = '<p class="muted">Cargando…</p>';
        openModal();
        fetch(asesoresUrlBase + encodeURIComponent(idCurso), { credentials: 'same-origin' })
          .then(function (r) {
            // #region agent log
            fetch('http://127.0.0.1:7783/ingest/030f6a8d-8a77-4dd0-9f5e-4b5381ffb3e1', { method: 'POST', headers: { 'Content-Type': 'application/json', 'X-Debug-Session-Id': '335d18' }, body: JSON.stringify({ sessionId: '335d18', runId: 'post-fix', hypothesisId: 'H6', location: 'views/coordinador/index.php:fetch', message: 'asesores fetch response', data: { status: r.status, ok: r.ok, idCurso: idCurso }, timestamp: Date.now() }) }).catch(function () {});
            // #endregion
            if (!r.ok) {
              return Promise.resolve('<p class="muted">No se pudo cargar (HTTP ' + r.status + ').</p>');
            }
            return r.text();
          })
          .then(function (html) {
            // #region agent log
            fetch('http://127.0.0.1:7783/ingest/030f6a8d-8a77-4dd0-9f5e-4b5381ffb3e1', { method: 'POST', headers: { 'Content-Type': 'application/json', 'X-Debug-Session-Id': '335d18' }, body: JSON.stringify({ sessionId: '335d18', runId: 'post-fix', hypothesisId: 'H6', location: 'views/coordinador/index.php:html', message: 'asesores body length', data: { len: html ? html.length : 0, head: html ? html.slice(0, 80) : '' }, timestamp: Date.now() }) }).catch(function () {});
            // #endregion
            body.innerHTML = html;
          })
          .catch(function () { body.innerHTML = '<p class="muted">No se pudo cargar.</p>'; });
        return;
      }

      if (e.target.closest('[data-close-asesores]')) {
        closeModal();
      }
    });

    document.addEventListener('keydown', function (e) {
      if (e.key !== 'Escape') return;
      if (modal.classList.contains('open')) {
        e.preventDefault();
        closeModal();
        if (openBtn && typeof openBtn.focus === 'function') openBtn.focus();
      }
    });
  })();
  </script>
</body>
</html>
