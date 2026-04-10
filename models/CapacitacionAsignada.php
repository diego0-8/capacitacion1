<?php

declare(strict_types=1);

class CapacitacionAsignada
{
    /** @return array<int, array<string,mixed>> */
    public static function porAsesor(PDO $pdo, string $cedula): array
    {
        $sql = 'SELECT ca.*, c.nombre_curso, c.descripcion 
                FROM capacitaciones_asignadas ca 
                JOIN cursos c ON c.id_cursos = ca.id_curso 
                WHERE ca.cedula_asesor = :c 
                ORDER BY ca.fecha_asignacion DESC';
        $st = $pdo->prepare($sql);
        $st->execute(['c' => $cedula]);
        return $st->fetchAll();
    }

    public static function buscar(PDO $pdo, int $idAsignacion): ?array
    {
        $sql = 'SELECT ca.*, c.nombre_curso FROM capacitaciones_asignadas ca 
                JOIN cursos c ON c.id_cursos = ca.id_curso 
                WHERE ca.id_asignacion = :id LIMIT 1';
        $st = $pdo->prepare($sql);
        $st->execute(['id' => $idAsignacion]);
        $row = $st->fetch();
        return $row ?: null;
    }

    public static function buscarPorAsesorCurso(PDO $pdo, string $cedula, int $idCurso): ?array
    {
        $sql = 'SELECT * FROM capacitaciones_asignadas WHERE cedula_asesor = :a AND id_curso = :c LIMIT 1';
        $st = $pdo->prepare($sql);
        $st->execute(['a' => $cedula, 'c' => $idCurso]);
        $row = $st->fetch();
        return $row ?: null;
    }

    /** @return array<int, array<string,mixed>> */
    public static function todasConDetalle(PDO $pdo): array
    {
        $sql = 'SELECT ca.*, u.nombre AS nombre_asesor, c.nombre_curso 
                FROM capacitaciones_asignadas ca 
                JOIN usuarios u ON u.cedula = ca.cedula_asesor 
                JOIN cursos c ON c.id_cursos = ca.id_curso 
                ORDER BY ca.fecha_asignacion DESC';
        return $pdo->query($sql)->fetchAll();
    }

    public static function crear(PDO $pdo, string $cedulaAsesor, int $idCurso): void
    {
        $sql = 'INSERT INTO capacitaciones_asignadas (cedula_asesor, id_curso, estado_capacitacion, progreso_porcentaje) 
                VALUES (:a, :c, \'pendiente\', 0)';
        $st = $pdo->prepare($sql);
        $st->execute(['a' => $cedulaAsesor, 'c' => $idCurso]);
    }

    public static function crearYRetornarId(PDO $pdo, string $cedulaAsesor, int $idCurso): int
    {
        self::crear($pdo, $cedulaAsesor, $idCurso);
        return (int) $pdo->lastInsertId();
    }

    public static function actualizarProgresoEstado(PDO $pdo, int $idAsignacion, int $progreso, string $estado): void
    {
        $sql = 'UPDATE capacitaciones_asignadas SET progreso_porcentaje = :p, estado_capacitacion = :e WHERE id_asignacion = :id';
        $st = $pdo->prepare($sql);
        $st->execute(['p' => $progreso, 'e' => $estado, 'id' => $idAsignacion]);
    }

    public static function completarEvaluacion(PDO $pdo, int $idAsignacion, float $calificacion, string $estado): void
    {
        if ($estado === 'completado') {
            $sql = 'UPDATE capacitaciones_asignadas SET calificacion_obtenida = :cal, estado_capacitacion = :e, fecha_completado = NOW() WHERE id_asignacion = :id';
        } else {
            $sql = 'UPDATE capacitaciones_asignadas SET calificacion_obtenida = :cal, estado_capacitacion = :e WHERE id_asignacion = :id';
        }
        $st = $pdo->prepare($sql);
        $st->execute(['cal' => $calificacion, 'e' => $estado, 'id' => $idAsignacion]);
    }
}
