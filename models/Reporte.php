<?php

declare(strict_types=1);

class Reporte
{
    /** @return array<int, array<string,mixed>> */
    public static function progresoAsesores(PDO $pdo): array
    {
        return $pdo->query('SELECT * FROM vista_progreso_asesores ORDER BY fecha_asignacion DESC')->fetchAll();
    }

    /** @return array<int, array<string,mixed>> */
    public static function asesoresAtrasados(PDO $pdo): array
    {
        return $pdo->query('SELECT * FROM vista_asesores_atrasados ORDER BY fecha_asignacion DESC')->fetchAll();
    }
}
