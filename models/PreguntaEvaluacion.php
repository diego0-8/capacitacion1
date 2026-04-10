<?php

declare(strict_types=1);

class PreguntaEvaluacion
{
    /** @return array<int, array<string,mixed>> */
    public static function porCurso(PDO $pdo, int $idCurso): array
    {
        $sql = 'SELECT * FROM preguntas_evaluacion WHERE id_curso = :c ORDER BY id_pregunta ASC';
        $st = $pdo->prepare($sql);
        $st->execute(['c' => $idCurso]);
        return $st->fetchAll();
    }

    public static function crear(
        PDO $pdo,
        int $idCurso,
        string $enunciado,
        string $a,
        string $b,
        string $c,
        string $d,
        string $correcta
    ): void {
        $sql = 'INSERT INTO preguntas_evaluacion (id_curso, enunciado, opcion_a, opcion_b, opcion_c, opcion_d, respuesta_correcta) 
                VALUES (:curso, :e, :a, :b, :c, :d, :ok)';
        $st = $pdo->prepare($sql);
        $st->execute([
            'curso' => $idCurso,
            'e' => $enunciado,
            'a' => $a,
            'b' => $b,
            'c' => $c,
            'd' => $d,
            'ok' => $correcta,
        ]);
    }

    public static function eliminar(PDO $pdo, int $idPregunta): void
    {
        $st = $pdo->prepare('DELETE FROM preguntas_evaluacion WHERE id_pregunta = :id');
        $st->execute(['id' => $idPregunta]);
    }

    public static function buscar(PDO $pdo, int $idPregunta): ?array
    {
        $st = $pdo->prepare('SELECT * FROM preguntas_evaluacion WHERE id_pregunta = :id LIMIT 1');
        $st->execute(['id' => $idPregunta]);
        $row = $st->fetch();
        return $row ?: null;
    }
}
