<?php

declare(strict_types=1);

/**
 * Verifica que asesores inactivos no aparezcan en listados operativos del coordinador/admin.
 *
 * Uso: php tools/verify_asesores_inactivos_visibilidad.php
 */

require __DIR__ . '/../config.php';

spl_autoload_register(static function (string $class): void {
    $paths = [
        dirname(__DIR__) . '/core/' . $class . '.php',
        dirname(__DIR__) . '/controllers/' . $class . '.php',
        dirname(__DIR__) . '/models/' . $class . '.php',
    ];
    foreach ($paths as $path) {
        if (is_file($path)) {
            require_once $path;
            return;
        }
    }
});

$errors = [];
$ok = [];

try {
    $pdo = getPDO();
} catch (PDOException $e) {
    fwrite(STDERR, 'No se pudo conectar a MySQL: ' . $e->getMessage() . PHP_EOL);
    exit(1);
}

$st = $pdo->query(
    "SELECT cedula, nombre, estado FROM usuarios WHERE rol = 'asesor' AND estado = 'inactivo' ORDER BY nombre"
);
$inactivos = $st->fetchAll();
$nInactivos = count($inactivos);

echo "DB: " . DB_NAME . PHP_EOL;
echo "Asesores inactivos en usuarios: {$nInactivos}" . PHP_EOL . PHP_EOL;

if ($nInactivos === 0) {
    echo "OK: no hay asesores inactivos para probar exclusiones.\n";
    exit(0);
}

$cedulaPrueba = (string) ($inactivos[0]['cedula'] ?? '');
echo "Prueba con cédula inactiva: {$cedulaPrueba} (" . ($inactivos[0]['nombre'] ?? '') . ")\n\n";

if (!Usuario::esAsesorActivo($pdo, $cedulaPrueba)) {
    $ok[] = 'Usuario::esAsesorActivo devuelve false para inactivo';
} else {
    $errors[] = 'Usuario::esAsesorActivo debería ser false para inactivo';
}

$listaActivos = Usuario::listarAsesoresActivos($pdo);
$enListaActivos = false;
foreach ($listaActivos as $a) {
    if ((string) ($a['cedula'] ?? '') === $cedulaPrueba) {
        $enListaActivos = true;
        break;
    }
}
if (!$enListaActivos) {
    $ok[] = 'listarAsesoresActivos no incluye al inactivo';
} else {
    $errors[] = 'listarAsesoresActivos incluye asesor inactivo';
}

$stCurso = $pdo->query('SELECT id_cursos FROM cursos ORDER BY id_cursos DESC LIMIT 1');
$idCurso = (int) ($stCurso->fetch()['id_cursos'] ?? 0);

if ($idCurso > 0) {
    $curso = Curso::buscar($pdo, $idCurso) ?? ['id_cursos' => $idCurso, 'nombre_curso' => ''];
    $data = CoordinadorReporte::asesoresPorCurso($pdo, $curso, $idCurso);
    $enReporte = false;
    foreach ($data['asesores'] ?? [] as $a) {
        if ((string) ($a['cedula_asesor'] ?? '') === $cedulaPrueba) {
            $enReporte = true;
            break;
        }
    }
    if (!$enReporte) {
        $ok[] = "CoordinadorReporte::asesoresPorCurso (curso {$idCurso}) no lista inactivo";
    } else {
        $errors[] = "CoordinadorReporte::asesoresPorCurso incluye inactivo en curso {$idCurso}";
    }

    $det = CoordinadorReporte::detalleAsesor($pdo, $curso, $idCurso, $cedulaPrueba);
    if (!empty($det['asesor_inactivo'])) {
        $ok[] = 'detalleAsesor marca asesor_inactivo';
    } else {
        $errors[] = 'detalleAsesor no bloquea asesor inactivo';
    }
}

$archivosPhp = [
    __DIR__ . '/../models/Usuario.php',
    __DIR__ . '/../models/CoordinadorReporte.php',
    __DIR__ . '/../models/CapacitacionAsignada.php',
    __DIR__ . '/../models/CursoAccesoAsesor.php',
    __DIR__ . '/../models/Reporte.php',
    __DIR__ . '/../controllers/CoordinadorController.php',
    __DIR__ . '/../controllers/AdminController.php',
];

$patronProhibido = "rol = 'asesor' ORDER BY nombre";
foreach ($archivosPhp as $path) {
    if (!is_file($path)) {
        continue;
    }
    $content = file_get_contents($path);
    if ($content !== false && str_contains($content, $patronProhibido)) {
        $errors[] = basename($path) . ' podría listar asesores sin filtrar estado activo';
    }
}

echo "Comprobaciones OK:\n";
foreach ($ok as $m) {
    echo "  ✓ {$m}\n";
}

if ($errors !== []) {
    echo "\nERRORES:\n";
    foreach ($errors as $m) {
        echo "  ✗ {$m}\n";
    }
    exit(1);
}

echo "\nOK: reglas de visibilidad para asesores inactivos consistentes.\n";
echo "Referencia: Usuario::sqlWhereAsesorVisibleEnListados() / listarAsesoresActivos()\n";
