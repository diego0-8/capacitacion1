<?php

declare(strict_types=1);

class Leccion
{
    /** @return array<int, array<string,mixed>> */
    public static function porCurso(PDO $pdo, int $idTreatedCurso): array
    {
        $sql = 'SELECT * FROM lecciones WHERE id_curso = :c ORDER BY id_modulo ASC, orden ASC, id_leccion ASC';
        $st = $pdo->prepare($sql);
        $st->execute(['c' => $idTreatedCurso]);
        return $st->fetchAll();
    }

    /** @return array<int, array<string,mixed>> */
    public static function porModulo(PDO $pdo, int $idModulo): array
    {
        $st = $pdo->prepare('SELECT * FROM lecciones WHERE id_modulo = :m ORDER BY orden ASC, id_leccion ASC');
        $st->execute(['m' => $idModulo]);
        return $st->fetchAll();
    }

    public static function buscar(PDO $pdo, int $idLeccion): ?array
    {
        $sql = 'SELECT * FROM lecciones WHERE id_leccion = :id LIMIT 1';
        $st = $pdo->prepare($sql);
        $st->execute(['id' => $idLeccion]);
        $row = $st->fetch();
        return $row ?: null;
    }

    public static function siguienteOrden(PDO $pdo, int $idCurso): int
    {
        $sql = 'SELECT COALESCE(MAX(orden), 0) + 1 AS n FROM lecciones WHERE id_curso = :c';
        $st = $pdo->prepare($sql);
        $st->execute(['c' => $idCurso]);
        $row = $st->fetch();
        return (int) ($row['n'] ?? 1);
    }

    public static function siguienteOrdenPorModulo(PDO $pdo, int $idModulo): int
    {
        $st = $pdo->prepare('SELECT COALESCE(MAX(orden), 0) + 1 AS n FROM lecciones WHERE id_modulo = :m');
        $st->execute(['m' => $idModulo]);
        $row = $st->fetch();
        return (int) ($row['n'] ?? 1);
    }

    public static function crear(
        PDO $pdo,
        int $idCurso,
        int $idModulo,
        string $titulo,
        string $contenido,
        ?string $imagenPath,
        ?string $imagenTexto,
        ?string $videoPath,
        int $orden,
        int $duracion
    ): int {
        $sql = 'INSERT INTO lecciones (id_curso, id_modulo, titulo_leccion, contenido, imagen_path, imagen_texto, video_path, orden, duracion_minutos)
                VALUES (:c, :m, :t, :cont, :img, :imgtxt, :vid, :o, :d)';
        $st = $pdo->prepare($sql);
        $st->execute([
            'c' => $idCurso,
            'm' => $idModulo,
            't' => $titulo,
            'cont' => $contenido,
            'img' => $imagenPath,
            'imgtxt' => $imagenTexto,
            'vid' => $videoPath,
            'o' => $orden,
            'd' => $duracion,
        ]);
        return (int) $pdo->lastInsertId();
    }

    public static function actualizar(
        PDO $pdo,
        int $idLeccion,
        int $idCurso,
        int $idModulo,
        string $titulo,
        string $contenido,
        ?string $imagenPath,
        ?string $imagenTexto,
        ?string $videoPath
    ): bool
    {
        $sql = 'UPDATE lecciones
                SET titulo_leccion = :t,
                    contenido = :cont,
                    imagen_path = :img,
                    imagen_texto = :imgtxt,
                    video_path = :vid
                WHERE id_leccion = :id AND id_curso = :c AND id_modulo = :m';
        $st = $pdo->prepare($sql);
        $st->execute([
            't' => $titulo,
            'cont' => $contenido,
            'img' => $imagenPath,
            'imgtxt' => $imagenTexto,
            'vid' => $videoPath,
            'id' => $idLeccion,
            'c' => $idCurso,
            'm' => $idModulo,
        ]);
        return $st->rowCount() > 0;
    }

    public static function eliminar(PDO $pdo, int $idLeccion): void
    {
        $st = $pdo->prepare('DELETE FROM lecciones WHERE id_leccion = :id');
        $st->execute(['id' => $idLeccion]);
    }

    public static function contarPorCurso(PDO $pdo, int $idCurso): int
    {
        $st = $pdo->prepare('SELECT COUNT(*) AS n FROM lecciones WHERE id_curso = :c');
        $st->execute(['c' => $idCurso]);
        $row = $st->fetch();
        return (int) ($row['n'] ?? 0);
    }

    /** @deprecated Progreso global: con varios módulos el orden no es único en el curso. */
    public static function calcularProgresoPorLeccion(PDO $pdo, int $idCurso, int $idLeccion): int
    {
        $leccion = self::buscar($pdo, $idLeccion);
        if (!$leccion || (int) $leccion['id_curso'] !== $idCurso) {
            return 0;
        }
        $total = self::contarPorCurso($pdo, $idCurso);
        if ($total === 0) {
            return 100;
        }
        $orden = (int) $leccion['orden'];
        $st = $pdo->prepare('SELECT COUNT(*) AS n FROM lecciones WHERE id_curso = :c AND orden <= :o');
        $st->execute(['c' => $idCurso, 'o' => $orden]);
        $row = $st->fetch();
        $hechas = (int) ($row['n'] ?? 0);
        return (int) min(100, round(($hechas / $total) * 100));
    }
}
