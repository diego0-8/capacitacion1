<?php

declare(strict_types=1);

define('DB_HOST', '127.0.0.1');
define('DB_NAME', 'capacitacion1');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

define('BASE_PATH', __DIR__);

$scriptDir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? ''));
define('BASE_URL', rtrim($scriptDir === '/' ? '' : $scriptDir, '/'));

define('UPLOADS_CURSOS_PATH', BASE_PATH . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'cursos');
define('UPLOADS_COORDINADOR_VIDEOS_PATH', BASE_PATH . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'coordinador' . DIRECTORY_SEPARATOR . 'videos');
define('UPLOAD_MAX_BYTES', 50 * 1024 * 1024);

define('SMTP_HOST', '');
define('SMTP_PORT', 587);
define('SMTP_USER', '');
define('SMTP_PASS', '');
define('SMTP_FROM_EMAIL', '');
define('SMTP_FROM_NAME', 'Capacitación');

function getPDO(): PDO
{
    static $pdo = null;
    if ($pdo instanceof PDO) {
        return $pdo;
    }
    $dsn = sprintf('mysql:host=%s;dbname=%s;charset=%s', DB_HOST, DB_NAME, DB_CHARSET);
    $pdo = new PDO($dsn, DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
    return $pdo;
}

if (!function_exists('debug_log')) {
    /**
     * Log NDJSON para debug mode (sin PII).
     * @param array<string,mixed> $data
     */
    function debug_log(string $location, string $message, array $data = [], string $runId = 'run1', string $hypothesisId = 'H'): void
    {
        $payload = [
            'sessionId' => '335d18',
            'runId' => $runId,
            'hypothesisId' => $hypothesisId,
            'location' => $location,
            'message' => $message,
            'data' => $data,
            'timestamp' => (int) round(microtime(true) * 1000),
        ];
        $line = json_encode($payload, JSON_UNESCAPED_UNICODE) . PHP_EOL;
        @file_put_contents(BASE_PATH . DIRECTORY_SEPARATOR . 'debug-335d18.log', $line, FILE_APPEND);
    }
}
