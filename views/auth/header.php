<?php
declare(strict_types=1);

/**
 * Navbar único para todos los roles.
 *
 * Variables opcionales desde la vista:
 * - $navActive: string clave de sección activa
 */

/** @var string $navActive */
$navActive = isset($navActive) ? (string) $navActive : '';

$rol = (string) ($_SESSION['usuario_rol'] ?? '');
$nombre = (string) ($_SESSION['usuario_nombre'] ?? '');

$link = static function (string $label, string $href, string $key) use ($navActive): string {
    $class = $navActive === $key ? ' class="active"' : '';
    return '<a' . $class . ' href="' . htmlspecialchars($href) . '">' . htmlspecialchars($label) . '</a>';
};

$brand = match ($rol) {
    'administrador' => 'Administrador',
    'coordinador' => 'Coordinador',
    'asesor' => 'Asesor',
    default => 'Usuario',
};
// #region agent log
@file_put_contents(
    BASE_PATH . DIRECTORY_SEPARATOR . 'debug-4338d8.log',
    json_encode(
        [
            'sessionId' => '4338d8',
            'runId' => 'post-fix',
            'hypothesisId' => 'H1',
            'location' => 'views/auth/header.php:render',
            'message' => 'header render',
            'data' => [
                'rol' => $rol,
                'navActive' => $navActive,
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
<nav class="topnav">
  <span><?php echo htmlspecialchars($brand); ?><?php echo $nombre !== '' ? ' — ' . htmlspecialchars($nombre) : ''; ?></span>

  <?php if ($rol === 'administrador'): ?>
    <?php echo $link('Inicio', BASE_URL . '/index.php?c=admin&a=index', 'admin_index'); ?>
    <?php echo $link('Cursos', BASE_URL . '/index.php?c=admin&a=cursos', 'admin_cursos'); ?>
    <?php echo $link('Asignaciones', BASE_URL . '/index.php?c=admin&a=asignaciones', 'admin_asignaciones'); ?>
    <?php echo $link('Usuarios', BASE_URL . '/index.php?c=admin&a=creacion_usuarios', 'admin_usuarios'); ?>
    <?php echo $link('Progreso', BASE_URL . '/index.php?c=admin&a=progreso', 'admin_progreso'); ?>
    <?php echo $link('Atrasados', BASE_URL . '/index.php?c=admin&a=atrasados', 'admin_atrasados'); ?>
  <?php elseif ($rol === 'coordinador'): ?>
    <?php echo $link('Mis cursos', BASE_URL . '/index.php?c=coordinador&a=index', 'coord_index'); ?>
    <?php echo $link('Reportes', BASE_URL . '/index.php?c=coordinador&a=reportes', 'coord_reportes'); ?>
  <?php elseif ($rol === 'asesor'): ?>
    <?php echo $link('Mis capacitaciones', BASE_URL . '/index.php?c=asesor&a=index', 'asesor_index'); ?>
  <?php endif; ?>

  <a href="<?php echo htmlspecialchars(BASE_URL . '/index.php?c=auth&a=logout'); ?>">Salir</a>
</nav>

