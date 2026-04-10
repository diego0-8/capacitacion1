<?php

declare(strict_types=1);

class AuthController extends Controller
{
    private function enviarPinVerificacion(string $email, string $nombre, string $pin): void
    {
        $base = BASE_PATH . DIRECTORY_SEPARATOR . 'lib' . DIRECTORY_SEPARATOR . 'PHPMailer' . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR;
        require_once $base . 'Exception.php';
        require_once $base . 'PHPMailer.php';
        require_once $base . 'SMTP.php';

        $mail = new PHPMailer\PHPMailer\PHPMailer(true);
        $mail->isSMTP();
        $mail->Host = SMTP_HOST;
        $mail->Port = (int) SMTP_PORT;
        $mail->SMTPAuth = true;
        $mail->Username = SMTP_USER;
        $mail->Password = SMTP_PASS;
        $mail->SMTPSecure = PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
        $mail->CharSet = 'UTF-8';

        $mail->setFrom(SMTP_FROM_EMAIL, SMTP_FROM_NAME);
        $mail->addAddress($email, $nombre);
        $mail->Subject = 'Código de verificación — Capacitación';

        $body = "Hola {$nombre},\n\n"
            . "Tu código de verificación es: {$pin}\n"
            . "Este código caduca en 15 minutos.\n\n"
            . "Si no solicitaste esta cuenta, ignora este mensaje.\n";
        $mail->Body = $body;
        $mail->send();
    }

    public function login(): void
    {
        if (!empty($_SESSION['usuario_cedula'])) {
            $this->redirect('?c=inicio&a=index');
            return;
        }
        $pdo = getPDO();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $usuario = trim((string) ($_POST['usuario'] ?? ''));
            $clave = (string) ($_POST['clave'] ?? '');
            // #region agent log
            try {
                $row = $usuario !== '' ? Usuario::buscarPorUsuario($pdo, $usuario) : null;
            } catch (Throwable $e) {
                @file_put_contents(
                    BASE_PATH . DIRECTORY_SEPARATOR . 'debug-4338d8.log',
                    json_encode(
                        [
                            'sessionId' => '4338d8',
                            'runId' => 'run1',
                            'hypothesisId' => 'H3',
                            'location' => 'AuthController::login',
                            'message' => 'exception in buscarPorUsuario',
                            'data' => ['type' => get_class($e), 'code' => $e->getCode()],
                            'timestamp' => (int) round(microtime(true) * 1000),
                        ],
                        JSON_UNESCAPED_UNICODE
                    ) . PHP_EOL,
                    FILE_APPEND
                );
                throw $e;
            }
            // #endregion
            if ($row && ($row['estado'] ?? '') === 'activo' && password_verify($clave, (string) ($row['clave'] ?? ''))) {
                $_SESSION['usuario_cedula'] = $row['cedula'];
                $_SESSION['usuario_nombre'] = $row['nombre'];
                $_SESSION['usuario_rol'] = $row['rol'];
                $_SESSION['usuario_login'] = $row['usuario'];
                // #region agent log
                @file_put_contents(
                    BASE_PATH . DIRECTORY_SEPARATOR . 'debug-4338d8.log',
                    json_encode(
                        [
                            'sessionId' => '4338d8',
                            'runId' => 'run1',
                            'hypothesisId' => 'H1',
                            'location' => 'AuthController::login',
                            'message' => 'login ok redirect inicio',
                            'data' => ['rol' => (string) ($row['rol'] ?? '')],
                            'timestamp' => (int) round(microtime(true) * 1000),
                        ],
                        JSON_UNESCAPED_UNICODE
                    ) . PHP_EOL,
                    FILE_APPEND
                );
                // #endregion
                $this->redirect('?c=inicio&a=index');
                return;
            }
            $this->flash('error', 'Usuario o contraseña incorrectos, o cuenta inactiva.');
        }
        $error = $this->flash('error');
        $mensaje = $this->flash('ok');
        $this->render('auth/login', ['error' => $error, 'mensaje' => $mensaje]);
    }

    public function registro(): void
    {
        if (!empty($_SESSION['usuario_cedula'])) {
            $this->redirect('?c=inicio&a=index');
            return;
        }
        $pdo = getPDO();

        $form = [
            'cedula' => trim((string) ($_POST['cedula'] ?? '')),
            'nombre' => trim((string) ($_POST['nombre'] ?? '')),
            'email' => trim((string) ($_POST['email'] ?? '')),
            'usuario' => trim((string) ($_POST['usuario'] ?? '')),
        ];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $clave = (string) ($_POST['clave'] ?? '');
            $clave2 = (string) ($_POST['clave2'] ?? '');

            $cedula = preg_replace('/\D+/', '', $form['cedula']) ?? '';
            $nombre = $form['nombre'];
            $email = strtolower($form['email']);
            $usuarioLogin = $form['usuario'];

            // #region agent log
            @file_put_contents(
                BASE_PATH . DIRECTORY_SEPARATOR . 'debug-bf5bfd.log',
                json_encode([
                    'sessionId' => 'bf5bfd',
                    'runId' => 'pre-fix',
                    'hypothesisId' => 'R1',
                    'location' => 'controllers/AuthController.php:registro',
                    'message' => 'Registro POST received (pre-validation)',
                    'data' => [
                        'cedula' => $cedula,
                        'usuario' => $usuarioLogin,
                        'emailLen' => strlen($email),
                        'claveLen' => strlen($clave),
                        'clave2Len' => strlen($clave2),
                        'claveEq' => ($clave === $clave2),
                    ],
                    'timestamp' => (int) floor(microtime(true) * 1000),
                ], JSON_UNESCAPED_SLASHES) . PHP_EOL,
                FILE_APPEND
            );
            // #endregion

            if ($cedula === '' || strlen($cedula) > 10) {
                $this->flash('error', 'La cédula es obligatoria (máx. 10 dígitos).');
                $this->redirect('?c=auth&a=registro');
                return;
            }
            if ($nombre === '' || strlen($nombre) > 100) {
                $this->flash('error', 'El nombre completo es obligatorio (máx. 100).');
                $this->redirect('?c=auth&a=registro');
                return;
            }
            if ($email === '' || strlen($email) > 100 || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $this->flash('error', 'Ingrese un correo válido.');
                $this->redirect('?c=auth&a=registro');
                return;
            }
            if ($usuarioLogin === '' || strlen($usuarioLogin) > 50) {
                $this->flash('error', 'El usuario es obligatorio (máx. 50).');
                $this->redirect('?c=auth&a=registro');
                return;
            }
            if ($clave === '' || strlen($clave) < 6) {
                $this->flash('error', 'La contraseña debe tener al menos 6 caracteres.');
                $this->redirect('?c=auth&a=registro');
                return;
            }
            if ($clave !== $clave2) {
                // #region agent log
                $yaExiste = Usuario::buscarPorCedula($pdo, $cedula) !== null;
                @file_put_contents(
                    BASE_PATH . DIRECTORY_SEPARATOR . 'debug-bf5bfd.log',
                    json_encode([
                        'sessionId' => 'bf5bfd',
                        'runId' => 'pre-fix',
                        'hypothesisId' => 'R2',
                        'location' => 'controllers/AuthController.php:registro',
                        'message' => 'Password confirmation mismatch branch taken',
                        'data' => [
                            'cedula' => $cedula,
                            'usuario' => $usuarioLogin,
                            'yaExisteCedula' => $yaExiste,
                        ],
                        'timestamp' => (int) floor(microtime(true) * 1000),
                    ], JSON_UNESCAPED_SLASHES) . PHP_EOL,
                    FILE_APPEND
                );
                // #endregion
                $this->flash('error', 'La confirmación de contraseña no coincide.');
                $this->redirect('?c=auth&a=registro');
                return;
            }

            if (Usuario::buscarPorCedula($pdo, $cedula) !== null) {
                $this->flash('error', 'Ya existe un usuario con esa cédula.');
                $this->redirect('?c=auth&a=registro');
                return;
            }
            if (Usuario::existeUsuarioLogin($pdo, $usuarioLogin)) {
                $this->flash('error', 'Ese usuario ya está en uso.');
                $this->redirect('?c=auth&a=registro');
                return;
            }
            if (Usuario::existeEmail($pdo, $email)) {
                $this->flash('error', 'Ese correo ya está en uso.');
                $this->redirect('?c=auth&a=registro');
                return;
            }

            try {
                $hash = password_hash($clave, PASSWORD_DEFAULT);
                // #region agent log
                @file_put_contents(
                    BASE_PATH . DIRECTORY_SEPARATOR . 'debug-bf5bfd.log',
                    json_encode([
                        'sessionId' => 'bf5bfd',
                        'runId' => 'post-change',
                        'hypothesisId' => 'C1',
                        'location' => 'controllers/AuthController.php:registro',
                        'message' => 'Creating user as activo (PIN/email bypass enabled)',
                        'data' => ['cedula' => $cedula, 'usuario' => $usuarioLogin],
                        'timestamp' => (int) floor(microtime(true) * 1000),
                    ], JSON_UNESCAPED_SLASHES) . PHP_EOL,
                    FILE_APPEND
                );
                // #endregion
                Usuario::crear($pdo, $cedula, $nombre, $usuarioLogin, $hash, 'asesor', $email, 'activo');
                // #region agent log
                @file_put_contents(
                    BASE_PATH . DIRECTORY_SEPARATOR . 'debug-bf5bfd.log',
                    json_encode([
                        'sessionId' => 'bf5bfd',
                        'runId' => 'post-change',
                        'hypothesisId' => 'C2',
                        'location' => 'controllers/AuthController.php:registro',
                        'message' => 'Usuario::crear executed successfully',
                        'data' => ['cedula' => $cedula, 'usuario' => $usuarioLogin],
                        'timestamp' => (int) floor(microtime(true) * 1000),
                    ], JSON_UNESCAPED_SLASHES) . PHP_EOL,
                    FILE_APPEND
                );
                // #endregion
            } catch (PDOException $e) {
                // #region agent log
                @file_put_contents(
                    BASE_PATH . DIRECTORY_SEPARATOR . 'debug-bf5bfd.log',
                    json_encode([
                        'sessionId' => 'bf5bfd',
                        'runId' => 'pre-fix',
                        'hypothesisId' => 'R4',
                        'location' => 'controllers/AuthController.php:registro',
                        'message' => 'PDOException during Usuario::crear or subsequent SQL',
                        'data' => ['cedula' => $cedula, 'sqlState' => $e->getCode()],
                        'timestamp' => (int) floor(microtime(true) * 1000),
                    ], JSON_UNESCAPED_SLASHES) . PHP_EOL,
                    FILE_APPEND
                );
                // #endregion
                $this->flash('error', 'No se pudo crear la cuenta. Revise la base de datos y vuelva a intentar.');
                $this->redirect('?c=auth&a=registro');
                return;
            }
            $this->flash('ok', 'Cuenta creada. Ya puede iniciar sesión.');
            $this->redirect('?c=auth&a=login');
            return;
        }

        $this->render('auth/registro', [
            'error' => $this->flash('error'),
            'mensaje' => $this->flash('ok'),
            'form' => $form,
        ]);
    }

    public function verificar(): void
    {
        if (!empty($_SESSION['usuario_cedula'])) {
            $this->redirect('?c=inicio&a=index');
            return;
        }
        $pdo = getPDO();
        $cedula = (string) ($_SESSION['pendiente_verificacion_cedula'] ?? '');
        if ($cedula === '') {
            $this->redirect('?c=auth&a=login');
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $pin = preg_replace('/\D+/', '', (string) ($_POST['pin'] ?? '')) ?? '';
            if (strlen($pin) !== 6) {
                $this->flash('error', 'Ingrese el código de 6 dígitos.');
                $this->redirect('?c=auth&a=verificar');
                return;
            }

            $u = Usuario::buscarPorCedula($pdo, $cedula);
            if ($u === null || ($u['rol'] ?? '') !== 'asesor') {
                $this->flash('error', 'Verificación no disponible.');
                $this->redirect('?c=auth&a=login');
                return;
            }
            if (($u['estado'] ?? '') === 'activo') {
                unset($_SESSION['pendiente_verificacion_cedula']);
                $this->flash('ok', 'Su cuenta ya está activa.');
                $this->redirect('?c=auth&a=login');
                return;
            }

            $exp = (string) ($u['pin_verificacion_expira_en'] ?? '');
            $hash = (string) ($u['pin_verificacion_hash'] ?? '');
            $intentos = (int) ($u['pin_verificacion_intentos'] ?? 0);
            if ($hash === '' || $exp === '') {
                $this->flash('error', 'Debe solicitar un nuevo código.');
                $this->redirect('?c=auth&a=verificar');
                return;
            }
            if ($intentos >= 5) {
                $this->flash('error', 'Superó el límite de intentos. Reenvíe un nuevo código.');
                $this->redirect('?c=auth&a=verificar');
                return;
            }
            if (new DateTimeImmutable('now') > new DateTimeImmutable($exp)) {
                $this->flash('error', 'El código ha expirado. Reenvíe un nuevo código.');
                $this->redirect('?c=auth&a=verificar');
                return;
            }

            if (!password_verify($pin, $hash)) {
                try {
                    $n = Usuario::incrementarIntentosPin($pdo, $cedula);
                } catch (RuntimeException $e) {
                    // #region agent log
                    @file_put_contents(
                        BASE_PATH . DIRECTORY_SEPARATOR . 'debug-bf5bfd.log',
                        json_encode([
                            'sessionId' => 'bf5bfd',
                            'runId' => 'pre-fix',
                            'hypothesisId' => 'H3',
                            'location' => 'controllers/AuthController.php:verificar',
                            'message' => 'RuntimeException during incrementarIntentosPin',
                            'data' => ['cedula' => $cedula, 'err' => $e->getMessage()],
                            'timestamp' => (int) floor(microtime(true) * 1000),
                        ], JSON_UNESCAPED_SLASHES) . PHP_EOL,
                        FILE_APPEND
                    );
                    // #endregion
                    $this->flash(
                        'error',
                        'Su base de datos no tiene habilitada la verificación por PIN. Ejecute el script database/migration_add_asesor_pin.sql en la base capacitacion1.'
                    );
                    $this->redirect('?c=auth&a=login');
                    return;
                }
                $this->flash('error', $n >= 5 ? 'Código incorrecto. Reenvíe un nuevo código.' : 'Código incorrecto.');
                $this->redirect('?c=auth&a=verificar');
                return;
            }

            try {
                Usuario::activarYLimpiarPin($pdo, $cedula);
            } catch (RuntimeException $e) {
                // #region agent log
                @file_put_contents(
                    BASE_PATH . DIRECTORY_SEPARATOR . 'debug-bf5bfd.log',
                    json_encode([
                        'sessionId' => 'bf5bfd',
                        'runId' => 'pre-fix',
                        'hypothesisId' => 'H4',
                        'location' => 'controllers/AuthController.php:verificar',
                        'message' => 'RuntimeException during activarYLimpiarPin',
                        'data' => ['cedula' => $cedula, 'err' => $e->getMessage()],
                        'timestamp' => (int) floor(microtime(true) * 1000),
                    ], JSON_UNESCAPED_SLASHES) . PHP_EOL,
                    FILE_APPEND
                );
                // #endregion
                $this->flash(
                    'error',
                    'Su base de datos no tiene habilitada la verificación por PIN. Ejecute el script database/migration_add_asesor_pin.sql en la base capacitacion1.'
                );
                $this->redirect('?c=auth&a=login');
                return;
            }
            unset($_SESSION['pendiente_verificacion_cedula']);
            $this->flash('ok', 'Cuenta verificada. Ya puede iniciar sesión.');
            $this->redirect('?c=auth&a=login');
            return;
        }

        $this->render('auth/verificar', [
            'error' => $this->flash('error'),
            'mensaje' => $this->flash('ok'),
        ]);
    }

    public function reenviar_pin(): void
    {
        if (!empty($_SESSION['usuario_cedula'])) {
            $this->redirect('?c=inicio&a=index');
            return;
        }
        $pdo = getPDO();
        $cedula = (string) ($_SESSION['pendiente_verificacion_cedula'] ?? '');
        if ($cedula === '') {
            $this->redirect('?c=auth&a=login');
            return;
        }

        $u = Usuario::buscarPorCedula($pdo, $cedula);
        if ($u === null || ($u['rol'] ?? '') !== 'asesor') {
            $this->redirect('?c=auth&a=login');
            return;
        }
        if (($u['estado'] ?? '') === 'activo') {
            unset($_SESSION['pendiente_verificacion_cedula']);
            $this->flash('ok', 'Su cuenta ya está activa.');
            $this->redirect('?c=auth&a=login');
            return;
        }

        try {
            $pin = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
            $pinHash = password_hash($pin, PASSWORD_DEFAULT);
            $expiraEn = (new DateTimeImmutable('now'))->modify('+15 minutes')->format('Y-m-d H:i:s');
            Usuario::setPinVerificacion($pdo, $cedula, $pinHash, $expiraEn);
        } catch (RuntimeException $e) {
            // #region agent log
            @file_put_contents(
                BASE_PATH . DIRECTORY_SEPARATOR . 'debug-bf5bfd.log',
                json_encode([
                    'sessionId' => 'bf5bfd',
                    'runId' => 'pre-fix',
                    'hypothesisId' => 'H5',
                    'location' => 'controllers/AuthController.php:reenviar_pin',
                    'message' => 'RuntimeException during setPinVerificacion in reenviar_pin',
                    'data' => ['cedula' => $cedula, 'err' => $e->getMessage()],
                    'timestamp' => (int) floor(microtime(true) * 1000),
                ], JSON_UNESCAPED_SLASHES) . PHP_EOL,
                FILE_APPEND
            );
            // #endregion
            $this->flash(
                'error',
                'Su base de datos no tiene habilitada la verificación por PIN. Ejecute el script database/migration_add_asesor_pin.sql en la base capacitacion1.'
            );
            $this->redirect('?c=auth&a=login');
            return;
        }

        try {
            $this->enviarPinVerificacion((string) $u['email'], (string) $u['nombre'], $pin);
        } catch (Throwable $e) {
            $this->flash('error', 'No se pudo reenviar el correo. Intente más tarde.');
            $this->redirect('?c=auth&a=verificar');
            return;
        }

        $this->flash('ok', 'Se envió un nuevo código. Revise su correo.');
        $this->redirect('?c=auth&a=verificar');
    }

    public function logout(): void
    {
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $p = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000, $p['path'], $p['domain'], (bool) $p['secure'], (bool) $p['httponly']);
        }
        session_destroy();
        session_start();
        $this->redirect('?c=auth&a=login');
    }

    public function forbidden(): void
    {
        if (empty($_SESSION['usuario_cedula'])) {
            $this->redirect('?c=auth&a=login');
            return;
        }
        $this->render('auth/forbidden', []);
    }
}
