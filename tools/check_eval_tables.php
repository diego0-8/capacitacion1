<?php

require dirname(__DIR__) . '/config.php';
$pdo = getPDO();
$tables = ['curso_eval_config', 'curso_eval_preguntas', 'curso_eval_opciones', 'curso_eval_respuesta'];
foreach ($tables as $t) {
    $r = $pdo->query("SHOW TABLES LIKE '$t'");
    echo $t . ': ' . ($r->rowCount() > 0 ? 'EXISTS' : 'MISSING') . PHP_EOL;
}
