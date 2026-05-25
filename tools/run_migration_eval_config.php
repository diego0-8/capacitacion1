<?php

require dirname(__DIR__) . '/config.php';
$pdo = getPDO();
$pdo->exec("
CREATE TABLE IF NOT EXISTS curso_eval_config (
  id_curso INT(11) NOT NULL,
  preguntas_requeridas TINYINT UNSIGNED NOT NULL DEFAULT 1,
  activo TINYINT(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (id_curso),
  CONSTRAINT fk_cec_id_curso FOREIGN KEY (id_curso) REFERENCES cursos (id_cursos) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
");
echo "curso_eval_config: CREATED\n";
