<?php

declare(strict_types=1);

class Controller
{
    protected function render(string $view, array $data = []): void
    {
        extract($data, EXTR_SKIP);
        $viewFile = BASE_PATH . '/views/' . $view . '.php';
        if (!is_file($viewFile)) {
            http_response_code(500);
            echo 'Vista no encontrada.';
            return;
        }
        require $viewFile;
    }

    protected function redirect(string $queryString): void
    {
        $url = BASE_URL . '/index.php' . $queryString;
        header('Location: ' . $url);
        exit;
    }

    /** @param string[]|null $roles */
    protected function requireAuth(?array $roles = null): void
    {
        if (empty($_SESSION['usuario_cedula'])) {
            $this->redirect('?c=auth&a=login');
        }
        if ($roles !== null && !in_array($_SESSION['usuario_rol'], $roles, true)) {
            $this->redirect('?c=auth&a=forbidden');
        }
    }

    protected function flash(string $key, ?string $message = null): ?string
    {
        if ($message !== null) {
            $_SESSION['flash'][$key] = $message;
            return null;
        }
        $m = $_SESSION['flash'][$key] ?? null;
        unset($_SESSION['flash'][$key]);
        return is_string($m) ? $m : null;
    }

    protected static function sanitizeFilename(string $name): string
    {
        $name = basename($name);
        $name = preg_replace('/[^a-zA-Z0-9._-]/', '_', $name) ?? 'archivo';
        return $name !== '' ? $name : 'archivo';
    }

    /** @return array{ok:bool,path?:string,error?:string} */
    protected function guardarArchivoCurso(int $idCurso): array
    {
        if (empty($_FILES['archivo']) || $_FILES['archivo']['error'] === UPLOAD_ERR_NO_FILE) {
            return ['ok' => false, 'error' => 'No se recibió ningún archivo.'];
        }
        if ($_FILES['archivo']['error'] !== UPLOAD_ERR_OK) {
            return ['ok' => false, 'error' => 'Error al subir el archivo.'];
        }
        if ($_FILES['archivo']['size'] > UPLOAD_MAX_BYTES) {
            return ['ok' => false, 'error' => 'El archivo supera el tamaño permitido.'];
        }

        $allowed = [
            'pdf' => 'application/pdf',
            'mp4' => 'video/mp4',
            'webm' => 'video/webm',
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'pptx' => 'application/vnd.openxmlformats-officedocument.presentationml.presentation',
        ];
        $ext = strtolower(pathinfo($_FILES['archivo']['name'], PATHINFO_EXTENSION));
        if (!isset($allowed[$ext])) {
            return ['ok' => false, 'error' => 'Tipo de archivo no permitido.'];
        }

        // Videos del coordinador: carpeta dedicada por curso.
        $isVideo = in_array($ext, ['mp4', 'webm'], true);
        $dirBase = $isVideo ? UPLOADS_COORDINADOR_VIDEOS_PATH : UPLOADS_CURSOS_PATH;
        $dir = $dirBase . DIRECTORY_SEPARATOR . $idCurso;
        if (!is_dir($dir) && !mkdir($dir, 0755, true) && !is_dir($dir)) {
            return ['ok' => false, 'error' => 'No se pudo crear la carpeta del curso.'];
        }

        $safe = self::sanitizeFilename($_FILES['archivo']['name']);
        $dest = $dir . DIRECTORY_SEPARATOR . $safe;
        if (is_file($dest)) {
            $safe = time() . '_' . $safe;
            $dest = $dir . DIRECTORY_SEPARATOR . $safe;
        }
        if (!move_uploaded_file($_FILES['archivo']['tmp_name'], $dest)) {
            return ['ok' => false, 'error' => 'No se pudo guardar el archivo.'];
        }

        $relativeBase = $isVideo ? 'uploads/coordinador/videos/' : 'uploads/cursos/';
        $relative = $relativeBase . $idCurso . '/' . $safe;
        return ['ok' => true, 'path' => $relative];
    }
}
