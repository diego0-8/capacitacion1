-- =============================================================================
-- Migración: Evaluación por módulo (1 a 3 preguntas)
--
-- Agrega tablas para configurar y registrar quizzes al finalizar un módulo:
-- - Config por módulo (cantidad de preguntas requeridas)
-- - Preguntas (tipo: imagen_par, vf, multi)
-- - Opciones (texto o imagen)
-- - Respuesta correcta (referencia a opción)
-- - Intentos y respuestas del asesor
-- - Registro de módulo completado
--
-- En phpMyAdmin: seleccione la base `capacitacion1` y ejecute este archivo.
-- =============================================================================

CREATE TABLE IF NOT EXISTS `modulo_quiz_config` (
  `id_modulo` int(11) NOT NULL,
  `preguntas_requeridas` tinyint(4) NOT NULL DEFAULT 1,
  `activo` tinyint(1) NOT NULL DEFAULT 1,
  `fecha_actualizacion` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `modulo_preguntas` (
  `id_pregunta_modulo` int(11) NOT NULL,
  `id_modulo` int(11) NOT NULL,
  `tipo` enum('imagen_par','vf','multi') NOT NULL,
  `enunciado` text NOT NULL,
  `orden` tinyint(4) NOT NULL,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `modulo_opciones` (
  `id_opcion` int(11) NOT NULL,
  `id_pregunta_modulo` int(11) NOT NULL,
  `clave` enum('a','b','c','d','ok','bad','true','false') NOT NULL,
  `texto` varchar(255) DEFAULT NULL,
  `imagen_path` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `modulo_preguntas_respuesta` (
  `id_pregunta_modulo` int(11) NOT NULL,
  `id_opcion_correcta` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `modulo_intentos` (
  `id_intento_modulo` int(11) NOT NULL,
  `id_modulo` int(11) NOT NULL,
  `cedula_asesor` varchar(10) NOT NULL,
  `total_preguntas` tinyint(4) NOT NULL,
  `correctas` tinyint(4) NOT NULL,
  `aprobado` tinyint(1) NOT NULL,
  `fecha_intento` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `modulo_respuestas` (
  `id_intento_modulo` int(11) NOT NULL,
  `id_pregunta_modulo` int(11) NOT NULL,
  `id_opcion_elegida` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `modulo_completado` (
  `id_modulo` int(11) NOT NULL,
  `cedula_asesor` varchar(10) NOT NULL,
  `fecha_completado` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

ALTER TABLE `modulo_quiz_config`
  ADD PRIMARY KEY (`id_modulo`),
  ADD CONSTRAINT `fk_modulo_quiz_config_mod` FOREIGN KEY (`id_modulo`) REFERENCES `cursos_modulos` (`id_modulo`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `modulo_preguntas`
  ADD PRIMARY KEY (`id_pregunta_modulo`),
  ADD UNIQUE KEY `uq_mod_preg_orden` (`id_modulo`,`orden`),
  ADD KEY `idx_mod_preg_modulo` (`id_modulo`),
  ADD CONSTRAINT `fk_mod_preg_mod` FOREIGN KEY (`id_modulo`) REFERENCES `cursos_modulos` (`id_modulo`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `modulo_opciones`
  ADD PRIMARY KEY (`id_opcion`),
  ADD UNIQUE KEY `uq_mod_opc_clave` (`id_pregunta_modulo`,`clave`),
  ADD KEY `idx_mod_opc_preg` (`id_pregunta_modulo`),
  ADD CONSTRAINT `fk_mod_opc_preg` FOREIGN KEY (`id_pregunta_modulo`) REFERENCES `modulo_preguntas` (`id_pregunta_modulo`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `modulo_preguntas_respuesta`
  ADD PRIMARY KEY (`id_pregunta_modulo`),
  ADD UNIQUE KEY `uq_mod_resp_opc` (`id_opcion_correcta`),
  ADD CONSTRAINT `fk_mod_resp_preg` FOREIGN KEY (`id_pregunta_modulo`) REFERENCES `modulo_preguntas` (`id_pregunta_modulo`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_mod_resp_opc` FOREIGN KEY (`id_opcion_correcta`) REFERENCES `modulo_opciones` (`id_opcion`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `modulo_intentos`
  ADD PRIMARY KEY (`id_intento_modulo`),
  ADD KEY `idx_mod_intento_hist` (`id_modulo`,`cedula_asesor`,`fecha_intento`),
  ADD CONSTRAINT `fk_mod_intento_mod` FOREIGN KEY (`id_modulo`) REFERENCES `cursos_modulos` (`id_modulo`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_mod_intento_asesor` FOREIGN KEY (`cedula_asesor`) REFERENCES `usuarios` (`cedula`) ON UPDATE CASCADE;

ALTER TABLE `modulo_respuestas`
  ADD PRIMARY KEY (`id_intento_modulo`,`id_pregunta_modulo`),
  ADD KEY `idx_mod_resp_preg` (`id_pregunta_modulo`),
  ADD KEY `idx_mod_resp_opc` (`id_opcion_elegida`),
  ADD CONSTRAINT `fk_mod_resp_intento` FOREIGN KEY (`id_intento_modulo`) REFERENCES `modulo_intentos` (`id_intento_modulo`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_mod_resp_preg2` FOREIGN KEY (`id_pregunta_modulo`) REFERENCES `modulo_preguntas` (`id_pregunta_modulo`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_mod_resp_opc2` FOREIGN KEY (`id_opcion_elegida`) REFERENCES `modulo_opciones` (`id_opcion`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `modulo_completado`
  ADD PRIMARY KEY (`id_modulo`,`cedula_asesor`),
  ADD CONSTRAINT `fk_mod_compl_mod` FOREIGN KEY (`id_modulo`) REFERENCES `cursos_modulos` (`id_modulo`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_mod_compl_asesor` FOREIGN KEY (`cedula_asesor`) REFERENCES `usuarios` (`cedula`) ON UPDATE CASCADE;

ALTER TABLE `modulo_preguntas`
  MODIFY `id_pregunta_modulo` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `modulo_opciones`
  MODIFY `id_opcion` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `modulo_intentos`
  MODIFY `id_intento_modulo` int(11) NOT NULL AUTO_INCREMENT;

