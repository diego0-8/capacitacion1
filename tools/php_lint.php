<?php
declare(strict_types=1);

/**
 * Recorre archivos PHP del proyecto y ejecuta `php -l` sobre cada uno.
 * Uso: php tools/php_lint.php
 */

$targets = [
    __DIR__ . '/../index.php',
    __DIR__ . '/../config.php',
    __DIR__ . '/../crear_hash.php',
    __DIR__ . '/../controllers',
    __DIR__ . '/../models',
    __DIR__ . '/../core',
    __DIR__ . '/../views',
];

$files = [];
foreach ($targets as $t) {
    if (is_file($t)) {
        $files[] = realpath($t) ?: $t;
        continue;
    }
    if (!is_dir($t)) {
        continue;
    }
    $it = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($t, FilesystemIterator::SKIP_DOTS)
    );
    foreach ($it as $f) {
        if (!$f->isFile()) {
            continue;
        }
        if (strtolower($f->getExtension()) !== 'php') {
            continue;
        }
        $files[] = $f->getPathname();
    }
}

sort($files);
$php = PHP_BINARY;

foreach ($files as $file) {
    $cmd = escapeshellarg($php) . ' -l ' . escapeshellarg($file);
    $out = [];
    $code = 0;
    exec($cmd, $out, $code);
    if ($code !== 0) {
        fwrite(STDERR, "FAIL: {$file}\n");
        fwrite(STDERR, implode("\n", $out) . "\n");
        exit(1);
    }
}

echo "PHP lint OK (" . count($files) . " archivos)\n";

