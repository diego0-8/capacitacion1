<?php

declare(strict_types=1);

/**
 * Comprueba que la vista del coordinador por curso use módulos + clases.
 *
 * Uso: php tools/verify_coordinador_curso_vista.php
 * Código de salida: 0 OK, 1 error.
 */

$root = dirname(__DIR__);
$vista = $root . DIRECTORY_SEPARATOR . 'views' . DIRECTORY_SEPARATOR . 'coordinador' . DIRECTORY_SEPARATOR . 'curso.php';
$controller = $root . DIRECTORY_SEPARATOR . 'controllers' . DIRECTORY_SEPARATOR . 'CoordinadorController.php';

$errores = [];

if (!is_file($vista)) {
    $errores[] = 'No existe views/coordinador/curso.php';
} else {
    $c = (string) file_get_contents($vista);
    if (stripos($c, 'modulo_crear') === false) {
        $errores[] = 'views/coordinador/curso.php no enlaza modulo_crear.';
    }
    if (stripos($c, 'leccion_crear') === false) {
        $errores[] = 'views/coordinador/curso.php no enlaza leccion_crear.';
    }
    if (stripos($c, 'name=\"id_modulo\"') === false && stripos($c, "name='id_modulo'") === false) {
        $errores[] = 'views/coordinador/curso.php no envía id_modulo en el formulario de clases.';
    }
}

if (!is_file($controller)) {
    $errores[] = 'No existe CoordinadorController.php';
} else {
    $cc = (string) file_get_contents($controller);
    if (stripos($cc, 'function modulo_crear') === false) {
        $errores[] = 'CoordinadorController no define modulo_crear.';
    }
    if (stripos($cc, 'function leccion_crear') === false) {
        $errores[] = 'CoordinadorController no define leccion_crear.';
    }
}

if ($errores !== []) {
    fwrite(STDERR, implode(PHP_EOL, $errores) . PHP_EOL);
    exit(1);
}

echo 'OK: vista coordinador/curso usa módulos + clases (lecciones) por módulo.' . PHP_EOL;
exit(0);
