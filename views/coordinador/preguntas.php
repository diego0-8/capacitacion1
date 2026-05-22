<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Evaluación final — <?php echo htmlspecialchars($curso['nombre_curso'] ?? ''); ?></title>
  <link rel="stylesheet" href="<?php echo htmlspecialchars(BASE_URL . '/assets/css/coordinador_preguntas.css'); ?>">
</head>
<body>
  <?php $navActive = 'coord_preguntas'; require BASE_PATH . '/views/auth/header.php'; ?>
  <main>
    <div class="coord-context-toolbar" role="navigation" aria-label="Contexto de evaluación">
      <span>Evaluación final del curso</span>
      <a href="<?php echo htmlspecialchars(BASE_URL . '/index.php?c=coordinador&a=curso&id=' . (int) ($curso['id_cursos'] ?? 0)); ?>">Volver al curso</a>
    </div>
    <h1><?php echo htmlspecialchars($curso['nombre_curso'] ?? ''); ?></h1>
    <?php if (!empty($mensaje)): ?>
      <p class="flash-ok"><?php echo htmlspecialchars($mensaje); ?></p>
    <?php endif; ?>
    <?php if (!empty($error)): ?>
      <p class="flash-err"><?php echo htmlspecialchars($error); ?></p>
    <?php endif; ?>

    <?php
    /**
     * @var array<string, mixed> $curso
     * @var string|null $evaluacionNombre
     * @var array<string, mixed> $cursoEvalConfig
     * @var array<int, array<string, mixed>> $cursoEvalSlots
     * @var int $cursoEvalMaxSlots
     * @var string|null $mensaje
     * @var string|null $error
     */
    $idCurso = (int) ($curso['id_cursos'] ?? 0);
    $evalNombre = trim((string) ($evaluacionNombre ?? ''));
    $tieneEvalNombre = $evalNombre !== '';
    $cursoEvalConfig = is_array($cursoEvalConfig ?? null) ? $cursoEvalConfig : ['preguntas_requeridas' => 1, 'activo' => 0];
    $cursoEvalSlots = is_array($cursoEvalSlots ?? null) ? $cursoEvalSlots : [];
    $cfg = $cursoEvalConfig;
    $slots = $cursoEvalSlots;
    $maxSlots = (int) ($cursoEvalMaxSlots ?? 10);
    if ($maxSlots < 1) $maxSlots = 10;
    $req = (int) ($cfg['preguntas_requeridas'] ?? 1);
    if ($req < 1) $req = 1;
    if ($req > $maxSlots) $req = $maxSlots;
    $act = (int) ($cfg['activo'] ?? 0) === 1;
    ?>

    <section class="eval-card" aria-label="Evaluación del curso">
      <div class="eval-card-row">
        <div>
          <h2 class="eval-title">Evaluación final del curso</h2>
          <div class="eval-meta">
            <span class="eval-name"><?php echo htmlspecialchars($tieneEvalNombre ? $evalNombre : 'Sin nombre'); ?></span>
            <span class="eval-dot">•</span>
            <span class="eval-count"><?php echo $req; ?> pregunta<?php echo $req === 1 ? '' : 's'; ?> visibles</span>
          </div>
        </div>
        <div class="eval-actions">
          <form class="eval-quick-enable" method="post" action="<?php echo htmlspecialchars(BASE_URL . '/index.php?c=coordinador&a=curso_eval_guardar'); ?>">
            <input type="hidden" name="id_curso" value="<?php echo (int) $idCurso; ?>">
            <input type="hidden" name="preguntas_requeridas" value="<?php echo (int) $req; ?>">
            <input type="hidden" name="quiz_activo" value="<?php echo $act ? 0 : 1; ?>">
            <button type="submit" class="<?php echo $act ? 'btn-enable-on' : 'btn-enable-off'; ?>">
              <?php echo $act ? 'Deshabilitar' : 'Habilitar'; ?>
            </button>
          </form>
          <button type="button" class="btn-secondary" data-open-eval="1"><?php echo $tieneEvalNombre ? 'Editar' : 'Crear'; ?></button>
          <a class="btn-primary" href="#quiz-final">Editar preguntas</a>
        </div>
      </div>
      <p class="hint-eval">Las preguntas del curso soportan: <strong>V/F</strong>, <strong>Selección múltiple</strong> y <strong>Imagen (correcto/incorrecto)</strong>. La nota del asesor es proporcional al acierto (máximo <strong>10/10</strong> si responde todo correctamente).</p>
    </section>
  </main>

  <div class="modal" id="eval-modal" aria-hidden="true">
    <div class="modal-backdrop" data-close-modal="eval-modal"></div>
    <div class="modal-card" role="dialog" aria-modal="true" aria-label="Evaluación del curso">
      <div class="modal-head">
        <strong><?php echo $tieneEvalNombre ? 'Editar evaluación' : 'Crear evaluación'; ?></strong>
        <button type="button" class="btn-x" data-close-modal="eval-modal">×</button>
      </div>
      <div class="modal-body">
        <form method="post" action="<?php echo htmlspecialchars(BASE_URL . '/index.php?c=coordinador&a=evaluacion_guardar'); ?>">
          <input type="hidden" name="id_curso" value="<?php echo $idCurso; ?>">
          <label for="evaluacion_nombre">Nombre de la evaluación</label>
          <input type="text" id="evaluacion_nombre" name="evaluacion_nombre" maxlength="150" value="<?php echo htmlspecialchars($evalNombre); ?>" placeholder="Ej.: Evaluación final">
          <button type="submit">Guardar</button>
        </form>
      </div>
    </div>
  </div>

  <main id="quiz-final">
    <section class="panel eval-panel" aria-label="Configurar evaluación final">
      <h2>Preguntas</h2>
      <form method="post" action="<?php echo htmlspecialchars(BASE_URL . '/index.php?c=coordinador&a=curso_eval_guardar'); ?>" enctype="multipart/form-data" id="curso-eval-form">
        <input type="hidden" name="id_curso" value="<?php echo (int) $idCurso; ?>">

        <label>Activar evaluación</label>
        <input type="checkbox" name="quiz_activo" value="1" <?php echo $act ? 'checked' : ''; ?>>

        <label>Preguntas visibles</label>
        <select name="preguntas_requeridas" id="curso-eval-req">
          <?php for ($n = 1; $n <= $maxSlots; $n++): ?>
            <option value="<?php echo $n; ?>" <?php echo $req === $n ? 'selected' : ''; ?>><?php echo $n; ?></option>
          <?php endfor; ?>
        </select>

        <?php for ($i = 1; $i <= $maxSlots; $i++): ?>
          <?php
          $slot = $slots[$i] ?? null;
          $p = is_array($slot) ? ($slot['pregunta'] ?? null) : null;
          $ops = is_array($slot) ? ($slot['opciones'] ?? []) : [];
          $tipo = is_array($p) ? (string) ($p['tipo'] ?? 'vf') : 'vf';
          $enun = is_array($p) ? (string) ($p['enunciado'] ?? '') : '';
          $corrId = is_array($slot) ? (int) ($slot['correcta'] ?? 0) : 0;
          $byClave = [];
          foreach ($ops as $o) {
              $byClave[(string) ($o['clave'] ?? '')] = $o;
          }
          $corrClave = '';
          foreach ($ops as $o) {
              if ((int) ($o['id_opcion'] ?? 0) === $corrId) {
                  $corrClave = (string) ($o['clave'] ?? '');
                  break;
              }
          }
          ?>
          <fieldset class="eval-fieldset" data-q-fieldset="<?php echo $i; ?>">
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
            <label>Imagen del enunciado (opcional)</label>
            <input type="file" name="q_enun_img_<?php echo $i; ?>" accept=".jpg,.jpeg,.png,image/jpeg,image/png">
            <?php $enunImg = is_array($p) ? (string) ($p['enunciado_imagen_path'] ?? '') : ''; ?>
            <?php if ($enunImg !== ''): ?>
              <div class="img-prev"><small class="muted">Actual:</small> <a target="_blank" rel="noopener" href="<?php echo htmlspecialchars(BASE_URL . '/' . $enunImg); ?>">Abrir</a></div>
            <?php endif; ?>

            <div class="quiz-tipo">
              <div data-q-block="<?php echo $i; ?>" data-q-kind="vf">
                <strong>V/F</strong>
                <select name="q_vf_correcta[<?php echo $i; ?>]">
                  <option value="true" <?php echo $corrClave === 'true' ? 'selected' : ''; ?>>Verdadero</option>
                  <option value="false" <?php echo $corrClave === 'false' ? 'selected' : ''; ?>>Falso</option>
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
                    <option value="<?php echo $k; ?>" <?php echo $corrClave === $k ? 'selected' : ''; ?>><?php echo strtoupper($k); ?></option>
                  <?php endforeach; ?>
                </select>
              </div>

              <div data-q-block="<?php echo $i; ?>" data-q-kind="imagen_par">
                <strong>Imagen par</strong>
                <div class="img-par">
                  <?php
                  $imgOk = (string) ($byClave['ok']['imagen_path'] ?? '');
                  $imgBad = (string) ($byClave['bad']['imagen_path'] ?? '');
                  ?>
                  <label>Imagen correcta</label>
                  <input type="file" name="q_img_ok_<?php echo $i; ?>" accept=".jpg,.jpeg,.png,image/jpeg,image/png">
                  <?php if ($imgOk !== ''): ?>
                    <div class="img-prev"><small class="muted">Actual:</small> <a target="_blank" rel="noopener" href="<?php echo htmlspecialchars(BASE_URL . '/' . $imgOk); ?>">Abrir</a></div>
                  <?php endif; ?>
                  <label>Imagen incorrecta</label>
                  <input type="file" name="q_img_bad_<?php echo $i; ?>" accept=".jpg,.jpeg,.png,image/jpeg,image/png">
                  <?php if ($imgBad !== ''): ?>
                    <div class="img-prev"><small class="muted">Actual:</small> <a target="_blank" rel="noopener" href="<?php echo htmlspecialchars(BASE_URL . '/' . $imgBad); ?>">Abrir</a></div>
                  <?php endif; ?>
                </div>
              </div>
            </div>
          </fieldset>
        <?php endfor; ?>

        <button type="submit">Guardar evaluación final</button>
      </form>
    </section>
  </main>

  <script>
  (function () {
    var evalModal = document.getElementById('eval-modal');
    function openEvalModal() {
      if (!evalModal) return;
      evalModal.setAttribute('aria-hidden', 'false');
      evalModal.classList.add('open');
      var input = evalModal.querySelector('#evaluacion_nombre');
      if (input && typeof input.focus === 'function') input.focus();
    }
    function closeEvalModal() {
      if (!evalModal) return;
      evalModal.setAttribute('aria-hidden', 'true');
      evalModal.classList.remove('open');
    }
    document.addEventListener('click', function (e) {
      if (e.target.closest('[data-open-eval]')) {
        e.preventDefault();
        openEvalModal();
        return;
      }
      if (e.target.closest('[data-close-modal="eval-modal"]')) {
        closeEvalModal();
      }
    });
    document.addEventListener('keydown', function (e) {
      if (e.key === 'Escape' && evalModal && evalModal.classList.contains('open')) {
        e.preventDefault();
        closeEvalModal();
      }
    });
  })();
  </script>
  <script>
  (function () {
    function setEnabledIn(el, enabled) {
      if (!el) return;
      var fields = el.querySelectorAll('input, select, textarea, button');
      for (var i = 0; i < fields.length; i++) {
        var f = fields[i];
        if (f && f.getAttribute('type') === 'hidden') continue;
        f.disabled = !enabled;
      }
    }

    function refreshEvalForm(form) {
      if (!form) return;
      var selReq = document.getElementById('curso-eval-req');
      var req = selReq ? parseInt(selReq.value || '1', 10) : 1;
      if (!(req >= 1)) req = 1;

      var fieldsets = form.querySelectorAll('fieldset[data-q-fieldset]');
      for (var i = 0; i < fieldsets.length; i++) {
        var fs = fieldsets[i];
        var q = parseInt(fs.getAttribute('data-q-fieldset') || '0', 10);
        var activeSlot = q > 0 && q <= req;
        fs.classList.toggle('hidden', !activeSlot);
        setEnabledIn(fs, activeSlot);
        if (!activeSlot) continue;

        var tipoSel = fs.querySelector('select[data-q-tipo]');
        var tipo = tipoSel ? (tipoSel.value || '') : '';
        var blocks = fs.querySelectorAll('[data-q-block]');
        for (var j = 0; j < blocks.length; j++) {
          var b = blocks[j];
          var kind = b.getAttribute('data-q-kind') || '';
          var show = (tipo !== '' && kind === tipo);
          b.classList.toggle('hidden', !show);
          setEnabledIn(b, show);
        }
      }
    }

    var form = document.getElementById('curso-eval-form');
    if (!form) return;
    refreshEvalForm(form);
    document.addEventListener('change', function (e) {
      var t = e.target;
      if (!t) return;
      if (t.id === 'curso-eval-req') refreshEvalForm(form);
      if (t.matches && t.matches('select[data-q-tipo]')) refreshEvalForm(form);
    });
  })();
  </script>
</body>
</html>
