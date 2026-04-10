<?php

declare(strict_types=1);

class ModuloIntento
{
    public static function registrarIntento(PDO $pdo, int $idModulo, string $cedulaAsesor, int $total, int $correctas, bool $aprobado): int
    {
        $sql = 'INSERT INTO modulo_intentos (id_modulo, cedula_asesor, total_preguntas, correctas, aprobado)
                VALUES (:m, :c, :t, :ok, :a)';
        $pdo->prepare($sql)->execute([
            'm' => $idModulo,
            'c' => $cedulaAsesor,
            't' => $total,
            'ok' => $correctas,
            'a' => $aprobado ? 1 : 0,
        ]);
        return (int) $pdo->lastInsertId();
    }

    /** @param array<int, array{pregunta:int,opcion:int}> $respuestas */
    public static function guardarRespuestas(PDO $pdo, int $idIntento, array $respuestas): void
    {
        $sql = 'INSERT INTO modulo_respuestas (id_intento_modulo, id_pregunta_modulo, id_opcion_elegida)
                VALUES (:i, :p, :o)';
        $st = $pdo->prepare($sql);
        foreach ($respuestas as $r) {
            $st->execute(['i' => $idIntento, 'p' => (int) $r['pregunta'], 'o' => (int) $r['opcion']]);
        }
    }
}

