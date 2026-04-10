<?php
declare(strict_types=1);

require __DIR__ . '/../config.php';

$files = [
    'views/admin/asignaciones.php',
    'views/admin/atrasados.php',
    'views/admin/creacion_usuarios.php',
    'views/admin/curso_form.php',
    'views/admin/cursos.php',
    'views/admin/index.php',
    'views/admin/progreso.php',
];

$missing = [];
foreach ($files as $f) {
    $full = BASE_PATH . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $f);
    $c = @file_get_contents($full);
    if ($c === false) {
        $missing[] = "NOFILE {$f}";
        continue;
    }
    if (strpos($c, "views/auth/header.php") === false) {
        $missing[] = "MISSING {$f}";
    }
}

if ($missing) {
    fwrite(STDERR, implode("\n", $missing) . "\n");
    exit(1);
}

echo "OK: todas las vistas admin incluyen views/auth/header.php\n";

