<?php

declare(strict_types=1);

class Router
{
    public function dispatch(): void
    {
        $c = preg_replace('/[^a-z]/', '', strtolower($_GET['c'] ?? 'inicio'));
        $a = preg_replace('/[^a-z_]/', '', strtolower($_GET['a'] ?? 'index'));

        if ($c === '') {
            $c = 'inicio';
        }
        if ($a === '') {
            $a = 'index';
        }

        $publicControllers = ['auth'];
        $sessionOk = !empty($_SESSION['usuario_cedula']);
        if (!$sessionOk && !in_array($c, $publicControllers, true)) {
            header('Location: ' . BASE_URL . '/index.php?c=auth&a=login');
            exit;
        }

        $map = [
            'inicio' => 'InicioController',
            'auth' => 'AuthController',
            'admin' => 'AdminController',
            'coordinador' => 'CoordinadorController',
            'asesor' => 'AsesorController',
        ];

        $class = $map[$c] ?? null;
        if ($class === null || !class_exists($class)) {
            http_response_code(404);
            echo 'Ruta no encontrada.';
            return;
        }

        $controller = new $class();
        if (!method_exists($controller, $a)) {
            http_response_code(404);
            echo 'Acción no encontrada.';
            return;
        }

        $controller->{$a}();
    }
}
