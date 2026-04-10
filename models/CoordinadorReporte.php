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
}

