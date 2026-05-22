<?php
/** @var array<int, array<string,mixed>> $usuarios */
/** @var array<string,mixed>|null $usuarioEdit */
/** @var int $totalUsuarios */
/** @var int $paginaActual */
/** @var int $totalPaginas */
/** @var int $porPagina */
/** @var string $filtroBusqueda */
/** @var string $filtroEmpresa */

$filtroBusqueda = $filtroBusqueda ?? '';
$filtroEmpresa = $filtroEmpresa ?? '';
$paginaActual = (int) ($paginaActual ?? 1);
$totalPaginas = (int) ($totalPaginas ?? 1);
$totalUsuarios = (int) ($totalUsuarios ?? 0);
$porPagina = (int) ($porPagina ?? 10);

$usuariosListUrl = static function (array $extra = []) use ($filtroBusqueda, $filtroEmpresa, $paginaActual): string {
    $params = [
        'c' => 'admin',
        'a' => 'creacion_usuarios',
        'q' => $extra['q'] ?? $filtroBusqueda,
        'empresa' => $extra['empresa'] ?? $filtroEmpresa,
        'p' => $extra['p'] ?? ($paginaActual > 1 ? (string) $paginaActual : ''),
    ];
    if (($params['p'] ?? '') === '1') {
        $params['p'] = '';
    }
    $parts = [];
    foreach ($params as $k => $v) {
        if ($v === null || $v === '') {
            continue;
        }
        $parts[] = rawurlencode((string) $k) . '=' . rawurlencode((string) $v);
    }

    return BASE_URL . '/index.php?' . implode('&', $parts);
};

$desde = $totalUsuarios === 0 ? 0 : (($paginaActual - 1) * $porPagina) + 1;
$hasta = min($totalUsuarios, $paginaActual * $porPagina);

$usuarioEditJson = 'null';
if (!empty($usuarioEdit) && is_array($usuarioEdit)) {
    $usuarioEditJson = json_encode([
        'cedula' => (string) ($usuarioEdit['cedula'] ?? ''),
        'nombre' => (string) ($usuarioEdit['nombre'] ?? ''),
        'usuario' => (string) ($usuarioEdit['usuario'] ?? ''),
        'rol' => (string) ($usuarioEdit['rol'] ?? 'asesor'),
        'email' => (string) ($usuarioEdit['email'] ?? ''),
        'estado' => (string) ($usuarioEdit['estado'] ?? 'activo'),
    ], JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Creación de usuarios</title>
  <link rel="stylesheet" href="<?php echo htmlspecialchars(BASE_URL . '/assets/css/admin_creacion_usuarios.css'); ?>">
</head>
<body>
  <?php $navActive = 'admin_usuarios'; require BASE_PATH . '/views/auth/header.php'; ?>

  <main>
    <?php if (!empty($mensaje)): ?>
      <p class="flash-ok"><?php echo htmlspecialchars($mensaje); ?></p>
    <?php endif; ?>
    <?php if (!empty($error)): ?>
      <p class="flash-err"><?php echo htmlspecialchars($error); ?></p>
    <?php endif; ?>

    <div class="card card-lista">
      <div class="lista-header">
        <h2>Usuarios existentes</h2>
        <button type="button" class="btn btn-crear-usuario" data-abrir-usuario-nuevo>Crear usuario</button>
      </div>

      <form class="usuarios-filtros" method="get" action="<?php echo htmlspecialchars(BASE_URL . '/index.php'); ?>">
        <input type="hidden" name="c" value="admin">
        <input type="hidden" name="a" value="creacion_usuarios">

        <div class="filtros-row">
          <div class="filtro-field filtro-field--grow">
            <label for="q">Buscar</label>
            <input
              type="search"
              id="q"
              name="q"
              placeholder="Nombre o cédula"
              value="<?php echo htmlspecialchars($filtroBusqueda); ?>"
            >
          </div>
          <div class="filtro-field">
            <label for="empresa">Empresa</label>
            <select id="empresa" name="empresa">
              <option value="" <?php echo $filtroEmpresa === '' ? 'selected' : ''; ?>>Todas</option>
              <option value="onix" <?php echo $filtroEmpresa === 'onix' ? 'selected' : ''; ?>>Onix</option>
              <option value="nextdata" <?php echo $filtroEmpresa === 'nextdata' ? 'selected' : ''; ?>>Nextdata</option>
              <option value="tys" <?php echo $filtroEmpresa === 'tys' ? 'selected' : ''; ?>>TyS</option>
            </select>
          </div>
          <div class="filtro-actions">
            <button type="submit" class="btn-filtrar">Buscar</button>
            <a class="btn-limpiar" href="<?php echo htmlspecialchars($usuariosListUrl(['q' => '', 'empresa' => '', 'p' => ''])); ?>">Limpiar</a>
          </div>
        </div>
      </form>

      <p class="listado-resumen muted">
        <?php if ($totalUsuarios === 0): ?>
          Sin resultados
        <?php else: ?>
          Mostrando <?php echo $desde; ?>–<?php echo $hasta; ?> de <?php echo $totalUsuarios; ?> usuario(s)
        <?php endif; ?>
      </p>

      <div class="table-wrap">
        <table>
          <thead>
            <tr>
              <th>Cédula</th>
              <th>Nombre</th>
              <th>Usuario</th>
              <th>Rol</th>
              <th>Empresa</th>
              <th>Estado</th>
              <th></th>
            </tr>
          </thead>
          <tbody>
            <?php if ($usuarios === []): ?>
              <tr>
                <td colspan="7" class="td-empty">No hay usuarios que coincidan con la búsqueda.</td>
              </tr>
            <?php else: ?>
            <?php
            $empresaLabels = [
                'onix' => 'Onix',
                'nextdata' => 'Nextdata',
                'tys' => 'TyS',
            ];
            foreach ($usuarios as $u):
                $empresaKey = strtolower((string) ($u['empresa'] ?? 'onix'));
                $empresaLabel = $empresaLabels[$empresaKey] ?? ucfirst($empresaKey);
                $estado = (string) ($u['estado'] ?? '');
                $activo = $estado === 'activo';
                ?>
              <tr>
                <td><?php echo htmlspecialchars((string) ($u['cedula'] ?? '')); ?></td>
                <td><?php echo htmlspecialchars((string) ($u['nombre'] ?? '')); ?></td>
                <td><code class="login-user"><?php echo htmlspecialchars((string) ($u['usuario'] ?? '')); ?></code></td>
                <td><?php echo htmlspecialchars((string) ($u['rol'] ?? '')); ?></td>
                <td><?php echo htmlspecialchars($empresaLabel); ?></td>
                <td>
                  <span class="estado-badge <?php echo $activo ? 'estado-badge--activo' : 'estado-badge--inactivo'; ?>">
                    <?php echo htmlspecialchars($activo ? 'Activo' : ($estado !== '' ? ucfirst($estado) : 'Inactivo')); ?>
                  </span>
                </td>
                <td>
                  <button
                    type="button"
                    class="btn-secondary btn-editar-usuario"
                    data-cedula="<?php echo htmlspecialchars((string) ($u['cedula'] ?? '')); ?>"
                    data-nombre="<?php echo htmlspecialchars((string) ($u['nombre'] ?? '')); ?>"
                    data-usuario="<?php echo htmlspecialchars((string) ($u['usuario'] ?? '')); ?>"
                    data-rol="<?php echo htmlspecialchars((string) ($u['rol'] ?? '')); ?>"
                    data-email="<?php echo htmlspecialchars((string) ($u['email'] ?? '')); ?>"
                    data-estado="<?php echo htmlspecialchars($estado); ?>"
                  >Editar</button>
                </td>
              </tr>
            <?php endforeach; ?>
            <?php endif; ?>
          </tbody>
        </table>
      </div>

      <?php if ($totalPaginas > 1): ?>
        <nav class="paginador" aria-label="Paginación de usuarios">
          <?php if ($paginaActual > 1): ?>
            <a class="paginador-link" href="<?php echo htmlspecialchars($usuariosListUrl(['p' => (string) ($paginaActual - 1)])); ?>">« Anterior</a>
          <?php else: ?>
            <span class="paginador-link paginador-link--disabled">« Anterior</span>
          <?php endif; ?>

          <?php
          $ventana = 2;
          $inicio = max(1, $paginaActual - $ventana);
          $fin = min($totalPaginas, $paginaActual + $ventana);
          for ($p = $inicio; $p <= $fin; $p++):
              $activa = $p === $paginaActual;
              ?>
            <?php if ($activa): ?>
              <span class="paginador-num paginador-num--active"><?php echo $p; ?></span>
            <?php else: ?>
              <a class="paginador-num" href="<?php echo htmlspecialchars($usuariosListUrl(['p' => (string) $p])); ?>"><?php echo $p; ?></a>
            <?php endif; ?>
          <?php endfor; ?>

          <?php if ($paginaActual < $totalPaginas): ?>
            <a class="paginador-link" href="<?php echo htmlspecialchars($usuariosListUrl(['p' => (string) ($paginaActual + 1)])); ?>">Siguiente »</a>
          <?php else: ?>
            <span class="paginador-link paginador-link--disabled">Siguiente »</span>
          <?php endif; ?>
        </nav>
      <?php endif; ?>
    </div>
  </main>

  <dialog id="usuario-modal" class="usuario-dialog" aria-labelledby="usuario-modal-titulo">
    <div class="usuario-dialog-inner">
      <button type="button" class="usuario-dialog-close" data-usuario-modal-cerrar aria-label="Cerrar">&times;</button>
      <h2 id="usuario-modal-titulo" class="usuario-dialog-titulo">Nuevo usuario</h2>

      <form
        id="usuario-form"
        class="usuario-form"
        method="post"
        action="<?php echo htmlspecialchars(BASE_URL . '/index.php?c=admin&a=usuarios_guardar'); ?>"
      >
        <input type="hidden" name="list_q" value="<?php echo htmlspecialchars($filtroBusqueda); ?>">
        <input type="hidden" name="list_empresa" value="<?php echo htmlspecialchars($filtroEmpresa); ?>">
        <input type="hidden" name="list_p" value="<?php echo $paginaActual > 1 ? (int) $paginaActual : ''; ?>">

        <label for="cedula">Cédula</label>
        <input type="text" id="cedula" name="cedula" required value="">

        <label for="nombre">Nombre</label>
        <input type="text" id="nombre" name="nombre" required value="">

        <label for="usuario">Usuario (login)</label>
        <input type="text" id="usuario" name="usuario" required value="">

        <label for="clave">Clave</label>
        <input type="password" id="clave" name="clave" required value="">
        <div class="help" id="help-clave-editar" hidden>Si la clave está vacía, se mantendrá la contraseña actual.</div>

        <div id="wrap-clave-confirmar">
          <label for="clave_confirmar">Confirmar clave</label>
          <input
            type="password"
            id="clave_confirmar"
            name="clave_confirmar"
            required
            placeholder="Repita la clave"
            value=""
          >
        </div>

        <label for="rol">Rol</label>
        <select id="rol" name="rol" required>
          <option value="administrador">administrador</option>
          <option value="coordinador">coordinador</option>
          <option value="asesor" selected>asesor</option>
        </select>

        <label for="email">Email</label>
        <input type="email" id="email" name="email" required value="">

        <label for="estado">Estado</label>
        <select id="estado" name="estado" required>
          <option value="activo" selected>activo</option>
          <option value="inactivo">inactivo</option>
        </select>

        <div class="usuario-form-actions">
          <button type="button" class="btn-cancelar" data-usuario-modal-cerrar>Cancelar</button>
          <button type="submit" class="btn-submit-usuario" id="usuario-form-submit">Crear usuario</button>
        </div>
      </form>
    </div>
  </dialog>

  <script>
    window.USUARIO_EDIT_INICIAL = <?php echo $usuarioEditJson; ?>;
  </script>
  <script src="<?php echo htmlspecialchars(BASE_URL . '/assets/js/admin_creacion_usuarios.js'); ?>"></script>
</body>
</html>
