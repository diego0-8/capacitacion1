<?php
/** @var array<string,mixed> $curso */
/** @var string $accesoActual */
/** @var array<int, array{cedula: string, nombre: string}> $permitidos */
/** @var array<int, array<string, mixed>> $asesores */
/** @var bool $migracionOk */
?>
<div class="acceso-modal-head">
  <div>
    <div class="muted">Acceso de asesores</div>
    <div class="title"><?php echo htmlspecialchars((string) ($curso['nombre_curso'] ?? '')); ?></div>
  </div>
</div>

<?php if (!$migracionOk): ?>
  <p class="flash-err" style="margin-top:1rem">
    Falta la migración de base de datos. Ejecute <code>database/migration_curso_acceso_asesor.sql</code> en capacitacion1.
  </p>
<?php else: ?>
  <?php
  $permitidosSet = [];
  foreach ($permitidos as $p) {
      $permitidosSet[(string) ($p['cedula'] ?? '')] = true;
  }
  $idCurso = (int) ($curso['id_cursos'] ?? 0);
  $nAsesores = count($asesores);
  ?>
  <form
    class="acceso-form"
    id="acceso-asesores-form"
    method="post"
    action="<?php echo htmlspecialchars(BASE_URL . '/index.php?c=coordinador&a=acceso_asesores_guardar'); ?>"
  >
    <input type="hidden" name="id_curso" value="<?php echo $idCurso; ?>">

    <fieldset class="acceso-modo">
      <legend class="sr-only">Modo de acceso</legend>
      <label class="acceso-radio">
        <input type="radio" name="acceso_asesores" value="publico" <?php echo $accesoActual === 'publico' ? 'checked' : ''; ?>>
        <span><strong>Todos los asesores</strong> — cualquier asesor puede ver el curso en «Cursos disponibles» e inscribirse.</span>
      </label>
      <label class="acceso-radio">
        <input type="radio" name="acceso_asesores" value="restringido" <?php echo $accesoActual === 'restringido' ? 'checked' : ''; ?>>
        <span><strong>Solo asesores seleccionados</strong> — solo ellos ven el curso; al guardar quedan asignados automáticamente.</span>
      </label>
    </fieldset>

    <div
      class="acceso-lista-wrap<?php echo $accesoActual === 'restringido' ? ' acceso-lista-wrap--open' : ''; ?>"
      id="acceso-lista-wrap"
      <?php echo $accesoActual !== 'restringido' ? 'hidden' : ''; ?>
    >
      <div class="acceso-lista-head">
        <strong>Asesores registrados <span class="muted">(<?php echo $nAsesores; ?> en el sistema)</span></strong>
        <?php if ($nAsesores > 0): ?>
          <div class="acceso-toolbar">
            <button type="button" class="btn-acceso-mini" data-acceso-select-all>Marcar todos</button>
            <button type="button" class="btn-acceso-mini" data-acceso-select-none>Desmarcar todos</button>
          </div>
        <?php endif; ?>
      </div>
      <p class="muted small">Solo se listan asesores <strong>activos</strong>. Los inhabilitados en «Usuarios» no aparecen hasta volver a activarlos.</p>
      <?php if ($asesores === []): ?>
        <p class="muted">No hay asesores activos. El administrador puede crearlos o reactivarlos en «Usuarios».</p>
      <?php else: ?>
        <div class="acceso-check-grid" id="acceso-check-grid">
          <?php foreach ($asesores as $a): ?>
            <?php
            $ced = (string) ($a['cedula'] ?? '');
            $nom = trim((string) ($a['nombre'] ?? ''));
            if ($nom === '') {
                $nom = '(Sin nombre)';
            }
            $chk = isset($permitidosSet[$ced]);
            ?>
            <label class="acceso-check">
              <input
                type="checkbox"
                class="acceso-check-input"
                name="cedulas_asesor[]"
                value="<?php echo htmlspecialchars($ced); ?>"
                <?php echo $chk ? 'checked' : ''; ?>
              >
              <span class="acceso-check-body">
                <span class="acceso-check-nombre"><?php echo htmlspecialchars($nom); ?></span>
                <span class="muted">CC <?php echo htmlspecialchars($ced); ?></span>
              </span>
            </label>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </div>

    <div class="acceso-form-actions">
      <button type="submit" class="btn-acceso-guardar">Guardar acceso</button>
    </div>
  </form>
<?php endif; ?>
