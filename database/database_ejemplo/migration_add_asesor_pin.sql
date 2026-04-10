-- =============================================================================
-- Migración: verificación por PIN (asesores)
--
-- Qué agrega:
--   - Columnas en `usuarios` para soportar verificación de cuenta por PIN:
--     - pin_verificacion_hash (hash del PIN)
--     - pin_verificacion_expira_en (caducidad)
--     - pin_verificacion_intentos (contador de intentos)
--
-- En phpMyAdmin: seleccione la base `capacitacion1` y ejecute este archivo.
-- =============================================================================

ALTER TABLE `usuarios`
  ADD COLUMN `pin_verificacion_hash` varchar(255) NULL,
  ADD COLUMN `pin_verificacion_expira_en` datetime NULL,
  ADD COLUMN `pin_verificacion_intentos` int(11) NOT NULL DEFAULT 0;

