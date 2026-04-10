<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Mis capacitaciones</title>
  <link rel="stylesheet" href="<?php echo htmlspecialchars(BASE_URL . '/assets/css/asesor_index.css'); ?>">
</head>
<body>
  <?php $navActive = 'asesor_index'; require BASE_PATH . '/views/auth/header.php'; ?>
  <main>
    <h1>Mis capacitaciones</h1>
    <?php if (!empty($mensaje)): ?>
      <p class="flash-ok"><?php echo htmlspecialchars($mensaje); ?></p>
    <?php endif; ?>
    <?php if (!empty($error)): ?>
      <p class="flash-err"><?php echo htmlspecialchars($error); ?></p>
    <?php endif; ?>
    <?php if (count($items) === 0): ?>
      <p>No tiene cursos asignados todavía.</p>
    <?php else: ?>
      <div class="cards">
        <?php foreach ($items as $it): ?>
          <?php
          $idCurso = (int) ($it['id_curso'] ?? 0);
          $ins = $insigniasPorCurso[$idCurso] ?? null;
          ?>
          <div class="card">
            <h2><?php echo htmlspecialchars($it['nombre_curso'] ?? ''); ?></h2>
            <?php
            $completado = (string) ($it['estado_capacitacion'] ?? '') === 'completado';
            $idAsign = (int) ($it['id_asignacion'] ?? 0);
            $certUrl = BASE_URL . '/index.php?c=asesor&a=certificado&id=' . $idAsign;
            ?>
            <div class="progress-row">
              <span class="meta progress-meta">Progreso: <?php echo (int) ($it['progreso_porcentaje'] ?? 0); ?>%</span>
              <?php if ($completado && $idAsign > 0): ?>
                <button
                  type="button"
                  class="btn-certificado-verde"
                  data-cert-modal
                  data-curso="<?php echo htmlspecialchars((string) ($it['nombre_curso'] ?? '')); ?>"
                  data-cert-url="<?php echo htmlspecialchars($certUrl); ?>"
                >Ver insignia</button>
              <?php endif; ?>
            </div>
            <div class="estado"><?php echo htmlspecialchars($it['estado_capacitacion'] ?? ''); ?></div>
            <?php if (is_array($ins)): ?>
              <div class="meta">
                Insignia: <?php echo htmlspecialchars((string) ($ins['tipo'] ?? 'curso_completado')); ?>
                <?php if (!empty($ins['otorgada_en'])): ?>
                  <span class="text-muted">(<?php echo htmlspecialchars((string) $ins['otorgada_en']); ?>)</span>
                <?php endif; ?>
              </div>
            <?php endif; ?>
            <a class="cta" href="<?php echo htmlspecialchars(BASE_URL . '/index.php?c=asesor&a=curso&id=' . (int) $it['id_asignacion']); ?>">Entrar al curso</a>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>

    <hr>

    <h1>Cursos disponibles</h1>
    <?php if (empty($cursos) || count($cursos) === 0): ?>
      <p>No hay cursos activos disponibles.</p>
    <?php else: ?>
      <div class="cards">
        <?php
        $asignados = [];
        foreach ($items as $it) {
            $asignados[(int) ($it['id_curso'] ?? 0)] = (int) ($it['id_asignacion'] ?? 0);
        }
        ?>
        <?php foreach ($cursos as $c): ?>
          <?php
          $idCurso = (int) ($c['id_cursos'] ?? 0);
          $yaIdAsignacion = $asignados[$idCurso] ?? 0;
          ?>
          <div class="card">
            <h2><?php echo htmlspecialchars((string) ($c['nombre_curso'] ?? '')); ?></h2>
            <div class="meta">
              <?php if (!empty($c['nombre_coordinador'])): ?>
                Coordinador: <?php echo htmlspecialchars((string) $c['nombre_coordinador']); ?>
              <?php endif; ?>
            </div>
            <?php if (!empty($c['descripcion'])): ?>
              <p><?php echo nl2br(htmlspecialchars((string) $c['descripcion'])); ?></p>
            <?php endif; ?>

            <?php if ($yaIdAsignacion > 0): ?>
              <a class="cta" href="<?php echo htmlspecialchars(BASE_URL . '/index.php?c=asesor&a=curso&id=' . $yaIdAsignacion); ?>">Continuar</a>
            <?php else: ?>
              <form method="post" action="<?php echo htmlspecialchars(BASE_URL . '/index.php?c=asesor&a=inscribirse'); ?>">
                <input type="hidden" name="id_curso" value="<?php echo $idCurso; ?>">
                <button class="cta" type="submit">Inscribirme</button>
              </form>
            <?php endif; ?>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
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
      <p class="cert-dialog-nombre"><?php echo htmlspecialchars((string) ($nombreAsesorCompleto ?? '')); ?></p>
      <div class="cert-dialog-actions">
        <a class="btn-cert-descargar" id="cert-modal-descargar" href="#" target="_blank" rel="noopener">Descargar</a>
      </div>
    </div>
  </dialog>

  <script>
    (function () {
      var modal = document.getElementById('certificado-modal');
      var cursoEl = document.getElementById('cert-modal-curso');
      var link = document.getElementById('cert-modal-descargar');
      if (!modal || !cursoEl || !link) return;
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
    })();
  </script>
</body>
</html>
