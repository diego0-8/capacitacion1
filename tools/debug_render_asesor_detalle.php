<?php

declare(strict_types=1);

/**
 * Renderiza la vista asesor_detalle en CLI y registra warnings/estructura.
 * Uso: php tools/debug_render_asesor_detalle.php
 */

$logPath = dirname(__DIR__) . '/debug-ee9745.log';

function agentLog(string $hypothesisId, string $location, string $message, array $data = []): void
{
    global $logPath;
    $line = json_encode([
        'sessionId' => 'ee9745',
        'hypothesisId' => $hypothesisId,
        'location' => $location,
        'message' => $message,
        'data' => $data,
        'timestamp' => (int) round(microtime(true) * 1000),
        'runId' => 'post-fix',
    ], JSON_UNESCAPED_UNICODE) . "\n";
    file_put_contents($logPath, $line, FILE_APPEND);
}

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

$_SESSION['usuario_rol'] = 'coordinador';
$_SESSION['usuario_nombre'] = 'Debug';

$pdo = getPDO();
$idCurso = (int) ($pdo->query('SELECT id_cursos FROM cursos ORDER BY id_cursos DESC LIMIT 1')->fetchColumn() ?: 0);
$cedula = (string) ($pdo->query("SELECT cedula FROM usuarios WHERE rol = 'asesor' AND estado = 'activo' LIMIT 1")->fetchColumn() ?: '');

$curso = Curso::buscar($pdo, $idCurso) ?? ['id_cursos' => $idCurso, 'nombre_curso' => ''];
$data = CoordinadorReporte::detalleAsesor($pdo, $curso, $idCurso, $cedula);
$data['asesor_inactivo'] = $data['asesor_inactivo'] ?? false;

agentLog('H-B', 'tools/debug_render_asesor_detalle.php', 'detalleAsesor payload', [
    'idCurso' => $idCurso,
    'cedula' => $cedula,
    'asesor_inactivo' => $data['asesor_inactivo'] ?? null,
    'timeline_count' => count($data['timeline'] ?? []),
    'asesor_keys' => array_keys($data['asesor'] ?? []),
]);

$viewFile = dirname(__DIR__) . '/views/coordinador/asesor_detalle.php';
$src = file_get_contents($viewFile) ?: '';
agentLog('H-A', 'tools/debug_render_asesor_detalle.php', 'view structure scan', [
    'lines' => substr_count($src, "\n") + 1,
    'if_colon' => substr_count($src, 'if (') + substr_count($src, 'if('),
    'endif_count' => substr_count($src, 'endif'),
    'endforeach_count' => substr_count($src, 'endforeach'),
    'else_colon' => substr_count($src, 'else:'),
    'foreach_colon' => preg_match_all('/foreach\s*\([^)]+\)\s*:/', $src),
    'open_brace_if' => substr_count($src, 'if (') + substr_count($src, '} elseif'),
]);

$errors = [];
set_error_handler(static function (int $errno, string $errstr, string $file, int $line) use (&$errors): bool {
    $errors[] = ['errno' => $errno, 'msg' => $errstr, 'file' => basename($file), 'line' => $line];
    return true;
});

ob_start();
extract($data, EXTR_SKIP);
require $viewFile;
$html = ob_get_clean();
restore_error_handler();

agentLog('H-C', 'tools/debug_render_asesor_detalle.php', 'render result', [
    'html_bytes' => strlen($html),
    'php_warnings' => count($errors),
    'warnings_sample' => array_slice($errors, 0, 5),
]);

echo "Log escrito en debug-ee9745.log\n";
echo 'Warnings: ' . count($errors) . "\n";
