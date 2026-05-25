<?php

require dirname(__DIR__) . '/config.php';
$pdo = getPDO();
$sql = file_get_contents(dirname(__DIR__) . '/database/migration_curso_eval.sql');
$statements = array_filter(array_map('trim', explode(';', $sql)));
foreach ($statements as $s) {
    if ($s !== '' && !str_starts_with($s, '--')) {
        $pdo->exec($s);
        echo "OK: " . substr($s, 0, 60) . "...\n";
    }
}
echo "\nMigration curso_eval completada.\n";
