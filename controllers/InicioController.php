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
            $this->redirect('?c=asesor&a=index');
        }
        $this->redirect('?c=auth&a=login');
    }
}
