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

    <div class="layout">
      <section class="card">
        <h2><?php echo !empty($usuarioEdit) ? 'Editar usuario' : 'Nuevo usuario'; ?></h2>
        <form method="post" action="<?php echo htmlspecialchars(BASE_URL . '/index.php?c=admin&a=usuarios_guardar'); ?>">
          <?php $modoEditar = !empty($usuarioEdit); ?>

          <label for="cedula">Cédula</label>
          <input
            type="text"
            id="cedula"
            name="cedula"
            required
            <?php echo $modoEditar ? 'readonly' : ''; ?>
            value="<?php echo htmlspecialchars((string) ($usuarioEdit['cedula'] ?? '')); ?>"
          >

          <label for="nombre">Nombre</label>
          <input type="text" id="nombre" name="nombre" required value="<?php echo htmlspecialchars((string) ($usuarioEdit['nombre'] ?? '')); ?>">

          <label for="usuario">Usuario (login)</label>
          <input type="text" id="usuario" name="usuario" required value="<?php echo htmlspecialchars((string) ($usuarioEdit['usuario'] ?? '')); ?>">

          <label for="clave">Clave</label>
          <input
            type="password"
            id="clave"
            name="clave"
            <?php echo $modoEditar ? '' : 'required'; ?>
            placeholder="<?php echo $modoEditar ? 'Dejar vacío para no cambiar' : ''; ?>"
          >
          <?php if ($modoEditar): ?>
            <div class="help">Si la clave está vacía, se mantendrá la contraseña actual.</div>
          <?php endif; ?>

          <?php if (!$modoEditar): ?>
            <label for="clave_confirmar">Confirmar clave</label>
            <input
              type="password"
              id="clave_confirmar"
              name="clave_confirmar"
              required
              placeholder="Repita la clave"
            >
          <?php endif; ?>

          <label for="rol">Rol</label>
          <select id="rol" name="rol" required>
            <?php
            $rolActual = (string) ($usuarioEdit['rol'] ?? 'asesor');
            foreach (['administrador', 'coordinador', 'asesor'] as $r) {
                $sel = $rolActual === $r ? 'selected' : '';
                echo '<option value="' . htmlspecialchars($r) . '" ' . $sel . '>' . htmlspecialchars($r) . '</option>';
            }
            ?>
          </select>

          <label for="email">Email</label>
          <input type="email" id="email" name="email" required value="<?php echo htmlspecialchars((string) ($usuarioEdit['email'] ?? '')); ?>">

          <label for="estado">Estado</label>
          <select id="estado" name="estado" required>
            <?php
            $estadoActual = (string) ($usuarioEdit['estado'] ?? 'activo');
            foreach (['activo', 'inactivo'] as $e) {
                $sel = $estadoActual === $e ? 'selected' : '';
                echo '<option value="' . htmlspecialchars($e) . '" ' . $sel . '>' . htmlspecialchars($e) . '</option>';
            }
            ?>
          </select>

          <button type="submit"><?php echo $modoEditar ? 'Guardar cambios' : 'Crear usuario'; ?></button>
        </form>
      </section>

      <aside class="card">
        <h2>Usuarios existentes</h2>
        <div class="table-wrap">
          <table>
            <thead>
              <tr>
                <th>Cédula</th>
                <th>Nombre</th>
                <th>Usuario</th>
                <th>Rol</th>
                <th></th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($usuarios as $u): ?>
                <tr>
                  <td><?php echo htmlspecialchars((string) ($u['cedula'] ?? '')); ?></td>
                  <td><?php echo htmlspecialchars((string) ($u['nombre'] ?? '')); ?></td>
                  <td><code class="login-user"><?php echo htmlspecialchars((string) ($u['usuario'] ?? '')); ?></code></td>
                  <td><?php echo htmlspecialchars((string) ($u['rol'] ?? '')); ?></td>
                  <td>
                    <a
                      class="btn-secondary"
                      href="<?php echo htmlspecialchars(BASE_URL . '/index.php?c=admin&a=creacion_usuarios&cedula=' . rawurlencode((string) ($u['cedula'] ?? ''))); ?>"
                    >Editar</a>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </aside>
    </div>
  </main>
</body>
</html>

