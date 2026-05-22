<?php

declare(strict_types=1);

class CursoAccesoAsesor
{
    private static ?bool $tieneAccesoColumn = null;
    private static ?bool $tieneTablaPermitidos = null;

    public static function tieneMigracion(PDO $pdo): bool
    {
        return self::tieneColumnaAcceso($pdo) && self::tieneTablaPermitidos($pdo);
    }

    private static function tieneColumnaAcceso(PDO $pdo): bool
    {
        if (self::$tieneAccesoColumn !== null) {
            return self::$tieneAccesoColumn;
        }
        $st = $pdo->prepare(
            'SELECT COUNT(*) AS n FROM information_schema.columns
             WHERE table_schema = :db AND table_name = \'cursos\' AND column_name = \'acceso_asesores\''
        );
        $st->execute(['db' => DB_NAME]);
        $row = $st->fetch();
        self::$tieneAccesoColumn = (int) ($row['n'] ?? 0) > 0;

        return self::$tieneAccesoColumn;
    }

    private static function tieneTablaPermitidos(PDO $pdo): bool
    {
        if (self::$tieneTablaPermitidos !== null) {
            return self::$tieneTablaPermitidos;
        }
        $st = $pdo->prepare(
            'SELECT COUNT(*) AS n FROM information_schema.tables
             WHERE table_schema = :db AND table_name = \'curso_asesores_permitidos\''
        );
        $st->execute(['db' => DB_NAME]);
        $row = $st->fetch();
        self::$tieneTablaPermitidos = (int) ($row['n'] ?? 0) === 1;

        return self::$tieneTablaPermitidos;
    }

    /** @return 'publico'|'restringido' */
    public static function modoAcceso(PDO $pdo, int $idCurso): string
    {
        if (!self::tieneColumnaAcceso($pdo)) {
            return 'publico';
        }
        $st = $pdo->prepare('SELECT acceso_asesores FROM cursos WHERE id_cursos = :id LIMIT 1');
        $st->execute(['id' => $idCurso]);
        $row = $st->fetch();
        $modo = (string) ($row['acceso_asesores'] ?? 'publico');

        return $modo === 'restringido' ? 'restringido' : 'publico';
    }

    public static function establecerModo(PDO $pdo, int $idCurso, string $modo): void
    {
        if (!self::tieneColumnaAcceso($pdo)) {
            throw new RuntimeException('Falta la columna acceso_asesores en cursos. Ejecute database/migration_curso_acceso_asesor.sql');
        }
        $modo = $modo === 'restringido' ? 'restringido' : 'publico';
        $st = $pdo->prepare('UPDATE cursos SET acceso_asesores = :m WHERE id_cursos = :id');
        $st->execute(['m' => $modo, 'id' => $idCurso]);
        if ($modo === 'publico' && self::tieneTablaPermitidos($pdo)) {
            $pdo->prepare('DELETE FROM curso_asesores_permitidos WHERE id_curso = :id')->execute(['id' => $idCurso]);
        }
    }

    /** @return array<int, array{cedula: string, nombre: string}> */
    public static function listarPermitidos(PDO $pdo, int $idCurso): array
    {
        if (!self::tieneTablaPermitidos($pdo)) {
            return [];
        }
        $st = $pdo->prepare(
            'SELECT cap.cedula_asesor AS cedula, u.nombre
             FROM curso_asesores_permitidos cap
             LEFT JOIN usuarios u ON u.cedula = cap.cedula_asesor
             WHERE cap.id_curso = :id
             ORDER BY COALESCE(u.nombre, cap.cedula_asesor) ASC'
        );
        $st->execute(['id' => $idCurso]);
        $out = [];
        foreach ($st->fetchAll() as $r) {
            $out[] = [
                'cedula' => (string) ($r['cedula'] ?? ''),
                'nombre' => (string) ($r['nombre'] ?? ''),
            ];
        }

        return $out;
    }

    public static function contarPermitidos(PDO $pdo, int $idCurso): int
    {
        if (!self::tieneTablaPermitidos($pdo)) {
            return 0;
        }
        $st = $pdo->prepare('SELECT COUNT(*) AS n FROM curso_asesores_permitidos WHERE id_curso = :id');
        $st->execute(['id' => $idCurso]);
        $row = $st->fetch();

        return (int) ($row['n'] ?? 0);
    }

    /**
     * @param string[] $cedulas
     */
    public static function sincronizarPermitidos(PDO $pdo, int $idCurso, array $cedulas): void
    {
        if (!self::tieneTablaPermitidos($pdo)) {
            throw new RuntimeException('Falta la tabla curso_asesores_permitidos. Ejecute database/migration_curso_acceso_asesor.sql');
        }
        $cedulas = array_values(array_unique(array_filter(array_map(
            static fn($c) => trim((string) $c),
            $cedulas
        ), static fn($c) => $c !== '')));

        $pdo->beginTransaction();
        try {
            $pdo->prepare('DELETE FROM curso_asesores_permitidos WHERE id_curso = :id')->execute(['id' => $idCurso]);
            $ins = $pdo->prepare(
                'INSERT INTO curso_asesores_permitidos (id_curso, cedula_asesor) VALUES (:c, :a)'
            );
            foreach ($cedulas as $ced) {
                $ins->execute(['c' => $idCurso, 'a' => $ced]);
            }
            $pdo->commit();
        } catch (Throwable $e) {
            $pdo->rollBack();
            throw $e;
        }
    }

    public static function asesorPuedeVer(PDO $pdo, int $idCurso, string $cedulaAsesor): bool
    {
        if ($cedulaAsesor === '') {
            return false;
        }
        $curso = Curso::buscar($pdo, $idCurso);
        if ($curso === null || ($curso['estado'] ?? '') !== 'activo') {
            return false;
        }
        if (!self::tieneColumnaAcceso($pdo) || self::modoAcceso($pdo, $idCurso) === 'publico') {
            return true;
        }
        if (CapacitacionAsignada::buscarPorAsesorCurso($pdo, $cedulaAsesor, $idCurso) !== null) {
            return true;
        }
        if (!self::tieneTablaPermitidos($pdo)) {
            return false;
        }
        $st = $pdo->prepare(
            'SELECT 1 FROM curso_asesores_permitidos WHERE id_curso = :c AND cedula_asesor = :a LIMIT 1'
        );
        $st->execute(['c' => $idCurso, 'a' => $cedulaAsesor]);

        return (bool) $st->fetch();
    }

    /**
     * Crea filas en capacitaciones_asignadas para cédulas que aún no tienen asignación.
     *
     * @param string[] $cedulas
     */
    public static function asegurarAsignaciones(PDO $pdo, int $idCurso, array $cedulas): void
    {
        foreach ($cedulas as $ced) {
            $ced = trim((string) $ced);
            if ($ced === '') {
                continue;
            }
            if (CapacitacionAsignada::buscarPorAsesorCurso($pdo, $ced, $idCurso) !== null) {
                continue;
            }
            CapacitacionAsignada::crear($pdo, $ced, $idCurso);
        }
    }

    /**
     * @param string[] $cedulasPost
     * @param array<int, array<string, mixed>> $asesoresLista filas con clave cedula (rol asesor)
     * @return string[]
     */
    public static function filtrarCedulasAsesores(array $cedulasPost, array $asesoresLista): array
    {
        $valid = [];
        foreach ($asesoresLista as $a) {
            $c = (string) ($a['cedula'] ?? '');
            if ($c !== '') {
                $valid[$c] = true;
            }
        }
        $out = [];
        foreach ($cedulasPost as $ced) {
            $ced = trim((string) $ced);
            if ($ced !== '' && isset($valid[$ced])) {
                $out[] = $ced;
            }
        }

        return array_values(array_unique($out));
    }

    /** @deprecated Use filtrarCedulasAsesores */
    public static function filtrarCedulasAsesoresActivos(array $cedulasPost, array $asesoresActivos): array
    {
        return self::filtrarCedulasAsesores($cedulasPost, $asesoresActivos);
    }
}
