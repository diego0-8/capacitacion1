<?php

declare(strict_types=1);

class ModuloCurso
{
    private static function tieneTabla(PDO $pdo): bool
    {
        static $cache = null;
        if ($cache !== null) {
            return $cache;
        }
        $sql = "SELECT COUNT(*) AS n
                FROM information_schema.tables
                WHERE table_schema = :db
                  AND table_name = 'cursos_modulos'";
        $st = $pdo->prepare($sql);
        $st->execute(['db' => DB_NAME]);
        $row = $st->fetch();
        $cache = (int) ($row['n'] ?? 0) === 1;
        return $cache;
    }

    public static function siguienteModulo(PDO $pdo, int $idCurso): int
    {
        if (!self::tieneTabla($pdo)) {
            return 1;
        }
        $st = $pdo->prepare('SELECT COALESCE(MAX(modulo), 0) + 1 AS n FROM cursos_modulos WHERE id_curso = :c');
        $st->execute(['c' => $idCurso]);
        $row = $st->fetch();
        return (int) ($row['n'] ?? 1);
    }

    /** @return array<int, array<string,mixed>> */
    public static function porCurso(PDO $pdo, int $idCurso): array
    {
        if (!self::tieneTabla($pdo)) {
            return [];
        }
        $sql = 'SELECT *
                FROM cursos_modulos
                WHERE id_curso = :c
                ORDER BY modulo ASC, id_modulo ASC';
        $st = $pdo->prepare($sql);
        $st->execute(['c' => $idCurso]);
        return $st->fetchAll();
    }

    public static function crear(PDO $pdo, int $idCurso, string $titulo): int
    {
        if (!self::tieneTabla($pdo)) {
            throw new RuntimeException('Falta la tabla cursos_modulos. Ejecute la migración.');
        }
        $modulo = self::siguienteModulo($pdo, $idCurso);
        $sql = 'INSERT INTO cursos_modulos (id_curso, modulo, titulo) VALUES (:c, :m, :t)';
        $st = $pdo->prepare($sql);
        $st->execute([
            'c' => $idCurso,
            'm' => $modulo,
            't' => $titulo,
        ]);
        return (int) $pdo->lastInsertId();
    }

    public static function eliminar(PDO $pdo, int $idModulo, int $idCurso): ?array
    {
        if (!self::tieneTabla($pdo)) {
            return null;
        }
        $st = $pdo->prepare('SELECT * FROM cursos_modulos WHERE id_modulo = :id AND id_curso = :c LIMIT 1');
        $st->execute(['id' => $idModulo, 'c' => $idCurso]);
        $row = $st->fetch();
        if (!$row) {
            return null;
        }
        $del = $pdo->prepare('DELETE FROM cursos_modulos WHERE id_modulo = :id');
        $del->execute(['id' => $idModulo]);
        return $row ?: null;
    }
}

