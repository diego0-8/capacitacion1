<?php
/** @var array<string,mixed> $curso */
/** @var array<int,array<string,mixed>> $asesores */
?>
<div class="asesores-modal-head">
  <div>
    <div class="muted">Asesores inscritos</div>
    <div class="title"><?php echo htmlspecialchars((string) ($curso['nombre_curso'] ?? '')); ?></div>
  </div>
</div>

<?php if (empty($asesores)): ?>
  <div class="asesores-empty">Aún no hay asesores inscritos en este curso.</div>
<?php else: ?>
  <div class="asesores-list">
    <?php foreach ($asesores as $a): ?>
      <?php
      $nombre = trim((string) ($a['nombre_asesor'] ?? ''));
      if ($nombre === '') {
          $nombre = '(Sin nombre en usuario)';
      }
      $ced = (string) ($a['cedula_asesor'] ?? '');
      $prog = (int) ($a['progreso_porcentaje'] ?? 0);
      $estado = (string) ($a['estado_capacitacion'] ?? '');
      $evalPct = (int) ($a['evaluacion_pct'] ?? 0);
      $mods = $a['modulos'] ?? [];
      ?>
      <details class="asesor-item">
        <summary class="asesor-sum">
          <div class="asesor-left">
            <strong><?php echo htmlspecialchars($nombre); ?></strong>
            <span class="muted">CC <?php echo htmlspecialchars($ced); ?></span>
          </div>
          <div class="asesor-right">
            <span class="chip"><?php echo htmlspecialchars($estado !== '' ? $estado : '—'); ?></span>
            <span class="chip">Curso: <?php echo (int) max(0, min(100, $prog)); ?>%</span>
            <span class="chip">Evaluación: <?php echo (int) max(0, min(100, $evalPct)); ?>%</span>
          </div>
        </summary>
        <div class="asesor-body">
          <div style="margin-bottom:10px">
            <a href="<?php echo htmlspecialchars(BASE_URL . '/index.php?c=coordinador&a=asesor_detalle&id=' . (int) ($curso['id_cursos'] ?? 0) . '&cedula=' . rawurlencode($ced)); ?>">Ver detalle (trazabilidad)</a>
          </div>
          <table class="mods">
            <thead>
              <tr>
                <th>Módulo</th>
                <th>Clases</th>
                <th>Quiz</th>
                <th>Progreso</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($mods as $m): ?>
                <?php
                $t = (string) ($m['titulo'] ?? '');
                $done = (int) ($m['done'] ?? 0);
                $total = (int) ($m['total'] ?? 0);
                $qa = !empty($m['quiz_activo']);
                $qok = !empty($m['quiz_aprobado']);
                $pct = (int) ($m['progreso'] ?? 0);
                ?>
                <tr>
                  <td><?php echo htmlspecialchars($t); ?></td>
                  <td><?php echo $done; ?> / <?php echo $total; ?></td>
                  <td>
                    <?php if ($qa): ?>
                      <?php echo $qok ? '<span class="ok">✓ Aprobado</span>' : '<span class="warn">Pendiente</span>'; ?>
                    <?php else: ?>
                      <span class="muted">No aplica</span>
                    <?php endif; ?>
                  </td>
                  <td>
                    <div class="bar"><span style="width: <?php echo min(100, max(0, $pct)); ?>%"></span></div>
                    <div class="muted small"><?php echo min(100, max(0, $pct)); ?>%</div>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </details>
    <?php endforeach; ?>
  </div>
<?php endif; ?>

