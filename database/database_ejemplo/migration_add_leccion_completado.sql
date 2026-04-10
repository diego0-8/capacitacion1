-- =============================================================================
-- Migración: Lecciones completadas por asesor
--
-- Agrega la tabla `leccion_completado` para marcar qué lecciones (cursos/clases)
-- ya completó cada asesor.
--
-- En phpMyAdmin: seleccione la base `capacitacion1` y ejecute este archivo.
-- =============================================================================

CREATE TABLE IF NOT EXISTS `leccion_completado` (
  `id_leccion` int(11) NOT NULL,
  `cedula_asesor` varchar(10) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

ALTER TABLE `leccion_completado`
  ADD PRIMARY KEY (`id_leccion`,`cedula_asesor`),
  ADD KEY `idx_lec_comp_asesor` (`cedula_asesor`),
  ADD CONSTRAINT `fk_lec_comp_leccion` FOREIGN KEY (`id_leccion`) REFERENCES `lecciones` (`id_leccion`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_lec_comp_asesor` FOREIGN KEY (`cedula_asesor`) REFERENCES `usuarios` (`cedula`) ON UPDATE CASCADE;

