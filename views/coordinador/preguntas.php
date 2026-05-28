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
    $idCurso = (int) ($curso['id_cursos'] ?? 0);
    $evalNombre = trim((string) ($evaluacionNombre ?? ''));
    $tieneEvalNombre = $evalNombre !== '';
    $cursoEvalConfig = is_array($cursoEvalConfig ?? null) ? $cursoEvalConfig : ['preguntas_requeridas' => 1, 'activo' => 0, 'modo_evaluacion' => 'unico'];
    $cursoEvalSlots = is_array($cursoEvalSlots ?? null) ? $cursoEvalSlots : [];
    $cfg = $cursoEvalConfig;
    $slots = $cursoEvalSlots;
    $maxSlots = (int) ($cursoEvalMaxSlots ?? 10);
    if ($maxSlots < 1) $maxSlots = 10;
    $req = (int) ($cfg['preguntas_requeridas'] ?? 1);
    if ($req < 1) $req = 1;
    if ($req > $maxSlots) $req = $maxSlots;
    $act = (int) ($cfg['activo'] ?? 0) === 1;
    $modoEval = (string) ($modoEval ?? 'unico');
    $variantes = is_array($variantes ?? null) ? $variantes : [];
    $varianteSlots = is_array($varianteSlots ?? null) ? $varianteSlots : [];
    $varianteAsesores = is_array($varianteAsesores ?? null) ? $varianteAsesores : [];
    $asesoresCurso = is_array($asesoresCurso ?? null) ? $asesoresCurso : [];
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
          <form class="eval-mode-form" method="post" action="<?php echo htmlspecialchars(BASE_URL . '/index.php?c=coordinador&a=curso_eval_guardar'); ?>">
            <input type="hidden" name="id_curso" value="<?php echo (int) $idCurso; ?>">
            <input type="hidden" name="preguntas_requeridas" value="<?php echo (int) $req; ?>">
            <input type="hidden" name="quiz_activo" value="<?php echo $act ? 1 : 0; ?>">
            <select name="modo_evaluacion" onchange="this.form.submit()">
              <option value="unico" <?php echo $modoEval === 'unico' ? 'selected' : ''; ?>>Único (todos igual)</option>
              <option value="manual" <?php echo $modoEval === 'manual' ? 'selected' : ''; ?>>Manual (asignar variantes)</option>
              <option value="aleatorio" <?php echo $modoEval === 'aleatorio' ? 'selected' : ''; ?>>Aleatorio (mezcla automática)</option>
            </select>
          </form>
          <form class="eval-quick-enable" method="post" action="<?php echo htmlspecialchars(BASE_URL . '/index.php?c=coordinador&a=curso_eval_guardar'); ?>">
            <input type="hidden" name="id_curso" value="<?php echo (int) $idCurso; ?>">
            <input type="hidden" name="preguntas_requeridas" value="<?php echo (int) $req; ?>">
            <input type="hidden" name="modo_evaluacion" value="<?php echo htmlspecialchars($modoEval); ?>">
            <input type="hidden" name="quiz_activo" value="<?php echo $act ? 0 : 1; ?>">
            <button type="submit" class="<?php echo $act ? 'btn-enable-on' : 'btn-enable-off'; ?>">
              <?php echo $act ? 'Deshabilitar' : 'Habilitar'; ?>
            </button>
          </form>
          <button type="button" class="btn-secondary" data-open-eval="1"><?php echo $tieneEvalNombre ? 'Editar' : 'Crear'; ?></button>
          <a class="btn-primary" href="#quiz-final">Editar preguntas</a>
        </div>
      </div>
      <p class="hint-eval">
        <?php if ($modoEval === 'unico'): ?>
          Modo <strong>Único</strong>: todos los asesores ven las mismas preguntas.
        <?php elseif ($modoEval === 'manual'): ?>
          Modo <strong>Manual</strong>: cree variantes y asigne asesores a cada una. Cada asesor solo ve las preguntas de su variante.
        <?php else: ?>
          Modo <strong>Aleatorio</strong>: cree variantes como pool. El sistema mezcla preguntas de distintas variantes para cada asesor.
        <?php endif; ?>
      </p>
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

  <?php if ($modoEval === 'unico'): ?>
    <!-- ══════════ FORMATO 1: ÚNICO ══════════ -->
    <section class="panel eval-panel" aria-label="Configurar evaluación final">
      <h2>Preguntas</h2>
      <form method="post" action="<?php echo htmlspecialchars(BASE_URL . '/index.php?c=coordinador&a=curso_eval_guardar'); ?>" enctype="multipart/form-data" id="curso-eval-form">
        <input type="hidden" name="id_curso" value="<?php echo (int) $idCurso; ?>">
        <input type="hidden" name="modo_evaluacion" value="unico">
        <input type="hidden" name="quiz_activo" value="<?php echo $act ? 1 : 0; ?>">

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
            <?php $idPregActual = is_array($p) ? (int) ($p['id_pregunta_curso'] ?? 0) : 0; ?>
            <?php if ($enunImg !== ''): ?>
              <div class="img-prev">
                <small class="muted">Actual:</small>
                <a target="_blank" rel="noopener" href="<?php echo htmlspecialchars(BASE_URL . '/' . $enunImg); ?>">Abrir</a>
                <button type="button" class="btn-delete-resource" data-delete-recurso="enunciado_imagen" data-delete-id="<?php echo $idPregActual; ?>">Eliminar</button>
              </div>
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
                    <div class="img-prev">
                      <small class="muted">Actual:</small>
                      <a target="_blank" rel="noopener" href="<?php echo htmlspecialchars(BASE_URL . '/' . $imgOk); ?>">Abrir</a>
                      <button type="button" class="btn-delete-resource" data-delete-recurso="img_ok" data-delete-id="<?php echo $idPregActual; ?>">Eliminar</button>
                    </div>
                  <?php endif; ?>
                  <label>Imagen incorrecta</label>
                  <input type="file" name="q_img_bad_<?php echo $i; ?>" accept=".jpg,.jpeg,.png,image/jpeg,image/png">
                  <?php if ($imgBad !== ''): ?>
                    <div class="img-prev">
                      <small class="muted">Actual:</small>
                      <a target="_blank" rel="noopener" href="<?php echo htmlspecialchars(BASE_URL . '/' . $imgBad); ?>">Abrir</a>
                      <button type="button" class="btn-delete-resource" data-delete-recurso="img_bad" data-delete-id="<?php echo $idPregActual; ?>">Eliminar</button>
                    </div>
                  <?php endif; ?>
                </div>
              </div>
            </div>
          </fieldset>
        <?php endfor; ?>

        <button type="submit">Guardar evaluación final</button>
      </form>
    </section>

  <?php else: ?>
    <!-- ══════════ FORMATOS 2 y 3: VARIANTES ══════════ -->
    <section class="panel eval-panel" aria-label="Variantes de evaluación">
      <div class="variantes-header">
        <h2>Variantes de evaluación</h2>
        <form method="post" action="<?php echo htmlspecialchars(BASE_URL . '/index.php?c=coordinador&a=eval_variante_crear'); ?>" class="variante-crear-form">
          <input type="hidden" name="id_curso" value="<?php echo $idCurso; ?>">
          <button type="submit" class="btn-add-variante">+ Agregar variante</button>
        </form>
      </div>

      <?php if ($variantes === []): ?>
        <p class="muted">No hay variantes creadas. Pulse "Agregar variante" para comenzar.</p>
      <?php endif; ?>

      <?php foreach ($variantes as $v):
        $vid = (int) $v['id_variante'];
        $vNombre = htmlspecialchars((string) $v['nombre_variante']);
        $vReq = (int) ($v['preguntas_requeridas'] ?? 3);
        $vSlots = $varianteSlots[$vid] ?? [];
        $vAsesores = $varianteAsesores[$vid] ?? [];
      ?>
      <div class="variante-accordion" data-variante-id="<?php echo $vid; ?>">
        <div class="variante-accordion-head" data-toggle-variante="<?php echo $vid; ?>">
          <span class="variante-accordion-title"><?php echo $vNombre; ?> (<?php echo $vReq; ?> preguntas)</span>
          <span class="variante-accordion-arrow">&#9660;</span>
        </div>
        <div class="variante-accordion-body" id="variante-body-<?php echo $vid; ?>" style="display:none;">

          <form method="post" action="<?php echo htmlspecialchars(BASE_URL . '/index.php?c=coordinador&a=eval_variante_guardar'); ?>" enctype="multipart/form-data" class="variante-form" data-variante-form="<?php echo $vid; ?>">
            <input type="hidden" name="id_curso" value="<?php echo $idCurso; ?>">
            <input type="hidden" name="id_variante" value="<?php echo $vid; ?>">

            <div class="variante-config-row">
              <div>
                <label>Nombre de la variante</label>
                <input type="text" name="nombre_variante" value="<?php echo $vNombre; ?>" maxlength="100">
              </div>
              <div>
                <label>Preguntas requeridas</label>
                <select name="v_preguntas_requeridas" data-vreq="<?php echo $vid; ?>">
                  <?php for ($n = 1; $n <= 10; $n++): ?>
                    <option value="<?php echo $n; ?>" <?php echo $vReq === $n ? 'selected' : ''; ?>><?php echo $n; ?></option>
                  <?php endfor; ?>
                </select>
              </div>
            </div>

            <?php for ($qi = 1; $qi <= 10; $qi++):
              $vSlot = $vSlots[$qi] ?? null;
              $vp = is_array($vSlot) ? ($vSlot['pregunta'] ?? null) : null;
              $vOps = is_array($vSlot) ? ($vSlot['opciones'] ?? []) : [];
              $vTipo = is_array($vp) ? (string) ($vp['tipo'] ?? 'vf') : 'vf';
              $vEnun = is_array($vp) ? (string) ($vp['enunciado'] ?? '') : '';
              $vCorrId = is_array($vSlot) ? (int) ($vSlot['correcta'] ?? 0) : 0;
              $vByClave = [];
              foreach ($vOps as $vo) { $vByClave[(string) ($vo['clave'] ?? '')] = $vo; }
              $vCorrClave = '';
              foreach ($vOps as $vo) {
                  if ((int) ($vo['id_opcion'] ?? 0) === $vCorrId) { $vCorrClave = (string) ($vo['clave'] ?? ''); break; }
              }
            ?>
            <fieldset class="eval-fieldset" data-vq-fieldset="<?php echo $vid; ?>_<?php echo $qi; ?>" data-vq-idx="<?php echo $qi; ?>" data-vq-vid="<?php echo $vid; ?>" <?php echo $qi > $vReq ? 'style="display:none;"' : ''; ?>>
              <legend>Pregunta <?php echo $qi; ?></legend>
              <label>Tipo</label>
              <select name="vq_tipo[<?php echo $qi; ?>]" data-vq-tipo="<?php echo $vid; ?>_<?php echo $qi; ?>">
                <option value="">(sin usar)</option>
                <option value="imagen_par" <?php echo $vTipo === 'imagen_par' ? 'selected' : ''; ?>>Imagen (correcto/incorrecto)</option>
                <option value="vf" <?php echo $vTipo === 'vf' ? 'selected' : ''; ?>>Verdadero / Falso</option>
                <option value="multi" <?php echo $vTipo === 'multi' ? 'selected' : ''; ?>>Selección múltiple (A–D)</option>
              </select>

              <label>Enunciado</label>
              <textarea name="vq_enunciado[<?php echo $qi; ?>]" rows="2" placeholder="Ej.: Seleccione la acción correcta"><?php echo htmlspecialchars($vEnun); ?></textarea>
              <label>Imagen del enunciado (opcional)</label>
              <input type="file" name="vq_enun_img_<?php echo $qi; ?>" accept=".jpg,.jpeg,.png,image/jpeg,image/png">
              <?php $vEnunImg = is_array($vp) ? (string) ($vp['enunciado_imagen_path'] ?? '') : ''; ?>
              <?php $vIdPregActual = is_array($vp) ? (int) ($vp['id_pregunta_curso'] ?? 0) : 0; ?>
              <?php if ($vEnunImg !== ''): ?>
                <div class="img-prev">
                  <small class="muted">Actual:</small>
                  <a target="_blank" rel="noopener" href="<?php echo htmlspecialchars(BASE_URL . '/' . $vEnunImg); ?>">Abrir</a>
                  <button type="button" class="btn-delete-resource" data-delete-recurso="enunciado_imagen" data-delete-id="<?php echo $vIdPregActual; ?>">Eliminar</button>
                </div>
              <?php endif; ?>

              <div class="quiz-tipo">
                <div data-vq-block="<?php echo $vid; ?>_<?php echo $qi; ?>" data-vq-kind="vf" <?php echo $vTipo !== 'vf' ? 'style="display:none;"' : ''; ?>>
                  <strong>V/F</strong>
                  <select name="vq_vf_correcta[<?php echo $qi; ?>]">
                    <option value="true" <?php echo $vCorrClave === 'true' ? 'selected' : ''; ?>>Verdadero</option>
                    <option value="false" <?php echo $vCorrClave === 'false' ? 'selected' : ''; ?>>Falso</option>
                  </select>
                </div>
                <div data-vq-block="<?php echo $vid; ?>_<?php echo $qi; ?>" data-vq-kind="multi" <?php echo $vTipo !== 'multi' ? 'style="display:none;"' : ''; ?>>
                  <strong>Multi (A–D)</strong>
                  <div>
                    <input type="text" name="vq_multi_a[<?php echo $qi; ?>]" placeholder="Opción A" value="<?php echo htmlspecialchars((string) (($vByClave['a']['texto'] ?? '') ?: '')); ?>">
                    <input type="text" name="vq_multi_b[<?php echo $qi; ?>]" placeholder="Opción B" value="<?php echo htmlspecialchars((string) (($vByClave['b']['texto'] ?? '') ?: '')); ?>">
                    <input type="text" name="vq_multi_c[<?php echo $qi; ?>]" placeholder="Opción C" value="<?php echo htmlspecialchars((string) (($vByClave['c']['texto'] ?? '') ?: '')); ?>">
                    <input type="text" name="vq_multi_d[<?php echo $qi; ?>]" placeholder="Opción D" value="<?php echo htmlspecialchars((string) (($vByClave['d']['texto'] ?? '') ?: '')); ?>">
                  </div>
                  <label>Correcta</label>
                  <select name="vq_multi_correcta[<?php echo $qi; ?>]">
                    <?php foreach (['a','b','c','d'] as $k): ?>
                      <option value="<?php echo $k; ?>" <?php echo $vCorrClave === $k ? 'selected' : ''; ?>><?php echo strtoupper($k); ?></option>
                    <?php endforeach; ?>
                  </select>
                </div>
                <div data-vq-block="<?php echo $vid; ?>_<?php echo $qi; ?>" data-vq-kind="imagen_par" <?php echo $vTipo !== 'imagen_par' ? 'style="display:none;"' : ''; ?>>
                  <strong>Imagen par</strong>
                  <div class="img-par">
                    <?php
                    $vImgOk = (string) ($vByClave['ok']['imagen_path'] ?? '');
                    $vImgBad = (string) ($vByClave['bad']['imagen_path'] ?? '');
                    ?>
                    <label>Imagen correcta</label>
                    <input type="file" name="vq_img_ok_<?php echo $qi; ?>" accept=".jpg,.jpeg,.png,image/jpeg,image/png">
                    <?php if ($vImgOk !== ''): ?>
                      <div class="img-prev">
                        <small class="muted">Actual:</small>
                        <a target="_blank" rel="noopener" href="<?php echo htmlspecialchars(BASE_URL . '/' . $vImgOk); ?>">Abrir</a>
                        <button type="button" class="btn-delete-resource" data-delete-recurso="img_ok" data-delete-id="<?php echo $vIdPregActual; ?>">Eliminar</button>
                      </div>
                    <?php endif; ?>
                    <label>Imagen incorrecta</label>
                    <input type="file" name="vq_img_bad_<?php echo $qi; ?>" accept=".jpg,.jpeg,.png,image/jpeg,image/png">
                    <?php if ($vImgBad !== ''): ?>
                      <div class="img-prev">
                        <small class="muted">Actual:</small>
                        <a target="_blank" rel="noopener" href="<?php echo htmlspecialchars(BASE_URL . '/' . $vImgBad); ?>">Abrir</a>
                        <button type="button" class="btn-delete-resource" data-delete-recurso="img_bad" data-delete-id="<?php echo $vIdPregActual; ?>">Eliminar</button>
                      </div>
                    <?php endif; ?>
                  </div>
                </div>
              </div>
            </fieldset>
            <?php endfor; ?>

            <button type="submit" class="btn-primary">Guardar variante</button>
          </form>

          <?php if ($modoEval === 'manual'): ?>
          <?php
          $asignadosOtras = [];
          foreach ($varianteAsesores as $otroVid => $otroCedulas) {
              if ((int) $otroVid === $vid) continue;
              foreach ($otroCedulas as $ced) {
                  $asignadosOtras[$ced] = (int) $otroVid;
              }
          }
          $nombresVariantes = [];
          foreach ($variantes as $vTmp) {
              $nombresVariantes[(int) $vTmp['id_variante']] = (string) $vTmp['nombre_variante'];
          }
          ?>
          <div class="variante-asesores-section">
            <h3>Asesores asignados a esta variante</h3>
            <form method="post" action="<?php echo htmlspecialchars(BASE_URL . '/index.php?c=coordinador&a=eval_variante_asesores_guardar'); ?>">
              <input type="hidden" name="id_curso" value="<?php echo $idCurso; ?>">
              <input type="hidden" name="id_variante" value="<?php echo $vid; ?>">
              <div class="asesores-checkbox-grid">
                <?php foreach ($asesoresCurso as $ase):
                  $aCed = (string) ($ase['cedula'] ?? '');
                  $aNom = (string) ($ase['nombre'] ?? '');
                  $checked = in_array($aCed, $vAsesores, true) ? 'checked' : '';
                  $enOtra = isset($asignadosOtras[$aCed]);
                  $nombreOtra = $enOtra ? ($nombresVariantes[$asignadosOtras[$aCed]] ?? 'otra variante') : '';
                ?>
                <label class="asesor-check-label<?php echo $enOtra ? ' asesor-en-otra' : ''; ?>">
                  <input type="checkbox" name="cedulas_asesor[]" value="<?php echo htmlspecialchars($aCed); ?>" <?php echo $checked; ?>>
                  <span><?php echo htmlspecialchars($aNom); ?> (<?php echo htmlspecialchars($aCed); ?>)</span>
                  <?php if ($enOtra): ?>
                    <small class="badge-otra-variante">en <?php echo htmlspecialchars($nombreOtra); ?></small>
                  <?php endif; ?>
                </label>
                <?php endforeach; ?>
              </div>
              <p class="muted" style="font-size:0.85rem; margin-top:0.5rem;">Si selecciona un asesor que está en otra variante, se moverá automáticamente a esta.</p>
              <button type="submit" class="btn-primary">Guardar asignación</button>
            </form>
          </div>
          <?php endif; ?>

          <form method="post" action="<?php echo htmlspecialchars(BASE_URL . '/index.php?c=coordinador&a=eval_variante_eliminar'); ?>" class="variante-delete-form" onsubmit="return confirm('¿Eliminar esta variante y todas sus preguntas?');">
            <input type="hidden" name="id_curso" value="<?php echo $idCurso; ?>">
            <input type="hidden" name="id_variante" value="<?php echo $vid; ?>">
            <button type="submit" class="btn-danger">Eliminar variante</button>
          </form>
        </div>
      </div>
      <?php endforeach; ?>
    </section>
  <?php endif; ?>
    <form id="form-delete-recurso" method="post" action="<?php echo htmlspecialchars(BASE_URL . '/index.php?c=coordinador&a=curso_eval_eliminar_recurso'); ?>" style="display:none;">
      <input type="hidden" name="id_curso" value="<?php echo $idCurso; ?>">
      <input type="hidden" name="id_pregunta_curso" id="del-id-pregunta" value="">
      <input type="hidden" name="recurso" id="del-recurso" value="">
    </form>
  </main>

  <style>
    .btn-delete-resource {
      display: inline-block;
      margin-left: 0.5rem;
      padding: 2px 8px;
      font-size: 0.8rem;
      background: #fef2f2;
      color: #b91c1c;
      border: 1px solid #fecaca;
      border-radius: 6px;
      cursor: pointer;
      font-weight: 600;
    }
    .btn-delete-resource:hover {
      background: #fee2e2;
    }
  </style>
  <script>
  (function () {
    document.addEventListener('click', function (e) {
      var btn = e.target.closest('.btn-delete-resource');
      if (!btn) return;
      e.preventDefault();
      var recurso = btn.getAttribute('data-delete-recurso') || '';
      var idP = btn.getAttribute('data-delete-id') || '';
      if (!recurso || !idP) return;
      if (!confirm('¿Eliminar este recurso? Esta acción no se puede deshacer.')) return;
      document.getElementById('del-id-pregunta').value = idP;
      document.getElementById('del-recurso').value = recurso;
      document.getElementById('form-delete-recurso').submit();
    });
  })();
  </script>
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
    // Formato 1 (único): toggle fieldsets
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
    if (form) {
      refreshEvalForm(form);
      document.addEventListener('change', function (e) {
        var t = e.target;
        if (!t) return;
        if (t.id === 'curso-eval-req') refreshEvalForm(form);
        if (t.matches && t.matches('select[data-q-tipo]')) refreshEvalForm(form);
      });
    }

    // Variantes: acordeones
    document.addEventListener('click', function (e) {
      var head = e.target.closest('[data-toggle-variante]');
      if (!head) return;
      var vid = head.getAttribute('data-toggle-variante');
      var body = document.getElementById('variante-body-' + vid);
      if (!body) return;
      var visible = body.style.display !== 'none';
      body.style.display = visible ? 'none' : 'block';
      var arrow = head.querySelector('.variante-accordion-arrow');
      if (arrow) arrow.textContent = visible ? '\u25BC' : '\u25B2';
    });

    // Variantes: toggle fieldsets según preguntas requeridas
    document.addEventListener('change', function (e) {
      var sel = e.target;
      if (!sel || !sel.matches || !sel.matches('select[data-vreq]')) return;
      var vid = sel.getAttribute('data-vreq');
      var req = parseInt(sel.value || '1', 10);
      if (!(req >= 1)) req = 1;
      var formEl = sel.closest('form');
      if (!formEl) return;
      var fieldsets = formEl.querySelectorAll('fieldset[data-vq-vid="' + vid + '"]');
      for (var i = 0; i < fieldsets.length; i++) {
        var fs = fieldsets[i];
        var idx = parseInt(fs.getAttribute('data-vq-idx') || '0', 10);
        fs.style.display = (idx >= 1 && idx <= req) ? '' : 'none';
      }
    });

    // Variantes: toggle tipo blocks
    document.addEventListener('change', function (e) {
      var sel = e.target;
      if (!sel || !sel.matches || !sel.matches('select[data-vq-tipo]')) return;
      var key = sel.getAttribute('data-vq-tipo');
      var tipo = sel.value || '';
      var blocks = document.querySelectorAll('[data-vq-block="' + key + '"]');
      for (var i = 0; i < blocks.length; i++) {
        var b = blocks[i];
        var kind = b.getAttribute('data-vq-kind') || '';
        b.style.display = (tipo !== '' && kind === tipo) ? '' : 'none';
      }
    });
  })();
  </script>
</body>
</html>
