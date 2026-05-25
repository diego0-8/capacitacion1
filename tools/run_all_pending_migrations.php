<?php

require dirname(__DIR__) . '/config.php';
$pdo = getPDO();

$migrations = [
    'migration_lecciones_pdf_path.sql' => "ALTER TABLE lecciones ADD COLUMN IF NOT EXISTS pdf_path VARCHAR(255) NULL DEFAULT NULL AFTER video_path",
    'migration_curso_acceso_asesor.sql' => null,
];

foreach ($migrations as $file => $directSql) {
    echo "--- $file ---\n";
    if ($directSql !== null) {
        try {
            $pdo->exec($directSql);
            echo "  OK\n";
        } catch (Throwable $e) {
            echo "  " . $e->getMessage() . "\n";
        }
    } else {
        $path = dirname(__DIR__) . '/database/' . $file;
        if (!is_file($path)) {
            echo "  FILE NOT FOUND\n";
            continue;
        }
        $sql = file_get_contents($path);
        $statements = preg_split('/;\s*$/m', $sql);
        foreach ($statements as $s) {
            $s = trim($s);
            if ($s === '' || str_starts_with($s, '--')) {
                continue;
            }
            try {
                $pdo->exec($s);
                echo "  OK: " . substr($s, 0, 50) . "...\n";
            } catch (Throwable $e) {
                echo "  " . $e->getMessage() . "\n";
            }
        }
    }
}
echo "\nDone.\n";
