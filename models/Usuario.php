<?php

declare(strict_types=1);

class Usuario
{
    private static function tieneEmpresa(PDO $pdo): bool
    {
        static $cache = null;

        if ($cache !== null) {
            return $cache;
        }
        $sql = "SELECT COUNT(*) AS n
                FROM information_schema.columns
                WHERE table_schema = :db
                  AND table_name = 'usuarios'
                  AND column_name = 'empresa'";
        $st = $pdo->prepare($sql);
        $st->execute(['db' => DB_NAME]);
        $row = $st->fetch();
        $n = (int) ($row['n'] ?? 0);
        $cache = $n === 1;

        return $cache;
    }

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
        $cache = $n === 3;

        return $cache;
    }

    public static function buscarPorUsuario(PDO $pdo, string $usuario): ?array
    {
        $hasEmpresa = self::tieneEmpresa($pdo);
        $sql = $hasEmpresa
            ? 'SELECT cedula, nombre, usuario, clave, rol, email, estado, empresa FROM usuarios WHERE usuario = :u LIMIT 1'
            : 'SELECT cedula, nombre, usuario, clave, rol, email, estado FROM usuarios WHERE usuario = :u LIMIT 1';
        $st = $pdo->prepare($sql);
        $st->execute(['u' => $usuario]);
        $row = $st->fetch();

        if (is_array($row) && !$hasEmpresa) {
            $row['empresa'] = 'onix';
        }

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

    /** Todos los usuarios con rol asesor (activos e inactivos), para configurar acceso al curso. */
    /** @return array<int, array<string,mixed>> */
    public static function listarTodosAsesores(PDO $pdo): array
    {
        $sql = 'SELECT cedula, nombre, usuario, email, estado FROM usuarios WHERE rol = \'asesor\' ORDER BY nombre';
        return $pdo->query($sql)->fetchAll();
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
        $hasEmpresa = self::tieneEmpresa($pdo);
        $sql = $hasEmpresa
            ? 'SELECT cedula, nombre, usuario, rol, email, estado, empresa FROM usuarios ORDER BY nombre'
            : 'SELECT cedula, nombre, usuario, rol, email, estado FROM usuarios ORDER BY nombre';
        $rows = $pdo->query($sql)->fetchAll();

        foreach ($rows as &$row) {
            if (!$hasEmpresa) {
                $row['empresa'] = 'onix';
            }
        }
        unset($row);

        return $rows;
    }

    /**
     * @return array{
     *   items: array<int, array<string,mixed>>,
     *   total: int,
     *   pagina: int,
     *   porPagina: int,
     *   totalPaginas: int
     * }
     */
    public static function listarTodosPaginado(
        PDO $pdo,
        string $busqueda = '',
        string $empresaFiltro = '',
        int $pagina = 1,
        int $porPagina = 10
    ): array {
        $hasEmpresa = self::tieneEmpresa($pdo);
        $where = [];
        $params = [];

        $busqueda = trim($busqueda);
        if ($busqueda !== '') {
            $where[] = '(nombre LIKE :q OR cedula LIKE :q)';
            $params['q'] = '%' . $busqueda . '%';
        }

        $empresaFiltro = strtolower(trim($empresaFiltro));
        if ($empresaFiltro !== '' && $empresaFiltro !== 'todas' && in_array($empresaFiltro, ['onix', 'nextdata', 'tys'], true)) {
            if ($hasEmpresa) {
                $where[] = 'empresa = :emp';
                $params['emp'] = $empresaFiltro;
            }
        }

        $whereSql = $where !== [] ? ' WHERE ' . implode(' AND ', $where) : '';

        $sqlCount = 'SELECT COUNT(*) AS n FROM usuarios' . $whereSql;
        $stCount = $pdo->prepare($sqlCount);
        $stCount->execute($params);
        $total = (int) ($stCount->fetch()['n'] ?? 0);

        $porPagina = max(1, $porPagina);
        $totalPaginas = max(1, (int) ceil($total / $porPagina));
        $pagina = max(1, min($pagina, $totalPaginas));
        $offset = ($pagina - 1) * $porPagina;

        $select = $hasEmpresa
            ? 'cedula, nombre, usuario, rol, email, estado, empresa'
            : 'cedula, nombre, usuario, rol, email, estado';
        $sql = "SELECT {$select} FROM usuarios{$whereSql} ORDER BY nombre LIMIT :lim OFFSET :off";
        $st = $pdo->prepare($sql);
        foreach ($params as $k => $v) {
            $st->bindValue(':' . $k, $v);
        }
        $st->bindValue(':lim', $porPagina, PDO::PARAM_INT);
        $st->bindValue(':off', $offset, PDO::PARAM_INT);
        $st->execute();
        $rows = $st->fetchAll();

        foreach ($rows as &$row) {
            if (!$hasEmpresa) {
                $row['empresa'] = 'onix';
            }
        }
        unset($row);

        return [
            'items' => $rows,
            'total' => $total,
            'pagina' => $pagina,
            'porPagina' => $porPagina,
            'totalPaginas' => $totalPaginas,
        ];
    }

    public static function claveEsHash(string $clave): bool
    {
        return preg_match('/^\$2[ayb]\$.{56}$/', $clave) === 1
            || str_starts_with($clave, '$argon2');
    }

    public static function buscarPorCedula(PDO $pdo, string $cedula): ?array
    {
        $hasEmpresa = self::tieneEmpresa($pdo);

        if (self::tienePinVerificacion($pdo)) {
            $sql = $hasEmpresa
                ? 'SELECT cedula, nombre, usuario, rol, email, estado, empresa, clave, pin_verificacion_hash, pin_verificacion_expira_en, pin_verificacion_intentos
                    FROM usuarios WHERE cedula = :c LIMIT 1'
                : 'SELECT cedula, nombre, usuario, rol, email, estado, clave, pin_verificacion_hash, pin_verificacion_expira_en, pin_verificacion_intentos
                    FROM usuarios WHERE cedula = :c LIMIT 1';
        } else {
            $sql = $hasEmpresa
                ? 'SELECT cedula, nombre, usuario, rol, email, estado, empresa, clave
                    FROM usuarios WHERE cedula = :c LIMIT 1'
                : 'SELECT cedula, nombre, usuario, rol, email, estado, clave
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
        if (is_array($row) && !$hasEmpresa) {
            $row['empresa'] = 'onix';
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
        string $empresa,
        string $estado
    ): void {
        $empresa = strtolower(trim($empresa));

        if (!in_array($empresa, ['onix', 'nextdata', 'tys'], true)) {
            $empresa = 'onix';
        }

        $hasEmpresa = self::tieneEmpresa($pdo);
        $sql = $hasEmpresa
            ? 'INSERT INTO usuarios (cedula, nombre, usuario, clave, rol, email, empresa, estado)
                VALUES (:ced, :nom, :user, :clave, :rol, :email, :empresa, :estado)'
            : 'INSERT INTO usuarios (cedula, nombre, usuario, clave, rol, email, estado)
                VALUES (:ced, :nom, :user, :clave, :rol, :email, :estado)';
        $st = $pdo->prepare($sql);
        $params = [
            'ced' => $cedula,
            'nom' => $nombre,
            'user' => $usuarioLogin,
            'clave' => $claveHash,
            'rol' => $rol,
            'email' => $email,
            'estado' => $estado,
        ];

        if ($hasEmpresa) {
            $params['empresa'] = $empresa;
        }
        $st->execute($params);
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
