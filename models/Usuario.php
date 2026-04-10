<?php

declare(strict_types=1);

class Usuario
{
    private static function tienePinVerificacion(PDO $pdo): bool
    {
        static $cache = null;
        if ($cache !== null) {
            return $cache;
        }
        $sql = "SELECT COUNT(*) AS n
                FROM information_schema.columns
                WHERE table_schema = :db
                  AND table_name = 'usuarios'
                  AND column_name IN ('pin_verificacion_hash','pin_verificacion_expira_en','pin_verificacion_intentos')";
        $st = $pdo->prepare($sql);
        $st->execute(['db' => DB_NAME]);
        $row = $st->fetch();
        $n = (int) ($row['n'] ?? 0);
        // #region agent log
        @file_put_contents(
            BASE_PATH . DIRECTORY_SEPARATOR . 'debug-bf5bfd.log',
            json_encode([
                'sessionId' => 'bf5bfd',
                'runId' => 'pre-fix',
                'hypothesisId' => 'H1',
                'location' => 'models/Usuario.php:tienePinVerificacion',
                'message' => 'Pin verification column check',
                'data' => ['db' => (string) DB_NAME, 'count' => $n],
                'timestamp' => (int) floor(microtime(true) * 1000),
            ], JSON_UNESCAPED_SLASHES) . PHP_EOL,
            FILE_APPEND
        );
        // #endregion
        $cache = $n === 3;
        return $cache;
    }

    public static function buscarPorUsuario(PDO $pdo, string $usuario): ?array
    {
        $sql = 'SELECT cedula, nombre, usuario, clave, rol, email, estado FROM usuarios WHERE usuario = :u LIMIT 1';
        $st = $pdo->prepare($sql);
        $st->execute(['u' => $usuario]);
        $row = $st->fetch();
        return $row ?: null;
    }

    /** @return array<int, array<string,mixed>> */
    public static function listarPorRol(PDO $pdo, string $rol): array
    {
        $sql = 'SELECT cedula, nombre, usuario, email, estado FROM usuarios WHERE rol = :r AND estado = :e ORDER BY nombre';
        $st = $pdo->prepare($sql);
        $st->execute(['r' => $rol, 'e' => 'activo']);
        return $st->fetchAll();
    }

    /**
     * Valida que la cédula corresponda a un usuario activo con rol coordinador
     * (el administrador elige la cédula, pero la BD define el rol).
     */
    public static function esCoordinadorActivo(PDO $pdo, string $cedula): bool
    {
        $sql = 'SELECT 1 FROM usuarios WHERE cedula = :c AND rol = \'coordinador\' AND estado = \'activo\' LIMIT 1';
        $st = $pdo->prepare($sql);
        $st->execute(['c' => $cedula]);
        return (bool) $st->fetch();
    }

    /** @return array<int, array<string,mixed>> */
    public static function listarTodos(PDO $pdo): array
    {
        $sql = 'SELECT cedula, nombre, usuario, rol, email, estado FROM usuarios ORDER BY nombre';
        return $pdo->query($sql)->fetchAll();
    }

    public static function buscarPorCedula(PDO $pdo, string $cedula): ?array
    {
        if (self::tienePinVerificacion($pdo)) {
            $sql = 'SELECT cedula, nombre, usuario, rol, email, estado, clave, pin_verificacion_hash, pin_verificacion_expira_en, pin_verificacion_intentos
                    FROM usuarios WHERE cedula = :c LIMIT 1';
        } else {
            $sql = 'SELECT cedula, nombre, usuario, rol, email, estado, clave
                    FROM usuarios WHERE cedula = :c LIMIT 1';
        }
        $st = $pdo->prepare($sql);
        $st->execute(['c' => $cedula]);
        $row = $st->fetch();
        if ($row && !self::tienePinVerificacion($pdo)) {
            $row['pin_verificacion_hash'] = null;
            $row['pin_verificacion_expira_en'] = null;
            $row['pin_verificacion_intentos'] = 0;
        }
        return $row ?: null;
    }

    public static function existeUsuarioLogin(PDO $pdo, string $usuarioLogin, ?string $cedulaExcluida = null): bool
    {
        $sql = 'SELECT COUNT(*) AS n FROM usuarios WHERE usuario = :u';
        $params = ['u' => $usuarioLogin];
        if ($cedulaExcluida !== null) {
            $sql .= ' AND cedula <> :c';
            $params['c'] = $cedulaExcluida;
        }
        $st = $pdo->prepare($sql);
        $st->execute($params);
        $row = $st->fetch();
        return (int) ($row['n'] ?? 0) > 0;
    }

    public static function existeEmail(PDO $pdo, string $email, ?string $cedulaExcluida = null): bool
    {
        $sql = 'SELECT COUNT(*) AS n FROM usuarios WHERE email = :e';
        $params = ['e' => $email];
        if ($cedulaExcluida !== null) {
            $sql .= ' AND cedula <> :c';
            $params['c'] = $cedulaExcluida;
        }
        $st = $pdo->prepare($sql);
        $st->execute($params);
        $row = $st->fetch();
        return (int) ($row['n'] ?? 0) > 0;
    }

    public static function crear(
        PDO $pdo,
        string $cedula,
        string $nombre,
        string $usuarioLogin,
        string $claveHash,
        string $rol,
        string $email,
        string $estado
    ): void {
        $sql = 'INSERT INTO usuarios (cedula, nombre, usuario, clave, rol, email, estado)
                VALUES (:ced, :nom, :user, :clave, :rol, :email, :estado)';
        $st = $pdo->prepare($sql);
        $st->execute([
            'ced' => $cedula,
            'nom' => $nombre,
            'user' => $usuarioLogin,
            'clave' => $claveHash,
            'rol' => $rol,
            'email' => $email,
            'estado' => $estado,
        ]);
    }

    public static function actualizar(
        PDO $pdo,
        string $cedula,
        string $nombre,
        string $usuarioLogin,
        ?string $claveHash,
        string $rol,
        string $email,
        string $estado
    ): void {
        if ($claveHash !== null && $claveHash !== '') {
            $sql = 'UPDATE usuarios
                    SET nombre = :nom,
                        usuario = :user,
                        clave = :clave,
                        rol = :rol,
                        email = :email,
                        estado = :estado
                    WHERE cedula = :ced';
            $st = $pdo->prepare($sql);
            $st->execute([
                'nom' => $nombre,
                'user' => $usuarioLogin,
                'clave' => $claveHash,
                'rol' => $rol,
                'email' => $email,
                'estado' => $estado,
                'ced' => $cedula,
            ]);
            return;
        }

        $sql = 'UPDATE usuarios
                SET nombre = :nom,
                    usuario = :user,
                    rol = :rol,
                    email = :email,
                    estado = :estado
                WHERE cedula = :ced';
        $st = $pdo->prepare($sql);
        $st->execute([
            'nom' => $nombre,
            'user' => $usuarioLogin,
            'rol' => $rol,
            'email' => $email,
            'estado' => $estado,
            'ced' => $cedula,
        ]);
    }

    public static function setPinVerificacion(PDO $pdo, string $cedula, string $pinHash, string $expiraEn): void
    {
        if (!self::tienePinVerificacion($pdo)) {
            throw new RuntimeException('Faltan columnas de verificación por PIN en la tabla usuarios.');
        }
        $sql = 'UPDATE usuarios
                SET pin_verificacion_hash = :h,
                    pin_verificacion_expira_en = :e,
                    pin_verificacion_intentos = 0
                WHERE cedula = :c';
        $st = $pdo->prepare($sql);
        $st->execute(['h' => $pinHash, 'e' => $expiraEn, 'c' => $cedula]);
    }

    public static function incrementarIntentosPin(PDO $pdo, string $cedula): int
    {
        if (!self::tienePinVerificacion($pdo)) {
            throw new RuntimeException('Faltan columnas de verificación por PIN en la tabla usuarios.');
        }
        $pdo->prepare('UPDATE usuarios SET pin_verificacion_intentos = pin_verificacion_intentos + 1 WHERE cedula = :c')
            ->execute(['c' => $cedula]);
        $st = $pdo->prepare('SELECT pin_verificacion_intentos AS n FROM usuarios WHERE cedula = :c LIMIT 1');
        $st->execute(['c' => $cedula]);
        $row = $st->fetch();
        return (int) ($row['n'] ?? 0);
    }

    public static function activarYLimpiarPin(PDO $pdo, string $cedula): void
    {
        if (!self::tienePinVerificacion($pdo)) {
            throw new RuntimeException('Faltan columnas de verificación por PIN en la tabla usuarios.');
        }
        $sql = 'UPDATE usuarios
                SET estado = \'activo\',
                    pin_verificacion_hash = NULL,
                    pin_verificacion_expira_en = NULL,
                    pin_verificacion_intentos = 0
                WHERE cedula = :c';
        $st = $pdo->prepare($sql);
        $st->execute(['c' => $cedula]);
    }

    public static function limpiarPin(PDO $pdo, string $cedula): void
    {
        if (!self::tienePinVerificacion($pdo)) {
            throw new RuntimeException('Faltan columnas de verificación por PIN en la tabla usuarios.');
        }
        $sql = 'UPDATE usuarios
                SET pin_verificacion_hash = NULL,
                    pin_verificacion_expira_en = NULL,
                    pin_verificacion_intentos = 0
                WHERE cedula = :c';
        $st = $pdo->prepare($sql);
        $st->execute(['c' => $cedula]);
    }
}
