<?php

declare(strict_types=1);

require __DIR__ . '/../config.php';

/**
 * Verifica si el coordinador cargó evaluación final por curso.
 * - Legacy: preguntas_evaluacion
 * - Nuevo: curso_eval_config + curso_eval_preguntas (+ opciones/respuesta)
 *
 * Uso:
 *   php tools/check_curso_eval_status.php
 */

function table_exists(PDO $pdo, string $table): bool
{
    $st = $pdo->prepare(
        'SELECT COUNT(*) AS n
         FROM information_schema.tables
         WHERE table_schema = :db AND table_name = :t'
    );
    $st->execute(['db' => DB_NAME, 't' => $table]);
    $row = $st->fetch();
    return (int) ($row['n'] ?? 0) > 0;
}

function col(PDO $pdo, string $sql, array $params = []): int
{
    $st = $pdo->prepare($sql);
    $st->execute($params);
    return (int) $st->fetchColumn();
}

try {
    $pdo = getPDO();
} catch (PDOException $e) {
    fwrite(STDERR, 'No se pudo conectar a MySQL: ' . $e->getMessage() . PHP_EOL);
    exit(1);
}

$hasLegacy = table_exists($pdo, 'preguntas_evaluacion');
$hasCfg = table_exists($pdo, 'curso_eval_config');
$hasPreg = table_exists($pdo, 'curso_eval_preguntas');
$hasOps = table_exists($pdo, 'curso_eval_opciones');
$hasResp = table_exists($pdo, 'curso_eval_respuesta');

echo "DB: " . DB_NAME . PHP_EOL;
echo "Tablas:"
    . " preguntas_evaluacion=" . ($hasLegacy ? 'SI' : 'NO')
    . " curso_eval_config=" . ($hasCfg ? 'SI' : 'NO')
    . " curso_eval_preguntas=" . ($hasPreg ? 'SI' : 'NO')
    . " curso_eval_opciones=" . ($hasOps ? 'SI' : 'NO')
    . " curso_eval_respuesta=" . ($hasResp ? 'SI' : 'NO')
    . PHP_EOL . PHP_EOL;

$cursos = $pdo->query('SELECT id_cursos, nombre_curso, evaluacion_nombre FROM cursos ORDER BY id_cursos DESC')->fetchAll();
if (!$cursos) {
    echo "No hay cursos.\n";
    exit(0);
}

echo str_pad('ID', 5)
    . str_pad('LEGACY_Q', 10)
    . str_pad('NUEVO_Q', 9)
    . str_pad('REQ', 5)
    . str_pad('ACT', 5)
    . "NOMBRE_CURSO / EVAL_NOMBRE\n";
echo str_repeat('-', 110) . "\n";

foreach ($cursos as $c) {
    $id = (int) ($c['id_cursos'] ?? 0);
    $nombreCurso = (string) ($c['nombre_curso'] ?? '');
    $evalNombre = (string) ($c['evaluacion_nombre'] ?? '');

    $legacyCount = 0;
    if ($hasLegacy && $id > 0) {
        $legacyCount = col($pdo, 'SELECT COUNT(*) FROM preguntas_evaluacion WHERE id_curso = :c', ['c' => $id]);
    }

    $nuevoCount = 0;
    $req = 0;
    $act = 0;
    if ($hasPreg && $id > 0) {
        $nuevoCount = col($pdo, 'SELECT COUNT(*) FROM curso_eval_preguntas WHERE id_curso = :c', ['c' => $id]);
    }
    if ($hasCfg && $id > 0) {
        $st = $pdo->prepare('SELECT preguntas_requeridas, activo FROM curso_eval_config WHERE id_curso = :c LIMIT 1');
        $st->execute(['c' => $id]);
        $row = $st->fetch() ?: [];
        $req = (int) ($row['preguntas_requeridas'] ?? 0);
        $act = (int) ($row['activo'] ?? 0);
    }

    $line = str_pad((string) $id, 5)
        . str_pad((string) $legacyCount, 10)
        . str_pad((string) $nuevoCount, 9)
        . str_pad((string) $req, 5)
        . str_pad((string) $act, 5)
        . $nombreCurso;

    if ($evalNombre !== '') {
        $line .= " / " . $evalNombre;
    }
    echo $line . "\n";
}

