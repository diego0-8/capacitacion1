<?php

declare(strict_types=1);

class CoordinadorReporte
{
    private static function tablaExiste(PDO $pdo, string $nombre): bool
    {
        $st = $pdo->prepare('SELECT COUNT(*) AS n FROM information_schema.tables WHERE table_schema = :db AND table_name = :t');
        $st->execute(['db' => DB_NAME, 't' => $nombre]);
        $r = $st->fetch();
        return (int) ($r['n'] ?? 0) === 1;
    }

    /** @return array{curso:array<string,mixed>,asesores:array<int,array<string,mixed>>,modulos:array<int,array<string,mixed>>} */
    public static function asesoresPorCurso(PDO $pdo, array $curso, int $idCurso): array
    {
        // #region agent log
        debug_log('CoordinadorReporte::asesoresPorCurso', 'enter', ['idCurso' => $idCurso], 'run1', 'H5');
        // #endregion
        // LEFT JOIN: si falta fila en usuarios, igual se lista la inscripción. Excluir solo no-asesores con usuario existente.
        $sql = 'SELECT ca.id_asignacion, ca.cedula_asesor, ca.id_curso, ca.fecha_asignacion,
                       ca.estado_capacitacion, ca.progreso_porcentaje, ca.calificacion_obtenida, ca.fecha_completado,
                       u.nombre AS nombre_asesor
                FROM capacitaciones_asignadas ca
                LEFT JOIN usuarios u ON u.cedula = ca.cedula_asesor
                WHERE ca.id_curso = :c
                  AND (u.cedula IS NULL OR u.rol = \'asesor\')
                ORDER BY COALESCE(u.nombre, ca.cedula_asesor) ASC';
        $st = $pdo->prepare($sql);
        $st->execute(['c' => $idCurso]);
        $asesores = $st->fetchAll();
        // #region agent log
        debug_log('CoordinadorReporte::asesoresPorCurso', 'tras_query_asesores', ['n' => count($asesores)], 'run1', 'H5');
        // #endregion

        $modulos = ModuloCurso::porCurso($pdo, $idCurso);
        $idModulos = [];
        foreach ($modulos as $m) {
            $idM = (int) ($m['id_modulo'] ?? 0);
            if ($idM > 0) {
                $idModulos[] = $idM;
            }
        }

        // Totales de lecciones por módulo
        $totalesModulo = []; // idModulo => total
        if (!empty($idModulos)) {
            $in = implode(',', array_fill(0, count($idModulos), '?'));
            $q = $pdo->prepare("SELECT id_modulo, COUNT(*) AS n FROM lecciones WHERE id_modulo IN ($in) GROUP BY id_modulo");
            $q->execute($idModulos);
            foreach ($q->fetchAll() as $r) {
                $totalesModulo[(int) $r['id_modulo']] = (int) $r['n'];
            }
        }

        // Completadas por módulo y asesor (agregado)
        $doneModuloAsesor = []; // [cedula][idModulo] => done
        if (!empty($idModulos) && self::tablaExiste($pdo, 'leccion_completado')) {
            $in = implode(',', array_fill(0, count($idModulos), '?'));
            $q = $pdo->prepare(
                "SELECT lc.cedula_asesor, l.id_modulo, COUNT(*) AS n
                 FROM leccion_completado lc
                 JOIN lecciones l ON l.id_leccion = lc.id_leccion
                 WHERE l.id_modulo IN ($in)
                 GROUP BY lc.cedula_asesor, l.id_modulo"
            );
            $q->execute($idModulos);
            foreach ($q->fetchAll() as $r) {
                $ced = (string) $r['cedula_asesor'];
                $idM = (int) $r['id_modulo'];
                $doneModuloAsesor[$ced][$idM] = (int) $r['n'];
            }
        }

        // Quiz activo por módulo
        $quizActivo = []; // idModulo => bool
        if (!empty($idModulos) && self::tablaExiste($pdo, 'modulo_quiz_config')) {
            $in = implode(',', array_fill(0, count($idModulos), '?'));
            $q = $pdo->prepare("SELECT id_modulo, activo FROM modulo_quiz_config WHERE id_modulo IN ($in)");
            $q->execute($idModulos);
            foreach ($q->fetchAll() as $r) {
                $quizActivo[(int) $r['id_modulo']] = (int) ($r['activo'] ?? 0) === 1;
            }
        }

        // Quiz aprobado (modulo_completado) por asesor
        $quizAprobado = []; // [cedula][idModulo] => true
        if (!empty($idModulos) && self::tablaExiste($pdo, 'modulo_completado')) {
            $in = implode(',', array_fill(0, count($idModulos), '?'));
            $q = $pdo->prepare("SELECT id_modulo, cedula_asesor FROM modulo_completado WHERE id_modulo IN ($in)");
            $q->execute($idModulos);
            foreach ($q->fetchAll() as $r) {
                $ced = (string) $r['cedula_asesor'];
                $idM = (int) $r['id_modulo'];
                $quizAprobado[$ced][$idM] = true;
            }
        }

        // Enriquecer asesores con estado por módulo
        foreach ($asesores as &$a) {
            $ced = (string) ($a['cedula_asesor'] ?? '');
            $mods = [];
            foreach ($modulos as $m) {
                $idM = (int) ($m['id_modulo'] ?? 0);
                if ($idM <= 0) {
                    continue;
                }
                $total = $totalesModulo[$idM] ?? 0;
                $done = $doneModuloAsesor[$ced][$idM] ?? 0;
                $qa = $quizActivo[$idM] ?? false;
                $qok = !empty($quizAprobado[$ced][$idM]);

                $pct = 0;
                if ($total > 0) {
                    $pct = (int) round(($done / $total) * 100);
                    if ($pct > 100) {
                        $pct = 100;
                    }
                }
                if ($qa) {
                    if ($total > 0 && $done >= $total && !$qok) {
                        $pct = 99;
                    }
                    if ($qok) {
                        $pct = 100;
                    }
                }

                $mods[] = [
                    'id_modulo' => $idM,
                    'titulo' => (string) ($m['titulo'] ?? ''),
                    'total' => $total,
                    'done' => $done,
                    'progreso' => $pct,
                    'quiz_activo' => $qa,
                    'quiz_aprobado' => $qok,
                ];
            }
            $a['modulos'] = $mods;

            $nota = (float) ($a['calificacion_obtenida'] ?? 0);
            $a['evaluacion_pct'] = (int) max(0, min(100, round($nota * 10)));
        }
        unset($a);

        return ['curso' => $curso, 'asesores' => $asesores, 'modulos' => $modulos];
    }

    /**
     * Reporte tabular por curso (resumen operable para export/filtrado).
     *
     * @return array{curso:array<string,mixed>,filas:array<int,array<string,mixed>>,modulos:array<int,array<string,mixed>>}
     */
    public static function reportePorCurso(PDO $pdo, array $curso, int $idCurso): array
    {
        $data = self::asesoresPorCurso($pdo, $curso, $idCurso);
        $modulos = $data['modulos'] ?? [];
        $asesores = $data['asesores'] ?? [];

        $idModulos = [];
        foreach ($modulos as $m) {
            $idM = (int) ($m['id_modulo'] ?? 0);
            if ($idM > 0) {
                $idModulos[] = $idM;
            }
        }

        // Quiz activos (para métricas globales)
        $quizActivo = []; // idModulo => bool
        if (!empty($idModulos) && self::tablaExiste($pdo, 'modulo_quiz_config')) {
            $in = implode(',', array_fill(0, count($idModulos), '?'));
            $q = $pdo->prepare("SELECT id_modulo, activo FROM modulo_quiz_config WHERE id_modulo IN ($in)");
            $q->execute($idModulos);
            foreach ($q->fetchAll() as $r) {
                $quizActivo[(int) $r['id_modulo']] = (int) ($r['activo'] ?? 0) === 1;
            }
        }
        $nQuizActivosTotal = 0;
        foreach ($idModulos as $idM) {
            if (!empty($quizActivo[$idM])) {
                $nQuizActivosTotal++;
            }
        }

        // Último intento de evaluación final por asesor (si existe)
        $ultimoIntentoEval = []; // cedula => row
        if (self::tablaExiste($pdo, 'intentos_evaluacion')) {
            $st = $pdo->prepare(
                'SELECT ie.*
                 FROM intentos_evaluacion ie
                 JOIN (
                    SELECT cedula_asesor, id_curso, MAX(fecha_intento) AS max_fecha
                    FROM intentos_evaluacion
                    WHERE id_curso = :c
                    GROUP BY cedula_asesor, id_curso
                 ) last
                   ON last.cedula_asesor = ie.cedula_asesor AND last.id_curso = ie.id_curso AND last.max_fecha = ie.fecha_intento
                 WHERE ie.id_curso = :c'
            );
            $st->execute(['c' => $idCurso]);
            foreach ($st->fetchAll() as $r) {
                $ultimoIntentoEval[(string) ($r['cedula_asesor'] ?? '')] = $r;
            }
        }

        $filas = [];
        foreach ($asesores as $a) {
            $ced = (string) ($a['cedula_asesor'] ?? '');
            $mods = $a['modulos'] ?? [];
            $modulosTotal = count($mods);
            $modulosCompletos = 0;
            $quicesAprobados = 0;
            foreach ($mods as $m) {
                $total = (int) ($m['total'] ?? 0);
                $done = (int) ($m['done'] ?? 0);
                $qa = !empty($m['quiz_activo']);
                $qok = !empty($m['quiz_aprobado']);
                // Regla de completado del módulo (decisión B): si no hay clases, no cuenta como completo.
                $okLecciones = $total > 0 && $done >= $total;
                $okQuiz = !$qa || $qok;
                if ($okLecciones && $okQuiz) {
                    $modulosCompletos++;
                }
                if ($qa && $qok) {
                    $quicesAprobados++;
                }
            }

            $eval = $ultimoIntentoEval[$ced] ?? null;
            $evalRes = is_array($eval) ? (string) ($eval['resultado'] ?? '') : '';
            $evalP = is_array($eval) ? (string) ($eval['puntaje_obtenido'] ?? '') : '';
            $evalF = is_array($eval) ? (string) ($eval['fecha_intento'] ?? '') : '';

            $filas[] = [
                'cedula_asesor' => $ced,
                'nombre_asesor' => (string) ($a['nombre_asesor'] ?? ''),
                'estado_capacitacion' => (string) ($a['estado_capacitacion'] ?? ''),
                'progreso_porcentaje' => (int) ($a['progreso_porcentaje'] ?? 0),
                'calificacion_obtenida' => (string) ($a['calificacion_obtenida'] ?? ''),
                'fecha_asignacion' => (string) ($a['fecha_asignacion'] ?? ''),
                'fecha_completado' => (string) ($a['fecha_completado'] ?? ''),
                'modulos_total' => $modulosTotal,
                'modulos_completos' => $modulosCompletos,
                'quices_activos' => $nQuizActivosTotal,
                'quices_aprobados' => $quicesAprobados,
                'evaluacion_resultado' => $evalRes,
                'evaluacion_puntaje' => $evalP,
                'evaluacion_fecha' => $evalF,
            ];
        }

        return ['curso' => $curso, 'filas' => $filas, 'modulos' => $modulos];
    }

    /**
     * Detalle / timeline por asesor dentro de un curso.
     *
     * @return array{curso:array<string,mixed>,asesor:array<string,mixed>,timeline:array<int,array<string,mixed>>}
     */
    public static function detalleAsesor(PDO $pdo, array $curso, int $idCurso, string $cedulaAsesor): array
    {
        $asig = CapacitacionAsignada::buscarPorAsesorCurso($pdo, $cedulaAsesor, $idCurso);
        if (!$asig) {
            return ['curso' => $curso, 'asesor' => ['cedula' => $cedulaAsesor], 'timeline' => []];
        }
        $u = Usuario::buscarPorCedula($pdo, $cedulaAsesor);

        $timeline = [];

        // Lecciones completadas
        if (self::tablaExiste($pdo, 'leccion_completado')) {
            $st = $pdo->prepare(
                'SELECT lc.created_at, l.id_modulo, l.titulo_leccion
                 FROM leccion_completado lc
                 JOIN lecciones l ON l.id_leccion = lc.id_leccion
                 WHERE lc.cedula_asesor = :a AND l.id_curso = :c
                 ORDER BY lc.created_at ASC'
            );
            $st->execute(['a' => $cedulaAsesor, 'c' => $idCurso]);
            foreach ($st->fetchAll() as $r) {
                $timeline[] = [
                    'ts' => (string) ($r['created_at'] ?? ''),
                    'tipo' => 'leccion',
                    'titulo' => (string) ($r['titulo_leccion'] ?? ''),
                    'id_modulo' => (int) ($r['id_modulo'] ?? 0),
                ];
            }
        }

        // Intentos de quiz por módulo
        if (self::tablaExiste($pdo, 'modulo_intentos')) {
            $st = $pdo->prepare(
                'SELECT mi.fecha_intento, mi.id_modulo, mi.total_preguntas, mi.correctas, mi.aprobado
                 FROM modulo_intentos mi
                 JOIN cursos_modulos cm ON cm.id_modulo = mi.id_modulo
                 WHERE mi.cedula_asesor = :a AND cm.id_curso = :c
                 ORDER BY mi.fecha_intento ASC'
            );
            $st->execute(['a' => $cedulaAsesor, 'c' => $idCurso]);
            foreach ($st->fetchAll() as $r) {
                $timeline[] = [
                    'ts' => (string) ($r['fecha_intento'] ?? ''),
                    'tipo' => 'quiz_modulo',
                    'id_modulo' => (int) ($r['id_modulo'] ?? 0),
                    'total' => (int) ($r['total_preguntas'] ?? 0),
                    'correctas' => (int) ($r['correctas'] ?? 0),
                    'aprobado' => !empty($r['aprobado']),
                ];
            }
        }

        // Intentos de evaluación final
        if (self::tablaExiste($pdo, 'intentos_evaluacion')) {
            $st = $pdo->prepare(
                'SELECT fecha_intento, puntaje_obtenido, resultado
                 FROM intentos_evaluacion
                 WHERE cedula_asesor = :a AND id_curso = :c
                 ORDER BY fecha_intento ASC'
            );
            $st->execute(['a' => $cedulaAsesor, 'c' => $idCurso]);
            foreach ($st->fetchAll() as $r) {
                $timeline[] = [
                    'ts' => (string) ($r['fecha_intento'] ?? ''),
                    'tipo' => 'evaluacion_final',
                    'puntaje' => (string) ($r['puntaje_obtenido'] ?? ''),
                    'resultado' => (string) ($r['resultado'] ?? ''),
                ];
            }
        }

        usort(
            $timeline,
            static fn(array $a, array $b): int => strcmp((string) ($a['ts'] ?? ''), (string) ($b['ts'] ?? ''))
        );

        return [
            'curso' => $curso,
            'asesor' => [
                'cedula' => $cedulaAsesor,
                'nombre' => (string) (($u['nombre'] ?? '') ?: ($asig['cedula_asesor'] ?? '')),
                'estado_capacitacion' => (string) ($asig['estado_capacitacion'] ?? ''),
                'progreso_porcentaje' => (int) ($asig['progreso_porcentaje'] ?? 0),
                'calificacion_obtenida' => (string) ($asig['calificacion_obtenida'] ?? ''),
                'fecha_asignacion' => (string) ($asig['fecha_asignacion'] ?? ''),
                'fecha_completado' => (string) ($asig['fecha_completado'] ?? ''),
            ],
            'timeline' => $timeline,
        ];
    }
}

