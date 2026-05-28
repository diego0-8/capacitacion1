<?php

declare(strict_types=1);

class CursoEvaluacion
{
    public static function getConfig(PDO $pdo, int $idCurso): array
    {
        $st = $pdo->prepare('SELECT id_curso, preguntas_requeridas, activo, modo_evaluacion FROM curso_eval_config WHERE id_curso = :c LIMIT 1');
        $st->execute(['c' => $idCurso]);
        $row = $st->fetch();

        if (!$row) {
            return ['id_curso' => $idCurso, 'preguntas_requeridas' => 1, 'activo' => 0, 'modo_evaluacion' => 'unico'];
        }
        if (!isset($row['modo_evaluacion']) || $row['modo_evaluacion'] === null) {
            $row['modo_evaluacion'] = 'unico';
        }
        return $row;
    }

    public static function upsertConfig(PDO $pdo, int $idCurso, int $preguntasRequeridas, int $activo, int $max = 10, string $modoEvaluacion = 'unico'): void
    {
        $preguntasRequeridas = max(1, min($max, $preguntasRequeridas));
        $activo = $activo ? 1 : 0;
        if (!in_array($modoEvaluacion, ['unico', 'manual', 'aleatorio'], true)) {
            $modoEvaluacion = 'unico';
        }
        $sql = 'INSERT INTO curso_eval_config (id_curso, preguntas_requeridas, activo, modo_evaluacion)
                VALUES (:c, :p, :a, :m)
                ON DUPLICATE KEY UPDATE preguntas_requeridas = VALUES(preguntas_requeridas),
                                        activo = VALUES(activo),
                                        modo_evaluacion = VALUES(modo_evaluacion)';
        $pdo->prepare($sql)->execute(['c' => $idCurso, 'p' => $preguntasRequeridas, 'a' => $activo, 'm' => $modoEvaluacion]);
    }

    /** @return array<int, array<string,mixed>> */
    public static function preguntasPorCurso(PDO $pdo, int $idCurso): array
    {
        $st = $pdo->prepare('SELECT * FROM curso_eval_preguntas WHERE id_curso = :c ORDER BY orden ASC');
        $st->execute(['c' => $idCurso]);

        return $st->fetchAll();
    }

    /** @return array<int, array<string,mixed>> */
    public static function opcionesPorPregunta(PDO $pdo, int $idPreguntaCurso): array
    {
        $st = $pdo->prepare('SELECT * FROM curso_eval_opciones WHERE id_pregunta_curso = :p ORDER BY id_opcion ASC');
        $st->execute(['p' => $idPreguntaCurso]);

        return $st->fetchAll();
    }

    public static function setPregunta(PDO $pdo, int $idCurso, int $orden, string $tipo, string $enunciado): int
    {
        $orden = max(1, $orden);

        if (!in_array($tipo, ['imagen_par', 'vf', 'multi'], true)) {
            $tipo = 'vf';
        }
        $enunciado = (string) $enunciado;

        $st = $pdo->prepare('SELECT id_pregunta_curso FROM curso_eval_preguntas WHERE id_curso = :c AND orden = :o LIMIT 1');
        $st->execute(['c' => $idCurso, 'o' => $orden]);
        $row = $st->fetch();

        if ($row) {
            $id = (int) $row['id_pregunta_curso'];
            $pdo->prepare('UPDATE curso_eval_preguntas SET tipo = :t, enunciado = :e WHERE id_pregunta_curso = :id')
                ->execute(['t' => $tipo, 'e' => $enunciado, 'id' => $id]);

            return $id;
        }
        $pdo->prepare('INSERT INTO curso_eval_preguntas (id_curso, tipo, enunciado, orden) VALUES (:c, :t, :e, :o)')
            ->execute(['c' => $idCurso, 't' => $tipo, 'e' => $enunciado, 'o' => $orden]);

        return (int) $pdo->lastInsertId();
    }

    /** @param array<int, array{clave:string,texto?:string|null,imagen_path?:string|null}> $opciones */
    public static function replaceOpciones(PDO $pdo, int $idPreguntaCurso, array $opciones): void
    {
        $pdo->prepare('DELETE FROM curso_eval_opciones WHERE id_pregunta_curso = :p')->execute(['p' => $idPreguntaCurso]);
        $sql = 'INSERT INTO curso_eval_opciones (id_pregunta_curso, clave, texto, imagen_path) VALUES (:p, :c, :t, :i)';
        $st = $pdo->prepare($sql);
        foreach ($opciones as $o) {
            $st->execute([
                'p' => $idPreguntaCurso,
                'c' => (string) $o['clave'],
                't' => array_key_exists('texto', $o) ? $o['texto'] : null,
                'i' => array_key_exists('imagen_path', $o) ? $o['imagen_path'] : null,
            ]);
        }
    }

    public static function setRespuestaCorrecta(PDO $pdo, int $idPreguntaCurso, int $idOpcionCorrecta): void
    {
        $sql = 'INSERT INTO curso_eval_respuesta (id_pregunta_curso, id_opcion_correcta)
                VALUES (:p, :o)
                ON DUPLICATE KEY UPDATE id_opcion_correcta = VALUES(id_opcion_correcta)';
        $pdo->prepare($sql)->execute(['p' => $idPreguntaCurso, 'o' => $idOpcionCorrecta]);
    }

    public static function getOpcionCorrecta(PDO $pdo, int $idPreguntaCurso): ?int
    {
        $st = $pdo->prepare('SELECT id_opcion_correcta FROM curso_eval_respuesta WHERE id_pregunta_curso = :p LIMIT 1');
        $st->execute(['p' => $idPreguntaCurso]);
        $row = $st->fetch();

        return $row ? (int) $row['id_opcion_correcta'] : null;
    }

    public static function eliminarPregunta(PDO $pdo, int $idCurso, int $orden): void
    {
        $pdo->prepare('DELETE FROM curso_eval_preguntas WHERE id_curso = :c AND orden = :o AND id_variante IS NULL')->execute(['c' => $idCurso, 'o' => $orden]);
    }

    /** @return array<int, array<string,mixed>> */
    public static function preguntasPorVariante(PDO $pdo, int $idVariante): array
    {
        $st = $pdo->prepare('SELECT * FROM curso_eval_preguntas WHERE id_variante = :v ORDER BY orden ASC');
        $st->execute(['v' => $idVariante]);

        return $st->fetchAll();
    }

    /** Preguntas sin variante (modo único / legacy). */
    public static function preguntasSinVariante(PDO $pdo, int $idCurso): array
    {
        $st = $pdo->prepare('SELECT * FROM curso_eval_preguntas WHERE id_curso = :c AND id_variante IS NULL ORDER BY orden ASC');
        $st->execute(['c' => $idCurso]);

        return $st->fetchAll();
    }
}

