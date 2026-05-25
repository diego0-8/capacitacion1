<?php

declare(strict_types=1);

/**
 * Diagnostica por qué el IDE marca error al final de views/coordinador/asesor_detalle.php.
 *
 * Uso:
 *   php tools/diagnose_asesor_detalle.php
 */

$root = dirname(__DIR__);
$viewPath = $root . '/views/coordinador/asesor_detalle.php';
$logPath = $root . '/debug-ee9745.log';

function agentLog(string $hypothesisId, string $message, array $data = []): void
{
    global $logPath;

    $payload = [
        'sessionId' => 'ee9745',
        'runId' => 'diagnose-asesor-detalle',
        'hypothesisId' => $hypothesisId,
        'location' => 'tools/diagnose_asesor_detalle.php',
        'message' => $message,
        'data' => $data,
        'timestamp' => (int) round(microtime(true) * 1000),
    ];

    // #region agent log
    file_put_contents($logPath, json_encode($payload, JSON_UNESCAPED_UNICODE) . PHP_EOL, FILE_APPEND);
    // #endregion
}

if (!is_file($viewPath)) {
    agentLog('H0', 'view file missing', ['path' => $viewPath]);
    fwrite(STDERR, "No existe la vista: {$viewPath}" . PHP_EOL);
    exit(1);
}

$src = (string) file_get_contents($viewPath);
$lines = preg_split('/\R/', $src);
$lineCount = is_array($lines) ? count($lines) : 0;
$lastLine = $lineCount > 0 ? (string) $lines[$lineCount - 1] : '';
$tail = substr($src, -80);

$lintOutput = [];
$lintCode = 0;
exec(PHP_BINARY . ' -l ' . escapeshellarg($viewPath) . ' 2>&1', $lintOutput, $lintCode);

agentLog('H2', 'php lint result', [
    'exit_code' => $lintCode,
    'output' => implode("\n", $lintOutput),
]);

$tokens = token_get_all($src);
$phpBraceBalance = 0;
$phpBraceMin = 0;
$phpOpenTags = 0;
$phpCloseTags = 0;
$altSyntax = [
    'if_colon' => preg_match_all('/<\?php\s+if\s*\([^?]*\)\s*:/', $src),
    'else_colon' => substr_count($src, 'else:'),
    'endif' => substr_count($src, 'endif'),
    'foreach_colon' => preg_match_all('/<\?php\s+foreach\s*\([^?]*\)\s*:/', $src),
    'endforeach' => substr_count($src, 'endforeach'),
];

foreach ($tokens as $token) {
    if (is_array($token)) {
        if ($token[0] === T_OPEN_TAG || $token[0] === T_OPEN_TAG_WITH_ECHO) {
            $phpOpenTags++;
        } elseif ($token[0] === T_CLOSE_TAG) {
            $phpCloseTags++;
        }
        continue;
    }

    if ($token === '{') {
        $phpBraceBalance++;
    } elseif ($token === '}') {
        $phpBraceBalance--;
        $phpBraceMin = min($phpBraceMin, $phpBraceBalance);
    }
}

agentLog('H1', 'eof and trailing line scan', [
    'line_count' => $lineCount,
    'last_line_is_empty' => $lastLine === '',
    'ends_with_lf' => str_ends_with($src, "\n"),
    'ends_with_crlf' => str_ends_with($src, "\r\n"),
    'tail_hex' => bin2hex($tail),
]);

agentLog('H2', 'php block structure scan', [
    'php_open_tags' => $phpOpenTags,
    'php_close_tags' => $phpCloseTags,
    'php_brace_balance' => $phpBraceBalance,
    'php_brace_min' => $phpBraceMin,
    'alternative_syntax' => $altSyntax,
]);

$bom = str_starts_with($src, "\xEF\xBB\xBF");
$controlMatches = [];
preg_match_all('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/', $src, $controlMatches, PREG_OFFSET_CAPTURE);
$invalidUtf8 = !mb_check_encoding($src, 'UTF-8');

agentLog('H3', 'encoding and invisible character scan', [
    'has_bom' => $bom,
    'invalid_utf8' => $invalidUtf8,
    'control_chars_count' => count($controlMatches[0] ?? []),
    'control_chars_sample' => array_slice($controlMatches[0] ?? [], 0, 10),
]);

if (!defined('BASE_PATH')) {
    define('BASE_PATH', $root);
}
if (!defined('BASE_URL')) {
    define('BASE_URL', '');
}

$_SESSION['usuario_rol'] = $_SESSION['usuario_rol'] ?? 'coordinador';
$_SESSION['usuario_nombre'] = $_SESSION['usuario_nombre'] ?? 'Diagnostico';

$curso = ['id_cursos' => 1, 'nombre_curso' => 'Curso diagnostico'];
$asesor = [
    'cedula' => '0000000000',
    'nombre' => 'Asesor Diagnostico',
    'estado_capacitacion' => 'asignado',
    'progreso_porcentaje' => 0,
    'fecha_asignacion' => '',
    'calificacion_obtenida' => '',
    'fecha_completado' => '',
];
$timeline = [];
$asesor_inactivo = false;

$warnings = [];
set_error_handler(static function (int $errno, string $errstr, string $file, int $line) use (&$warnings): bool {
    $warnings[] = [
        'errno' => $errno,
        'message' => $errstr,
        'file' => str_replace('\\', '/', $file),
        'line' => $line,
    ];
    return true;
});

ob_start();
require $viewPath;
$html = (string) ob_get_clean();
restore_error_handler();

agentLog('H4', 'isolated render result', [
    'html_bytes' => strlen($html),
    'warnings_count' => count($warnings),
    'warnings_sample' => array_slice($warnings, 0, 10),
]);

echo 'Diagnostico completado. Revise debug-ee9745.log' . PHP_EOL;
echo 'Lint: ' . ($lintCode === 0 ? 'OK' : 'ERROR') . PHP_EOL;
echo 'Lineas: ' . $lineCount . ', ultima vacia: ' . ($lastLine === '' ? 'SI' : 'NO') . PHP_EOL;
echo 'Warnings render: ' . count($warnings) . PHP_EOL;
