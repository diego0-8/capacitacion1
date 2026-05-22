<?php

declare(strict_types=1);

class AsesorController extends Controller
{
    /** @return array<string,mixed>|null */
    private function asegurarAsignacion(PDO $pdo, int $idAsignacion): ?array
    {
        $row = CapacitacionAsignada::buscar($pdo, $idAsignacion);

        if ($row === null || ($row['cedula_asesor'] ?? '') !== ($_SESSION['usuario_cedula'] ?? '')) {
            return null;
        }
        return $row;
    }

    /** Nota sobre 10: 10.0 si acierta todas las preguntas evaluadas. */
    private static function puntajeEvaluacionFinal(int $correctas, int $total): float
    {
        if ($total <= 0) {
            return 0.0;
        }
        if ($correctas >= $total) {
            return 10.0;
        }

        return min(10.0, round(($correctas / $total) * 10, 2));
    }

    private static function notaEvaluacionTexto(float $puntaje): string
    {
        return $puntaje >= 9.995 ? '10' : number_format($puntaje, 2);
    }

    /**
     * @param array<int, array<string, mixed>> $modulos
     * @param array<int, array<int, array<string, mixed>>> $leccionesPorModulo
     * @return array<int, array<string, mixed>>
     */
    private function buildModuloEstadoAsesor(
        PDO $pdo,
        int $idCurso,
        string $cedulaAsesor,
        array $modulos,
        array $leccionesPorModulo
    ): array {
        $completadasSet = LeccionCompletado::idsCompletadasPorCurso($pdo, $idCurso, $cedulaAsesor);
        $moduloEstado = [];
        foreach ($modulos as $m) {
            $idModulo = (int) ($m['id_modulo'] ?? 0);

            if ($idModulo <= 0) {
                continue;
            }
            $lecs = $leccionesPorModulo[$idModulo] ?? [];
            $total = count($lecs);
            $done = 0;
            foreach ($lecs as $L) {
                $idL = (int) ($L['id_leccion'] ?? 0);

                if ($idL > 0 && isset($completadasSet[$idL])) {
                    $done++;
                }
            }
            $cfg = ModuloQuiz::getConfig($pdo, $idModulo);
            $quizActivo = (int) ($cfg['activo'] ?? 0) === 1;
            $quizAprobado = ModuloCompletado::estaCompletado($pdo, $idModulo, $cedulaAsesor);
            $canQuiz = $quizActivo && $total > 0 && $done >= $total;

            $pct = 0;

            if ($total > 0) {
                $pct = (int) round(($done / $total) * 100);

                if ($pct > 100) {
                    $pct = 100;
                }
            }
            if ($quizActivo) {
            if ($total > 0 && $done >= $total && !$quizAprobado) {
                    $pct = 99;
                }
                if ($quizAprobado) {
                    $pct = 100;
                }
            }

            $moduloEstado[$idModulo] = [
                'total' => $total,
                'completadas' => $done,
                'progreso' => $pct,
                'quiz_activo' => $quizActivo,
                'quiz_aprobado' => $quizAprobado,
                'can_quiz' => $canQuiz,
            ];
        }

        return $moduloEstado;
    }

    /** Promedio del % por módulo (misma métrica que el acordeón lateral). */
    private static function progresoGeneralCurso(array $moduloEstado): int
    {
            if ($moduloEstado === []) {
            return 0;
        }
        $sum = 0;
        foreach ($moduloEstado as $st) {
            $sum += (int) ($st['progreso'] ?? 0);
        }

        return (int) round($sum / count($moduloEstado));
    }

    private static function asesorCompletoTodosModulos(array $moduloEstado): bool
    {
            if ($moduloEstado === []) {
            return false;
        }
        foreach ($moduloEstado as $st) {
            $total = (int) ($st['total'] ?? 0);
            $done = (int) ($st['completadas'] ?? 0);
            $quizActivo = !empty($st['quiz_activo']);
            $quizOk = !empty($st['quiz_aprobado']);
            // Regla: un módulo sin clases NO cuenta como 100%.
            if ($total === 0) {
                return false;
            }
            if ($done < $total) {
                return false;
            }
            if ($quizActivo && !$quizOk) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param array<string, mixed> $asig
     * @return array{0: int, 1: string}
     */
    private function resolverProgresoYEstado(array $asig, array $moduloEstado, int $nLeccionesCurso): array
    {
        $estado = (string) ($asig['estado_capacitacion'] ?? 'en_progreso');

        if ($estado === 'completado') {
            return [100, 'completado'];
        }
        $pg = self::progresoGeneralCurso($moduloEstado);

        if ($nLeccionesCurso > 0 && self::asesorCompletoTodosModulos($moduloEstado)) {
            return [100, 'evaluacion_pendiente'];
        }
        if ($estado === 'evaluacion_pendiente') {
            $estado = 'en_progreso';
        } elseif ($estado === 'pendiente') {
            $estado = 'en_progreso';
        }

        return [$pg, $estado];
    }

    public function index(): void
    {
        $this->requireAuth(['asesor']);
        $pdo = getPDO();
        $cedula = (string) ($_SESSION['usuario_cedula'] ?? '');
        $items = CapacitacionAsignada::porAsesor($pdo, $cedula);
        $cursos = Curso::activosParaAsesor($pdo, $cedula);
        $insignias = Insignia::mapCursoCompletadoPorAsesor($pdo, $cedula);

        $asignadosPorCurso = [];
        foreach ($items as $it) {
            $asignadosPorCurso[(int) ($it['id_curso'] ?? 0)] = $it;
        }

        $cursosInscripcion = [];
        foreach ($cursos as $c) {
            $idCurso = (int) ($c['id_cursos'] ?? 0);
            if ($idCurso > 0 && !isset($asignadosPorCurso[$idCurso])) {
                $cursosInscripcion[] = $c;
            }
        }

        $misCursosPendientes = [];
        $certificaciones = [];
        foreach ($items as $it) {
            if ((string) ($it['estado_capacitacion'] ?? '') === 'completado') {
                $certificaciones[] = $it;
            } else {
                $misCursosPendientes[] = $it;
            }
        }

        $tabsValidos = ['inicio', 'inscripciones', 'mis-cursos', 'certificaciones'];
        $tabInicial = (string) ($_GET['tab'] ?? 'inicio');
        if (!in_array($tabInicial, $tabsValidos, true)) {
            $tabInicial = 'inicio';
        }

        $this->render('asesor/index', [
            'items' => $items,
            'cursos' => $cursos,
            'cursosInscripcion' => $cursosInscripcion,
            'misCursosPendientes' => $misCursosPendientes,
            'certificaciones' => $certificaciones,
            'stats' => [
                'pendientes' => count($misCursosPendientes),
                'completados' => count($certificaciones),
                'disponibles_inscripcion' => count($cursosInscripcion),
            ],
            'tabInicial' => $tabInicial,
            'insigniasPorCurso' => $insignias,
            'nombreAsesorCompleto' => (string) ($_SESSION['usuario_nombre'] ?? ''),
            'mensaje' => $this->flash('ok'),
            'error' => $this->flash('error'),
        ]);
    }

    /** Vista imprimible del certificado de curso completado (guardar como PDF desde el navegador). */
    public function certificado(): void
    {
        $this->requireAuth(['asesor']);
        $pdo = getPDO();
        $idAsignacion = (int) ($_GET['id'] ?? 0);

        if ($idAsignacion <= 0) {
            $this->flash('error', 'Solicitud inválida.');

            $this->redirect('?c=asesor&a=index');
            return;
        }
        $asig = $this->asegurarAsignacion($pdo, $idAsignacion);

        if ($asig === null) {
            $this->flash('error', 'No tiene acceso a esta capacitación.');

            $this->redirect('?c=asesor&a=index');
            return;
        }
        if ((string) ($asig['estado_capacitacion'] ?? '') !== 'completado') {
            $this->flash('error', 'El certificado solo está disponible cuando el curso está completado.');

            $this->redirect('?c=asesor&a=index');
            return;
        }

        CapacitacionAsignada::registrarPrimeraDescargaCertificado($pdo, $idAsignacion);
        $asig = CapacitacionAsignada::buscar($pdo, $idAsignacion);

        if ($asig === null) {
            $this->flash('error', 'No tiene acceso a esta capacitación.');

            $this->redirect('?c=asesor&a=index');
            return;
        }

        $cedula = (string) ($_SESSION['usuario_cedula'] ?? '');
        $u = Usuario::buscarPorCedula($pdo, $cedula);
        $empresaKey = is_array($u) ? (string) ($u['empresa'] ?? 'onix') : 'onix';

        if (!in_array($empresaKey, ['onix', 'nextdata', 'tys'], true)) {
            $empresaKey = 'onix';
        }
        $empresaNombre = match ($empresaKey) {
            'nextdata' => 'Nextdata S.A.S',
            'tys' => 'TyS S.A.S',
            default => 'Onix bpo',
        };
        $empresaNit = match ($empresaKey) {
            'nextdata' => '900.309.922-9',
            'tys' => '900.871.877-3',
            default => '900.832.042-4',
        };
        $empresaFondoUrl = BASE_URL . '/img/' . match ($empresaKey) {
            'nextdata' => 'nextdata.png',
            'tys' => 'tys.png',
            default => 'onix.png',
        };

        $rawFechaCert = (string) ($asig['fecha_primera_descarga_certificado'] ?? '');
        $fechaCertificado = '';

        if ($rawFechaCert !== '') {
            try {
                $dt = new DateTimeImmutable($rawFechaCert);
                $fechaCertificado = $dt->format('d/m/Y');

            }
            catch (Exception) {
                $fechaCertificado = '';
            }
        }

        $cedulaSoloDigitos = preg_replace('/\D+/', '', $cedula) ?? '';
        $documentoAsesor = $cedulaSoloDigitos !== '' ? 'CC ' . $cedulaSoloDigitos : '';


        $this->render('asesor/certificado_print', [
            'nombreCurso' => (string) ($asig['nombre_curso'] ?? ''),
            'nombreAsesor' => (string) ($_SESSION['usuario_nombre'] ?? ''),
            'fechaCertificado' => $fechaCertificado,
            'empresaKey' => $empresaKey,
            'empresaNombre' => $empresaNombre,
            'empresaNit' => $empresaNit,
            'empresaFondoUrl' => $empresaFondoUrl,
            'documentoAsesor' => $documentoAsesor,
        ]);
    }

    public function inscribirse(): void
    {
        $this->requireAuth(['asesor']);

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('?c=asesor&a=index');
            return;
        }
        $pdo = getPDO();
        $idCurso = (int) ($_POST['id_curso'] ?? 0);

        if ($idCurso <= 0) {
            $this->flash('error', 'Curso inválido.');

            $this->redirect('?c=asesor&a=index');
            return;
        }

        $curso = Curso::buscar($pdo, $idCurso);

        if (!$curso || ($curso['estado'] ?? '') !== 'activo') {
            $this->flash('error', 'Curso no disponible.');

            $this->redirect('?c=asesor&a=index');
            return;
        }

        $cedula = (string) $_SESSION['usuario_cedula'];
        if (!CursoAccesoAsesor::asesorPuedeVer($pdo, $idCurso, $cedula)) {
            $this->flash('error', 'No tiene permiso para inscribirse en este curso.');
            $this->redirect('?c=asesor&a=index');
            return;
        }
        $ya = CapacitacionAsignada::buscarPorAsesorCurso($pdo, $cedula, $idCurso);

        if ($ya) {
            $this->redirect('?c=asesor&a=curso&id=' . (int) $ya['id_asignacion']);
            return;
        }

        try {
            $idAsignacion = CapacitacionAsignada::crearYRetornarId($pdo, $cedula, $idCurso);

        }
            catch (Throwable $e) {
            $this->flash('error', 'No se pudo inscribir al curso.');

            $this->redirect('?c=asesor&a=index');
            return;
        }

        $this->flash('ok', 'Inscripción realizada.');

        $this->redirect('?c=asesor&a=curso&id=' . $idAsignacion);
    }

    public function curso(): void
    {
        $this->requireAuth(['asesor']);
        $pdo = getPDO();
        $idAsignacion = (int) ($_GET['id'] ?? 0);
        $asig = $this->asegurarAsignacion($pdo, $idAsignacion);

        if ($asig === null) {
            $this->flash('error', 'Capacitación no encontrada.');

            $this->redirect('?c=asesor&a=index');
            return;
        }
        $idCurso = (int) $asig['id_curso'];

        if (($asig['estado_capacitacion'] ?? '') === 'pendiente') {
            CapacitacionAsignada::actualizarProgresoEstado($pdo, $idAsignacion, (int) $asig['progreso_porcentaje'], 'en_progreso');
            $asig['estado_capacitacion'] = 'en_progreso';
        }

        $cedulaAsesor = (string) ($_SESSION['usuario_cedula'] ?? '');
        $modulos = ModuloCurso::porCurso($pdo, $idCurso);
        $leccionesPorModulo = [];
        $lecciones = Leccion::porCurso($pdo, $idCurso);
        foreach ($modulos as $m) {
            $idModulo = (int) ($m['id_modulo'] ?? 0);

            if ($idModulo > 0) {
                $leccionesPorModulo[$idModulo] = Leccion::porModulo($pdo, $idModulo);
            }
        }

        $completadasSet = LeccionCompletado::idsCompletadasPorCurso($pdo, $idCurso, $cedulaAsesor);

        // Lección seleccionada para panel derecho
        $idLeccionSel = (int) ($_GET['id_leccion'] ?? 0);
        $leccionSeleccionada = null;

        if ($idLeccionSel > 0) {
            $tmp = Leccion::buscar($pdo, $idLeccionSel);

            if ($tmp && (int) ($tmp['id_curso'] ?? 0) === $idCurso) {
                $leccionSeleccionada = $tmp;
            }
        }
        if ($leccionSeleccionada === null && !empty($lecciones)) {
            $leccionSeleccionada = Leccion::buscar($pdo, (int) $lecciones[0]['id_leccion']);
            $idLeccionSel = (int) ($leccionSeleccionada['id_leccion'] ?? 0);
        }

        $idModuloAcordeonAbierto = 0;

        if ($leccionSeleccionada !== null) {
            $idModuloAcordeonAbierto = (int) ($leccionSeleccionada['id_modulo'] ?? 0);
        }
        if ($idModuloAcordeonAbierto <= 0 && !empty($modulos)) {
            $idModuloAcordeonAbierto = (int) ($modulos[0]['id_modulo'] ?? 0);
        }

        // Progreso por módulo + gating del quiz (misma fuente que barra general)
        $moduloEstado = $this->buildModuloEstadoAsesor($pdo, $idCurso, $cedulaAsesor, $modulos, $leccionesPorModulo);

        // Datos de quiz para modal (se renderiza en la misma vista)
        $quizDataPorModulo = [];
        foreach ($modulos as $m) {
            $idModulo = (int) ($m['id_modulo'] ?? 0);

            if ($idModulo <= 0) {
                continue;
            }
            $cfg = ModuloQuiz::getConfig($pdo, $idModulo);

            if ((int) ($cfg['activo'] ?? 0) !== 1) {
                continue;
            }
            $req = (int) ($cfg['preguntas_requeridas'] ?? 1);
            $preguntas = ModuloQuiz::preguntasPorModulo($pdo, $idModulo);
            $preguntas = array_values(array_filter($preguntas, static fn($p) => (int) ($p['orden'] ?? 0) >= 1 && (int) ($p['orden'] ?? 0) <= $req));
            $items = [];
            foreach ($preguntas as $p) {
                $idP = (int) $p['id_pregunta_modulo'];
                $items[] = ['pregunta' => $p, 'opciones' => ModuloQuiz::opcionesPorPregunta($pdo, $idP)];
            }
            $quizDataPorModulo[$idModulo] = ['config' => $cfg, 'items' => $items];
        }
        $nLecciones = count($lecciones);

        if ($nLecciones === 0 && ($asig['estado_capacitacion'] ?? '') === 'en_progreso') {
            CapacitacionAsignada::actualizarProgresoEstado($pdo, $idAsignacion, 100, 'evaluacion_pendiente');
            $asig['estado_capacitacion'] = 'evaluacion_pendiente';
            $asig['progreso_porcentaje'] = 100;
        } else {
            [$p, $e] = $this->resolverProgresoYEstado($asig, $moduloEstado, $nLecciones);

            if ((int) ($asig['progreso_porcentaje'] ?? 0) !== $p || (string) ($asig['estado_capacitacion'] ?? '') !== $e) {
                CapacitacionAsignada::actualizarProgresoEstado($pdo, $idAsignacion, $p, $e);
            }
            $asig['progreso_porcentaje'] = $p;
            $asig['estado_capacitacion'] = $e;
        }

        $cursoEvalEstado = [
            'available' => false,
            'reason' => 'missing',
            'active' => false,
            'required' => 0,
            'items' => 0,
        ];
        try {
            $cfgCursoEval = CursoEvaluacion::getConfig($pdo, $idCurso);
            $reqCursoEval = max(1, min(10, (int) ($cfgCursoEval['preguntas_requeridas'] ?? 1)));
            $actCursoEval = (int) ($cfgCursoEval['activo'] ?? 0) === 1;
            $pregsCursoEval = CursoEvaluacion::preguntasPorCurso($pdo, $idCurso);
            $byOrdenCursoEval = [];
            foreach ($pregsCursoEval as $pCursoEval) {
                $ordenCursoEval = (int) ($pCursoEval['orden'] ?? 0);

                if ($ordenCursoEval > 0) {
                    $byOrdenCursoEval[$ordenCursoEval] = $pCursoEval;
                }
            }
            $itemsCursoEval = 0;
            for ($ordenCursoEval = 1; $ordenCursoEval <= $reqCursoEval; $ordenCursoEval++) {
                if (isset($byOrdenCursoEval[$ordenCursoEval])) {
                    $itemsCursoEval++;
                }
            }
            $cursoEvalEstado['active'] = $actCursoEval;
            $cursoEvalEstado['required'] = $reqCursoEval;
            $cursoEvalEstado['items'] = $itemsCursoEval;

            if ($actCursoEval && $itemsCursoEval >= $reqCursoEval) {
                $cursoEvalEstado['available'] = true;
                $cursoEvalEstado['reason'] = 'new_ready';
            } elseif (!$actCursoEval && count($pregsCursoEval) > 0) {
                $cursoEvalEstado['reason'] = 'new_inactive';
            } elseif ($actCursoEval && $itemsCursoEval < $reqCursoEval) {
                $cursoEvalEstado['reason'] = 'new_incomplete';
            }
        } catch (Throwable $e) {
            $cursoEvalEstado['reason'] = 'new_error';
        }

        if ($cursoEvalEstado['reason'] === 'missing' || $cursoEvalEstado['reason'] === 'new_error') {
            $legacyCursoEval = PreguntaEvaluacion::porCurso($pdo, $idCurso);

            if (count($legacyCursoEval) > 0) {
                $cursoEvalEstado['available'] = true;
                $cursoEvalEstado['reason'] = 'legacy_ready';
                $cursoEvalEstado['items'] = count($legacyCursoEval);
                $cursoEvalEstado['required'] = count($legacyCursoEval);
            }
        }
        $this->render('asesor/curso', [
            'asignacion' => $asig,
            'curso' => Curso::buscar($pdo, $idCurso),
            'insigniaCursoCompletado' => (Insignia::mapCursoCompletadoPorAsesor($pdo, $cedulaAsesor))[$idCurso] ?? null,
            'modulos' => $modulos,
            'leccionesPorModulo' => $leccionesPorModulo,
            'totalLecciones' => count($lecciones),
            'leccionSeleccionada' => $leccionSeleccionada,
            'idLeccionSeleccionada' => $idLeccionSel,
            'idModuloAcordeonAbierto' => $idModuloAcordeonAbierto,
            'leccionesCompletadasSet' => $completadasSet,
            'moduloEstado' => $moduloEstado,
            'quizDataPorModulo' => $quizDataPorModulo,
            'cursoEvalEstado' => $cursoEvalEstado,
            'mensaje' => $this->flash('ok'),
            'error' => $this->flash('error'),
        ]);
    }

    public function leccion(): void
    {
        $this->requireAuth(['asesor']);
        // Compatibilidad: antes existía una pantalla separada de lección.
        // Ahora se renderiza dentro de `asesor/curso` (col-8). Redirigimos manteniendo el contexto.
        $idAsignacion = (int) ($_GET['id_asignacion'] ?? 0);
        $idLeccion = (int) ($_GET['id_leccion'] ?? 0);

        if ($idAsignacion <= 0) {
            $this->redirect('?c=asesor&a=index');
            return;
        }
        $this->redirect('?c=asesor&a=curso&id=' . $idAsignacion . ($idLeccion > 0 ? '&id_leccion=' . $idLeccion : ''));
    }

    public function modulo_quiz(): void
    {
        $this->requireAuth(['asesor']);
        $pdo = getPDO();
        $idAsignacion = (int) ($_GET['id_asignacion'] ?? 0);
        $idModulo = (int) ($_GET['id_modulo'] ?? 0);
        $asig = $this->asegurarAsignacion($pdo, $idAsignacion);

        if ($asig === null || $idModulo <= 0) {
            $this->flash('error', 'No disponible.');

            $this->redirect('?c=asesor&a=index');
            return;
        }
        $idCurso = (int) $asig['id_curso'];
        $cfg = ModuloQuiz::getConfig($pdo, $idModulo);

        if ((int) ($cfg['activo'] ?? 0) !== 1) {
            $this->flash('error', 'Evaluación no disponible.');

            $this->redirect('?c=asesor&a=curso&id=' . $idAsignacion);
            return;
        }
        // Solo permitir quiz cuando completó todas las lecciones del módulo
        $lecs = Leccion::porModulo($pdo, $idModulo);
        $completadasSet = LeccionCompletado::idsCompletadasPorCurso($pdo, $idCurso, (string) $_SESSION['usuario_cedula']);
        $total = count($lecs);
        $done = 0;
        foreach ($lecs as $L) {
            $idL = (int) ($L['id_leccion'] ?? 0);

            if ($idL > 0 && isset($completadasSet[$idL])) {
                $done++;
            }
        }
        if ($total > 0 && $done < $total) {
            $this->flash('error', 'Debe completar todas las clases del módulo antes de presentar la evaluación.');

            $this->redirect('?c=asesor&a=curso&id=' . $idAsignacion);
            return;
        }
        $preguntas = ModuloQuiz::preguntasPorModulo($pdo, $idModulo);
        $req = (int) ($cfg['preguntas_requeridas'] ?? 1);
        $preguntas = array_values(array_filter($preguntas, static fn($p) => (int) ($p['orden'] ?? 0) >= 1 && (int) ($p['orden'] ?? 0) <= $req));
        $data = [];
        foreach ($preguntas as $p) {
            $idP = (int) $p['id_pregunta_modulo'];
            $ops = ModuloQuiz::opcionesPorPregunta($pdo, $idP);
            $data[] = ['pregunta' => $p, 'opciones' => $ops];
        }
        $this->render('asesor/modulo_quiz', [
            'asignacion' => $asig,
            'curso' => Curso::buscar($pdo, $idCurso),
            'idModulo' => $idModulo,
            'items' => $data,
            'mensaje' => $this->flash('ok'),
            'error' => $this->flash('error'),
        ]);
    }

    public function modulo_quiz_enviar(): void
    {
        $this->requireAuth(['asesor']);

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('?c=asesor&a=index');
            return;
        }
        $pdo = getPDO();
        $idAsignacion = (int) ($_POST['id_asignacion'] ?? 0);
        $idModulo = (int) ($_POST['id_modulo'] ?? 0);
        $asig = $this->asegurarAsignacion($pdo, $idAsignacion);

        if ($asig === null || $idModulo <= 0) {
            $this->flash('error', 'No disponible.');

            $this->redirect('?c=asesor&a=index');
            return;
        }
        // Solo permitir quiz cuando completó todas las lecciones del módulo
        $idCurso = (int) $asig['id_curso'];
        $lecs = Leccion::porModulo($pdo, $idModulo);
        $completadasSet = LeccionCompletado::idsCompletadasPorCurso($pdo, $idCurso, (string) $_SESSION['usuario_cedula']);
        $total = count($lecs);
        $done = 0;
        foreach ($lecs as $L) {
            $idL = (int) ($L['id_leccion'] ?? 0);

            if ($idL > 0 && isset($completadasSet[$idL])) {
                $done++;
            }
        }
        if ($total > 0 && $done < $total) {
            $this->flash('error', 'Debe completar todas las clases del módulo antes de presentar la evaluación.');

            $this->redirect('?c=asesor&a=curso&id=' . $idAsignacion);
            return;
        }
        $cfg = ModuloQuiz::getConfig($pdo, $idModulo);

        if ((int) ($cfg['activo'] ?? 0) !== 1) {
            $this->flash('error', 'Evaluación no disponible.');

            $this->redirect('?c=asesor&a=curso&id=' . $idAsignacion);
            return;
        }
        $req = (int) ($cfg['preguntas_requeridas'] ?? 1);
        $preguntas = ModuloQuiz::preguntasPorModulo($pdo, $idModulo);
        $preguntas = array_values(array_filter($preguntas, static fn($p) => (int) ($p['orden'] ?? 0) >= 1 && (int) ($p['orden'] ?? 0) <= $req));

        if (count($preguntas) !== $req) {
            $this->flash('error', 'El coordinador aún no configuró la evaluación completa.');

            $this->redirect('?c=asesor&a=curso&id=' . $idAsignacion);
            return;
        }

        $respuestas = [];
        $correctas = 0;
        foreach ($preguntas as $p) {
            $idP = (int) $p['id_pregunta_modulo'];
            $key = 'p_' . $idP;
            $elegida = (int) ($_POST[$key] ?? 0);

            if ($elegida <= 0) {
            $this->flash('error', 'Responda todas las preguntas.');

                $this->redirect('?c=asesor&a=modulo_quiz&id_asignacion=' . $idAsignacion . '&id_modulo=' . $idModulo);
                return;
            }
            $corr = ModuloQuiz::getOpcionCorrecta($pdo, $idP);

            if ($corr !== null && $elegida === $corr) {
                $correctas++;
            }
            $respuestas[] = ['pregunta' => $idP, 'opcion' => $elegida];
        }

        $aprobado = $correctas === $req;
        $idIntento = ModuloIntento::registrarIntento($pdo, $idModulo, (string) $_SESSION['usuario_cedula'], $req, $correctas, $aprobado);

        ModuloIntento::guardarRespuestas($pdo, $idIntento, $respuestas);

        if ($aprobado) {
            ModuloCompletado::marcar($pdo, $idModulo, (string) $_SESSION['usuario_cedula']);

            $this->flash('ok', 'Módulo aprobado.');
        } else {
            $this->flash('error', 'Módulo no aprobado. Intente de nuevo.');
        }
        $cedula = (string) $_SESSION['usuario_cedula'];
        $modulos = ModuloCurso::porCurso($pdo, $idCurso);
        $leccionesPorModulo = [];
        foreach ($modulos as $m) {
            $idM = (int) ($m['id_modulo'] ?? 0);

            if ($idM > 0) {
                $leccionesPorModulo[$idM] = Leccion::porModulo($pdo, $idM);
            }
        }
        $moduloEstado = $this->buildModuloEstadoAsesor($pdo, $idCurso, $cedula, $modulos, $leccionesPorModulo);
        $nLecciones = count(Leccion::porCurso($pdo, $idCurso));
        [$p, $e] = $this->resolverProgresoYEstado($asig, $moduloEstado, $nLecciones);

        CapacitacionAsignada::actualizarProgresoEstado($pdo, $idAsignacion, $p, $e);

        $this->redirect('?c=asesor&a=curso&id=' . $idAsignacion);
    }

    public function leccion_completar(): void
    {
        $this->requireAuth(['asesor']);

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('?c=asesor&a=index');
            return;
        }
        $pdo = getPDO();
        $idAsignacion = (int) ($_POST['id_asignacion'] ?? 0);
        $idLeccion = (int) ($_POST['id_leccion'] ?? 0);
        $asig = $this->asegurarAsignacion($pdo, $idAsignacion);
        $leccion = Leccion::buscar($pdo, $idLeccion);

        if ($asig === null || !$leccion || (int) $leccion['id_curso'] !== (int) $asig['id_curso']) {
            $this->flash('error', 'Acción no válida.');

            $this->redirect('?c=asesor&a=index');
            return;
        }
        $idCurso = (int) $asig['id_curso'];

        LeccionCompletado::marcar($pdo, $idLeccion, (string) $_SESSION['usuario_cedula']);
        $cedula = (string) $_SESSION['usuario_cedula'];
        $modulos = ModuloCurso::porCurso($pdo, $idCurso);
        $leccionesPorModulo = [];
        foreach ($modulos as $m) {
            $idM = (int) ($m['id_modulo'] ?? 0);

            if ($idM > 0) {
                $leccionesPorModulo[$idM] = Leccion::porModulo($pdo, $idM);
            }
        }
        $moduloEstado = $this->buildModuloEstadoAsesor($pdo, $idCurso, $cedula, $modulos, $leccionesPorModulo);
        $nLecciones = count(Leccion::porCurso($pdo, $idCurso));
        [$progreso, $estado] = $this->resolverProgresoYEstado($asig, $moduloEstado, $nLecciones);

        CapacitacionAsignada::actualizarProgresoEstado($pdo, $idAsignacion, $progreso, $estado);

        $this->flash('ok', 'Progreso actualizado.');

        $this->redirect('?c=asesor&a=curso&id=' . $idAsignacion . '&id_leccion=' . $idLeccion);
    }

    public function evaluacion(): void
    {
        $this->requireAuth(['asesor']);
        $pdo = getPDO();
        $idAsignacion = (int) ($_GET['id'] ?? 0);
        $asig = $this->asegurarAsignacion($pdo, $idAsignacion);
        if ($asig === null) {
            $this->flash('error', 'Capacitación no encontrada.');
            $this->redirect('?c=asesor&a=index');
            return;
        }
        $estado = (string) ($asig['estado_capacitacion'] ?? '');
        if ($estado !== 'evaluacion_pendiente') {
            $this->flash('error', 'Debe completar el material antes de la evaluación.');
            $this->redirect('?c=asesor&a=curso&id=' . $idAsignacion);
            return;
        }
        $idCurso = (int) $asig['id_curso'];
        try {
            $cfg = CursoEvaluacion::getConfig($pdo, $idCurso);
            $req = (int) ($cfg['preguntas_requeridas'] ?? 1);
            $req = max(1, min(10, $req));
            $act = (int) ($cfg['activo'] ?? 0) === 1;
            $pregs = CursoEvaluacion::preguntasPorCurso($pdo, $idCurso);
            $byOrden = [];
            foreach ($pregs as $p) {
                $orden = (int) ($p['orden'] ?? 0);
                if ($orden > 0) {
                    $byOrden[$orden] = $p;
                }
            }
            $items = [];
            for ($orden = 1; $orden <= $req; $orden++) {
                $p = $byOrden[$orden] ?? null;
                if (!$p) {
                    continue;
                }
                $idP = (int) ($p['id_pregunta_curso'] ?? 0);
                if ($idP <= 0) {
                    continue;
                }
                $ops = CursoEvaluacion::opcionesPorPregunta($pdo, $idP);
                $corr = CursoEvaluacion::getOpcionCorrecta($pdo, $idP);
                $items[] = ['pregunta' => $p, 'opciones' => $ops, 'correcta' => $corr];
            }
            if ($act) {
                if (count($items) >= $req) {
                    $this->render('asesor/evaluacion', [
                        'asignacion' => $asig,
                        'curso' => Curso::buscar($pdo, $idCurso),
                        'cursoEvalItems' => $items,
                        'cursoEvalReq' => $req,
                        'mensaje' => $this->flash('ok'),
                        'error' => $this->flash('error'),
                    ]);
                    return;
                }
                $this->flash('error', 'El coordinador aún no ha completado la evaluación final de este curso.');
                $this->redirect('?c=asesor&a=curso&id=' . $idAsignacion);
                return;
            }
            if (count($pregs) > 0) {
                $this->flash('error', 'La evaluación final fue cargada, pero aún no está habilitada por el coordinador.');
                $this->redirect('?c=asesor&a=curso&id=' . $idAsignacion);
                return;
            }
        } catch (Throwable $e) {
            // Continuar con evaluación legacy (preguntas_evaluacion).
        }

        $preguntas = PreguntaEvaluacion::porCurso($pdo, $idCurso);
        if (count($preguntas) === 0) {
            $this->flash('error', 'El coordinador aún no ha cargado preguntas para este curso.');
            $this->redirect('?c=asesor&a=curso&id=' . $idAsignacion);
            return;
        }
        $this->render('asesor/evaluacion', [
            'asignacion' => $asig,
            'curso' => Curso::buscar($pdo, $idCurso),
            'preguntas' => $preguntas,
            'mensaje' => $this->flash('ok'),
            'error' => $this->flash('error'),
        ]);
    }

    public function evaluacion_enviar(): void
    {
        $this->requireAuth(['asesor']);

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('?c=asesor&a=index');
            return;
        }
        $pdo = getPDO();
        $idAsignacion = (int) ($_POST['id_asignacion'] ?? 0);
        $asig = $this->asegurarAsignacion($pdo, $idAsignacion);

        if ($asig === null) {
            $this->flash('error', 'Sesión inválida.');

            $this->redirect('?c=asesor&a=index');
            return;
        }
        $estadoCap = (string) ($asig['estado_capacitacion'] ?? '');

        if ($estadoCap !== 'evaluacion_pendiente') {
            $this->flash('error', 'No puede enviar la evaluación ahora.');

            $this->redirect('?c=asesor&a=index');
            return;
        }
        $idCurso = (int) $asig['id_curso'];
        // Intentar evaluación nueva (curso_eval_*). Si no aplica, fallback legacy.
        try {
            $cfg = CursoEvaluacion::getConfig($pdo, $idCurso);
            $req = (int) ($cfg['preguntas_requeridas'] ?? 1);
            $req = max(1, min(10, $req));
            $act = (int) ($cfg['activo'] ?? 0) === 1;

            if ($act) {
                $pregs = CursoEvaluacion::preguntasPorCurso($pdo, $idCurso);
                $byOrden = [];
                foreach ($pregs as $p) {
                    $orden = (int) ($p['orden'] ?? 0);

                    if ($orden > 0) {
                        $byOrden[$orden] = $p;
                    }
                }

                $correctas = 0;
                $total = 0;
                for ($orden = 1; $orden <= $req; $orden++) {
                    $p = $byOrden[$orden] ?? null;

                    if (!$p) {
                        continue;
                    }
                    $idP = (int) ($p['id_pregunta_curso'] ?? 0);

                    if ($idP <= 0) {
                        continue;
                    }
                    $corr = CursoEvaluacion::getOpcionCorrecta($pdo, $idP);
                    $name = 'p_' . $idP;
                    $resp = (int) ($_POST[$name] ?? 0);
                    $total++;

                    if ($corr !== null && $resp === (int) $corr) {
                        $correctas++;
                    }
                }
                if ($total === 0) {
                    $this->flash('error', 'No hay preguntas.');

                    $this->redirect('?c=asesor&a=curso&id=' . $idAsignacion);
                    return;
                }

                $ratio = $correctas / $total;
                $puntaje = self::puntajeEvaluacionFinal($correctas, $total);
                $resultado = $ratio >= 0.7 ? 'aprobado' : 'reprobado';
                $cedula = (string) ($_SESSION['usuario_cedula'] ?? '');

                IntentoEvaluacion::registrar($pdo, $cedula, $idCurso, $puntaje, $resultado);

                if ($resultado === 'aprobado') {
                    if (CursoProgreso::asesorCompletoTodosModulos($pdo, $cedula, $idCurso)) {
                        CapacitacionAsignada::completarEvaluacion($pdo, $idAsignacion, $puntaje, 'completado');

                        Insignia::otorgarCursoCompletado($pdo, $cedula, $idCurso, [
                            'puntaje' => $puntaje,
                            'correctas' => $correctas,
                            'total' => $total,
                        ]);

                        $this->flash('ok', 'Evaluación aprobada. Insignia otorgada. Nota: ' . self::notaEvaluacionTexto($puntaje) . '/10.');
                    } else {
                        CapacitacionAsignada::completarEvaluacion($pdo, $idAsignacion, $puntaje, 'evaluacion_pendiente');

                        $this->flash('error', 'Evaluación aprobada, pero aún faltan módulos por completar al 100%.');
                    }
                } else {
                    CapacitacionAsignada::completarEvaluacion($pdo, $idAsignacion, $puntaje, 'evaluacion_pendiente');

                    $this->flash('error', 'No alcanzó el mínimo (70%). Puede revisar el material e intentar de nuevo.');
                }
                $this->redirect('?c=asesor&a=curso&id=' . $idAsignacion);
                return;
            }
            $pregsInactivas = CursoEvaluacion::preguntasPorCurso($pdo, $idCurso);

            if (count($pregsInactivas) > 0) {
                $this->flash('error', 'La evaluación final fue cargada, pero aún no está habilitada por el coordinador.');

                $this->redirect('?c=asesor&a=curso&id=' . $idAsignacion);
                return;
            }
        } catch (Throwable $e) {
            // continuar con legacy
        }

        $preguntas = PreguntaEvaluacion::porCurso($pdo, $idCurso);
        $correctas = 0;
        $total = count($preguntas);

        if ($total === 0) {
            $this->flash('error', 'No hay preguntas.');

            $this->redirect('?c=asesor&a=curso&id=' . $idAsignacion);
            return;
        }
        foreach ($preguntas as $p) {
            $name = 'p_' . (int) $p['id_pregunta'];
            $resp = (string) ($_POST[$name] ?? '');

            if ($resp === ($p['respuesta_correcta'] ?? '')) {
                $correctas++;
            }
        }
        $ratio = $correctas / $total;
        $puntaje = self::puntajeEvaluacionFinal($correctas, $total);
        $resultado = $ratio >= 0.7 ? 'aprobado' : 'reprobado';
        $cedula = (string) ($_SESSION['usuario_cedula'] ?? '');

        IntentoEvaluacion::registrar($pdo, $cedula, $idCurso, $puntaje, $resultado);

        if ($resultado === 'aprobado') {
            // Por seguridad, validar que el curso realmente cumple la regla de completado (módulos 100% + quiz).
            if (CursoProgreso::asesorCompletoTodosModulos($pdo, $cedula, $idCurso)) {
                CapacitacionAsignada::completarEvaluacion($pdo, $idAsignacion, $puntaje, 'completado');

                Insignia::otorgarCursoCompletado($pdo, $cedula, $idCurso, [
                    'puntaje' => $puntaje,
                    'correctas' => $correctas,
                    'total' => $total,
                ]);

                $this->flash('ok', 'Evaluación aprobada. Insignia otorgada. Nota: ' . self::notaEvaluacionTexto($puntaje) . '/10.');
            } else {
                CapacitacionAsignada::completarEvaluacion($pdo, $idAsignacion, $puntaje, 'evaluacion_pendiente');

                $this->flash('error', 'Evaluación aprobada, pero aún faltan módulos por completar al 100%.');
            }
        } else {
            CapacitacionAsignada::completarEvaluacion($pdo, $idAsignacion, $puntaje, 'evaluacion_pendiente');

            $this->flash('error', 'No alcanzó el mínimo (70%). Puede revisar el material e intentar de nuevo.');
        }
        $this->redirect('?c=asesor&a=curso&id=' . $idAsignacion);
    }
}
