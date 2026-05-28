<?php

declare(strict_types=1);

class CursoEvalVariante
{
    public static function getModoEvaluacion(PDO $pdo, int $idCurso): string
    {
        $st = $pdo->prepare('SELECT modo_evaluacion FROM curso_eval_config WHERE id_curso = :c LIMIT 1');
        $st->execute(['c' => $idCurso]);
        $row = $st->fetch();

        $modo = (string) ($row['modo_evaluacion'] ?? 'unico');

        return in_array($modo, ['unico', 'manual', 'aleatorio'], true) ? $modo : 'unico';
    }

    public static function setModoEvaluacion(PDO $pdo, int $idCurso, string $modo): void
    {
        if (!in_array($modo, ['unico', 'manual', 'aleatorio'], true)) {
            $modo = 'unico';
        }
        $sql = 'INSERT INTO curso_eval_config (id_curso, preguntas_requeridas, activo, modo_evaluacion)
                VALUES (:c, 1, 0, :m)
                ON DUPLICATE KEY UPDATE modo_evaluacion = VALUES(modo_evaluacion)';
        $pdo->prepare($sql)->execute(['c' => $idCurso, 'm' => $modo]);
    }

    // ── Variantes CRUD ──

    public static function crearVariante(PDO $pdo, int $idCurso, string $nombre, int $orden, int $preguntasRequeridas = 3): int
    {
        $orden = max(1, $orden);
        $preguntasRequeridas = max(1, min(10, $preguntasRequeridas));
        $sql = 'INSERT INTO curso_eval_variantes (id_curso, nombre_variante, orden, preguntas_requeridas)
                VALUES (:c, :n, :o, :p)';
        $pdo->prepare($sql)->execute([
            'c' => $idCurso,
            'n' => $nombre,
            'o' => $orden,
            'p' => $preguntasRequeridas,
        ]);

        return (int) $pdo->lastInsertId();
    }

    public static function actualizarVariante(PDO $pdo, int $idVariante, string $nombre, int $preguntasRequeridas): void
    {
        $preguntasRequeridas = max(1, min(10, $preguntasRequeridas));
        $pdo->prepare('UPDATE curso_eval_variantes SET nombre_variante = :n, preguntas_requeridas = :p WHERE id_variante = :id')
            ->execute(['n' => $nombre, 'p' => $preguntasRequeridas, 'id' => $idVariante]);
    }

    public static function eliminarVariante(PDO $pdo, int $idVariante): void
    {
        $pdo->prepare('DELETE FROM curso_eval_variantes WHERE id_variante = :id')->execute(['id' => $idVariante]);
    }

    /** @return array<int, array<string,mixed>> */
    public static function variantesPorCurso(PDO $pdo, int $idCurso): array
    {
        $st = $pdo->prepare('SELECT * FROM curso_eval_variantes WHERE id_curso = :c ORDER BY orden ASC');
        $st->execute(['c' => $idCurso]);

        return $st->fetchAll();
    }

    public static function buscarVariante(PDO $pdo, int $idVariante): ?array
    {
        $st = $pdo->prepare('SELECT * FROM curso_eval_variantes WHERE id_variante = :id LIMIT 1');
        $st->execute(['id' => $idVariante]);
        $row = $st->fetch();

        return $row ?: null;
    }

    public static function siguienteOrden(PDO $pdo, int $idCurso): int
    {
        $st = $pdo->prepare('SELECT COALESCE(MAX(orden), 0) + 1 AS nxt FROM curso_eval_variantes WHERE id_curso = :c');
        $st->execute(['c' => $idCurso]);

        return (int) ($st->fetch()['nxt'] ?? 1);
    }

    // ── Asignación de asesores a variantes (Formato 2) ──

    /** @param string[] $cedulas */
    public static function sincronizarAsesoresVariante(PDO $pdo, int $idVariante, array $cedulas): void
    {
        $variante = self::buscarVariante($pdo, $idVariante);
        if ($variante) {
            $idCurso = (int) $variante['id_curso'];
            $cedulasLimpias = [];
            foreach ($cedulas as $ced) {
                $ced = trim($ced);
                if ($ced !== '') {
                    $cedulasLimpias[] = $ced;
                }
            }
            if ($cedulasLimpias !== []) {
                $stOtras = $pdo->prepare(
                    'SELECT id_variante FROM curso_eval_variantes WHERE id_curso = :c AND id_variante != :v'
                );
                $stOtras->execute(['c' => $idCurso, 'v' => $idVariante]);
                $otrosIds = $stOtras->fetchAll(PDO::FETCH_COLUMN);
                if ($otrosIds !== []) {
                    $stDel = $pdo->prepare(
                        'DELETE FROM curso_eval_variante_asesores WHERE id_variante = :v AND cedula_asesor = :c'
                    );
                    foreach ($cedulasLimpias as $ced) {
                        foreach ($otrosIds as $otroVid) {
                            $stDel->execute(['v' => $otroVid, 'c' => $ced]);
                        }
                    }
                }
            }
        }

        $pdo->prepare('DELETE FROM curso_eval_variante_asesores WHERE id_variante = :v')->execute(['v' => $idVariante]);
        if ($cedulas === []) {
            return;
        }
        $sql = 'INSERT INTO curso_eval_variante_asesores (id_variante, cedula_asesor) VALUES (:v, :c)';
        $st = $pdo->prepare($sql);
        foreach ($cedulas as $ced) {
            $ced = trim($ced);
            if ($ced !== '') {
                $st->execute(['v' => $idVariante, 'c' => $ced]);
            }
        }
    }

    /** @return string[] */
    public static function asesoresPorVariante(PDO $pdo, int $idVariante): array
    {
        $st = $pdo->prepare('SELECT cedula_asesor FROM curso_eval_variante_asesores WHERE id_variante = :v');
        $st->execute(['v' => $idVariante]);

        return $st->fetchAll(PDO::FETCH_COLUMN);
    }

    public static function varianteDelAsesor(PDO $pdo, int $idCurso, string $cedula): ?int
    {
        $sql = 'SELECT va.id_variante
                FROM curso_eval_variante_asesores va
                JOIN curso_eval_variantes v ON v.id_variante = va.id_variante
                WHERE v.id_curso = :c AND va.cedula_asesor = :ced
                LIMIT 1';
        $st = $pdo->prepare($sql);
        $st->execute(['c' => $idCurso, 'ced' => $cedula]);
        $row = $st->fetch();

        return $row ? (int) $row['id_variante'] : null;
    }

    // ── Preguntas por variante ──

    /** @return array<int, array<string,mixed>> */
    public static function preguntasPorVariante(PDO $pdo, int $idVariante): array
    {
        $st = $pdo->prepare('SELECT * FROM curso_eval_preguntas WHERE id_variante = :v ORDER BY orden ASC');
        $st->execute(['v' => $idVariante]);

        return $st->fetchAll();
    }

    public static function setPreguntaVariante(PDO $pdo, int $idCurso, int $idVariante, int $orden, string $tipo, string $enunciado): int
    {
        $orden = max(1, $orden);
        if (!in_array($tipo, ['imagen_par', 'vf', 'multi'], true)) {
            $tipo = 'vf';
        }

        $st = $pdo->prepare(
            'SELECT id_pregunta_curso FROM curso_eval_preguntas
             WHERE id_curso = :c AND id_variante = :v AND orden = :o LIMIT 1'
        );
        $st->execute(['c' => $idCurso, 'v' => $idVariante, 'o' => $orden]);
        $row = $st->fetch();

        if ($row) {
            $id = (int) $row['id_pregunta_curso'];
            $pdo->prepare('UPDATE curso_eval_preguntas SET tipo = :t, enunciado = :e WHERE id_pregunta_curso = :id')
                ->execute(['t' => $tipo, 'e' => $enunciado, 'id' => $id]);

            return $id;
        }

        $pdo->prepare(
            'INSERT INTO curso_eval_preguntas (id_curso, id_variante, tipo, enunciado, orden)
             VALUES (:c, :v, :t, :e, :o)'
        )->execute(['c' => $idCurso, 'v' => $idVariante, 't' => $tipo, 'e' => $enunciado, 'o' => $orden]);

        return (int) $pdo->lastInsertId();
    }

    public static function eliminarPreguntaVariante(PDO $pdo, int $idCurso, int $idVariante, int $orden): void
    {
        $pdo->prepare(
            'DELETE FROM curso_eval_preguntas WHERE id_curso = :c AND id_variante = :v AND orden = :o'
        )->execute(['c' => $idCurso, 'v' => $idVariante, 'o' => $orden]);
    }

    // ── Instancia aleatoria (Formato 3) ──

    public static function obtenerInstancia(PDO $pdo, int $idCurso, string $cedula): ?array
    {
        $st = $pdo->prepare(
            'SELECT * FROM curso_eval_instancia_asesor WHERE id_curso = :c AND cedula_asesor = :ced LIMIT 1'
        );
        $st->execute(['c' => $idCurso, 'ced' => $cedula]);
        $row = $st->fetch();
        if (!$row) {
            return null;
        }
        $row['preguntas_ids'] = json_decode((string) $row['preguntas_ids'], true) ?: [];
        $row['preguntas_orden'] = json_decode((string) $row['preguntas_orden'], true) ?: [];
        $row['opciones_orden'] = json_decode((string) $row['opciones_orden'], true) ?: [];

        return $row;
    }

    /**
     * Genera una instancia aleatoria seleccionando preguntas de todas las variantes
     * distribuyendo equitativamente, y genera un orden aleatorio de opciones.
     */
    public static function generarInstanciaAleatoria(PDO $pdo, int $idCurso, string $cedula, int $numPreguntas): array
    {
        $variantes = self::variantesPorCurso($pdo, $idCurso);
        if ($variantes === []) {
            return [];
        }

        $todasPreguntas = [];
        $porVariante = [];
        foreach ($variantes as $v) {
            $vid = (int) $v['id_variante'];
            $pregs = self::preguntasPorVariante($pdo, $vid);
            foreach ($pregs as $p) {
                $idP = (int) ($p['id_pregunta_curso'] ?? 0);
                if ($idP > 0) {
                    $todasPreguntas[$idP] = $p;
                    $porVariante[$vid][] = $idP;
                }
            }
        }

        if ($todasPreguntas === []) {
            return [];
        }

        $seleccionadas = [];
        $numVariantes = count($porVariante);
        $porCada = max(1, intdiv($numPreguntas, $numVariantes));
        $restante = $numPreguntas - ($porCada * $numVariantes);

        foreach ($porVariante as $vid => $ids) {
            shuffle($ids);
            $tomar = $porCada;
            if ($restante > 0) {
                $tomar++;
                $restante--;
            }
            $seleccionadas = array_merge($seleccionadas, array_slice($ids, 0, $tomar));
        }

        $seleccionadas = array_slice($seleccionadas, 0, $numPreguntas);
        $preguntasIds = $seleccionadas;

        $preguntasOrden = $seleccionadas;
        shuffle($preguntasOrden);

        $opcionesOrden = [];
        foreach ($preguntasIds as $idP) {
            $ops = CursoEvaluacion::opcionesPorPregunta($pdo, $idP);
            $claves = [];
            foreach ($ops as $o) {
                $claves[] = (string) ($o['clave'] ?? '');
            }
            shuffle($claves);
            $opcionesOrden[(string) $idP] = $claves;
        }

        $pdo->prepare('DELETE FROM curso_eval_instancia_asesor WHERE id_curso = :c AND cedula_asesor = :ced')
            ->execute(['c' => $idCurso, 'ced' => $cedula]);

        $sql = 'INSERT INTO curso_eval_instancia_asesor (id_curso, cedula_asesor, preguntas_ids, preguntas_orden, opciones_orden)
                VALUES (:c, :ced, :ids, :ord, :ops)';
        $pdo->prepare($sql)->execute([
            'c' => $idCurso,
            'ced' => $cedula,
            'ids' => json_encode($preguntasIds),
            'ord' => json_encode($preguntasOrden),
            'ops' => json_encode($opcionesOrden),
        ]);

        return [
            'id_instancia' => (int) $pdo->lastInsertId(),
            'preguntas_ids' => $preguntasIds,
            'preguntas_orden' => $preguntasOrden,
            'opciones_orden' => $opcionesOrden,
        ];
    }

    /**
     * Reintento: mantiene las mismas preguntas pero regenera el orden de preguntas y opciones.
     */
    public static function regenerarOrdenInstancia(PDO $pdo, int $idInstancia): void
    {
        $st = $pdo->prepare('SELECT * FROM curso_eval_instancia_asesor WHERE id_instancia = :id LIMIT 1');
        $st->execute(['id' => $idInstancia]);
        $row = $st->fetch();
        if (!$row) {
            return;
        }

        $preguntasIds = json_decode((string) $row['preguntas_ids'], true) ?: [];
        $nuevoOrden = $preguntasIds;
        shuffle($nuevoOrden);

        $opcionesOrden = [];
        foreach ($preguntasIds as $idP) {
            $ops = CursoEvaluacion::opcionesPorPregunta($pdo, $idP);
            $claves = [];
            foreach ($ops as $o) {
                $claves[] = (string) ($o['clave'] ?? '');
            }
            shuffle($claves);
            $opcionesOrden[(string) $idP] = $claves;
        }

        $pdo->prepare(
            'UPDATE curso_eval_instancia_asesor SET preguntas_orden = :ord, opciones_orden = :ops WHERE id_instancia = :id'
        )->execute([
            'ord' => json_encode($nuevoOrden),
            'ops' => json_encode($opcionesOrden),
            'id' => $idInstancia,
        ]);
    }

    /**
     * Arma los items de evaluación a partir de una instancia aleatoria,
     * reordenando las opciones según opciones_orden.
     * @return array<int, array{pregunta:array, opciones:array, correcta:int|null}>
     */
    public static function itemsDesdeInstancia(PDO $pdo, array $instancia): array
    {
        $orden = $instancia['preguntas_orden'] ?? [];
        $opOrd = $instancia['opciones_orden'] ?? [];
        $items = [];

        foreach ($orden as $idP) {
            $idP = (int) $idP;
            $st = $pdo->prepare('SELECT * FROM curso_eval_preguntas WHERE id_pregunta_curso = :id LIMIT 1');
            $st->execute(['id' => $idP]);
            $pregunta = $st->fetch();
            if (!$pregunta) {
                continue;
            }

            $ops = CursoEvaluacion::opcionesPorPregunta($pdo, $idP);
            $corr = CursoEvaluacion::getOpcionCorrecta($pdo, $idP);

            $claveOrden = $opOrd[(string) $idP] ?? null;
            if (is_array($claveOrden) && $claveOrden !== []) {
                $byKey = [];
                foreach ($ops as $o) {
                    $byKey[(string) ($o['clave'] ?? '')] = $o;
                }
                $opsOrdenadas = [];
                foreach ($claveOrden as $k) {
                    if (isset($byKey[$k])) {
                        $opsOrdenadas[] = $byKey[$k];
                    }
                }
                $ops = $opsOrdenadas;
            }

            $items[] = ['pregunta' => $pregunta, 'opciones' => $ops, 'correcta' => $corr];
        }

        return $items;
    }
}
