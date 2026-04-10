<?php

declare(strict_types=1);

class Curso
{
    private static function tieneCedulaCoordinador(PDO $pdo): bool
    {
        // Si el proyecto se ejecuta con una BD importada antes de la migración,
        // evitar romper la app mostrando igualmente cursos.
        $sql = "SELECT COUNT(*) AS n
                FROM information_schema.columns
                WHERE table_schema = :db
                  AND table_name = 'cursos'
                  AND column_name = 'cedula_coordinador'";
        $st = $pdo->prepare($sql);
        $st->execute(['db' => DB_NAME]);
        $row = $st->fetch();
        return (int) ($row['n'] ?? 0) > 0;
    }

    /** @return array<int, array<string,mixed>> */
    public static function todos(PDO $pdo): array
    {
        if (!self::tieneCedulaCoordinador($pdo)) {
            $rows = $pdo->query('SELECT * FROM cursos ORDER BY id_cursos DESC')->fetchAll();
            foreach ($rows as &$r) {
                $r['nombre_coordinador'] = null;
            }
            return $rows;
        }

        $sql = 'SELECT c.*, u.nombre AS nombre_coordinador 
                FROM cursos c 
                LEFT JOIN usuarios u ON u.cedula = c.cedula_coordinador 
                ORDER BY c.id_cursos DESC';
        return $pdo->query($sql)->fetchAll();
    }

    /** @return array<int, array<string,mixed>> */
    public static function activos(PDO $pdo): array
    {
        if (!self::tieneCedulaCoordinador($pdo)) {
            $st = $pdo->query("SELECT * FROM cursos WHERE estado = 'activo' ORDER BY id_cursos DESC");
            $rows = $st->fetchAll();
            foreach ($rows as &$r) {
                $r['nombre_coordinador'] = null;
            }
            return $rows;
        }

        $sql = "SELECT c.*, u.nombre AS nombre_coordinador
                FROM cursos c
                LEFT JOIN usuarios u ON u.cedula = c.cedula_coordinador
                WHERE c.estado = 'activo'
                ORDER BY c.id_cursos DESC";
        return $pdo->query($sql)->fetchAll();
    }

    public static function buscar(PDO $pdo, int $id): ?array
    {
        if (!self::tieneCedulaCoordinador($pdo)) {
            $st = $pdo->prepare('SELECT * FROM cursos WHERE id_cursos = :id LIMIT 1');
            $st->execute(['id' => $id]);
            $row = $st->fetch();
            if (!$row) {
                return null;
            }
            $row['nombre_coordinador'] = null;
            return $row;
        }

        $sql = 'SELECT c.*, u.nombre AS nombre_coordinador 
                FROM cursos c 
                LEFT JOIN usuarios u ON u.cedula = c.cedula_coordinador 
                WHERE c.id_cursos = :id LIMIT 1';
        $st = $pdo->prepare($sql);
        $st->execute(['id' => $id]);
        $row = $st->fetch();
        return $row ?: null;
    }

    /** @return array<int, array<string,mixed>> */
    public static function porCoordinador(PDO $pdo, string $cedula): array
    {
        if (!self::tieneCedulaCoordinador($pdo)) {
            return [];
        }
        $sql = 'SELECT * FROM cursos WHERE cedula_coordinador = :c AND estado = \'activo\' ORDER BY nombre_curso';
        $st = $pdo->prepare($sql);
        $st->execute(['c' => $cedula]);
        return $st->fetchAll();
    }

    public static function crear(PDO $pdo, string $nombre, ?string $descripcion, string $estado, ?string $cedulaCoordinador): int
    {
        if (!self::tieneCedulaCoordinador($pdo)) {
            $sql = 'INSERT INTO cursos (nombre_curso, descripcion, estado) VALUES (:n, :d, :e)';
            $st = $pdo->prepare($sql);
            $st->execute([
                'n' => $nombre,
                'd' => $descripcion,
                'e' => $estado,
            ]);
            return (int) $pdo->lastInsertId();
        }

        $sql = 'INSERT INTO cursos (nombre_curso, descripcion, estado, cedula_coordinador) VALUES (:n, :d, :e, :coord)';
        $st = $pdo->prepare($sql);
        $st->execute([
            'n' => $nombre,
            'd' => $descripcion,
            'e' => $estado,
            'coord' => $cedulaCoordinador ?: null,
        ]);
        return (int) $pdo->lastInsertId();
    }

    public static function actualizar(PDO $pdo, int $id, string $nombre, ?string $descripcion, string $estado, ?string $cedulaCoordinador): void
    {
        if (!self::tieneCedulaCoordinador($pdo)) {
            $sql = 'UPDATE cursos SET nombre_curso = :n, descripcion = :d, estado = :e WHERE id_cursos = :id';
            $st = $pdo->prepare($sql);
            $st->execute([
                'n' => $nombre,
                'd' => $descripcion,
                'e' => $estado,
                'id' => $id,
            ]);
            return;
        }

        $sql = 'UPDATE cursos SET nombre_curso = :n, descripcion = :d, estado = :e, cedula_coordinador = :coord WHERE id_cursos = :id';
        $st = $pdo->prepare($sql);
        $st->execute([
            'n' => $nombre,
            'd' => $descripcion,
            'e' => $estado,
            'coord' => $cedulaCoordinador ?: null,
            'id' => $id,
        ]);
    }

    public static function actualizarDescripcion(PDO $pdo, int $id, ?string $descripcion): void
    {
        $st = $pdo->prepare('UPDATE cursos SET descripcion = :d WHERE id_cursos = :id');
        $st->execute(['d' => $descripcion, 'id' => $id]);
    }
}
