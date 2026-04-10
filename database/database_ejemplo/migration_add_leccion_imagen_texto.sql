-- =============================================================================
-- Migración: Texto asociado a imagen de lección
--
-- Agrega el campo `imagen_texto` para almacenar el texto que se mostrará al
-- girar (flip) la imagen en la vista del asesor.
--
-- En phpMyAdmin: seleccione la base `capacitacion1` y ejecute este archivo.
-- =============================================================================

ALTER TABLE `lecciones`
  ADD COLUMN `imagen_texto` text DEFAULT NULL AFTER `imagen_path`;

