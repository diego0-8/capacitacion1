<?php

declare(strict_types=1);

class ModuloQuiz
{
    public static function getConfig(PDO $pdo, int $idModulo): array
    {
        $st = $pdo->prepare('SELECT id_modulo, preguntas_requeridas, activo FROM modulo_quiz_config WHERE id_modulo = :m LIMIT 1');
        $st->execute(['m' => $idModulo]);
        $row = $st->fetch();
        if (!$row) {
            return ['id_modulo' => $idModulo, 'preguntas_requeridas' => 1, 'activo' => 0];
        }
        return $row;
    }

    public static function upsertConfig(PDO $pdo, int $idModulo, int $preguntasRequeridas, int $activo): void
    {
        $preguntasRequeridas = max(1, min(3, $preguntasRequeridas));
        $activo = $activo ? 1 : 0;
        $sql = 'INSERT INTO modulo_quiz_config (id_modulo, preguntas_requeridas, activo)
                VALUES (:m, :p, :a)
                ON DUPLICATE KEY UPDATE preguntas_requeridas = VALUES(preguntas_requeridas), activo = VALUES(activo)';
        $pdo->prepare($sql)->execute(['m' => $idModulo, 'p' => $preguntasRequeridas, 'a' => $activo]);
    }

    /** @return array<int, array<string,mixed>> */
    public static function preguntasPorModulo(PDO $pdo, int $idModulo): array
    {
        $st = $pdo->prepare('SELECT * FROM modulo_preguntas WHERE id_modulo = :m ORDER BY orden ASC');
        $st->execute(['m' => $idModulo]);
        return $st->fetchAll();
    }

    /** @return array<int, array<string,mixed>> */
    public static function opcionesPorPregunta(PDO $pdo, int $idPreguntaModulo): array
    {
        $st = $pdo->prepare('SELECT * FROM modulo_opciones WHERE id_pregunta_modulo = :p ORDER BY id_opcion ASC');
        $st->execute(['p' => $idPreguntaModulo]);
        return $st->fetchAll();
    }

    public static function setPregunta(PDO $pdo, int $idModulo, int $orden, string $tipo, string $enunciado): int
    {
        $orden = max(1, min(3, $orden));
        if (!in_array($tipo, ['imagen_par', 'vf', 'multi'], true)) {
            $tipo = 'vf';
        }
        $enunciado = (string) $enunciado;

        $st = $pdo->prepare('SELECT id_pregunta_modulo FROM modulo_preguntas WHERE id_modulo = :m AND orden = :o LIMIT 1');
        $st->execute(['m' => $idModulo, 'o' => $orden]);
        $row = $st->fetch();
        if ($row) {
            $id = (int) $row['id_pregunta_modulo'];
            $pdo->prepare('UPDATE modulo_preguntas SET tipo = :t, enunciado = :e WHERE id_pregunta_modulo = :id')
                ->execute(['t' => $tipo, 'e' => $enunciado, 'id' => $id]);
            return $id;
        }
        $pdo->prepare('INSERT INTO modulo_preguntas (id_modulo, tipo, enunciado, orden) VALUES (:m, :t, :e, :o)')
            ->execute(['m' => $idModulo, 't' => $tipo, 'e' => $enunciado, 'o' => $orden]);
        return (int) $pdo->lastInsertId();
    }

    /** @param array<int, array{clave:string,texto?:string|null,imagen_path?:string|null}> $opciones */
    public static function replaceOpciones(PDO $pdo, int $idPreguntaModulo, array $opciones): void
    {
        $pdo->prepare('DELETE FROM modulo_opciones WHERE id_pregunta_modulo = :p')->execute(['p' => $idPreguntaModulo]);
        $sql = 'INSERT INTO modulo_opciones (id_pregunta_modulo, clave, texto, imagen_path) VALUES (:p, :c, :t, :i)';
        $st = $pdo->prepare($sql);
        foreach ($opciones as $o) {
            $st->execute([
                'p' => $idPreguntaModulo,
                'c' => (string) $o['clave'],
                't' => array_key_exists('texto', $o) ? $o['texto'] : null,
                'i' => array_key_exists('imagen_path', $o) ? $o['imagen_path'] : null,
            ]);
        }
    }

    public static function setRespuestaCorrecta(PDO $pdo, int $idPreguntaModulo, int $idOpcionCorrecta): void
    {
        $sql = 'INSERT INTO modulo_preguntas_respuesta (id_pregunta_modulo, id_opcion_correcta)
                VALUES (:p, :o)
                ON DUPLICATE KEY UPDATE id_opcion_correcta = VALUES(id_opcion_correcta)';
        $pdo->prepare($sql)->execute(['p' => $idPreguntaModulo, 'o' => $idOpcionCorrecta]);
    }

    public static function getOpcionCorrecta(PDO $pdo, int $idPreguntaModulo): ?int
    {
        $st = $pdo->prepare('SELECT id_opcion_correcta FROM modulo_preguntas_respuesta WHERE id_pregunta_modulo = :p LIMIT 1');
        $st->execute(['p' => $idPreguntaModulo]);
        $row = $st->fetch();
        return $row ? (int) $row['id_opcion_correcta'] : null;
    }
}

