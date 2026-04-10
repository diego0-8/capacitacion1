<?php

declare(strict_types=1);

final class Insignia
{
    public const TIPO_CURSO_COMPLETADO = 'curso_completado';

    private static function tablaExiste(PDO $pdo): bool
    {
        try {
            $res = $pdo->query("SHOW TABLES LIKE 'insignias_usuario'");
            return $res !== false && $res->fetch() !== false;
        } catch (Throwable $e) {
            return false;
        }
    }

    /**
     * Otorga (idempotente) la insignia de curso completado.
     *
     * @param array<string,mixed> $metadata
     */
    public static function otorgarCursoCompletado(PDO $pdo, string $cedulaAsesor, int $idCurso, array $metadata = []): void
    {
        if (!self::tablaExiste($pdo)) {
            return;
        }
        $sql = 'INSERT INTO insignias_usuario (cedula_asesor, id_curso, tipo, otorgada_en, metadata)
                VALUES (:a, :c, :t, NOW(), :m)
                ON DUPLICATE KEY UPDATE otorgada_en = otorgada_en';
        $metaJson = $metadata !== [] ? json_encode($metadata, JSON_UNESCAPED_UNICODE) : null;
        $pdo->prepare($sql)->execute([
            'a' => $cedulaAsesor,
            'c' => $idCurso,
            't' => self::TIPO_CURSO_COMPLETADO,
            'm' => $metaJson,
        ]);
    }

    /** @return array<int, array<string,mixed>> */
    public static function porAsesor(PDO $pdo, string $cedulaAsesor): array
    {
        if (!self::tablaExiste($pdo)) {
            return [];
        }
        $st = $pdo->prepare('SELECT * FROM insignias_usuario WHERE cedula_asesor = :a ORDER BY otorgada_en DESC');
        $st->execute(['a' => $cedulaAsesor]);
        return $st->fetchAll();
    }

    /**
     * Mapa `id_curso => row` solo para insignia `curso_completado`.
     *
     * @return array<int, array<string,mixed>>
     */
    public static function mapCursoCompletadoPorAsesor(PDO $pdo, string $cedulaAsesor): array
    {
        if (!self::tablaExiste($pdo)) {
            return [];
        }
        $st = $pdo->prepare(
            'SELECT * FROM insignias_usuario
             WHERE cedula_asesor = :a AND tipo = :t
             ORDER BY otorgada_en DESC'
        );
        $st->execute(['a' => $cedulaAsesor, 't' => self::TIPO_CURSO_COMPLETADO]);
        $rows = $st->fetchAll();
        $map = [];
        foreach ($rows as $r) {
            $map[(int) ($r['id_curso'] ?? 0)] = $r;
        }
        return $map;
    }
}

