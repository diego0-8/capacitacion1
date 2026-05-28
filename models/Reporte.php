<?php

declare(strict_types=1);

class Reporte
{
    /**
     * Asignaciones con asesor activo (excluye usuarios marcados inactivos en administración).
     *
     * @return array<int, array<string, mixed>>
     */
    private static function asignacionesSoloAsesoresActivos(PDO $pdo, string $extraWhere = ''): array
    {
        $where = $extraWhere !== '' ? ' AND (' . $extraWhere . ')' : '';
        $sql = 'SELECT ca.cedula_asesor AS cedula, u.nombre AS asesor, c.nombre_curso,
                       ca.progreso_porcentaje, ca.estado_capacitacion, ca.calificacion_obtenida,
                       ca.fecha_asignacion, ca.fecha_completado
                FROM capacitaciones_asignadas ca
                INNER JOIN usuarios u ON u.cedula = ca.cedula_asesor
                    AND u.rol = \'asesor\' AND u.estado = \'activo\'
                JOIN cursos c ON c.id_cursos = ca.id_curso
                WHERE 1=1' . $where . '
                ORDER BY ca.fecha_asignacion DESC';

        return $pdo->query($sql)->fetchAll();
    }

    /** @return array<int, array<string,mixed>> */
    public static function progresoAsesores(PDO $pdo): array
    {
        try {
            $vista = $pdo->query('SELECT * FROM vista_progreso_asesores LIMIT 1')->fetch();
            if ($vista !== false && array_key_exists('estado', $vista)) {
                $rows = $pdo->query(
                    "SELECT * FROM vista_progreso_asesores WHERE estado = 'activo' ORDER BY fecha_asignacion DESC"
                )->fetchAll();

                return $rows;
            }
        } catch (Throwable $e) {
            // Vista inexistente o sin columna estado: consulta directa.
        }

        return self::asignacionesSoloAsesoresActivos($pdo);
    }

    /** @return array<int, array<string,mixed>> */
    public static function asesoresAtrasados(PDO $pdo): array
    {
        try {
            $rows = $pdo->query('SELECT * FROM vista_asesores_atrasados ORDER BY fecha_asignacion DESC')->fetchAll();
            if ($rows === []) {
                return [];
            }
            if (array_key_exists('estado', $rows[0] ?? [])) {
                return array_values(array_filter(
                    $rows,
                    static fn(array $r): bool => ($r['estado'] ?? '') === Usuario::ESTADO_ACTIVO
                ));
            }
            $cedulas = array_unique(array_filter(array_map(
                static fn(array $r): string => trim((string) ($r['cedula'] ?? $r['cedula_asesor'] ?? '')),
                $rows
            )));
            $activos = [];
            foreach ($cedulas as $ced) {
                if (Usuario::esAsesorActivo($pdo, $ced)) {
                    $activos[$ced] = true;
                }
            }

            return array_values(array_filter(
                $rows,
                static fn(array $r): bool => isset($activos[trim((string) ($r['cedula'] ?? $r['cedula_asesor'] ?? ''))])
            ));
        } catch (Throwable $e) {
            return self::asignacionesSoloAsesoresActivos(
                $pdo,
                "ca.estado_capacitacion NOT IN ('completado') AND ca.progreso_porcentaje < 100"
            );
        }
    }
}
