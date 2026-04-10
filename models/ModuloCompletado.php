<?php

declare(strict_types=1);

class ModuloCompletado
{
    public static function marcar(PDO $pdo, int $idModulo, string $cedulaAsesor): void
    {
        $sql = 'INSERT INTO modulo_completado (id_modulo, cedula_asesor)
                VALUES (:m, :c)
                ON DUPLICATE KEY UPDATE fecha_completado = fecha_completado';
        $pdo->prepare($sql)->execute(['m' => $idModulo, 'c' => $cedulaAsesor]);
    }

    public static function estaCompletado(PDO $pdo, int $idModulo, string $cedulaAsesor): bool
    {
        $st = $pdo->prepare('SELECT 1 FROM modulo_completado WHERE id_modulo = :m AND cedula_asesor = :c LIMIT 1');
        $st->execute(['m' => $idModulo, 'c' => $cedulaAsesor]);
        return (bool) $st->fetch();
    }
}

