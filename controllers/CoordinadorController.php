<?php

declare(strict_types=1);

class CoordinadorController extends Controller
{
    /**
     * Solo el coordinador cuya cédula coincide con cursos.cedula_coordinador
     * (asignada por el administrador) puede gestionar ese curso; el resto no lo ve ni accede por URL.
     */
    private function asegurarCurso(PDO $pdo, int $idCurso): ?array
    {
        $curso = Curso::buscar($pdo, $idCurso);
        if ($curso === null) {
            return null;
        }
        if (($curso['cedula_coordinador'] ?? '') !== ($_SESSION['usuario_cedula'] ?? '')) {
            return null;
        }
        return $curso;
    }

    private function redirectVistaCurso(int $idCurso, int $idModulo = 0, int $idLeccion = 0): void
    {
        $q = '?c=coordinador&a=curso&id=' . $idCurso;
        if ($idModulo > 0) {
            $q .= '&id_modulo=' . $idModulo;
        }
        if ($idLeccion > 0) {
            $q .= '&id_leccion=' . $idLeccion;
        }
        $this->redirect($q);
    }

    public function index(): void
    {
        $this->requireAuth(['coordinador']);
        $pdo = getPDO();
        $this->render('coordinador/index', [
            'cursos' => Curso::porCoordinador($pdo, $_SESSION['usuario_cedula']),
            'mensaje' => $this->flash('ok'),
            'error' => $this->flash('error'),
        ]);
    }

    public function asesores(): void
    {
        $this->requireAuth(['coordinador']);
        $pdo = getPDO();
        $idCurso = (int) ($_GET['id_curso'] ?? 0);
        // #region agent log
        debug_log('CoordinadorController::asesores', 'enter', ['idCurso' => $idCurso], 'run1', 'H1');
        // #endregion
        $curso = $this->asegurarCurso($pdo, $idCurso);
        if ($curso === null) {
            // #region agent log
            debug_log('CoordinadorController::asesores', 'curso_denegado', ['idCurso' => $idCurso], 'run1', 'H2');
            // #endregion
            http_response_code(403);
            echo 'No disponible.';
            return;
        }
        try {
            $data = CoordinadorReporte::asesoresPorCurso($pdo, $curso, $idCurso);
            // #region agent log
            debug_log(
                'CoordinadorController::asesores',
                'ok',
                ['idCurso' => $idCurso, 'nAsesores' => count($data['asesores'] ?? [])],
                'post-fix',
                'H3'
            );
            // #endregion
            $this->render('coordinador/asesores_modal', $data);
        } catch (Throwable $e) {
            // #region agent log
            debug_log(
                'CoordinadorController::asesores',
                'exception',
                ['idCurso' => $idCurso, 'type' => get_class($e), 'code' => (string) $e->getCode()],
                'run1',
                'H4'
            );
            // #endregion
            http_response_code(500);
            echo '<p class="muted">No se pudo cargar la lista (error del servidor).</p>';
        }
    }

    public function curso(): void
    {
        $this->requireAuth(['coordinador']);
        $pdo = getPDO();
        $id = (int) ($_GET['id'] ?? 0);
        // #region agent log
        debug_log('controllers/CoordinadorController.php:curso', 'enter', ['idCurso' => $id, 'role' => 'coordinador'], 'pre-fix', 'H1');
        // #endregion
        $curso = $this->asegurarCurso($pdo, $id);
        if ($curso === null) {
            $this->flash('error', 'Curso no disponible.');
            $this->redirect('?c=coordinador&a=index');
            return;
        }

        $modulos = ModuloCurso::porCurso($pdo, $id);
        $leccionesPorModulo = [];
        $quizPorModulo = [];
        foreach ($modulos as $m) {
            $idModulo = (int) ($m['id_modulo'] ?? 0);
            if ($idModulo > 0) {
                $leccionesPorModulo[$idModulo] = Leccion::porModulo($pdo, $idModulo);
                $cfg = ModuloQuiz::getConfig($pdo, $idModulo);
                $pregs = ModuloQuiz::preguntasPorModulo($pdo, $idModulo);
                $slots = [];
                foreach ($pregs as $p) {
                    $orden = (int) ($p['orden'] ?? 0);
                    if ($orden < 1 || $orden > 3) {
                        continue;
                    }
                    $idP = (int) $p['id_pregunta_modulo'];
                    $ops = ModuloQuiz::opcionesPorPregunta($pdo, $idP);
                    $corr = ModuloQuiz::getOpcionCorrecta($pdo, $idP);
                    $slots[$orden] = ['pregunta' => $p, 'opciones' => $ops, 'correcta' => $corr];
                }
                $quizPorModulo[$idModulo] = ['config' => $cfg, 'slots' => $slots];
            }
        }

        $idLeccionCtx = (int) ($_GET['id_leccion'] ?? 0);
        $idModuloCtx = (int) ($_GET['id_modulo'] ?? 0);
        $idModuloAbierto = 0;
        $idLeccionResaltada = 0;
        if ($idLeccionCtx > 0) {
            $lecCtx = Leccion::buscar($pdo, $idLeccionCtx);
            if ($lecCtx && (int) ($lecCtx['id_curso'] ?? 0) === $id) {
                $idModuloAbierto = (int) ($lecCtx['id_modulo'] ?? 0);
                $idLeccionResaltada = $idLeccionCtx;
            }
        }
        if ($idModuloAbierto <= 0 && $idModuloCtx > 0) {
            foreach ($modulos as $mx) {
                if ((int) ($mx['id_modulo'] ?? 0) === $idModuloCtx) {
                    $idModuloAbierto = $idModuloCtx;
                    break;
                }
            }
        }
        if ($idModuloAbierto <= 0 && !empty($modulos)) {
            $idModuloAbierto = (int) ($modulos[0]['id_modulo'] ?? 0);
        }

        // #region agent log
        debug_log(
            'controllers/CoordinadorController.php:curso',
            'loaded',
            [
                'idCurso' => $id,
                'modules' => count($modulos),
                'lessonsModuleKeys' => count($leccionesPorModulo),
                'idModuloAbierto' => $idModuloAbierto,
                'idLeccionResaltada' => $idLeccionResaltada,
            ],
            'pre-fix',
            'H1'
        );
        // #endregion
        $this->render('coordinador/curso', [
            'curso' => $curso,
            'modulos' => $modulos,
            'leccionesPorModulo' => $leccionesPorModulo,
            'quizPorModulo' => $quizPorModulo,
            'idModuloAbierto' => $idModuloAbierto,
            'idLeccionResaltada' => $idLeccionResaltada,
            'mensaje' => $this->flash('ok'),
            'error' => $this->flash('error'),
        ]);
    }

    public function modulo_crear(): void
    {
        $this->requireAuth(['coordinador']);
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('?c=coordinador&a=index');
            return;
        }
        $pdo = getPDO();
        $idCurso = (int) ($_POST['id_curso'] ?? 0);
        // #region agent log
        debug_log('controllers/CoordinadorController.php:modulo_crear', 'enter', ['idCurso' => $idCurso], 'pre-fix', 'H2');
        // #endregion
        if ($this->asegurarCurso($pdo, $idCurso) === null) {
            $this->flash('error', 'Curso no permitido.');
            $this->redirect('?c=coordinador&a=index');
            return;
        }

        $titulo = trim((string) ($_POST['titulo_modulo'] ?? ''));
        if ($titulo === '') {
            $this->flash('error', 'El título del módulo es obligatorio.');
            $this->redirectVistaCurso($idCurso);
            return;
        }
        if (strlen($titulo) > 150) {
            $this->flash('error', 'El título es demasiado largo (máx. 150).');
            $this->redirectVistaCurso($idCurso);
            return;
        }

        try {
            ModuloCurso::crear($pdo, $idCurso, $titulo);
            $this->flash('ok', 'Módulo creado.');
            // #region agent log
            debug_log('controllers/CoordinadorController.php:modulo_crear', 'ok', ['idCurso' => $idCurso], 'pre-fix', 'H2');
            // #endregion
        } catch (Throwable $e) {
            $this->flash('error', 'No se pudo crear el módulo.');
            // #region agent log
            debug_log('controllers/CoordinadorController.php:modulo_crear', 'err', ['idCurso' => $idCurso, 'type' => get_class($e)], 'pre-fix', 'H2');
            // #endregion
        }
        $this->redirectVistaCurso($idCurso);
    }

    public function modulo_eliminar(): void
    {
        $this->requireAuth(['coordinador']);
        $pdo = getPDO();
        $idCurso = (int) ($_GET['id_curso'] ?? 0);
        if ($this->asegurarCurso($pdo, $idCurso) === null) {
            $this->flash('error', 'Curso no permitido.');
            $this->redirect('?c=coordinador&a=index');
            return;
        }
        $idModulo = (int) ($_GET['id_modulo'] ?? 0);
        if ($idModulo <= 0) {
            $this->flash('error', 'Módulo inválido.');
            $this->redirectVistaCurso($idCurso);
            return;
        }
        $row = ModuloCurso::eliminar($pdo, $idModulo, $idCurso);
        $this->flash($row ? 'ok' : 'error', $row ? 'Módulo eliminado.' : 'No se pudo eliminar.');
        $this->redirectVistaCurso($idCurso);
    }

    /** @return array{ok:bool,path?:string,error?:string} */
    private function guardarArchivoLeccionOpcional(int $idCurso): array
    {
        if (empty($_FILES['archivo']) || $_FILES['archivo']['error'] === UPLOAD_ERR_NO_FILE) {
            return ['ok' => true, 'path' => null];
        }
        return $this->guardarArchivoCurso($idCurso);
    }

    /** @return array{ok:bool,path?:string,error?:string} */
    private function guardarMediaLeccionOpcional(int $idCurso, string $inputName, array $extsPermitidas, int $maxBytes): array
    {
        if (empty($_FILES[$inputName]) || $_FILES[$inputName]['error'] === UPLOAD_ERR_NO_FILE) {
            return ['ok' => true, 'path' => null];
        }
        if ($_FILES[$inputName]['error'] !== UPLOAD_ERR_OK) {
            return ['ok' => false, 'error' => 'Error al subir el archivo.'];
        }
        if ((int) ($_FILES[$inputName]['size'] ?? 0) > $maxBytes) {
            return ['ok' => false, 'error' => 'El archivo supera el tamaño permitido.'];
        }
        $ext = strtolower(pathinfo((string) ($_FILES[$inputName]['name'] ?? ''), PATHINFO_EXTENSION));
        if (!in_array($ext, $extsPermitidas, true)) {
            return ['ok' => false, 'error' => 'Tipo de archivo no permitido.'];
        }

        $dirBase = BASE_PATH . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'lecciones';
        $dir = $dirBase . DIRECTORY_SEPARATOR . $idCurso;
        if (!is_dir($dir) && !mkdir($dir, 0755, true) && !is_dir($dir)) {
            return ['ok' => false, 'error' => 'No se pudo crear la carpeta de uploads.'];
        }

        $safe = self::sanitizeFilename((string) $_FILES[$inputName]['name']);
        $dest = $dir . DIRECTORY_SEPARATOR . time() . '_' . $safe;
        if (!move_uploaded_file($_FILES[$inputName]['tmp_name'], $dest)) {
            return ['ok' => false, 'error' => 'No se pudo guardar el archivo.'];
        }
        return ['ok' => true, 'path' => 'uploads/lecciones/' . $idCurso . '/' . basename($dest)];
    }

    public function leccion_crear(): void
    {
        $this->requireAuth(['coordinador']);
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('?c=coordinador&a=index');
            return;
        }
        $pdo = getPDO();
        $idCurso = (int) ($_POST['id_curso'] ?? 0);
        $idModulo = (int) ($_POST['id_modulo'] ?? 0);
        // #region agent log
        debug_log('controllers/CoordinadorController.php:leccion_crear', 'enter', ['idCurso' => $idCurso, 'idModulo' => $idModulo], 'pre-fix', 'H3');
        // #endregion
        if ($this->asegurarCurso($pdo, $idCurso) === null) {
            $this->flash('error', 'Curso no permitido.');
            $this->redirect('?c=coordinador&a=index');
            return;
        }

        if ($idModulo <= 0) {
            $this->flash('error', 'Módulo inválido.');
            $this->redirectVistaCurso($idCurso);
            return;
        }

        $titulo = trim((string) ($_POST['titulo_leccion'] ?? ''));
        $contenido = trim((string) ($_POST['contenido'] ?? ''));
        if ($titulo === '') {
            $this->flash('error', 'El título (ej. 1.1 …) es obligatorio.');
            $this->redirectVistaCurso($idCurso, $idModulo);
            return;
        }

        // Crear “curso/clase” como borrador: aquí solo se exige el título; la configuración se hace en el panel derecho.
        $imagenPath = null;
        $imagenTexto = null;
        $videoPath = null;

        $orden = Leccion::siguienteOrdenPorModulo($pdo, $idModulo);
        $idNueva = Leccion::crear($pdo, $idCurso, $idModulo, $titulo, $contenido, $imagenPath, $imagenTexto, $videoPath, $orden, 0);
        $this->flash('ok', 'Clase/curso agregado al módulo.');
        // #region agent log
        debug_log('controllers/CoordinadorController.php:leccion_crear', 'ok', ['idCurso' => $idCurso, 'idModulo' => $idModulo, 'orden' => $orden], 'pre-fix', 'H3');
        // #endregion
        $this->redirectVistaCurso($idCurso, $idModulo, $idNueva);
    }

    public function leccion_actualizar(): void
    {
        $this->requireAuth(['coordinador']);
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('?c=coordinador&a=index');
            return;
        }
        $pdo = getPDO();
        $idCurso = (int) ($_POST['id_curso'] ?? 0);
        if ($this->asegurarCurso($pdo, $idCurso) === null) {
            $this->flash('error', 'Curso no permitido.');
            $this->redirect('?c=coordinador&a=index');
            return;
        }
        $idModulo = (int) ($_POST['id_modulo'] ?? 0);
        $idLeccion = (int) ($_POST['id_leccion'] ?? 0);
        $titulo = trim((string) ($_POST['titulo_leccion'] ?? ''));
        $contenido = trim((string) ($_POST['contenido'] ?? ''));
        $imagenTexto = trim((string) ($_POST['imagen_texto'] ?? ''));
        if ($idModulo <= 0 || $idLeccion <= 0) {
            $this->flash('error', 'Datos inválidos.');
            $this->redirectVistaCurso($idCurso);
            return;
        }
        if ($titulo === '') {
            $this->flash('error', 'El título es obligatorio.');
            $this->redirectVistaCurso($idCurso, $idModulo, $idLeccion);
            return;
        }

        if ($contenido === '') {
            $this->flash('error', 'El cuadro de texto (descripción) es obligatorio.');
            $this->redirectVistaCurso($idCurso, $idModulo, $idLeccion);
            return;
        }

        try {
            $leccion = Leccion::buscar($pdo, $idLeccion);
            if (!$leccion || (int) $leccion['id_curso'] !== $idCurso || (int) $leccion['id_modulo'] !== $idModulo) {
                $this->flash('error', 'No se pudo actualizar.');
                $this->redirectVistaCurso($idCurso, $idModulo, $idLeccion);
                return;
            }

            $imgUp = $this->guardarMediaLeccionOpcional($idCurso, 'imagen', ['jpg', 'jpeg', 'png'], 8 * 1024 * 1024);
            if (!$imgUp['ok']) {
                $this->flash('error', $imgUp['error'] ?? 'Error al subir imagen.');
                $this->redirectVistaCurso($idCurso, $idModulo, $idLeccion);
                return;
            }
            $vidUp = $this->guardarMediaLeccionOpcional($idCurso, 'video', ['mp4'], 80 * 1024 * 1024);
            if (!$vidUp['ok']) {
                $this->flash('error', $vidUp['error'] ?? 'Error al subir video.');
                $this->redirectVistaCurso($idCurso, $idModulo, $idLeccion);
                return;
            }

            $imagenPath = ($imgUp['path'] ?? null) ?: ((string) ($leccion['imagen_path'] ?? '') !== '' ? (string) $leccion['imagen_path'] : null);
            $imagenTextoFinal = $imagenTexto !== '' ? $imagenTexto : ((string) ($leccion['imagen_texto'] ?? '') !== '' ? (string) $leccion['imagen_texto'] : null);
            $videoPath = ($vidUp['path'] ?? null) ?: ((string) ($leccion['video_path'] ?? '') !== '' ? (string) $leccion['video_path'] : null);

            $ok = Leccion::actualizar($pdo, $idLeccion, $idCurso, $idModulo, $titulo, $contenido, $imagenPath, $imagenTextoFinal, $videoPath);
            $this->flash($ok ? 'ok' : 'error', $ok ? 'Clase actualizada.' : 'No se pudo actualizar.');
        } catch (Throwable $e) {
            $this->flash('error', 'No se pudo actualizar.');
        }
        $this->redirectVistaCurso($idCurso, $idModulo, $idLeccion);
    }

    private function guardarImagenQuiz(int $idCurso, string $inputName): array
    {
        if (empty($_FILES[$inputName]) || $_FILES[$inputName]['error'] === UPLOAD_ERR_NO_FILE) {
            return ['ok' => true, 'path' => null];
        }
        if ($_FILES[$inputName]['error'] !== UPLOAD_ERR_OK) {
            return ['ok' => false, 'error' => 'Error al subir la imagen.'];
        }
        if ($_FILES[$inputName]['size'] > UPLOAD_MAX_BYTES) {
            return ['ok' => false, 'error' => 'La imagen supera el tamaño permitido.'];
        }

        $ext = strtolower(pathinfo($_FILES[$inputName]['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, ['jpg', 'jpeg', 'png'], true)) {
            return ['ok' => false, 'error' => 'Imagen no permitida (solo jpg/png).'];
        }

        $dirBase = BASE_PATH . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'quiz_modulo';
        $dir = $dirBase . DIRECTORY_SEPARATOR . $idCurso;
        if (!is_dir($dir) && !mkdir($dir, 0755, true) && !is_dir($dir)) {
            return ['ok' => false, 'error' => 'No se pudo crear la carpeta de quiz.'];
        }

        $safe = self::sanitizeFilename($_FILES[$inputName]['name']);
        $dest = $dir . DIRECTORY_SEPARATOR . time() . '_' . $safe;
        if (!move_uploaded_file($_FILES[$inputName]['tmp_name'], $dest)) {
            return ['ok' => false, 'error' => 'No se pudo guardar la imagen.'];
        }
        return ['ok' => true, 'path' => 'uploads/quiz_modulo/' . $idCurso . '/' . basename($dest)];
    }

    public function modulo_quiz_guardar(): void
    {
        $this->requireAuth(['coordinador']);
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('?c=coordinador&a=index');
            return;
        }
        $pdo = getPDO();
        $idCurso = (int) ($_POST['id_curso'] ?? 0);
        if ($this->asegurarCurso($pdo, $idCurso) === null) {
            $this->flash('error', 'Curso no permitido.');
            $this->redirect('?c=coordinador&a=index');
            return;
        }
        $idModulo = (int) ($_POST['id_modulo'] ?? 0);
        if ($idModulo <= 0) {
            $this->flash('error', 'Módulo inválido.');
            $this->redirectVistaCurso($idCurso);
            return;
        }

        $req = (int) ($_POST['preguntas_requeridas'] ?? 1);
        $activo = !empty($_POST['quiz_activo']) ? 1 : 0;
        ModuloQuiz::upsertConfig($pdo, $idModulo, $req, $activo);

        // Guardar preguntas 1..3
        for ($orden = 1; $orden <= 3; $orden++) {
            $tipo = (string) ($_POST['q_tipo'][$orden] ?? '');
            $enun = (string) ($_POST['q_enunciado'][$orden] ?? '');
            if (!in_array($tipo, ['imagen_par', 'vf', 'multi'], true)) {
                continue;
            }

            $idPregunta = ModuloQuiz::setPregunta($pdo, $idModulo, $orden, $tipo, $enun);

            if ($tipo === 'vf') {
                ModuloQuiz::replaceOpciones($pdo, $idPregunta, [
                    ['clave' => 'true', 'texto' => 'Verdadero'],
                    ['clave' => 'false', 'texto' => 'Falso'],
                ]);
                $ops = ModuloQuiz::opcionesPorPregunta($pdo, $idPregunta);
                $map = [];
                foreach ($ops as $o) {
                    $map[(string) $o['clave']] = (int) $o['id_opcion'];
                }
                $corr = (string) ($_POST['q_vf_correcta'][$orden] ?? 'true');
                ModuloQuiz::setRespuestaCorrecta($pdo, $idPregunta, $map[$corr] ?? $map['true']);
            } elseif ($tipo === 'multi') {
                $a = (string) ($_POST['q_multi_a'][$orden] ?? '');
                $b = (string) ($_POST['q_multi_b'][$orden] ?? '');
                $c = (string) ($_POST['q_multi_c'][$orden] ?? '');
                $d = (string) ($_POST['q_multi_d'][$orden] ?? '');
                ModuloQuiz::replaceOpciones($pdo, $idPregunta, [
                    ['clave' => 'a', 'texto' => $a],
                    ['clave' => 'b', 'texto' => $b],
                    ['clave' => 'c', 'texto' => $c],
                    ['clave' => 'd', 'texto' => $d],
                ]);
                $ops = ModuloQuiz::opcionesPorPregunta($pdo, $idPregunta);
                $map = [];
                foreach ($ops as $o) {
                    $map[(string) $o['clave']] = (int) $o['id_opcion'];
                }
                $corr = (string) ($_POST['q_multi_correcta'][$orden] ?? 'a');
                ModuloQuiz::setRespuestaCorrecta($pdo, $idPregunta, $map[$corr] ?? $map['a']);
            } else { // imagen_par
                $okImg = $this->guardarImagenQuiz($idCurso, 'q_img_ok_' . $orden);
                $badImg = $this->guardarImagenQuiz($idCurso, 'q_img_bad_' . $orden);
                if (!$okImg['ok'] || !$badImg['ok']) {
                    $this->flash('error', $okImg['error'] ?? $badImg['error'] ?? 'No se pudo guardar imágenes.');
                    $this->redirectVistaCurso($idCurso, $idModulo);
                    return;
                }
                ModuloQuiz::replaceOpciones($pdo, $idPregunta, [
                    ['clave' => 'ok', 'imagen_path' => $okImg['path'] ?? null],
                    ['clave' => 'bad', 'imagen_path' => $badImg['path'] ?? null],
                ]);
                $ops = ModuloQuiz::opcionesPorPregunta($pdo, $idPregunta);
                $map = [];
                foreach ($ops as $o) {
                    $map[(string) $o['clave']] = (int) $o['id_opcion'];
                }
                ModuloQuiz::setRespuestaCorrecta($pdo, $idPregunta, $map['ok']);
            }
        }

        $this->flash('ok', 'Evaluación del módulo guardada.');
        $this->redirectVistaCurso($idCurso, $idModulo);
    }

    public function leccion_eliminar(): void
    {
        $this->requireAuth(['coordinador']);
        $pdo = getPDO();
        $idLeccion = (int) ($_GET['id_leccion'] ?? 0);
        $idCurso = (int) ($_GET['id_curso'] ?? 0);
        $leccion = Leccion::buscar($pdo, $idLeccion);
        if (!$leccion || (int) $leccion['id_curso'] !== $idCurso || $this->asegurarCurso($pdo, $idCurso) === null) {
            $this->flash('error', 'No se puede eliminar.');
            $this->redirectVistaCurso($idCurso);
            return;
        }
        $idModuloDel = (int) ($leccion['id_modulo'] ?? 0);
        $ruta = (string) ($leccion['ruta_video'] ?? '');
        if ($ruta !== '' && (str_starts_with($ruta, 'uploads/cursos/') || str_starts_with($ruta, 'uploads/coordinador/videos/'))) {
            $full = BASE_PATH . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $ruta);
            if (is_file($full)) {
                @unlink($full);
            }
        }
        $img = (string) ($leccion['imagen_path'] ?? '');
        if ($img !== '' && str_starts_with($img, 'uploads/lecciones/')) {
            $full = BASE_PATH . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $img);
            if (is_file($full)) {
                @unlink($full);
            }
        }
        $vid = (string) ($leccion['video_path'] ?? '');
        if ($vid !== '' && str_starts_with($vid, 'uploads/lecciones/')) {
            $full = BASE_PATH . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $vid);
            if (is_file($full)) {
                @unlink($full);
            }
        }
        Leccion::eliminar($pdo, $idLeccion);
        $this->flash('ok', 'Clase eliminada.');
        $this->redirectVistaCurso($idCurso, $idModuloDel);
    }

    public function preguntas(): void
    {
        $this->requireAuth(['coordinador']);
        $pdo = getPDO();
        $id = (int) ($_GET['id'] ?? 0);
        if ($this->asegurarCurso($pdo, $id) === null) {
            $this->flash('error', 'Curso no disponible.');
            $this->redirect('?c=coordinador&a=index');
            return;
        }
        $this->render('coordinador/preguntas', [
            'curso' => Curso::buscar($pdo, $id),
            'preguntas' => PreguntaEvaluacion::porCurso($pdo, $id),
            'mensaje' => $this->flash('ok'),
            'error' => $this->flash('error'),
        ]);
    }

    public function pregunta_guardar(): void
    {
        $this->requireAuth(['coordinador']);
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('?c=coordinador&a=index');
            return;
        }
        $pdo = getPDO();
        $idCurso = (int) ($_POST['id_curso'] ?? 0);
        if ($this->asegurarCurso($pdo, $idCurso) === null) {
            $this->flash('error', 'Curso no permitido.');
            $this->redirect('?c=coordinador&a=index');
            return;
        }
        $enunciado = trim((string) ($_POST['enunciado'] ?? ''));
        $a = trim((string) ($_POST['opcion_a'] ?? ''));
        $b = trim((string) ($_POST['opcion_b'] ?? ''));
        $c = trim((string) ($_POST['opcion_c'] ?? ''));
        $d = trim((string) ($_POST['opcion_d'] ?? ''));
        $ok = (string) ($_POST['respuesta_correcta'] ?? 'a');
        if (!in_array($ok, ['a', 'b', 'c', 'd'], true)) {
            $ok = 'a';
        }
        if ($enunciado === '' || $a === '' || $b === '' || $c === '' || $d === '') {
            $this->flash('error', 'Complete todas las opciones y el enunciado.');
            $this->redirect('?c=coordinador&a=preguntas&id=' . $idCurso);
            return;
        }
        PreguntaEvaluacion::crear($pdo, $idCurso, $enunciado, $a, $b, $c, $d, $ok);
        $this->flash('ok', 'Pregunta agregada.');
        $this->redirect('?c=coordinador&a=preguntas&id=' . $idCurso);
    }

    public function pregunta_eliminar(): void
    {
        $this->requireAuth(['coordinador']);
        $pdo = getPDO();
        $idP = (int) ($_GET['id_pregunta'] ?? 0);
        $idCurso = (int) ($_GET['id_curso'] ?? 0);
        $p = PreguntaEvaluacion::buscar($pdo, $idP);
        if (!$p || (int) $p['id_curso'] !== $idCurso || $this->asegurarCurso($pdo, $idCurso) === null) {
            $this->flash('error', 'No se puede eliminar.');
            $this->redirect('?c=coordinador&a=index');
            return;
        }
        PreguntaEvaluacion::eliminar($pdo, $idP);
        $this->flash('ok', 'Pregunta eliminada.');
        $this->redirect('?c=coordinador&a=preguntas&id=' . $idCurso);
    }
}
