<?php

require dirname(__DIR__) . '/config.php';

spl_autoload_register(static function (string $class): void {
    $base = dirname(__DIR__);
    foreach (['/core/', '/controllers/', '/models/'] as $dir) {
        $path = $base . $dir . $class . '.php';
        if (is_file($path)) {
            require_once $path;
            return;
        }
    }
});

$pdo = getPDO();

$_SESSION['usuario_rol'] = 'coordinador';
$_SESSION['usuario_nombre'] = 'Test';
$_SESSION['usuario_cedula'] = '';

$idCurso = (int) ($pdo->query('SELECT id_cursos FROM cursos ORDER BY id_cursos DESC LIMIT 1')->fetchColumn() ?: 0);

if ($idCurso <= 0) {
    echo "No hay cursos en la DB\n";
    exit(1);
}

$_SESSION['usuario_cedula'] = (string) ($pdo->query("SELECT cedula_coordinador FROM cursos WHERE id_cursos = $idCurso")->fetchColumn() ?: '');

echo "Testing preguntas for curso $idCurso (cedula coord: {$_SESSION['usuario_cedula']})\n";

try {
    $curso = Curso::buscar($pdo, $idCurso);
    echo "Curso: " . ($curso['nombre_curso'] ?? 'null') . "\n";

    $cfg = CursoEvaluacion::getConfig($pdo, $idCurso);
    echo "Config: preguntas_requeridas=" . ($cfg['preguntas_requeridas'] ?? '?') . ", activo=" . ($cfg['activo'] ?? '?') . "\n";

    $pregs = CursoEvaluacion::preguntasPorCurso($pdo, $idCurso);
    echo "Preguntas count: " . count($pregs) . "\n";

    echo "\nSin errores en la lógica del controlador.\n";
} catch (Throwable $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . ":" . $e->getLine() . "\n";
}
