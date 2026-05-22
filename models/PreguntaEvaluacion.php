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

    /**
     * @param array{a?:?string,b?:?string,c?:?string,d?:?string}|null $imagenes Rutas relativas (uploads/...) o null
     */
    public static function crear(
        PDO $pdo,
        int $idCurso,
        string $enunciado,
        string $a,
        string $b,
        string $c,
        string $d,
        string $correcta,
        ?array $imagenes = null
    ): void {
        $ia = $imagenes['a'] ?? null;
        $ib = $imagenes['b'] ?? null;
        $ic = $imagenes['c'] ?? null;
        $idImg = $imagenes['d'] ?? null;
        $sql = 'INSERT INTO preguntas_evaluacion (id_curso, enunciado, opcion_a, opcion_b, opcion_c, opcion_d, respuesta_correcta,
                opcion_a_imagen, opcion_b_imagen, opcion_c_imagen, opcion_d_imagen)
                VALUES (:curso, :e, :a, :b, :c, :d, :ok, :ia, :ib, :ic, :idimg)';
        $st = $pdo->prepare($sql);
        $st->execute([
            'curso' => $idCurso,
            'e' => $enunciado,
            'a' => $a,
            'b' => $b,
            'c' => $c,
            'd' => $d,
            'ok' => $correcta,
            'ia' => $ia,
            'ib' => $ib,
            'ic' => $ic,
            'idimg' => $idImg,
        ]);
    }

    /**
     * @param array{a?:?string,b?:?string,c?:?string,d?:?string}|null $imagenes Rutas relativas (uploads/...) o null
     */
    public static function actualizar(
        PDO $pdo,
        int $idCurso,
        int $idPregunta,
        string $enunciado,
        string $a,
        string $b,
        string $c,
        string $d,
        string $correcta,
        ?array $imagenes = null
    ): bool {
        $ia = $imagenes['a'] ?? null;
        $ib = $imagenes['b'] ?? null;
        $ic = $imagenes['c'] ?? null;
        $idImg = $imagenes['d'] ?? null;

        $sql = 'UPDATE preguntas_evaluacion 
                SET enunciado = :e,
                    opcion_a = :a,
                    opcion_b = :b,
                    opcion_c = :c,
                    opcion_d = :d,
                    respuesta_correcta = :ok,
                    opcion_a_imagen = :ia,
                    opcion_b_imagen = :ib,
                    opcion_c_imagen = :ic,
                    opcion_d_imagen = :idimg
                WHERE id_pregunta = :id AND id_curso = :curso';
        $st = $pdo->prepare($sql);
        $st->execute([
            'curso' => $idCurso,
            'id' => $idPregunta,
            'e' => $enunciado,
            'a' => $a,
            'b' => $b,
            'c' => $c,
            'd' => $d,
            'ok' => $correcta,
            'ia' => $ia,
            'ib' => $ib,
            'ic' => $ic,
            'idimg' => $idImg,
        ]);

        return $st->rowCount() > 0;
    }

    /** @return array<int, array{clave:string,texto:string,imagen_path:string}> */
    public static function opcionesParaVista(array $p): array
    {
        $out = [];
        foreach (['a', 'b', 'c', 'd'] as $letra) {
            $out[] = [
                'clave' => $letra,
                'texto' => (string) ($p['opcion_' . $letra] ?? ''),
                'imagen_path' => (string) ($p['opcion_' . $letra . '_imagen'] ?? ''),
            ];
        }

        return $out;
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
