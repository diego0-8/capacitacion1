<?php

declare(strict_types=1);

final class CursoProgreso
{
    /**
     * Regla: un módulo sin lecciones NO puede considerarse completo (decisión B).
     */
    public static function asesorCompletoTodosModulos(PDO $pdo, string $cedulaAsesor, int $idCurso): bool
    {
        $modulos = ModuloCurso::porCurso($pdo, $idCurso);
        if ($modulos === []) {
            return false;
        }

        foreach ($modulos as $m) {
            $idModulo = (int) ($m['id_modulo'] ?? 0);
            if ($idModulo <= 0) {
                continue;
            }

            $lecs = Leccion::porModulo($pdo, $idModulo);
            $total = count($lecs);
            if ($total === 0) {
                return false;
            }

            $doneSet = LeccionCompletado::idsCompletadasPorCurso($pdo, $idCurso, $cedulaAsesor);
            $done = 0;
            foreach ($lecs as $L) {
                $idL = (int) ($L['id_leccion'] ?? 0);
                if ($idL > 0 && isset($doneSet[$idL])) {
                    $done++;
                }
            }
            if ($done < $total) {
                return false;
            }

            $cfg = ModuloQuiz::getConfig($pdo, $idModulo);
            $quizActivo = (int) ($cfg['activo'] ?? 0) === 1;
            if ($quizActivo && !ModuloCompletado::estaCompletado($pdo, $idModulo, $cedulaAsesor)) {
                return false;
            }
        }

        return true;
    }
}

