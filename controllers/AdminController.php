<?php

declare(strict_types=1);

class AdminController extends Controller
{
    public function index(): void
    {
        $this->requireAuth(['administrador']);
        $pdo = getPDO();
        $this->render('admin/index', [
            'mensaje' => $this->flash('ok'),
            'totalCursos' => count(Curso::todos($pdo)),
            'totalAsignaciones' => count(CapacitacionAsignada::todasConDetalle($pdo)),
        ]);
    }

    public function cursos(): void
    {
        $this->requireAuth(['administrador']);
        $pdo = getPDO();
        $this->render('admin/cursos', [
            'cursos' => Curso::todos($pdo),
            'mensaje' => $this->flash('ok'),
            'error' => $this->flash('error'),
        ]);
    }

    public function curso_form(): void
    {
        $this->requireAuth(['administrador']);
        $pdo = getPDO();
        $id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
        $curso = $id > 0 ? Curso::buscar($pdo, $id) : null;
        if ($id > 0 && $curso === null) {
            $this->flash('error', 'Curso no encontrado.');
            $this->redirect('?c=admin&a=cursos');
            return;
        }
        $this->render('admin/curso_form', [
            'curso' => $curso,
            'coordinadores' => Usuario::listarPorRol($pdo, 'coordinador'),
            'error' => $this->flash('error'),
        ]);
    }

    public function curso_guardar(): void
    {
        $this->requireAuth(['administrador']);
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('?c=admin&a=cursos');
            return;
        }
        $pdo = getPDO();
        $id = isset($_POST['id_cursos']) ? (int) $_POST['id_cursos'] : 0;
        $nombre = trim((string) ($_POST['nombre_curso'] ?? ''));
        $descripcion = trim((string) ($_POST['descripcion'] ?? '')) ?: null;
        $estado = ($_POST['estado'] ?? 'activo') === 'inactivo' ? 'inactivo' : 'activo';
        $coordRaw = trim((string) ($_POST['cedula_coordinador'] ?? ''));
        $cedulaCoord = $coordRaw === '' ? null : $coordRaw;

        if ($nombre === '') {
            $this->flash('error', 'El nombre del curso es obligatorio.');
            $this->redirect($id > 0 ? '?c=admin&a=curso_form&id=' . $id : '?c=admin&a=curso_form');
            return;
        }

        if ($cedulaCoord !== null && !Usuario::esCoordinadorActivo($pdo, $cedulaCoord)) {
            $this->flash('error', 'La cédula indicada no es un coordinador activo. Revise el rol del usuario en “Usuarios”.');
            $this->redirect($id > 0 ? '?c=admin&a=curso_form&id=' . $id : '?c=admin&a=curso_form');
            return;
        }

        try {
            if ($id > 0) {
                Curso::actualizar($pdo, $id, $nombre, $descripcion, $estado, $cedulaCoord);
                $this->flash('ok', 'Curso actualizado.');
            } else {
                Curso::crear($pdo, $nombre, $descripcion, $estado, $cedulaCoord);
                $this->flash('ok', 'Curso creado.');
            }
        } catch (Throwable $e) {
            $msg = $e->getMessage();
            if ($e instanceof PDOException && str_contains($msg, 'cedula_coordinador')) {
                $this->flash(
                    'error',
                    'Falta la columna cedula_coordinador en la tabla cursos. Ejecute el script database/migration_add_coordinador.sql en su base capacitacion1.'
                );
            } else {
                $this->flash('error', 'No se pudo guardar el curso. Revise restricciones de la base de datos o datos enviados.');
            }
        }
        $this->redirect('?c=admin&a=cursos');
    }

    public function asignaciones(): void
    {
        $this->requireAuth(['administrador']);
        $pdo = getPDO();
        $this->render('admin/asignaciones', [
            'cursos' => Curso::todos($pdo),
            'asesores' => Usuario::listarPorRol($pdo, 'asesor'),
            'asignaciones' => CapacitacionAsignada::todasConDetalle($pdo),
            'mensaje' => $this->flash('ok'),
            'error' => $this->flash('error'),
        ]);
    }

    public function asignacion_guardar(): void
    {
        $this->requireAuth(['administrador']);
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('?c=admin&a=asignaciones');
            return;
        }
        $pdo = getPDO();
        $cedula = trim((string) ($_POST['cedula_asesor'] ?? ''));
        $idCurso = (int) ($_POST['id_curso'] ?? 0);
        if ($cedula === '' || $idCurso <= 0) {
            $this->flash('error', 'Seleccione asesor y curso.');
            $this->redirect('?c=admin&a=asignaciones');
            return;
        }
        $existe = CapacitacionAsignada::buscarPorAsesorCurso($pdo, $cedula, $idCurso);
        if ($existe) {
            $this->flash('error', 'Este asesor ya tiene asignado ese curso.');
            $this->redirect('?c=admin&a=asignaciones');
            return;
        }
        try {
            CapacitacionAsignada::crear($pdo, $cedula, $idCurso);
            $this->flash('ok', 'Asignación registrada.');
        } catch (Throwable $e) {
            $this->flash('error', 'No se pudo asignar.');
        }
        $this->redirect('?c=admin&a=asignaciones');
    }

    public function progreso(): void
    {
        $this->requireAuth(['administrador']);
        $pdo = getPDO();
        $this->render('admin/progreso', ['filas' => Reporte::progresoAsesores($pdo)]);
    }

    public function atrasados(): void
    {
        $this->requireAuth(['administrador']);
        $pdo = getPDO();
        $this->render('admin/atrasados', ['filas' => Reporte::asesoresAtrasados($pdo)]);
    }

    public function creacion_usuarios(): void
    {
        $this->requireAuth(['administrador']);
        $pdo = getPDO();

        $cedula = trim((string) ($_GET['cedula'] ?? ''));
        $usuarioEdit = null;
        if ($cedula !== '') {
            $usuarioEdit = Usuario::buscarPorCedula($pdo, $cedula);
            if ($usuarioEdit === null) {
                $this->flash('error', 'Usuario no encontrado.');
                $this->redirect('?c=admin&a=creacion_usuarios');
                return;
            }
        }

        $this->render('admin/creacion_usuarios', [
            'usuarios' => Usuario::listarTodos($pdo),
            'usuarioEdit' => $usuarioEdit,
            'mensaje' => $this->flash('ok'),
            'error' => $this->flash('error'),
        ]);
    }

    public function usuarios_guardar(): void
    {
        $this->requireAuth(['administrador']);

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('?c=admin&a=creacion_usuarios');
            return;
        }

        $pdo = getPDO();

        $cedula = trim((string) ($_POST['cedula'] ?? ''));
        $nombre = trim((string) ($_POST['nombre'] ?? ''));
        $usuarioLogin = trim((string) ($_POST['usuario'] ?? ''));
        $claveRaw = (string) ($_POST['clave'] ?? '');
        $claveConfirmar = (string) ($_POST['clave_confirmar'] ?? '');
        $rol = trim((string) ($_POST['rol'] ?? 'asesor'));
        $email = trim((string) ($_POST['email'] ?? ''));
        $estado = trim((string) ($_POST['estado'] ?? 'activo'));

        $rolesPermitidos = ['administrador', 'coordinador', 'asesor'];
        $estadosPermitidos = ['activo', 'inactivo'];

        if ($cedula === '' || $nombre === '' || $usuarioLogin === '' || $email === '') {
            $this->flash('error', 'Complete cédula, nombre, usuario y email.');
            $this->redirect('?c=admin&a=creacion_usuarios&cedula=' . rawurlencode($cedula));
            return;
        }
        if (!in_array($rol, $rolesPermitidos, true)) {
            $this->flash('error', 'Rol no válido.');
            $this->redirect('?c=admin&a=creacion_usuarios&cedula=' . rawurlencode($cedula));
            return;
        }
        if (!in_array($estado, $estadosPermitidos, true)) {
            $this->flash('error', 'Estado no válido.');
            $this->redirect('?c=admin&a=creacion_usuarios&cedula=' . rawurlencode($cedula));
            return;
        }
        if (strlen($cedula) > 10) {
            $this->flash('error', 'La cédula no debe superar 10 caracteres.');
            $this->redirect('?c=admin&a=creacion_usuarios&cedula=' . rawurlencode($cedula));
            return;
        }

        $existe = Usuario::buscarPorCedula($pdo, $cedula);
        $claveHash = null;

        if ($existe === null) {
            if ($claveRaw === '') {
                $this->flash('error', 'La clave es obligatoria al crear un usuario.');
                $this->redirect('?c=admin&a=creacion_usuarios');
                return;
            }
            if ($claveConfirmar === '' || $claveRaw !== $claveConfirmar) {
                $this->flash('error', 'La clave y su confirmación no coinciden.');
                $this->redirect('?c=admin&a=creacion_usuarios&cedula=' . rawurlencode($cedula));
                return;
            }
            $claveHash = password_hash($claveRaw, PASSWORD_DEFAULT);
        } else {
            if ($claveRaw !== '') {
                if ($claveConfirmar !== '' && $claveRaw !== $claveConfirmar) {
                    $this->flash('error', 'La clave y su confirmación no coinciden.');
                    $this->redirect('?c=admin&a=creacion_usuarios&cedula=' . rawurlencode($cedula));
                    return;
                }
                $claveHash = password_hash($claveRaw, PASSWORD_DEFAULT);
            }
        }

        $cedulaExcluida = $existe === null ? null : $cedula;
        if (Usuario::existeUsuarioLogin($pdo, $usuarioLogin, $cedulaExcluida)) {
            $this->flash('error', 'El usuario (login) ya está en uso.');
            $this->redirect('?c=admin&a=creacion_usuarios&cedula=' . rawurlencode($cedula));
            return;
        }
        if (Usuario::existeEmail($pdo, $email, $cedulaExcluida)) {
            $this->flash('error', 'El email ya está en uso.');
            $this->redirect('?c=admin&a=creacion_usuarios&cedula=' . rawurlencode($cedula));
            return;
        }

        try {
            if ($existe === null) {
                Usuario::crear($pdo, $cedula, $nombre, $usuarioLogin, $claveHash, $rol, $email, $estado);
                $this->flash('ok', 'Usuario creado.');
            } else {
                Usuario::actualizar($pdo, $cedula, $nombre, $usuarioLogin, $claveHash, $rol, $email, $estado);
                $this->flash('ok', 'Usuario actualizado.');
            }
        } catch (Throwable $e) {
            $this->flash('error', 'No se pudo guardar el usuario. Verifique restricciones o datos.');
        }

        $this->redirect('?c=admin&a=creacion_usuarios&cedula=' . rawurlencode($cedula));
    }
}
