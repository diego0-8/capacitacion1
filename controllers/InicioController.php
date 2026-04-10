<?php

declare(strict_types=1);

class InicioController extends Controller
{
    public function index(): void
    {
        $this->requireAuth();
        $rol = $_SESSION['usuario_rol'] ?? '';
        if ($rol === 'administrador') {
            $this->redirect('?c=admin&a=index');
        }
        if ($rol === 'coordinador') {
            $this->redirect('?c=coordinador&a=index');
        }
        if ($rol === 'asesor') {
            // #region agent log
            @file_put_contents(
                BASE_PATH . DIRECTORY_SEPARATOR . 'debug-4338d8.log',
                json_encode(
                    [
                        'sessionId' => '4338d8',
                        'runId' => 'run1',
                        'hypothesisId' => 'H4',
                        'location' => 'InicioController::index',
                        'message' => 'redirect asesor',
                        'data' => [],
                        'timestamp' => (int) round(microtime(true) * 1000),
                    ],
                    JSON_UNESCAPED_UNICODE
                ) . PHP_EOL,
                FILE_APPEND
            );
            // #endregion
            $this->redirect('?c=asesor&a=index');
        }
        $this->redirect('?c=auth&a=login');
    }
}
