<?php

declare(strict_types=1);

session_start();

require_once __DIR__ . '/config.php';

spl_autoload_register(static function (string $class): void {
    $paths = [
        BASE_PATH . '/core/' . $class . '.php',
        BASE_PATH . '/controllers/' . $class . '.php',
        BASE_PATH . '/models/' . $class . '.php',
    ];
    foreach ($paths as $path) {
        if (is_file($path)) {
            require_once $path;
            return;
        }
    }
});

$router = new Router();
$router->dispatch();
