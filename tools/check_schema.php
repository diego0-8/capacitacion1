<?php
declare(strict_types=1);

require __DIR__ . '/../config.php';

$pdo = getPDO();
$checks = [
    'capacitaciones_asignadas' => ['fecha_primera_descarga_certificado'],
    'usuarios' => ['empresa', 'pin_verificacion_hash', 'pin_verificacion_expira_en', 'pin_verificacion_intentos'],
    'cursos' => ['cedula_coordinador', 'acceso_asesores'],
];

$tableChecks = [
    'curso_asesores_permitidos',
    'curso_eval_config',
    'curso_eval_preguntas',
    'curso_eval_opciones',
    'curso_eval_respuesta',
];

$columnChecksExtra = [
    'curso_eval_preguntas' => ['enunciado_imagen_path'],
];

$missing = [];
foreach ($checks as $table => $cols) {
    $st = $pdo->prepare(
        'SELECT column_name FROM information_schema.columns
         WHERE table_schema = :db AND table_name = :t'
    );
    $st->execute(['db' => DB_NAME, 't' => $table]);
    $have = array_column($st->fetchAll(), 'column_name');
    foreach ($cols as $c) {
        if (!in_array($c, $have, true)) {
            $missing[] = "{$table}.{$c}";
        }
    }
}

$stTbl = $pdo->prepare(
    'SELECT table_name FROM information_schema.tables WHERE table_schema = :db'
);
$stTbl->execute(['db' => DB_NAME]);
$tables = array_column($stTbl->fetchAll(), 'table_name');
foreach ($tableChecks as $t) {
    if (!in_array($t, $tables, true)) {
        $missing[] = "tabla:{$t}";
    }
}

foreach ($columnChecksExtra as $table => $cols) {
    if (!in_array($table, $tables, true)) {
        continue;
    }
    $st = $pdo->prepare(
        'SELECT column_name FROM information_schema.columns
         WHERE table_schema = :db AND table_name = :t'
    );
    $st->execute(['db' => DB_NAME, 't' => $table]);
    $have = array_column($st->fetchAll(), 'column_name');
    foreach ($cols as $c) {
        if (!in_array($c, $have, true)) {
            $missing[] = "{$table}.{$c}";
        }
    }
}

if ($missing) {
    fwrite(STDERR, "FALTAN columnas/tablas:\n" . implode("\n", $missing) . "\n");
    exit(1);
}

echo "OK: columnas y tablas requeridas presentes\n";
