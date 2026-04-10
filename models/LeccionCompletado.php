<?php

declare(strict_types=1);

class LeccionCompletado
{
    public static function marcar(PDO $pdo, int $idLeccion, string $cedulaAsesor): void
    {
        $sql = 'INSERT INTO leccion_completado (id_leccion, cedula_asesor)
                VALUES (:l, :a)
                ON DUPLICATE KEY UPDATE created_at = created_at';
        $st = $pdo->prepare($sql);
        $st->execute(['l' => $idLeccion, 'a' => $cedulaAsesor]);
    }

    public static function estaCompletada(PDO $pdo, int $idLeccion, string $cedulaAsesor): bool
    {
        $st = $pdo->prepare('SELECT 1 FROM leccion_completado WHERE id_leccion = :l AND cedula_asesor = :a LIMIT 1');
        $st->execute(['l' => $idLeccion, 'a' => $cedulaAsesor]);
        return (bool) $st->fetchColumn();
    }

    /** @return array<int,int> set de ids_leccion completadas (id=>1) */
    public static function idsCompletadasPorCurso(PDO $pdo, int $idCurso, string $cedulaAsesor): array
    {
        $sql = 'SELECT lc.id_leccion
                FROM leccion_completado lc
                JOIN lecciones l ON l.id_leccion = lc.id_leccion
                WHERE l.id_curso = :c AND lc.cedula_asesor = :a';
        $st = $pdo->prepare($sql);
        $st->execute(['c' => $idCurso, 'a' => $cedulaAsesor]);
        $rows = $st->fetchAll();
        $set = [];
        foreach ($rows as $r) {
            $set[(int) $r['id_leccion']] = 1;
        }
        return $set;
    }
}

