<?php

declare(strict_types=1);

class IntentoEvaluacion
{
    public static function registrar(PDO $pdo, string $cedula, int $idCurso, float $puntaje, string $resultado): void
    {
        $sql = 'INSERT INTO intentos_evaluacion (cedula_asesor, id_curso, puntaje_obtenido, resultado) VALUES (:a, :c, :p, :r)';
        $st = $pdo->prepare($sql);
        $st->execute(['a' => $cedula, 'c' => $idCurso, 'p' => $puntaje, 'r' => $resultado]);
    }
}
