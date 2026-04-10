-- =============================================================================
-- Migración: módulos del curso
--
-- Qué agrega:
--   - Tabla `cursos_modulos` para almacenar módulos (contenedores) creados por el coordinador.
--
-- En phpMyAdmin: seleccione la base `capacitacion1` y ejecute este archivo.
-- =============================================================================

CREATE TABLE IF NOT EXISTS `cursos_modulos` (
  `id_modulo` int(11) NOT NULL AUTO_INCREMENT,
  `id_curso` int(11) NOT NULL,
  `modulo` int(11) NOT NULL,
  `titulo` varchar(150) NOT NULL,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

ALTER TABLE `cursos_modulos`
  ADD PRIMARY KEY (`id_modulo`),
  ADD UNIQUE KEY `uq_curso_mod_num` (`id_curso`,`modulo`),
  ADD KEY `idx_curso_mod` (`id_curso`),
  ADD CONSTRAINT `fk_curso_modulos` FOREIGN KEY (`id_curso`) REFERENCES `cursos` (`id_cursos`) ON DELETE CASCADE ON UPDATE CASCADE;



